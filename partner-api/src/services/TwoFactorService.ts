import { PrismaClient } from '@prisma/client';
import crypto from 'crypto';
import speakeasy from 'speakeasy';
import QRCode from 'qrcode';
import { ValidationError, UnauthorizedError } from '@/middleware/errorHandler';
import { logger } from '@/utils/logger';

export class TwoFactorService {
  constructor(private prisma: PrismaClient) {}

  /**
   * Génère un secret TOTP pour un utilisateur
   */
  async generateTotpSecret(userId: number): Promise<{
    secret: string;
    qrCodeUrl: string;
    backupCodes: string[];
  }> {
    // Récupération de l'utilisateur
    const user = await this.prisma.partnerUser.findUnique({
      where: { id: userId },
      include: {
        partner: {
          select: { NameBanque: true }
        }
      }
    });

    if (!user) {
      throw new ValidationError('User not found');
    }

    // Génération du secret TOTP
    const secret = speakeasy.generateSecret({
      name: `IOB Partner (${user.email})`,
      issuer: `IOB - ${user.partner.NameBanque}`,
      length: 32
    });

    // Génération des codes de récupération
    const backupCodes = this.generateBackupCodes();

    // Sauvegarde en base (temporaire, en attente de validation)
    await this.prisma.partnerUser.update({
      where: { id: userId },
      data: {
        totpSecret: secret.base32,
        totpEnabled: false, // Sera activé après validation
        backupCodes: JSON.stringify(backupCodes.map(code => ({ code, used: false })))
      }
    });

    // Génération du QR Code
    const qrCodeUrl = await QRCode.toDataURL(secret.otpauth_url!);

    logger.info(`TOTP secret generated for user ${userId}`);

    return {
      secret: secret.base32,
      qrCodeUrl,
      backupCodes
    };
  }

  /**
   * Valide et active le TOTP pour un utilisateur
   */
  async enableTotp(userId: number, token: string): Promise<void> {
    const user = await this.prisma.partnerUser.findUnique({
      where: { id: userId }
    });

    if (!user || !user.totpSecret) {
      throw new ValidationError('TOTP not configured for this user');
    }

    // Vérification du token TOTP
    const isValid = speakeasy.totp.verify({
      secret: user.totpSecret,
      encoding: 'base32',
      token,
      window: 2 // Permet une tolérance de ±2 intervalles (60s)
    });

    if (!isValid) {
      throw new UnauthorizedError('Invalid TOTP token');
    }

    // Activation du TOTP
    await this.prisma.partnerUser.update({
      where: { id: userId },
      data: {
        totpEnabled: true,
        totpVerifiedAt: new Date()
      }
    });

    logger.info(`TOTP enabled for user ${userId}`);
  }

  /**
   * Désactive le TOTP pour un utilisateur
   */
  async disableTotp(userId: number, token: string): Promise<void> {
    const user = await this.prisma.partnerUser.findUnique({
      where: { id: userId }
    });

    if (!user || !user.totpEnabled) {
      throw new ValidationError('TOTP not enabled for this user');
    }

    // Vérification du token TOTP ou code de récupération
    const isValidTotp = user.totpSecret && speakeasy.totp.verify({
      secret: user.totpSecret,
      encoding: 'base32',
      token,
      window: 2
    });

    const isValidBackup = await this.validateBackupCode(userId, token);

    if (!isValidTotp && !isValidBackup) {
      throw new UnauthorizedError('Invalid TOTP token or backup code');
    }

    // Désactivation du TOTP
    await this.prisma.partnerUser.update({
      where: { id: userId },
      data: {
        totpSecret: null,
        totpEnabled: false,
        totpVerifiedAt: null,
        backupCodes: null
      }
    });

    logger.info(`TOTP disabled for user ${userId}`);
  }

  /**
   * Vérifie un token TOTP
   */
  async verifyTotp(userId: number, token: string): Promise<boolean> {
    const user = await this.prisma.partnerUser.findUnique({
      where: { id: userId }
    });

    if (!user || !user.totpEnabled || !user.totpSecret) {
      return false;
    }

    // Vérification du token TOTP
    const isValidTotp = speakeasy.totp.verify({
      secret: user.totpSecret,
      encoding: 'base32',
      token,
      window: 2
    });

    if (isValidTotp) {
      return true;
    }

    // Vérification des codes de récupération
    return await this.validateBackupCode(userId, token);
  }

  /**
   * Génère de nouveaux codes de récupération
   */
  async regenerateBackupCodes(userId: number): Promise<string[]> {
    const user = await this.prisma.partnerUser.findUnique({
      where: { id: userId }
    });

    if (!user || !user.totpEnabled) {
      throw new ValidationError('TOTP not enabled for this user');
    }

    const backupCodes = this.generateBackupCodes();

    await this.prisma.partnerUser.update({
      where: { id: userId },
      data: {
        backupCodes: JSON.stringify(backupCodes.map(code => ({ code, used: false })))
      }
    });

    logger.info(`Backup codes regenerated for user ${userId}`);

    return backupCodes;
  }

  /**
   * Récupère le statut 2FA d'un utilisateur
   */
  async getTotpStatus(userId: number): Promise<{
    enabled: boolean;
    verifiedAt: Date | null;
    backupCodesCount: number;
  }> {
    const user = await this.prisma.partnerUser.findUnique({
      where: { id: userId }
    });

    if (!user) {
      throw new ValidationError('User not found');
    }

    let backupCodesCount = 0;
    if (user.backupCodes) {
      try {
        const codes = JSON.parse(user.backupCodes);
        backupCodesCount = codes.filter((c: any) => !c.used).length;
      } catch (e) {
        // Ignore parsing errors
      }
    }

    return {
      enabled: user.totpEnabled || false,
      verifiedAt: user.totpVerifiedAt,
      backupCodesCount
    };
  }

  // ===== MÉTHODES PRIVÉES =====

  private generateBackupCodes(): string[] {
    const codes: string[] = [];
    for (let i = 0; i < 10; i++) {
      // Génération de codes à 8 chiffres
      const code = crypto.randomInt(10000000, 99999999).toString();
      codes.push(code);
    }
    return codes;
  }

  private async validateBackupCode(userId: number, code: string): Promise<boolean> {
    const user = await this.prisma.partnerUser.findUnique({
      where: { id: userId }
    });

    if (!user || !user.backupCodes) {
      return false;
    }

    try {
      const backupCodes = JSON.parse(user.backupCodes);
      const codeIndex = backupCodes.findIndex((c: any) => c.code === code && !c.used);

      if (codeIndex === -1) {
        return false;
      }

      // Marquer le code comme utilisé
      backupCodes[codeIndex].used = true;

      await this.prisma.partnerUser.update({
        where: { id: userId },
        data: {
          backupCodes: JSON.stringify(backupCodes)
        }
      });

      logger.info(`Backup code used for user ${userId}`);
      return true;

    } catch (e) {
      logger.error('Error parsing backup codes', { userId, error: e });
      return false;
    }
  }
}

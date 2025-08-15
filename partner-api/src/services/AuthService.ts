import { PrismaClient } from '@prisma/client';
import bcrypt from 'bcrypt';
import jwt from 'jsonwebtoken';
import crypto from 'crypto';
import { ValidationError, UnauthorizedError } from '@/middleware/errorHandler';
import { logger } from '@/utils/logger';

export class AuthService {
  constructor(private prisma: PrismaClient) {}

  /**
   * Authentification d'un utilisateur partenaire
   */
  async login(loginData: {
    email: string;
    password: string;
    partner_code: string;
  }): Promise<{
    access_token: string;
    refresh_token: string;
    expires_in: number;
    user: any;
  }> {
    const { email, password, partner_code } = loginData;

    // Validation des données d'entrée
    if (!email || !password || !partner_code) {
      throw new ValidationError('Email, password and partner_code are required');
    }

    // Recherche du partenaire par code
    const partner = await this.prisma.partner.findFirst({
      where: {
        NameBanque: {
          contains: partner_code
        }
      }
    });

    if (!partner) {
      logger.warn(`Login attempt with invalid partner code: ${partner_code}`);
      throw new UnauthorizedError('Invalid partner code');
    }

    // Recherche de l'utilisateur
    const user = await this.prisma.partnerUser.findFirst({
      where: {
        email: email,
        RefBanque: partner.RefBanque,
        isActive: true
      },
      include: {
        partner: {
          select: {
            RefBanque: true,
            NameBanque: true,
            RefPays: true
          }
        }
      }
    });

    if (!user) {
      logger.warn(`Login attempt with invalid email: ${email} for partner: ${partner_code}`);
      throw new UnauthorizedError('Invalid credentials');
    }

    // Vérification du mot de passe
    const isPasswordValid = await bcrypt.compare(password, user.password);
    if (!isPasswordValid) {
      logger.warn(`Login attempt with invalid password for user: ${email}`);
      throw new UnauthorizedError('Invalid credentials');
    }

    // Payload pour les tokens JWT
    const tokenPayload = {
      userId: user.id,
      partnerId: partner.RefBanque,
      email: user.email,
      role: user.role
    };

    const accessToken = this.generateAccessToken(tokenPayload);
    const refreshToken = this.generateRefreshToken(tokenPayload);

    // Mise à jour de la dernière connexion
    await this.prisma.partnerUser.update({
      where: { id: user.id },
      data: { lastLoginAt: new Date() }
    });

    logger.info(`Successful login for user ${user.id} from partner ${partner.RefBanque}`);

    return {
      access_token: accessToken,
      refresh_token: refreshToken,
      expires_in: 24 * 60 * 60, // 24 heures
      user: {
        id: user.id,
        email: user.email,
        firstName: user.firstName,
        lastName: user.lastName,
        role: user.role,
        partner: {
          id: user.partner.RefBanque,
          name: user.partner.NameBanque,
          country: user.partner.RefPays
        }
      }
    };
  }

  /**
   * Renouvellement d'un token d'accès
   */
  async refreshToken(refreshToken: string): Promise<{
    access_token: string;
    expires_in: number;
  }> {
    try {
      const payload = jwt.verify(refreshToken, process.env.PARTNER_JWT_SECRET!) as any;
      
      // Vérification que l'utilisateur existe toujours
      const user = await this.prisma.partnerUser.findUnique({
        where: { id: payload.userId }
      });

      if (!user || !user.isActive) {
        throw new UnauthorizedError('User is inactive');
      }

      // Génération d'un nouveau token d'accès
      const newTokenPayload = {
        userId: payload.userId,
        partnerId: payload.partnerId,
        email: payload.email,
        role: payload.role
      };

      const accessToken = this.generateAccessToken(newTokenPayload);

      return {
        access_token: accessToken,
        expires_in: 24 * 60 * 60
      };

    } catch (error) {
      logger.error('Error refreshing token', { error });
      throw new UnauthorizedError('Invalid refresh token');
    }
  }

  // ===== MÉTHODES PRIVÉES =====

  private generateAccessToken(payload: any): string {
    return jwt.sign(
      payload, 
      process.env.PARTNER_JWT_SECRET!,
      { expiresIn: process.env.JWT_EXPIRES_IN || '24h' }
    );
  }

  private generateRefreshToken(payload: any): string {
    return jwt.sign(
      payload,
      process.env.PARTNER_JWT_SECRET!,
      { expiresIn: process.env.REFRESH_TOKEN_EXPIRES_IN || '7d' }
    );
  }
}
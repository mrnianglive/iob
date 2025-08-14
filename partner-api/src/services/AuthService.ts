import { PrismaClient } from '@prisma/client';
import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken';
import crypto from 'crypto';
import { PartnerLoginRequest, PartnerLoginResponse } from '@/types/partner';
import { UnauthorizedError, ValidationError } from '@/middleware/errorHandler';
import { logger } from '@/utils/logger';

export class AuthService {
  constructor(private prisma: PrismaClient) {}

  /**
   * Authentification d'un utilisateur partenaire
   */
  async loginPartner(loginData: PartnerLoginRequest): Promise<PartnerLoginResponse> {
    const { email, password, partner_code } = loginData;

    // Validation des données d'entrée
    if (!email || !password || !partner_code) {
      throw new ValidationError('Email, password and partner_code are required');
    }

    // Recherche du partenaire par code
    const partner = await this.prisma.partner.findFirst({
      where: {
        // Utilisation du nom de la banque comme partner_code pour simplifier
        NameBanque: {
          contains: partner_code,
          mode: 'insensitive'
        },
        IsActive: true
      }
    });

    if (!partner) {
      logger.warn(`Login attempt with invalid partner code: ${partner_code}`);
      throw new UnauthorizedError('Invalid partner code');
    }

    // Recherche de l'utilisateur
    const user = await this.prisma.partnerUser.findFirst({
      where: {
        Email: email,
        RefBanque: partner.RefBanque,
        IsActive: true
      },
      include: {
        partner: {
          select: {
            RefBanque: true,
            NameBanque: true,
            RefPays: true,
            LogoBanque: true,
            IsActive: true,
            AnalyticsEnabled: true,
            ExportEnabled: true,
            WebhooksEnabled: true,
            ApiAccessEnabled: true,
            country: {
              select: {
                NamePays: true,
                CodePays: true
              }
            }
          }
        }
      }
    });

    if (!user) {
      logger.warn(`Login attempt with invalid email: ${email} for partner: ${partner_code}`);
      throw new UnauthorizedError('Invalid credentials');
    }

    // Vérification du mot de passe
    const isPasswordValid = await bcrypt.compare(password, user.Password);
    if (!isPasswordValid) {
      logger.warn(`Invalid password attempt for user: ${email}`);
      throw new UnauthorizedError('Invalid credentials');
    }

    // Construction des permissions
    const permissions = this.buildUserPermissions(user);

    // Génération des tokens
    const tokenPayload = {
      userId: user.RefUser,
      partnerId: user.RefBanque,
      email: user.Email,
      role: user.Role,
      permissions
    };

    const accessToken = this.generateAccessToken(tokenPayload);
    const refreshToken = this.generateRefreshToken(tokenPayload);

    // Mise à jour de la dernière connexion
    await this.prisma.partnerUser.update({
      where: { RefUser: user.RefUser },
      data: { LastLogin: new Date() }
    });

    // Log de la connexion réussie
    logger.info(`Successful login for user ${user.RefUser} from partner ${user.RefBanque}`);

    return {
      access_token: accessToken,
      refresh_token: refreshToken,
      partner: {
        id: user.partner.RefBanque,
        name: user.partner.NameBanque,
        country: user.partner.country.NamePays,
        logo: user.partner.LogoBanque,
        permissions
      },
      expires_in: 24 * 60 * 60 // 24 heures en secondes
    };
  }

  /**
   * Rafraîchissement d'un token d'accès
   */
  async refreshToken(refreshToken: string): Promise<{ access_token: string; expires_in: number }> {
    try {
      const payload = jwt.verify(refreshToken, process.env.PARTNER_JWT_SECRET!) as any;
      
      // Vérification que l'utilisateur existe toujours
      const user = await this.prisma.partnerUser.findUnique({
        where: { RefUser: payload.userId },
        include: {
          partner: {
            select: { IsActive: true }
          }
        }
      });

      if (!user || !user.IsActive || !user.partner.IsActive) {
        throw new UnauthorizedError('User or partner inactive');
      }

      // Génération d'un nouveau token d'accès
      const newTokenPayload = {
        userId: payload.userId,
        partnerId: payload.partnerId,
        email: payload.email,
        role: payload.role,
        permissions: payload.permissions
      };

      const accessToken = this.generateAccessToken(newTokenPayload);

      return {
        access_token: accessToken,
        expires_in: 24 * 60 * 60
      };

    } catch (error) {
      logger.warn('Invalid refresh token attempt');
      throw new UnauthorizedError('Invalid refresh token');
    }
  }

  /**
   * Génération d'une API Key pour un partenaire
   */
  async generateApiKey(partnerId: number): Promise<{ api_key: string; api_secret: string }> {
    const apiKey = this.generateRandomKey(32);
    const apiSecret = this.generateRandomKey(64);

    await this.prisma.partner.update({
      where: { RefBanque: partnerId },
      data: {
        ApiKey: apiKey,
        ApiSecret: apiSecret
      }
    });

    logger.info(`New API key generated for partner ${partnerId}`);

    return { api_key: apiKey, api_secret: apiSecret };
  }

  /**
   * Validation d'une API Key
   */
  async validateApiKey(apiKey: string): Promise<{ partnerId: number; isValid: boolean }> {
    const partner = await this.prisma.partner.findUnique({
      where: { 
        ApiKey: apiKey,
        IsActive: true,
        ApiAccessEnabled: true
      }
    });

    return {
      partnerId: partner?.RefBanque || 0,
      isValid: !!partner
    };
  }

  /**
   * Révocation d'une API Key
   */
  async revokeApiKey(partnerId: number): Promise<void> {
    await this.prisma.partner.update({
      where: { RefBanque: partnerId },
      data: {
        ApiKey: null,
        ApiSecret: null
      }
    });

    logger.info(`API key revoked for partner ${partnerId}`);
  }

  /**
   * Création d'un utilisateur partenaire
   */
  async createPartnerUser(userData: {
    email: string;
    password: string;
    name: string;
    partnerId: number;
    role: string;
    permissions: {
      viewOperations?: boolean;
      viewAnalytics?: boolean;
      viewAgencies?: boolean;
      exportData?: boolean;
      manageUsers?: boolean;
      apiAccess?: boolean;
    };
  }) {
    const { email, password, name, partnerId, role, permissions } = userData;

    // Vérification que le partenaire existe
    const partner = await this.prisma.partner.findUnique({
      where: { RefBanque: partnerId, IsActive: true }
    });

    if (!partner) {
      throw new ValidationError('Partner not found or inactive');
    }

    // Vérification que l'email n'existe pas déjà
    const existingUser = await this.prisma.partnerUser.findUnique({
      where: { Email: email }
    });

    if (existingUser) {
      throw new ValidationError('Email already exists');
    }

    // Hashage du mot de passe
    const hashedPassword = await bcrypt.hash(password, 12);

    // Création de l'utilisateur
    const user = await this.prisma.partnerUser.create({
      data: {
        Login: email, // Utilisation de l'email comme login
        Password: hashedPassword,
        Name: name,
        Email: email,
        RefBanque: partnerId,
        Role: role,
        ViewOperations: permissions.viewOperations ?? true,
        ViewAnalytics: permissions.viewAnalytics ?? true,
        ViewAgencies: permissions.viewAgencies ?? true,
        ExportData: permissions.exportData ?? false,
        ManageUsers: permissions.manageUsers ?? false,
        ApiAccess: permissions.apiAccess ?? false,
      }
    });

    logger.info(`New partner user created: ${user.RefUser} for partner ${partnerId}`);

    return {
      id: user.RefUser,
      email: user.Email,
      name: user.Name,
      role: user.Role,
      partnerId: user.RefBanque
    };
  }

  // ===== MÉTHODES PRIVÉES =====

  private generateAccessToken(payload: any): string {
    return jwt.sign(payload, process.env.PARTNER_JWT_SECRET!, {
      expiresIn: process.env.JWT_EXPIRES_IN || '24h',
      issuer: 'iob-partner-api',
      audience: 'partner'
    });
  }

  private generateRefreshToken(payload: any): string {
    return jwt.sign(payload, process.env.PARTNER_JWT_SECRET!, {
      expiresIn: process.env.REFRESH_TOKEN_EXPIRES_IN || '7d',
      issuer: 'iob-partner-api',
      audience: 'partner-refresh'
    });
  }

  private generateRandomKey(length: number): string {
    return crypto.randomBytes(length).toString('hex');
  }

  private buildUserPermissions(user: any): string[] {
    const permissions: string[] = [];

    if (user.ViewOperations) permissions.push('view_operations');
    if (user.ViewAnalytics) permissions.push('view_analytics');
    if (user.ViewAgencies) permissions.push('view_agencies');
    if (user.ExportData) permissions.push('export_data');
    if (user.ManageUsers) permissions.push('manage_users');
    if (user.ApiAccess) permissions.push('api_access');

    return permissions;
  }
}
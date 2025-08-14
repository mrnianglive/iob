import { Request, Response, NextFunction } from 'express';
import jwt from 'jsonwebtoken';
import crypto from 'crypto';
import { PrismaClient } from '@prisma/client';
import { logger } from '@/utils/logger';
import { PARTNER_PERMISSIONS } from '@/types/partner';

const prisma = new PrismaClient();

export class PartnerAuthError extends Error {
  constructor(message: string, public statusCode: number = 401) {
    super(message);
    this.name = 'PartnerAuthError';
  }
}

export class PartnerAuthMiddleware {
  /**
   * Middleware pour valider le token JWT partenaire
   */
  static async validatePartnerToken(req: Request, res: Response, next: NextFunction) {
    try {
      const authHeader = req.headers.authorization;
      
      if (!authHeader || !authHeader.startsWith('Bearer ')) {
        throw new PartnerAuthError('Authorization token required');
      }

      const token = authHeader.split(' ')[1];
      
      if (!token) {
        throw new PartnerAuthError('Authorization token required');
      }

      const payload = jwt.verify(token, process.env.PARTNER_JWT_SECRET!) as any;
      
      // Validation que l'utilisateur existe et appartient à un partenaire
      const user = await prisma.partnerUser.findUnique({
        where: { RefUser: payload.userId },
        include: { 
          partner: {
            select: {
              RefBanque: true,
              NameBanque: true,
              IsActive: true,
              AnalyticsEnabled: true,
              ExportEnabled: true,
              WebhooksEnabled: true,
              ApiAccessEnabled: true,
            }
          }
        }
      });

      if (!user || !user.IsActive) {
        throw new PartnerAuthError('User not found or inactive');
      }

      if (!user.partner || !user.partner.IsActive) {
        throw new PartnerAuthError('Partner not found or inactive');
      }

      // Construction des permissions basées sur le rôle et les features du partenaire
      const permissions = PartnerAuthMiddleware.buildUserPermissions(user, user.partner);

      req.partnerContext = {
        partnerId: user.RefBanque,
        userId: user.RefUser,
        permissions,
      };

      // Log de l'accès
      logger.info(`Partner access: User ${user.RefUser} from partner ${user.RefBanque}`);

      next();
    } catch (error) {
      if (error instanceof jwt.JsonWebTokenError) {
        return res.status(401).json({
          success: false,
          error: 'Invalid token',
        });
      }

      if (error instanceof PartnerAuthError) {
        return res.status(error.statusCode).json({
          success: false,
          error: error.message,
        });
      }

      logger.error('Partner auth error:', error);
      return res.status(500).json({
        success: false,
        error: 'Authentication error',
      });
    }
  }

  /**
   * Middleware pour valider l'API Key partenaire
   */
  static async validateApiKey(req: Request, res: Response, next: NextFunction) {
    try {
      const apiKey = req.headers['x-api-key'] as string;
      const signature = req.headers['x-signature'] as string;
      const timestamp = req.headers['x-timestamp'] as string;

      if (!apiKey) {
        throw new PartnerAuthError('API Key required');
      }

      const partner = await prisma.partner.findUnique({
        where: { ApiKey: apiKey },
        select: {
          RefBanque: true,
          NameBanque: true,
          ApiSecret: true,
          IsActive: true,
          ApiAccessEnabled: true,
          RateLimit: true,
        }
      });

      if (!partner || !partner.IsActive || !partner.ApiAccessEnabled) {
        throw new PartnerAuthError('Invalid API key or partner inactive');
      }

      // Validation de la signature HMAC si fournie
      if (signature && timestamp) {
        const expectedSignature = PartnerAuthMiddleware.generateSignature(
          req.body,
          partner.ApiSecret,
          parseInt(timestamp)
        );

        if (signature !== expectedSignature) {
          throw new PartnerAuthError('Invalid signature');
        }

        // Vérification du timestamp (max 5 minutes)
        const now = Date.now();
        const requestTime = parseInt(timestamp);
        const maxAge = 5 * 60 * 1000; // 5 minutes

        if (Math.abs(now - requestTime) > maxAge) {
          throw new PartnerAuthError('Request timestamp too old');
        }
      }

      req.partnerContext = {
        partnerId: partner.RefBanque,
        permissions: [PARTNER_PERMISSIONS.API_ACCESS],
        apiKey,
      };

      // Log de l'accès API
      await PartnerAuthMiddleware.logApiAccess(req, partner.RefBanque);

      logger.info(`API access: Partner ${partner.RefBanque} via API key`);

      next();
    } catch (error) {
      if (error instanceof PartnerAuthError) {
        return res.status(error.statusCode).json({
          success: false,
          error: error.message,
        });
      }

      logger.error('API key validation error:', error);
      return res.status(500).json({
        success: false,
        error: 'API authentication error',
      });
    }
  }

  /**
   * Middleware hybride : JWT ou API Key
   */
  static async hybridAuth(req: Request, res: Response, next: NextFunction) {
    const authHeader = req.headers.authorization;
    const apiKey = req.headers['x-api-key'];

    if (authHeader && authHeader.startsWith('Bearer ')) {
      return PartnerAuthMiddleware.validatePartnerToken(req, res, next);
    } else if (apiKey) {
      return PartnerAuthMiddleware.validateApiKey(req, res, next);
    } else {
      return res.status(401).json({
        success: false,
        error: 'Authentication required (JWT token or API key)',
      });
    }
  }

  /**
   * Middleware pour vérifier les permissions
   */
  static requirePermission(permission: string) {
    return (req: Request, res: Response, next: NextFunction) => {
      const userPermissions = req.partnerContext?.permissions || [];

      if (!userPermissions.includes(permission)) {
        return res.status(403).json({
          success: false,
          error: `Permission ${permission} required`,
        });
      }

      next();
    };
  }

  /**
   * Construction des permissions utilisateur
   */
  private static buildUserPermissions(user: any, partner: any): string[] {
    const permissions: string[] = [];

    // Permissions basées sur les features du partenaire
    if (partner.AnalyticsEnabled && user.ViewAnalytics) {
      permissions.push(PARTNER_PERMISSIONS.VIEW_ANALYTICS);
    }

    if (partner.ExportEnabled && user.ExportData) {
      permissions.push(PARTNER_PERMISSIONS.EXPORT_DATA);
    }

    if (partner.ApiAccessEnabled && user.ApiAccess) {
      permissions.push(PARTNER_PERMISSIONS.API_ACCESS);
    }

    // Permissions basées sur le rôle utilisateur
    if (user.ViewOperations) {
      permissions.push(PARTNER_PERMISSIONS.VIEW_OPERATIONS);
    }

    if (user.ViewAgencies) {
      permissions.push(PARTNER_PERMISSIONS.VIEW_AGENCIES);
    }

    if (user.ManageUsers && user.Role === 'partner_admin') {
      permissions.push(PARTNER_PERMISSIONS.MANAGE_USERS);
    }

    return permissions;
  }

  /**
   * Génération de signature HMAC
   */
  private static generateSignature(data: any, secret: string, timestamp: number): string {
    const payload = JSON.stringify(data) + timestamp;
    return crypto.createHmac('sha256', secret).update(payload).digest('hex');
  }

  /**
   * Log des accès API
   */
  private static async logApiAccess(req: Request, partnerId: number) {
    try {
      const startTime = Date.now();
      
      // Hook pour mesurer le temps de réponse
      const originalSend = req.res!.send;
      req.res!.send = function(data) {
        const responseTime = Date.now() - startTime;
        
        // Log asynchrone pour ne pas bloquer la réponse
        setImmediate(async () => {
          try {
            await prisma.partnerApiLog.create({
              data: {
                RefBanque: partnerId,
                Endpoint: req.path,
                Method: req.method,
                StatusCode: req.res!.statusCode,
                ResponseTime: responseTime,
                UserAgent: req.headers['user-agent'] || null,
                IpAddress: req.ip || req.connection.remoteAddress || 'unknown',
                ApiKey: req.headers['x-api-key'] as string || null,
              }
            });
          } catch (error) {
            logger.error('Failed to log API access:', error);
          }
        });

        return originalSend.call(this, data);
      };
    } catch (error) {
      logger.error('Error setting up API logging:', error);
    }
  }
}

// Export des middlewares principaux
export const partnerAuthMiddleware = PartnerAuthMiddleware.hybridAuth;
export const requirePermission = PartnerAuthMiddleware.requirePermission;
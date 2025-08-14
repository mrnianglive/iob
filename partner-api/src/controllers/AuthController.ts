import { Request, Response } from 'express';
import { PrismaClient } from '@prisma/client';
import Joi from 'joi';
import { AuthService } from '@/services/AuthService';
import { asyncHandler, ValidationError } from '@/middleware/errorHandler';
import { logger } from '@/utils/logger';

const prisma = new PrismaClient();
const authService = new AuthService(prisma);

// Schémas de validation
const loginSchema = Joi.object({
  email: Joi.string().email().required(),
  password: Joi.string().min(6).required(),
  partner_code: Joi.string().required()
});

const refreshTokenSchema = Joi.object({
  refresh_token: Joi.string().required()
});

const createUserSchema = Joi.object({
  email: Joi.string().email().required(),
  password: Joi.string().min(8).required(),
  name: Joi.string().min(2).required(),
  role: Joi.string().valid('partner_admin', 'partner_viewer', 'partner_operator').required(),
  permissions: Joi.object({
    viewOperations: Joi.boolean().default(true),
    viewAnalytics: Joi.boolean().default(true),
    viewAgencies: Joi.boolean().default(true),
    exportData: Joi.boolean().default(false),
    manageUsers: Joi.boolean().default(false),
    apiAccess: Joi.boolean().default(false)
  }).default({})
});

export class AuthController {
  /**
   * @swagger
   * /auth/login:
   *   post:
   *     summary: Authentification partenaire
   *     tags: [Authentication]
   *     requestBody:
   *       required: true
   *       content:
   *         application/json:
   *           schema:
   *             type: object
   *             required:
   *               - email
   *               - password
   *               - partner_code
   *             properties:
   *               email:
   *                 type: string
   *                 format: email
   *                 example: admin@partner.com
   *               password:
   *                 type: string
   *                 example: securepassword123
   *               partner_code:
   *                 type: string
   *                 example: PARTNER_BANK_001
   *     responses:
   *       200:
   *         description: Authentification réussie
   *         content:
   *           application/json:
   *             schema:
   *               type: object
   *               properties:
   *                 success:
   *                   type: boolean
   *                 data:
   *                   type: object
   *                   properties:
   *                     access_token:
   *                       type: string
   *                     refresh_token:
   *                       type: string
   *                     partner:
   *                       type: object
   *                       properties:
   *                         id:
   *                           type: number
   *                         name:
   *                           type: string
   *                         country:
   *                           type: string
   *                         permissions:
   *                           type: array
   *                           items:
   *                             type: string
   *                     expires_in:
   *                       type: number
   *       401:
   *         description: Identifiants invalides
   */
  static login = asyncHandler(async (req: Request, res: Response) => {
    // Validation des données d'entrée
    const { error, value } = loginSchema.validate(req.body);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const loginResult = await authService.loginPartner(value);

    // Log de la connexion (sans informations sensibles)
    logger.info('Partner login successful', {
      partnerId: loginResult.partner.id,
      partnerName: loginResult.partner.name,
      ip: req.ip,
      userAgent: req.get('User-Agent')
    });

    res.status(200).json({
      success: true,
      data: loginResult
    });
  });

  /**
   * @swagger
   * /auth/refresh:
   *   post:
   *     summary: Rafraîchir le token d'accès
   *     tags: [Authentication]
   *     requestBody:
   *       required: true
   *       content:
   *         application/json:
   *           schema:
   *             type: object
   *             required:
   *               - refresh_token
   *             properties:
   *               refresh_token:
   *                 type: string
   *     responses:
   *       200:
   *         description: Token rafraîchi avec succès
   *       401:
   *         description: Token de rafraîchissement invalide
   */
  static refreshToken = asyncHandler(async (req: Request, res: Response) => {
    const { error, value } = refreshTokenSchema.validate(req.body);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const result = await authService.refreshToken(value.refresh_token);

    res.status(200).json({
      success: true,
      data: result
    });
  });

  /**
   * @swagger
   * /auth/me:
   *   get:
   *     summary: Informations de l'utilisateur connecté
   *     tags: [Authentication]
   *     security:
   *       - BearerAuth: []
   *     responses:
   *       200:
   *         description: Informations utilisateur récupérées
   */
  static getProfile = asyncHandler(async (req: Request, res: Response) => {
    const userId = req.partnerContext?.userId;
    
    if (!userId) {
      throw new ValidationError('User context not found');
    }

    const user = await prisma.partnerUser.findUnique({
      where: { RefUser: userId },
      select: {
        RefUser: true,
        Name: true,
        Email: true,
        Role: true,
        RefBanque: true,
        LastLogin: true,
        ViewOperations: true,
        ViewAnalytics: true,
        ViewAgencies: true,
        ExportData: true,
        ManageUsers: true,
        ApiAccess: true,
        partner: {
          select: {
            NameBanque: true,
            LogoBanque: true,
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
      throw new ValidationError('User not found');
    }

    res.status(200).json({
      success: true,
      data: {
        id: user.RefUser,
        name: user.Name,
        email: user.Email,
        role: user.Role,
        last_login: user.LastLogin,
        permissions: req.partnerContext?.permissions || [],
        partner: {
          id: user.RefBanque,
          name: user.partner.NameBanque,
          logo: user.partner.LogoBanque,
          country: user.partner.country.NamePays
        }
      }
    });
  });

  /**
   * @swagger
   * /auth/api-key:
   *   post:
   *     summary: Générer une nouvelle API Key
   *     tags: [Authentication]
   *     security:
   *       - BearerAuth: []
   *     responses:
   *       200:
   *         description: API Key générée avec succès
   *       403:
   *         description: Permission insuffisante
   */
  static generateApiKey = asyncHandler(async (req: Request, res: Response) => {
    const partnerId = req.partnerContext?.partnerId;
    const permissions = req.partnerContext?.permissions || [];

    if (!partnerId) {
      throw new ValidationError('Partner context not found');
    }

    if (!permissions.includes('api_access')) {
      return res.status(403).json({
        success: false,
        error: 'API access permission required'
      });
    }

    const result = await authService.generateApiKey(partnerId);

    logger.info('API key generated', {
      partnerId,
      userId: req.partnerContext?.userId
    });

    res.status(200).json({
      success: true,
      data: result,
      message: 'API key generated successfully. Store the secret securely, it will not be shown again.'
    });
  });

  /**
   * @swagger
   * /auth/api-key:
   *   delete:
   *     summary: Révoquer l'API Key
   *     tags: [Authentication]
   *     security:
   *       - BearerAuth: []
   *     responses:
   *       200:
   *         description: API Key révoquée avec succès
   */
  static revokeApiKey = asyncHandler(async (req: Request, res: Response) => {
    const partnerId = req.partnerContext?.partnerId;
    const permissions = req.partnerContext?.permissions || [];

    if (!partnerId) {
      throw new ValidationError('Partner context not found');
    }

    if (!permissions.includes('api_access')) {
      return res.status(403).json({
        success: false,
        error: 'API access permission required'
      });
    }

    await authService.revokeApiKey(partnerId);

    logger.info('API key revoked', {
      partnerId,
      userId: req.partnerContext?.userId
    });

    res.status(200).json({
      success: true,
      message: 'API key revoked successfully'
    });
  });

  /**
   * @swagger
   * /auth/users:
   *   post:
   *     summary: Créer un utilisateur partenaire
   *     tags: [Authentication]
   *     security:
   *       - BearerAuth: []
   *     requestBody:
   *       required: true
   *       content:
   *         application/json:
   *           schema:
   *             type: object
   *             required:
   *               - email
   *               - password
   *               - name
   *               - role
   *             properties:
   *               email:
   *                 type: string
   *                 format: email
   *               password:
   *                 type: string
   *                 minLength: 8
   *               name:
   *                 type: string
   *               role:
   *                 type: string
   *                 enum: [partner_admin, partner_viewer, partner_operator]
   *               permissions:
   *                 type: object
   *                 properties:
   *                   viewOperations:
   *                     type: boolean
   *                   viewAnalytics:
   *                     type: boolean
   *                   viewAgencies:
   *                     type: boolean
   *                   exportData:
   *                     type: boolean
   *                   manageUsers:
   *                     type: boolean
   *                   apiAccess:
   *                     type: boolean
   *     responses:
   *       201:
   *         description: Utilisateur créé avec succès
   */
  static createUser = asyncHandler(async (req: Request, res: Response) => {
    const partnerId = req.partnerContext?.partnerId;
    const permissions = req.partnerContext?.permissions || [];

    if (!partnerId) {
      throw new ValidationError('Partner context not found');
    }

    if (!permissions.includes('manage_users')) {
      return res.status(403).json({
        success: false,
        error: 'User management permission required'
      });
    }

    const { error, value } = createUserSchema.validate(req.body);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const user = await authService.createPartnerUser({
      ...value,
      partnerId
    });

    logger.info('Partner user created', {
      createdUserId: user.id,
      createdByUserId: req.partnerContext?.userId,
      partnerId
    });

    res.status(201).json({
      success: true,
      data: user,
      message: 'User created successfully'
    });
  });

  /**
   * @swagger
   * /auth/logout:
   *   post:
   *     summary: Déconnexion (invalidation côté client)
   *     tags: [Authentication]
   *     security:
   *       - BearerAuth: []
   *     responses:
   *       200:
   *         description: Déconnexion réussie
   */
  static logout = asyncHandler(async (req: Request, res: Response) => {
    // Note: Avec JWT, la déconnexion est principalement côté client
    // Dans une implémentation plus avancée, on pourrait maintenir une blacklist des tokens

    logger.info('User logout', {
      userId: req.partnerContext?.userId,
      partnerId: req.partnerContext?.partnerId
    });

    res.status(200).json({
      success: true,
      message: 'Logged out successfully'
    });
  });
}
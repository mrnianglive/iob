import { Request, Response } from 'express';
import { PrismaClient } from '@prisma/client';
import Joi from 'joi';
import { AuthService } from '@/services/AuthService';
import { asyncHandler, ValidationError } from '@/middleware/errorHandler';
import { logger } from '@/utils/logger';

const prisma = new PrismaClient();
const authService = new AuthService(prisma);

// Joi validation schemas
const loginSchema = Joi.object({
  email: Joi.string().email().required(),
  password: Joi.string().required(),
  partner_code: Joi.string().required()
});

const refreshTokenSchema = Joi.object({
  refresh_token: Joi.string().required()
});

const createUserSchema = Joi.object({
  login: Joi.string().required(),
  password: Joi.string().min(8).required(),
  name: Joi.string().required(),
  email: Joi.string().email().required(),
  role: Joi.string().valid('partner_admin', 'partner_viewer', 'partner_operator').required(),
  api_access: Joi.boolean().default(false),
  view_operations: Joi.boolean().default(true),
  view_analytics: Joi.boolean().default(true),
  view_agencies: Joi.boolean().default(true),
  export_data: Joi.boolean().default(false),
  manage_users: Joi.boolean().default(false)
});

export class AuthController {
  /**
   * @swagger
   * /auth/login:
   *   post:
   *     tags: [Authentication]
   *     summary: Authentification partenaire
   *     description: |
   *       Authentifie un utilisateur partenaire et retourne les tokens JWT.
   *       
   *       **Note importante :** Le `partner_code` correspond au nom de la banque (NameBanque) dans la base de données.
   *       
   *       **Exemple de flux :**
   *       1. L'utilisateur saisit ses identifiants + code partenaire
   *       2. Le système valide les informations
   *       3. Génération des tokens JWT (access + refresh)
   *       4. Retour des informations partenaire et permissions
   *     requestBody:
   *       required: true
   *       content:
   *         application/json:
   *           schema:
   *             $ref: '#/components/schemas/PartnerLoginRequest'
   *           examples:
   *             exemple_standard:
   *               summary: Connexion standard
   *               value:
   *                 email: admin@partner.com
   *                 password: SecurePassword123!
   *                 partner_code: Banque Partenaire XYZ
   *             exemple_viewer:
   *               summary: Utilisateur en lecture seule
   *               value:
   *                 email: viewer@partner.com
   *                 password: ViewerPass456!
   *                 partner_code: Banque Partenaire XYZ
   *     responses:
   *       '200':
   *         description: Authentification réussie
   *         content:
   *           application/json:
   *             schema:
   *               $ref: '#/components/schemas/PartnerLoginResponse'
   *             examples:
   *               success:
   *                 summary: Connexion réussie
   *                 value:
   *                   success: true
   *                   data:
   *                     access_token: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
   *                     refresh_token: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
   *                     partner:
   *                       id: 1
   *                       name: Banque Partenaire XYZ
   *                       country: France
   *                       logo: https://example.com/logo.png
   *                       permissions: [view_operations, view_analytics, export_data]
   *                     expires_in: 86400
   *       '400':
   *         $ref: '#/components/responses/ValidationError'
   *       '401':
   *         description: Identifiants invalides
   *         content:
   *           application/json:
   *             schema:
   *               $ref: '#/components/schemas/ErrorResponse'
   *             example:
   *               success: false
   *               error: Email, mot de passe ou code partenaire invalide
   *               code: INVALID_CREDENTIALS
   *       '429':
   *         $ref: '#/components/responses/RateLimitError'
   */
  static login = asyncHandler(async (req: Request, res: Response) => {
    const { error } = loginSchema.validate(req.body);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const loginData = req.body;
    const result = await authService.loginPartner(loginData);

    logger.info('Partner user login successful', {
      partnerId: result.partner.id,
      userEmail: loginData.email
    });

    res.json({
      success: true,
      data: result
    });
  });

  /**
   * @swagger
   * /auth/refresh:
   *   post:
   *     tags: [Authentication]
   *     summary: Renouvellement du token d'accès
   *     description: |
   *       Génère un nouveau token d'accès à partir du refresh token.
   *       
   *       **Utilisation :**
   *       - Utilisez cet endpoint quand le token d'accès expire (401 Unauthorized)
   *       - Le refresh token a une durée de vie plus longue (7 jours par défaut)
   *       - Un nouveau token d'accès est généré avec la même durée de vie
   *     requestBody:
   *       required: true
   *       content:
   *         application/json:
   *           schema:
   *             type: object
   *             required: [refresh_token]
   *             properties:
   *               refresh_token:
   *                 type: string
   *                 description: Token de rafraîchissement obtenu lors du login
   *                 example: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
   *     responses:
   *       '200':
   *         description: Token renouvelé avec succès
   *         content:
   *           application/json:
   *             schema:
   *               type: object
   *               properties:
   *                 success:
   *                   type: boolean
   *                   example: true
   *                 data:
   *                   type: object
   *                   properties:
   *                     access_token:
   *                       type: string
   *                       example: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
   *                     expires_in:
   *                       type: integer
   *                       example: 86400
   *       '400':
   *         $ref: '#/components/responses/ValidationError'
   *       '401':
   *         description: Refresh token invalide ou expiré
   *         content:
   *           application/json:
   *             schema:
   *               $ref: '#/components/schemas/ErrorResponse'
   *             example:
   *               success: false
   *               error: Refresh token invalide ou expiré
   *               code: INVALID_REFRESH_TOKEN
   */
  static refreshToken = asyncHandler(async (req: Request, res: Response) => {
    const { error } = refreshTokenSchema.validate(req.body);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const { refresh_token } = req.body;
    const result = await authService.refreshToken(refresh_token);

    res.json({
      success: true,
      data: result
    });
  });

  /**
   * @swagger
   * /auth/me:
   *   get:
   *     tags: [Authentication]
   *     summary: Profil utilisateur connecté
   *     description: |
   *       Récupère les informations du profil de l'utilisateur connecté.
   *       
   *       **Informations retournées :**
   *       - Données utilisateur (nom, email, rôle)
   *       - Informations partenaire (nom, pays, logo)
   *       - Permissions détaillées
   *       - Dernière connexion
   *     security:
   *       - BearerAuth: []
   *     responses:
   *       '200':
   *         description: Profil récupéré avec succès
   *         content:
   *           application/json:
   *             schema:
   *               type: object
   *               properties:
   *                 success:
   *                   type: boolean
   *                   example: true
   *                 data:
   *                   type: object
   *                   properties:
   *                     user:
   *                       type: object
   *                       properties:
   *                         id:
   *                           type: integer
   *                           example: 123
   *                         name:
   *                           type: string
   *                           example: Jean Dupont
   *                         email:
   *                           type: string
   *                           example: jean.dupont@partner.com
   *                         role:
   *                           type: string
   *                           example: partner_admin
   *                         last_login:
   *                           type: string
   *                           format: date-time
   *                           example: 2024-01-15T10:30:00Z
   *                         permissions:
   *                           type: array
   *                           items:
   *                             type: string
   *                           example: [view_operations, view_analytics, manage_users]
   *                     partner:
   *                       $ref: '#/components/schemas/Partner'
   *       '401':
   *         $ref: '#/components/responses/UnauthorizedError'
   */
  static getProfile = asyncHandler(async (req: Request, res: Response) => {
    const partnerId = req.partnerContext?.partnerId;
    const userId = req.partnerContext?.userId;

    if (!partnerId || !userId) {
      throw new ValidationError('Partner context not found');
    }

    const user = await prisma.partnerUser.findUnique({
      where: { RefUser: userId },
      include: {
        partner: {
          include: {
            country: true
          }
        }
      }
    });

    if (!user) {
      throw new ValidationError('User not found');
    }

    // Build permissions array
    const permissions = [];
    if (user.ViewOperations) permissions.push('view_operations');
    if (user.ViewAnalytics) permissions.push('view_analytics');
    if (user.ViewAgencies) permissions.push('view_agencies');
    if (user.ExportData) permissions.push('export_data');
    if (user.ManageUsers) permissions.push('manage_users');
    if (user.ApiAccess) permissions.push('api_access');

    res.json({
      success: true,
      data: {
        user: {
          id: user.RefUser,
          name: user.Name,
          email: user.Email,
          role: user.Role,
          last_login: user.LastLogin,
          permissions
        },
        partner: {
          id: user.partner.RefBanque,
          name: user.partner.NameBanque,
          country: user.partner.country.NamePays,
          logo: user.partner.LogoBanque,
          contact_email: user.partner.ContactEmail,
          is_active: user.partner.IsActive
        }
      }
    });
  });

  /**
   * @swagger
   * /auth/api-key:
   *   post:
   *     tags: [Authentication]
   *     summary: Générer une nouvelle paire API Key/Secret
   *     description: |
   *       Génère une nouvelle paire API Key + Secret pour les intégrations système.
   *       
   *       **Important :**
   *       - L'ancienne API Key sera révoquée
   *       - Le secret n'est affiché qu'une seule fois
   *       - Stockez le secret de manière sécurisée
   *       - Utilisez ces clés pour l'authentification HMAC
   *       
   *       **Permissions requises :** `api_access`
   *     security:
   *       - BearerAuth: []
   *     responses:
   *       '200':
   *         description: API Key générée avec succès
   *         content:
   *           application/json:
   *             schema:
   *               type: object
   *               properties:
   *                 success:
   *                   type: boolean
   *                   example: true
   *                 data:
   *                   type: object
   *                   properties:
   *                     api_key:
   *                       type: string
   *                       example: pk_live_1234567890abcdef
   *                     api_secret:
   *                       type: string
   *                       example: sk_live_abcdef1234567890
   *                     created_at:
   *                       type: string
   *                       format: date-time
   *                       example: 2024-01-15T10:30:00Z
   *                 message:
   *                   type: string
   *                   example: "ATTENTION: Le secret ne sera plus jamais affiché. Stockez-le en sécurité."
   *       '401':
   *         $ref: '#/components/responses/UnauthorizedError'
   *       '403':
   *         $ref: '#/components/responses/ForbiddenError'
   */
  static generateApiKey = asyncHandler(async (req: Request, res: Response) => {
    const partnerId = req.partnerContext?.partnerId;
    if (!partnerId) {
      throw new ValidationError('Partner context not found');
    }

    const result = await authService.generateApiKey(partnerId);

    logger.info('API key generated for partner', { partnerId });

    res.json({
      success: true,
      data: result,
      message: "ATTENTION: Le secret ne sera plus jamais affiché. Stockez-le en sécurité."
    });
  });

  /**
   * @swagger
   * /auth/api-key:
   *   delete:
   *     tags: [Authentication]
   *     summary: Révoquer l'API Key actuelle
   *     description: |
   *       Révoque l'API Key actuelle du partenaire.
   *       
   *       **Attention :**
   *       - Cette action est irréversible
   *       - Toutes les intégrations utilisant cette clé cesseront de fonctionner
   *       - Générez une nouvelle clé avant de révoquer l'ancienne en production
   *       
   *       **Permissions requises :** `api_access`
   *     security:
   *       - BearerAuth: []
   *     responses:
   *       '200':
   *         description: API Key révoquée avec succès
   *         content:
   *           application/json:
   *             schema:
   *               type: object
   *               properties:
   *                 success:
   *                   type: boolean
   *                   example: true
   *                 message:
   *                   type: string
   *                   example: API Key révoquée avec succès
   *       '401':
   *         $ref: '#/components/responses/UnauthorizedError'
   *       '403':
   *         $ref: '#/components/responses/ForbiddenError'
   */
  static revokeApiKey = asyncHandler(async (req: Request, res: Response) => {
    const partnerId = req.partnerContext?.partnerId;
    if (!partnerId) {
      throw new ValidationError('Partner context not found');
    }

    await authService.revokeApiKey(partnerId);

    logger.info('API key revoked for partner', { partnerId });

    res.json({
      success: true,
      message: 'API Key révoquée avec succès'
    });
  });

  /**
   * @swagger
   * /auth/users:
   *   post:
   *     tags: [Authentication]
   *     summary: Créer un nouvel utilisateur partenaire
   *     description: |
   *       Crée un nouvel utilisateur pour le partenaire connecté.
   *       
   *       **Rôles disponibles :**
   *       - `partner_admin` : Accès complet, gestion des utilisateurs
   *       - `partner_viewer` : Lecture seule, pas d'export
   *       - `partner_operator` : Accès opérations, pas d'analytics
   *       
   *       **Permissions requises :** `manage_users`
   *     security:
   *       - BearerAuth: []
   *     requestBody:
   *       required: true
   *       content:
   *         application/json:
   *           schema:
   *             type: object
   *             required: [login, password, name, email, role]
   *             properties:
   *               login:
   *                 type: string
   *                 example: john.doe
   *               password:
   *                 type: string
   *                 minLength: 8
   *                 example: SecurePass123!
   *               name:
   *                 type: string
   *                 example: John Doe
   *               email:
   *                 type: string
   *                 format: email
   *                 example: john.doe@partner.com
   *               role:
   *                 type: string
   *                 enum: [partner_admin, partner_viewer, partner_operator]
   *                 example: partner_viewer
   *               api_access:
   *                 type: boolean
   *                 default: false
   *                 example: false
   *               view_operations:
   *                 type: boolean
   *                 default: true
   *                 example: true
   *               view_analytics:
   *                 type: boolean
   *                 default: true
   *                 example: true
   *               view_agencies:
   *                 type: boolean
   *                 default: true
   *                 example: true
   *               export_data:
   *                 type: boolean
   *                 default: false
   *                 example: false
   *               manage_users:
   *                 type: boolean
   *                 default: false
   *                 example: false
   *     responses:
   *       '201':
   *         description: Utilisateur créé avec succès
   *         content:
   *           application/json:
   *             schema:
   *               type: object
   *               properties:
   *                 success:
   *                   type: boolean
   *                   example: true
   *                 data:
   *                   type: object
   *                   properties:
   *                     id:
   *                       type: integer
   *                       example: 456
   *                     login:
   *                       type: string
   *                       example: john.doe
   *                     name:
   *                       type: string
   *                       example: John Doe
   *                     email:
   *                       type: string
   *                       example: john.doe@partner.com
   *                     role:
   *                       type: string
   *                       example: partner_viewer
   *                     permissions:
   *                       type: array
   *                       items:
   *                         type: string
   *                       example: [view_operations, view_analytics]
   *       '400':
   *         $ref: '#/components/responses/ValidationError'
   *       '401':
   *         $ref: '#/components/responses/UnauthorizedError'
   *       '403':
   *         $ref: '#/components/responses/ForbiddenError'
   *       '409':
   *         description: Utilisateur déjà existant
   *         content:
   *           application/json:
   *             schema:
   *               $ref: '#/components/schemas/ErrorResponse'
   *             example:
   *               success: false
   *               error: Un utilisateur avec cet email existe déjà
   *               code: USER_ALREADY_EXISTS
   */
  static createUser = asyncHandler(async (req: Request, res: Response) => {
    const { error } = createUserSchema.validate(req.body);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const partnerId = req.partnerContext?.partnerId;
    if (!partnerId) {
      throw new ValidationError('Partner context not found');
    }

    const userData = {
      ...req.body,
      partner_id: partnerId
    };

    const user = await authService.createPartnerUser(userData);

    logger.info('Partner user created', {
      partnerId,
      userId: user.id,
      createdBy: req.partnerContext?.userId
    });

    res.status(201).json({
      success: true,
      data: user
    });
  });

  /**
   * @swagger
   * /auth/logout:
   *   post:
   *     tags: [Authentication]
   *     summary: Déconnexion utilisateur
   *     description: |
   *       Déconnecte l'utilisateur et invalide le token JWT.
   *       
   *       **Note :** En réalité, les tokens JWT ne peuvent pas être révoqués côté serveur
   *       sans un système de blacklist. Cette endpoint sert principalement à :
   *       - Logger la déconnexion
   *       - Nettoyer les données côté client
   *       - Confirmer la déconnexion
   *     security:
   *       - BearerAuth: []
   *     responses:
   *       '200':
   *         description: Déconnexion réussie
   *         content:
   *           application/json:
   *             schema:
   *               type: object
   *               properties:
   *                 success:
   *                   type: boolean
   *                   example: true
   *                 message:
   *                   type: string
   *                   example: Déconnexion réussie
   *       '401':
   *         $ref: '#/components/responses/UnauthorizedError'
   */
  static logout = asyncHandler(async (req: Request, res: Response) => {
    const partnerId = req.partnerContext?.partnerId;
    const userId = req.partnerContext?.userId;

    logger.info('Partner user logout', { partnerId, userId });

    res.json({
      success: true,
      message: 'Déconnexion réussie'
    });
  });
}
import { Request, Response } from 'express';
import { PrismaClient } from '@prisma/client';
import Joi from 'joi';
import { TwoFactorService } from '@/services/TwoFactorService';
import { asyncHandler, ValidationError } from '@/middleware/errorHandler';
import { logger } from '@/utils/logger';

const prisma = new PrismaClient();
const twoFactorService = new TwoFactorService(prisma);

// Joi validation schemas
const enableTotpSchema = Joi.object({
  token: Joi.string().length(6).pattern(/^\d+$/).required()
});

const verifyTotpSchema = Joi.object({
  token: Joi.string().required()
});

export class TwoFactorController {
  /**
   * @swagger
   * /auth/2fa/setup:
   *   post:
   *     tags: [Two-Factor Authentication]
   *     summary: Configurer l'authentification à deux facteurs
   *     description: |
   *       Génère un secret TOTP et un QR code pour configurer l'authentification à deux facteurs.
   *       
   *       **Étapes :**
   *       1. Appelez cet endpoint pour obtenir le QR code
   *       2. Scannez le QR code avec une app d'authentification (Google Authenticator, Authy, etc.)
   *       3. Utilisez `/auth/2fa/enable` avec un code généré pour activer le 2FA
   *       
   *       **Apps recommandées :**
   *       - Google Authenticator
   *       - Microsoft Authenticator
   *       - Authy
   *       - 1Password
   *     security:
   *       - BearerAuth: []
   *     responses:
   *       '200':
   *         description: Configuration 2FA générée avec succès
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
   *                     qr_code:
   *                       type: string
   *                       description: QR code en base64 data URL
   *                       example: data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...
   *                     backup_codes:
   *                       type: array
   *                       description: Codes de récupération à sauvegarder
   *                       items:
   *                         type: string
   *                       example: ["12345678", "87654321", "11223344"]
   *                 message:
   *                   type: string
   *                   example: "Sauvegardez les codes de récupération dans un endroit sûr"
   *       '401':
   *         $ref: '#/components/responses/UnauthorizedError'
   */
  static setupTotp = asyncHandler(async (req: Request, res: Response) => {
    const userId = req.partnerContext?.userId;
    if (!userId) {
      throw new ValidationError('User context not found');
    }

    const result = await twoFactorService.generateTotpSecret(userId);

    logger.info('2FA setup initiated', { userId });

    res.json({
      success: true,
      data: {
        qr_code: result.qrCodeUrl,
        backup_codes: result.backupCodes
      },
      message: "Sauvegardez les codes de récupération dans un endroit sûr"
    });
  });

  /**
   * @swagger
   * /auth/2fa/enable:
   *   post:
   *     tags: [Two-Factor Authentication]
   *     summary: Activer l'authentification à deux facteurs
   *     description: |
   *       Active le 2FA après avoir configuré l'application d'authentification.
   *       
   *       **Prérequis :**
   *       - Avoir appelé `/auth/2fa/setup` au préalable
   *       - Avoir configuré l'app d'authentification avec le QR code
   *       
   *       **Note :** Une fois activé, le 2FA sera requis à chaque connexion.
   *     security:
   *       - BearerAuth: []
   *     requestBody:
   *       required: true
   *       content:
   *         application/json:
   *           schema:
   *             type: object
   *             required: [token]
   *             properties:
   *               token:
   *                 type: string
   *                 description: Code à 6 chiffres généré par l'app d'authentification
   *                 pattern: ^\d{6}$
   *                 example: "123456"
   *     responses:
   *       '200':
   *         description: 2FA activé avec succès
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
   *                   example: "Authentification à deux facteurs activée avec succès"
   *       '400':
   *         $ref: '#/components/responses/ValidationError'
   *       '401':
   *         description: Code TOTP invalide
   *         content:
   *           application/json:
   *             schema:
   *               $ref: '#/components/schemas/ErrorResponse'
   *             example:
   *               success: false
   *               error: Code d'authentification invalide
   *               code: INVALID_TOTP_TOKEN
   */
  static enableTotp = asyncHandler(async (req: Request, res: Response) => {
    const { error } = enableTotpSchema.validate(req.body);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const userId = req.partnerContext?.userId;
    if (!userId) {
      throw new ValidationError('User context not found');
    }

    const { token } = req.body;
    await twoFactorService.enableTotp(userId, token);

    logger.info('2FA enabled', { userId });

    res.json({
      success: true,
      message: 'Authentification à deux facteurs activée avec succès'
    });
  });

  /**
   * @swagger
   * /auth/2fa/disable:
   *   post:
   *     tags: [Two-Factor Authentication]
   *     summary: Désactiver l'authentification à deux facteurs
   *     description: |
   *       Désactive le 2FA pour l'utilisateur connecté.
   *       
   *       **Attention :** Cette action supprime définitivement :
   *       - Le secret TOTP
   *       - Tous les codes de récupération
   *       
   *       **Authentification requise :** Code TOTP ou code de récupération valide.
   *     security:
   *       - BearerAuth: []
   *     requestBody:
   *       required: true
   *       content:
   *         application/json:
   *           schema:
   *             type: object
   *             required: [token]
   *             properties:
   *               token:
   *                 type: string
   *                 description: Code TOTP à 6 chiffres ou code de récupération à 8 chiffres
   *                 example: "123456"
   *     responses:
   *       '200':
   *         description: 2FA désactivé avec succès
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
   *                   example: "Authentification à deux facteurs désactivée"
   *       '400':
   *         $ref: '#/components/responses/ValidationError'
   *       '401':
   *         description: Code invalide
   *         content:
   *           application/json:
   *             schema:
   *               $ref: '#/components/schemas/ErrorResponse'
   *             example:
   *               success: false
   *               error: Code d'authentification ou de récupération invalide
   *               code: INVALID_TOKEN
   */
  static disableTotp = asyncHandler(async (req: Request, res: Response) => {
    const { error } = verifyTotpSchema.validate(req.body);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const userId = req.partnerContext?.userId;
    if (!userId) {
      throw new ValidationError('User context not found');
    }

    const { token } = req.body;
    await twoFactorService.disableTotp(userId, token);

    logger.info('2FA disabled', { userId });

    res.json({
      success: true,
      message: 'Authentification à deux facteurs désactivée'
    });
  });

  /**
   * @swagger
   * /auth/2fa/verify:
   *   post:
   *     tags: [Two-Factor Authentication]
   *     summary: Vérifier un code d'authentification à deux facteurs
   *     description: |
   *       Vérifie un code TOTP ou un code de récupération.
   *       
   *       **Utilisation :**
   *       - Pendant le processus de connexion après validation du mot de passe
   *       - Pour valider des actions sensibles
   *       
   *       **Types de codes acceptés :**
   *       - Code TOTP à 6 chiffres (généré par l'app)
   *       - Code de récupération à 8 chiffres (usage unique)
   *     security:
   *       - BearerAuth: []
   *     requestBody:
   *       required: true
   *       content:
   *         application/json:
   *           schema:
   *             type: object
   *             required: [token]
   *             properties:
   *               token:
   *                 type: string
   *                 description: Code TOTP ou code de récupération
   *                 example: "123456"
   *     responses:
   *       '200':
   *         description: Code vérifié avec succès
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
   *                     valid:
   *                       type: boolean
   *                       example: true
   *                     backup_code_used:
   *                       type: boolean
   *                       description: Indique si un code de récupération a été utilisé
   *                       example: false
   *       '400':
   *         $ref: '#/components/responses/ValidationError'
   *       '401':
   *         $ref: '#/components/responses/UnauthorizedError'
   */
  static verifyTotp = asyncHandler(async (req: Request, res: Response) => {
    const { error } = verifyTotpSchema.validate(req.body);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const userId = req.partnerContext?.userId;
    if (!userId) {
      throw new ValidationError('User context not found');
    }

    const { token } = req.body;
    const isValid = await twoFactorService.verifyTotp(userId, token);

    // Déterminer si c'est un code de récupération (8 chiffres) ou TOTP (6 chiffres)
    const isBackupCode = token.length === 8 && /^\d+$/.test(token);

    res.json({
      success: true,
      data: {
        valid: isValid,
        backup_code_used: isValid && isBackupCode
      }
    });
  });

  /**
   * @swagger
   * /auth/2fa/backup-codes:
   *   post:
   *     tags: [Two-Factor Authentication]
   *     summary: Régénérer les codes de récupération
   *     description: |
   *       Génère de nouveaux codes de récupération et invalide les anciens.
   *       
   *       **Important :**
   *       - Les anciens codes de récupération ne fonctionneront plus
   *       - Sauvegardez les nouveaux codes dans un endroit sûr
   *       - Chaque code ne peut être utilisé qu'une seule fois
   *       
   *       **Prérequis :** Le 2FA doit être activé.
   *     security:
   *       - BearerAuth: []
   *     responses:
   *       '200':
   *         description: Nouveaux codes de récupération générés
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
   *                     backup_codes:
   *                       type: array
   *                       description: Nouveaux codes de récupération
   *                       items:
   *                         type: string
   *                       example: ["12345678", "87654321", "11223344", "55667788", "99887766"]
   *                 message:
   *                   type: string
   *                   example: "Nouveaux codes de récupération générés. Sauvegardez-les en sécurité."
   *       '400':
   *         description: 2FA non activé
   *         content:
   *           application/json:
   *             schema:
   *               $ref: '#/components/schemas/ErrorResponse'
   *             example:
   *               success: false
   *               error: L'authentification à deux facteurs n'est pas activée
   *               code: TOTP_NOT_ENABLED
   *       '401':
   *         $ref: '#/components/responses/UnauthorizedError'
   */
  static regenerateBackupCodes = asyncHandler(async (req: Request, res: Response) => {
    const userId = req.partnerContext?.userId;
    if (!userId) {
      throw new ValidationError('User context not found');
    }

    const backupCodes = await twoFactorService.regenerateBackupCodes(userId);

    logger.info('Backup codes regenerated', { userId });

    res.json({
      success: true,
      data: {
        backup_codes: backupCodes
      },
      message: 'Nouveaux codes de récupération générés. Sauvegardez-les en sécurité.'
    });
  });

  /**
   * @swagger
   * /auth/2fa/status:
   *   get:
   *     tags: [Two-Factor Authentication]
   *     summary: Statut de l'authentification à deux facteurs
   *     description: |
   *       Récupère le statut actuel du 2FA pour l'utilisateur connecté.
   *       
   *       **Informations retournées :**
   *       - État d'activation du 2FA
   *       - Date de première activation
   *       - Nombre de codes de récupération restants
   *     security:
   *       - BearerAuth: []
   *     responses:
   *       '200':
   *         description: Statut 2FA récupéré avec succès
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
   *                     enabled:
   *                       type: boolean
   *                       description: Indique si le 2FA est activé
   *                       example: true
   *                     verified_at:
   *                       type: string
   *                       format: date-time
   *                       description: Date de première activation du 2FA
   *                       example: "2024-01-15T10:30:00Z"
   *                     backup_codes_count:
   *                       type: integer
   *                       description: Nombre de codes de récupération non utilisés
   *                       example: 8
   *       '401':
   *         $ref: '#/components/responses/UnauthorizedError'
   */
  static getTotpStatus = asyncHandler(async (req: Request, res: Response) => {
    const userId = req.partnerContext?.userId;
    if (!userId) {
      throw new ValidationError('User context not found');
    }

    const status = await twoFactorService.getTotpStatus(userId);

    res.json({
      success: true,
      data: {
        enabled: status.enabled,
        verified_at: status.verifiedAt,
        backup_codes_count: status.backupCodesCount
      }
    });
  });
}

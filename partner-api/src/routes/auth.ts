import { Router } from 'express';
import { AuthController } from '@/controllers/AuthController';
import { TwoFactorController } from '@/controllers/TwoFactorController';
import { partnerAuthMiddleware } from '@/middleware/partnerAuth';

const router = Router();

/**
 * @swagger
 * tags:
 *   name: Authentication
 *   description: Authentification et gestion des utilisateurs partenaires
 */

/**
 * @swagger
 * tags:
 *   name: Two-Factor Authentication
 *   description: Gestion de l'authentification à deux facteurs (2FA/TOTP)
 */

// Routes publiques
router.post('/login', AuthController.login);
router.post('/refresh', AuthController.refreshToken);

// Routes protégées (nécessitent une authentification)
router.get('/me', partnerAuthMiddleware, AuthController.getProfile);
router.post('/logout', partnerAuthMiddleware, AuthController.logout);

// Gestion des API Keys
router.post('/api-key', partnerAuthMiddleware, AuthController.generateApiKey);
router.delete('/api-key', partnerAuthMiddleware, AuthController.revokeApiKey);

// Gestion des utilisateurs partenaires
router.post('/users', partnerAuthMiddleware, AuthController.createUser);

// Routes 2FA/TOTP
router.post('/2fa/setup', partnerAuthMiddleware, TwoFactorController.setupTotp);
router.post('/2fa/enable', partnerAuthMiddleware, TwoFactorController.enableTotp);
router.post('/2fa/disable', partnerAuthMiddleware, TwoFactorController.disableTotp);
router.post('/2fa/verify', partnerAuthMiddleware, TwoFactorController.verifyTotp);
router.post('/2fa/backup-codes', partnerAuthMiddleware, TwoFactorController.regenerateBackupCodes);
router.get('/2fa/status', partnerAuthMiddleware, TwoFactorController.getTotpStatus);

export default router;
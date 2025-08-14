import { Router } from 'express';
import { AuthController } from '@/controllers/AuthController';
import { partnerAuthMiddleware } from '@/middleware/partnerAuth';

const router = Router();

/**
 * @swagger
 * tags:
 *   name: Authentication
 *   description: Authentification et gestion des utilisateurs partenaires
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

export default router;
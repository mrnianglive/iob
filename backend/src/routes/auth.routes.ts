import { Router } from 'express';
import { AuthController } from '../controllers/auth.controller';
import { authenticateToken } from '../middleware/auth.middleware';
import { validate, loginSchema, twoFactorSchema, refreshTokenSchema } from '../utils/validation.schemas';

const router = Router();
const authController = new AuthController();

// Public routes
router.post('/login', validate(loginSchema), authController.login);
router.post('/verify-2fa', validate(twoFactorSchema), authController.verify2FA);
router.post('/refresh', validate(refreshTokenSchema), authController.refreshToken);
router.post('/forgot-password', authController.forgotPassword);
router.post('/reset-password', authController.resetPassword);

// Protected routes
router.use(authenticateToken);
router.get('/me', authController.getProfile);
router.post('/logout', authController.logout);
router.post('/change-password', authController.changePassword);
router.post('/setup-2fa', authController.setup2FA);
router.post('/disable-2fa', authController.disable2FA);

export default router;
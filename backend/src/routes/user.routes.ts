import { Router } from 'express';
import { UserController } from '../controllers/user.controller';
import { authenticateToken, requireAdmin } from '../middleware/auth.middleware';
import { validate, createUserSchema, updateUserSchema } from '../utils/validation.schemas';

const router = Router();
const userController = new UserController();

// All routes require authentication
router.use(authenticateToken);

// User management (admin only)
router.get('/', requireAdmin, userController.getUsers);
router.post('/', requireAdmin, validate(createUserSchema), userController.createUser);
router.get('/:id', requireAdmin, userController.getUserById);
router.put('/:id', requireAdmin, validate(updateUserSchema), userController.updateUser);
router.delete('/:id', requireAdmin, userController.deleteUser);

// User permissions
router.get('/:id/permissions', requireAdmin, userController.getUserPermissions);
router.put('/:id/permissions', requireAdmin, userController.updateUserPermissions);

// User cash registers
router.get('/:id/cash-registers', requireAdmin, userController.getUserCashRegisters);
router.put('/:id/cash-registers', requireAdmin, userController.updateUserCashRegisters);

export default router;
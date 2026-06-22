import { Router } from 'express';
import { OperationController } from '../controllers/operation.controller';
import { authenticateToken, requireCashRegisterAccess } from '../middleware/auth.middleware';
import { validate, createOperationSchema, approveOperationSchema, validateOperationSchema } from '../utils/validation.schemas';

const router = Router();
const operationController = new OperationController();

// All routes require authentication
router.use(authenticateToken);

// Operations CRUD
router.get('/', operationController.getOperations);
router.post('/', validate(createOperationSchema), operationController.createOperation);
router.get('/:id', operationController.getOperationById);
router.put('/:id', operationController.updateOperation);
router.delete('/:id', operationController.deleteOperation);

// Validation and approval
router.post('/:id/approve', validate(approveOperationSchema), operationController.approveOperation);
router.post('/:id/validate', validate(validateOperationSchema), operationController.validateOperation);
router.post('/:id/cancel-validation', operationController.cancelValidation);

// Balance validation
router.get('/validate-balance', operationController.validateBalance);

// Bill breakdown
router.get('/:id/bill-breakdown', operationController.getBillBreakdown);

export default router;
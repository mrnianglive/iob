import { Router } from 'express';
import { CashRegisterController } from '../controllers/cashRegister.controller';
import { authenticateToken, requireCashRegisterAccess } from '../middleware/auth.middleware';
import { validate, transferFundsSchema } from '../utils/validation.schemas';

const router = Router();
const cashRegisterController = new CashRegisterController();

// All routes require authentication
router.use(authenticateToken);

// Cash registers
router.get('/', cashRegisterController.getCashRegisters);
router.get('/:id', cashRegisterController.getCashRegisterById);
router.get('/:id/balance', cashRegisterController.getBalance);
router.get('/:id/operations', cashRegisterController.getOperations);
router.get('/:id/products', cashRegisterController.getProducts);

// Fund transfers
router.post('/transfer', validate(transferFundsSchema), cashRegisterController.transferFunds);
router.get('/transfers', cashRegisterController.getTransfers);

// Opening hours
router.get('/:id/opening-hours', cashRegisterController.getOpeningHours);
router.put('/:id/opening-hours', cashRegisterController.updateOpeningHours);

export default router;
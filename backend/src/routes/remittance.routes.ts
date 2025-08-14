import { Router } from 'express';
import { RemittanceController } from '../controllers/remittance.controller';
import { authenticateToken } from '../middleware/auth.middleware';
import { validate, createRemittanceSchema } from '../utils/validation.schemas';

const router = Router();
const remittanceController = new RemittanceController();

// All routes require authentication
router.use(authenticateToken);

// Remittance operations
router.get('/', remittanceController.getRemittances);
router.post('/', validate(createRemittanceSchema), remittanceController.createRemittance);
router.get('/:id', remittanceController.getRemittanceById);
router.put('/:id', remittanceController.updateRemittance);
router.delete('/:id', remittanceController.deleteRemittance);

// Approval workflow
router.post('/:id/approve', remittanceController.approveRemittance);
router.post('/:id/reject', remittanceController.rejectRemittance);

// Fees calculation
router.get('/fees', remittanceController.calculateFees);
router.get('/exchange-rates', remittanceController.getExchangeRates);

export default router;
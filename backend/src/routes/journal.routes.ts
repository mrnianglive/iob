import { Router } from 'express';
import { JournalController } from '../controllers/journal.controller';
import { authenticateToken } from '../middleware/auth.middleware';
import { validate, journalFilterSchema, validateOperationSchema } from '../utils/validation.schemas';

const router = Router();
const journalController = new JournalController();

// All routes require authentication
router.use(authenticateToken);

// Journal operations
router.get('/operations', validate(journalFilterSchema), journalController.getJournalOperations);
router.get('/stats', validate(journalFilterSchema), journalController.getJournalStats);
router.post('/validate', validate(validateOperationSchema), journalController.validateOperation);
router.post('/cancel-validation', journalController.cancelValidation);
router.post('/bulk-validate', journalController.bulkValidate);

export default router;
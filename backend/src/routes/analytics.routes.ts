import { Router } from 'express';
import { AnalyticsController } from '../controllers/analytics.controller';
import { authenticateToken } from '../middleware/auth.middleware';
import { validate, analyticsFilterSchema } from '../utils/validation.schemas';

const router = Router();
const analyticsController = new AnalyticsController();

// All routes require authentication
router.use(authenticateToken);

// Analytics endpoints
router.get('/operations', validate(analyticsFilterSchema), analyticsController.getOperationsAnalytics);
router.get('/stats', validate(analyticsFilterSchema), analyticsController.getStats);
router.get('/charts', validate(analyticsFilterSchema), analyticsController.getChartsData);
router.post('/export', validate(analyticsFilterSchema), analyticsController.exportData);
router.get('/commissions', validate(analyticsFilterSchema), analyticsController.getCommissions);
router.get('/performance', validate(analyticsFilterSchema), analyticsController.getPerformance);

export default router;
import { Router } from 'express';
import { DashboardController } from '../controllers/dashboard.controller';
import { authenticateToken } from '../middleware/auth.middleware';
import { validate, dashboardFilterSchema } from '../utils/validation.schemas';

const router = Router();
const dashboardController = new DashboardController();

// All routes require authentication
router.use(authenticateToken);

// Dashboard stats
router.get('/stats', validate(dashboardFilterSchema), dashboardController.getStats);
router.get('/operations', validate(dashboardFilterSchema), dashboardController.getRecentOperations);
router.get('/alerts', dashboardController.getAlerts);
router.get('/quick-links', dashboardController.getQuickLinks);
router.get('/balance-summary', dashboardController.getBalanceSummary);

export default router;
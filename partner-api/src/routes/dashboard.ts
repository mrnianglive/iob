import { Router } from 'express';
import { DashboardController } from '@/controllers/DashboardController';

const router = Router();

/**
 * @swagger
 * tags:
 *   name: Dashboard
 *   description: Endpoints du dashboard partenaire
 */

// Statistiques générales
router.get('/stats', DashboardController.getStats);

// Opérations récentes
router.get('/operations/recent', DashboardController.getRecentOperations);

// Agences avec statistiques
router.get('/agencies', DashboardController.getAgencies);

// Indicateurs de performance
router.get('/performance', DashboardController.getPerformance);

// Résumé complet
router.get('/summary', DashboardController.getSummary);

export default router;
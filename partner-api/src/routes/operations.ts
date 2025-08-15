import { Router } from 'express';
import { OperationsController } from '@/controllers/OperationsController';

const router = Router();

/**
 * @swagger
 * tags:
 *   name: Operations
 *   description: Gestion des opérations partenaires
 */

// Liste des opérations avec filtres
router.get('/', OperationsController.getOperations);

// Détails d'une opération
router.get('/:id', OperationsController.getOperation);

// Statistiques des opérations
router.get('/stats', OperationsController.getOperationStats);

// Résumé des opérations par période
router.get('/summary', OperationsController.getOperationsSummary);

// Export des opérations
router.post('/export', OperationsController.exportOperations);

export default router;
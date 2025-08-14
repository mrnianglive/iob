import { Router } from 'express';
import { AnalyticsController } from '../controllers/AnalyticsController';
import { PartnerAuthMiddleware } from '../middleware/partnerAuth';

const router = Router();

// Apply partner authentication to all routes
router.use(PartnerAuthMiddleware.hybridAuth);

/**
 * @swagger
 * tags:
 *   name: Analytics
 *   description: Analytics et rapports pour partenaires
 */

/**
 * @swagger
 * /analytics/kpis:
 *   get:
 *     tags: [Analytics]
 *     summary: Obtenir les KPIs analytics
 *     description: Récupère les indicateurs clés de performance
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     parameters:
 *       - name: period
 *         in: query
 *         description: Période d'analyse
 *         schema:
 *           type: string
 *           enum: [7d, 30d, 90d, 1y]
 *           default: 30d
 *       - name: agencyId
 *         in: query
 *         description: Filtrer par agence
 *         schema:
 *           type: integer
 *       - name: productId
 *         in: query
 *         description: Filtrer par produit
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: KPIs récupérés avec succès
 */
router.get('/kpis', AnalyticsController.getKPIs);

/**
 * @swagger
 * /analytics/volume-chart:
 *   get:
 *     tags: [Analytics]
 *     summary: Obtenir les données du graphique de volume
 *     description: Récupère les données pour le graphique de volume temporel
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     parameters:
 *       - name: period
 *         in: query
 *         description: Période d'analyse
 *         schema:
 *           type: string
 *           enum: [7d, 30d, 90d, 1y]
 *           default: 30d
 *     responses:
 *       200:
 *         description: Données du graphique récupérées avec succès
 */
router.get('/volume-chart', AnalyticsController.getVolumeChart);

/**
 * @swagger
 * /analytics/type-chart:
 *   get:
 *     tags: [Analytics]
 *     summary: Obtenir la distribution par type d'opération
 *     description: Récupère les données pour le graphique en secteurs des types d'opération
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     parameters:
 *       - name: period
 *         in: query
 *         description: Période d'analyse
 *         schema:
 *           type: string
 *           enum: [7d, 30d, 90d, 1y]
 *           default: 30d
 *     responses:
 *       200:
 *         description: Données du graphique récupérées avec succès
 */
router.get('/type-chart', AnalyticsController.getTypeChart);

/**
 * @swagger
 * /analytics/report:
 *   get:
 *     tags: [Analytics]
 *     summary: Obtenir le rapport d'analytics
 *     description: Récupère les données tabulaires pour le rapport
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     parameters:
 *       - name: period
 *         in: query
 *         description: Période d'analyse
 *         schema:
 *           type: string
 *           enum: [7d, 30d, 90d, 1y]
 *           default: 30d
 *     responses:
 *       200:
 *         description: Rapport récupéré avec succès
 */
router.get('/report', AnalyticsController.getReport);

/**
 * @swagger
 * /analytics/export:
 *   get:
 *     tags: [Analytics]
 *     summary: Exporter les données analytics
 *     description: Exporte les données analytics au format Excel
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     parameters:
 *       - name: period
 *         in: query
 *         description: Période d'analyse
 *         schema:
 *           type: string
 *           enum: [7d, 30d, 90d, 1y]
 *           default: 30d
 *     responses:
 *       200:
 *         description: Fichier Excel généré
 *         content:
 *           application/vnd.openxmlformats-officedocument.spreadsheetml.sheet:
 *             schema:
 *               type: string
 *               format: binary
 */
router.get('/export', AnalyticsController.exportData);

/**
 * @swagger
 * /analytics/generate-report:
 *   post:
 *     tags: [Analytics]
 *     summary: Générer un rapport personnalisé
 *     description: Lance la génération d'un rapport analytics personnalisé
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             required:
 *               - period
 *             properties:
 *               period:
 *                 type: string
 *                 enum: [7d, 30d, 90d, 1y]
 *                 description: Période du rapport
 *     responses:
 *       200:
 *         description: Génération du rapport lancée
 */
router.post('/generate-report', AnalyticsController.generateReport);

export default router;
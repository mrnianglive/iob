import { Router } from 'express';
import { RemittanceController } from '../controllers/RemittanceController';
import { PartnerAuthMiddleware } from '../middleware/partnerAuth';

const router = Router();

// Apply partner authentication to all routes
router.use(PartnerAuthMiddleware.hybridAuth);

/**
 * @swagger
 * tags:
 *   name: Remittance
 *   description: Gestion des remises inter-agences
 */

/**
 * @swagger
 * /remittance:
 *   get:
 *     tags: [Remittance]
 *     summary: Obtenir la liste des remises
 *     description: Récupère les remises avec filtres et pagination
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     parameters:
 *       - name: page
 *         in: query
 *         description: Numéro de page
 *         schema:
 *           type: integer
 *           default: 1
 *       - name: limit
 *         in: query
 *         description: Nombre d'éléments par page
 *         schema:
 *           type: integer
 *           default: 20
 *           maximum: 100
 *       - name: status
 *         in: query
 *         description: Filtrer par statut
 *         schema:
 *           type: string
 *           enum: [pending, validated, cancelled]
 *       - name: dateFrom
 *         in: query
 *         description: Date de début (ISO 8601)
 *         schema:
 *           type: string
 *           format: date
 *       - name: dateTo
 *         in: query
 *         description: Date de fin (ISO 8601)
 *         schema:
 *           type: string
 *           format: date
 *     responses:
 *       200:
 *         description: Liste des remises récupérée avec succès
 */
router.get('/', RemittanceController.getRemittances);

/**
 * @swagger
 * /remittance:
 *   post:
 *     tags: [Remittance]
 *     summary: Créer une nouvelle remise
 *     description: Crée une remise entre deux agences
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
 *               - sourceAgencyId
 *               - destinationAgencyId
 *               - amount
 *             properties:
 *               sourceAgencyId:
 *                 type: integer
 *                 description: ID de l'agence source
 *               destinationAgencyId:
 *                 type: integer
 *                 description: ID de l'agence destination
 *               amount:
 *                 type: number
 *                 description: Montant de la remise
 *               currency:
 *                 type: string
 *                 enum: [EUR, USD, GBP, XOF, XAF]
 *                 default: EUR
 *               description:
 *                 type: string
 *                 description: Description optionnelle
 *     responses:
 *       201:
 *         description: Remise créée avec succès
 */
router.post('/', RemittanceController.createRemittance);

/**
 * @swagger
 * /remittance/stats:
 *   get:
 *     tags: [Remittance]
 *     summary: Obtenir les statistiques des remises
 *     description: Récupère les statistiques globales des remises
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     responses:
 *       200:
 *         description: Statistiques récupérées avec succès
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 pending:
 *                   type: integer
 *                   description: Nombre de remises en attente
 *                 totalAmount:
 *                   type: number
 *                   description: Montant total des remises validées
 *                 today:
 *                   type: integer
 *                   description: Nombre de remises créées aujourd'hui
 */
router.get('/stats', RemittanceController.getStats);

/**
 * @swagger
 * /remittance/{id}:
 *   get:
 *     tags: [Remittance]
 *     summary: Obtenir les détails d'une remise
 *     description: Récupère les détails complets d'une remise
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     parameters:
 *       - name: id
 *         in: path
 *         required: true
 *         description: ID de la remise
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Détails de la remise récupérés avec succès
 *       404:
 *         description: Remise non trouvée
 */
router.get('/:id', RemittanceController.getRemittanceDetails);

/**
 * @swagger
 * /remittance/{id}/validate:
 *   put:
 *     tags: [Remittance]
 *     summary: Valider une remise
 *     description: Valide une remise en attente
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     parameters:
 *       - name: id
 *         in: path
 *         required: true
 *         description: ID de la remise
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Remise validée avec succès
 *       400:
 *         description: Remise déjà traitée
 *       404:
 *         description: Remise non trouvée
 */
router.put('/:id/validate', RemittanceController.validateRemittance);

/**
 * @swagger
 * /remittance/{id}/cancel:
 *   put:
 *     tags: [Remittance]
 *     summary: Annuler une remise
 *     description: Annule une remise en attente
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     parameters:
 *       - name: id
 *         in: path
 *         required: true
 *         description: ID de la remise
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Remise annulée avec succès
 *       400:
 *         description: Remise ne peut plus être annulée
 *       404:
 *         description: Remise non trouvée
 */
router.put('/:id/cancel', RemittanceController.cancelRemittance);

export default router;

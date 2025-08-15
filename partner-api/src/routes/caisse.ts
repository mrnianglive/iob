import { Router } from 'express';
import { CaisseController } from '../controllers/CaisseController';
import { PartnerAuthMiddleware } from '../middleware/partnerAuth';

const router = Router();

// Apply partner authentication to all routes
router.use(PartnerAuthMiddleware.hybridAuth);

/**
 * @swagger
 * tags:
 *   name: Caisse
 *   description: Gestion des caisses et mouvements de fonds
 */

/**
 * @swagger
 * /caisse/balance:
 *   get:
 *     tags: [Caisse]
 *     summary: Obtenir le solde des caisses
 *     description: Récupère le solde actuel et les mouvements du jour
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     responses:
 *       200:
 *         description: Solde récupéré avec succès
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 current:
 *                   type: number
 *                   description: Solde actuel total
 *                 todayIn:
 *                   type: number
 *                   description: Entrées du jour
 *                 todayOut:
 *                   type: number
 *                   description: Sorties du jour
 */
router.get('/balance', CaisseController.getBalance);

/**
 * @swagger
 * /caisse/movements:
 *   get:
 *     tags: [Caisse]
 *     summary: Obtenir les mouvements de caisse
 *     description: Récupère l'historique des mouvements récents
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     parameters:
 *       - name: limit
 *         in: query
 *         description: Nombre de mouvements à récupérer
 *         schema:
 *           type: integer
 *           default: 20
 *           maximum: 100
 *     responses:
 *       200:
 *         description: Mouvements récupérés avec succès
 */
router.get('/movements', CaisseController.getMovements);

/**
 * @swagger
 * /caisse/transfer:
 *   post:
 *     tags: [Caisse]
 *     summary: Effectuer un transfert entre caisses
 *     description: Transfère des fonds d'une caisse à une autre
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
 *               - destinationCaisseId
 *               - amount
 *               - reason
 *             properties:
 *               destinationCaisseId:
 *                 type: integer
 *                 description: ID de la caisse de destination
 *               amount:
 *                 type: number
 *                 description: Montant à transférer
 *               reason:
 *                 type: string
 *                 description: Motif du transfert
 *     responses:
 *       201:
 *         description: Transfert effectué avec succès
 */
router.post('/transfer', CaisseController.transfer);

/**
 * @swagger
 * /caisse/deposit:
 *   post:
 *     tags: [Caisse]
 *     summary: Effectuer un dépôt
 *     description: Ajoute des fonds à une caisse
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
 *               - amount
 *               - source
 *               - description
 *             properties:
 *               amount:
 *                 type: number
 *                 description: Montant du dépôt
 *               source:
 *                 type: string
 *                 enum: [bank_transfer, cash_delivery, other]
 *                 description: Source du dépôt
 *               description:
 *                 type: string
 *                 description: Description du dépôt
 *     responses:
 *       201:
 *         description: Dépôt effectué avec succès
 */
router.post('/deposit', CaisseController.deposit);

/**
 * @swagger
 * /caisse/available:
 *   get:
 *     tags: [Caisse]
 *     summary: Obtenir les caisses disponibles
 *     description: Liste des caisses disponibles pour transfert
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     responses:
 *       200:
 *         description: Liste des caisses disponibles
 */
router.get('/available', CaisseController.getAvailableCaisses);

export default router;

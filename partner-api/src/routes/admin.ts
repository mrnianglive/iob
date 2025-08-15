import { Router } from 'express';
import { AdminController } from '../controllers/AdminController';
import { PartnerAuthMiddleware } from '../middleware/partnerAuth';

const router = Router();

// Apply partner authentication to all routes
router.use(PartnerAuthMiddleware.hybridAuth);

/**
 * @swagger
 * tags:
 *   name: Administration
 *   description: Gestion administrative des utilisateurs, agences et produits
 */

/**
 * @swagger
 * /admin/users:
 *   get:
 *     tags: [Administration]
 *     summary: Obtenir la liste des utilisateurs
 *     description: Récupère la liste des utilisateurs avec pagination
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
 *     responses:
 *       200:
 *         description: Liste des utilisateurs récupérée avec succès
 */
router.get('/users', AdminController.getUsers);

/**
 * @swagger
 * /admin/users:
 *   post:
 *     tags: [Administration]
 *     summary: Créer un nouvel utilisateur
 *     description: Crée un nouvel utilisateur dans le système
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
 *               - name
 *               - email
 *               - password
 *             properties:
 *               name:
 *                 type: string
 *                 description: Nom complet de l'utilisateur
 *               email:
 *                 type: string
 *                 format: email
 *                 description: Adresse email de l'utilisateur
 *               password:
 *                 type: string
 *                 minLength: 8
 *                 description: Mot de passe de l'utilisateur
 *               role:
 *                 type: string
 *                 enum: [admin, operator, viewer]
 *                 default: operator
 *               agencyId:
 *                 type: integer
 *                 description: ID de l'agence associée
 *     responses:
 *       201:
 *         description: Utilisateur créé avec succès
 */
router.post('/users', AdminController.createUser);

/**
 * @swagger
 * /admin/users/{id}:
 *   put:
 *     tags: [Administration]
 *     summary: Modifier un utilisateur
 *     description: Met à jour les informations d'un utilisateur
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     parameters:
 *       - name: id
 *         in: path
 *         required: true
 *         description: ID de l'utilisateur
 *         schema:
 *           type: integer
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             properties:
 *               name:
 *                 type: string
 *               email:
 *                 type: string
 *                 format: email
 *               role:
 *                 type: string
 *                 enum: [admin, operator, viewer]
 *               agencyId:
 *                 type: integer
 *               isActive:
 *                 type: boolean
 *     responses:
 *       200:
 *         description: Utilisateur modifié avec succès
 */
router.put('/users/:id', AdminController.updateUser);

/**
 * @swagger
 * /admin/users/{id}:
 *   delete:
 *     tags: [Administration]
 *     summary: Supprimer un utilisateur
 *     description: Désactive un utilisateur (suppression logique)
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     parameters:
 *       - name: id
 *         in: path
 *         required: true
 *         description: ID de l'utilisateur
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Utilisateur supprimé avec succès
 */
router.delete('/users/:id', AdminController.deleteUser);

/**
 * @swagger
 * /admin/agencies:
 *   get:
 *     tags: [Administration]
 *     summary: Obtenir la liste des agences
 *     description: Récupère la liste des agences avec pagination
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
 *     responses:
 *       200:
 *         description: Liste des agences récupérée avec succès
 */
router.get('/agencies', AdminController.getAgencies);

/**
 * @swagger
 * /admin/agencies:
 *   post:
 *     tags: [Administration]
 *     summary: Créer une nouvelle agence
 *     description: Crée une nouvelle agence dans le système
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
 *               - name
 *             properties:
 *               name:
 *                 type: string
 *                 description: Nom de l'agence
 *               address:
 *                 type: string
 *                 description: Adresse de l'agence
 *               phone:
 *                 type: string
 *                 description: Numéro de téléphone
 *               email:
 *                 type: string
 *                 format: email
 *                 description: Adresse email de l'agence
 *               isActive:
 *                 type: boolean
 *                 default: true
 *     responses:
 *       201:
 *         description: Agence créée avec succès
 */
router.post('/agencies', AdminController.createAgency);

/**
 * @swagger
 * /admin/products:
 *   get:
 *     tags: [Administration]
 *     summary: Obtenir la liste des produits
 *     description: Récupère la liste des produits avec pagination
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
 *     responses:
 *       200:
 *         description: Liste des produits récupérée avec succès
 */
router.get('/products', AdminController.getProducts);

/**
 * @swagger
 * /admin/products:
 *   post:
 *     tags: [Administration]
 *     summary: Créer un nouveau produit
 *     description: Crée un nouveau produit dans le système
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
 *               - name
 *               - commissionRate
 *             properties:
 *               name:
 *                 type: string
 *                 description: Nom du produit
 *               description:
 *                 type: string
 *                 description: Description du produit
 *               commissionRate:
 *                 type: number
 *                 minimum: 0
 *                 maximum: 100
 *                 description: Taux de commission en pourcentage
 *               minAmount:
 *                 type: number
 *                 minimum: 0
 *                 description: Montant minimum
 *               maxAmount:
 *                 type: number
 *                 minimum: 0
 *                 description: Montant maximum
 *               isActive:
 *                 type: boolean
 *                 default: true
 *     responses:
 *       201:
 *         description: Produit créé avec succès
 */
router.post('/products', AdminController.createProduct);

/**
 * @swagger
 * /admin/permissions:
 *   get:
 *     tags: [Administration]
 *     summary: Obtenir la liste des permissions
 *     description: Récupère la liste des permissions disponibles
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     responses:
 *       200:
 *         description: Liste des permissions récupérée avec succès
 */
router.get('/permissions', AdminController.getPermissions);

export default router;

import { Router } from 'express';
import { Request, Response } from 'express';
import { PrismaClient } from '@prisma/client';
import Joi from 'joi';
import crypto from 'crypto';
import { asyncHandler, ValidationError } from '@/middleware/errorHandler';
import { PARTNER_PERMISSIONS } from '@/types/partner';
import { requirePermission } from '@/middleware/partnerAuth';
import { logger } from '@/utils/logger';

const router = Router();
const prisma = new PrismaClient();

// Schémas de validation
const createWebhookSchema = Joi.object({
  url: Joi.string().uri().required(),
  events: Joi.array().items(
    Joi.string().valid('operation_created', 'operation_approved', 'operation_rejected', 'operation_cancelled')
  ).min(1).required(),
  is_active: Joi.boolean().default(true)
});

const updateWebhookSchema = Joi.object({
  url: Joi.string().uri().optional(),
  events: Joi.array().items(
    Joi.string().valid('operation_created', 'operation_approved', 'operation_rejected', 'operation_cancelled')
  ).min(1).optional(),
  is_active: Joi.boolean().optional()
});

/**
 * @swagger
 * tags:
 *   name: Webhooks
 *   description: Gestion des webhooks partenaires
 */

/**
 * @swagger
 * /webhooks:
 *   get:
 *     summary: Liste des webhooks du partenaire
 *     tags: [Webhooks]
 *     security:
 *       - BearerAuth: []
 *     responses:
 *       200:
 *         description: Liste des webhooks récupérée
 */
const getWebhooks = asyncHandler(async (req: Request, res: Response) => {
  const partnerId = req.partnerContext?.partnerId;

  if (!partnerId) {
    throw new ValidationError('Partner context not found');
  }

  const webhooks = await prisma.partnerWebhook.findMany({
    where: { RefBanque: partnerId },
    select: {
      RefWebhook: true,
      Url: true,
      Events: true,
      IsActive: true,
      CreatedAt: true,
      UpdatedAt: true
    },
    orderBy: { CreatedAt: 'desc' }
  });

  const formattedWebhooks = webhooks.map(webhook => ({
    id: webhook.RefWebhook,
    url: webhook.Url,
    events: webhook.Events,
    is_active: webhook.IsActive,
    created_at: webhook.CreatedAt,
    updated_at: webhook.UpdatedAt
  }));

  res.status(200).json({
    success: true,
    data: formattedWebhooks
  });
});

/**
 * @swagger
 * /webhooks:
 *   post:
 *     summary: Créer un nouveau webhook
 *     tags: [Webhooks]
 *     security:
 *       - BearerAuth: []
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             required:
 *               - url
 *               - events
 *             properties:
 *               url:
 *                 type: string
 *                 format: uri
 *                 example: https://your-api.com/webhooks/iob
 *               events:
 *                 type: array
 *                 items:
 *                   type: string
 *                   enum: [operation_created, operation_approved, operation_rejected, operation_cancelled]
 *                 example: [operation_created, operation_approved]
 *               is_active:
 *                 type: boolean
 *                 default: true
 *     responses:
 *       201:
 *         description: Webhook créé avec succès
 */
const createWebhook = asyncHandler(async (req: Request, res: Response) => {
  const partnerId = req.partnerContext?.partnerId;

  if (!partnerId) {
    throw new ValidationError('Partner context not found');
  }

  const { error, value } = createWebhookSchema.validate(req.body);
  if (error) {
    throw new ValidationError(error.details[0].message);
  }

  // Générer un secret pour le webhook
  const secret = crypto.randomBytes(32).toString('hex');

  const webhook = await prisma.partnerWebhook.create({
    data: {
      RefBanque: partnerId,
      Url: value.url,
      Events: value.events,
      Secret: secret,
      IsActive: value.is_active
    },
    select: {
      RefWebhook: true,
      Url: true,
      Events: true,
      IsActive: true,
      CreatedAt: true
    }
  });

  logger.info('Webhook created', {
    webhookId: webhook.RefWebhook,
    partnerId,
    url: webhook.Url,
    events: webhook.Events
  });

  res.status(201).json({
    success: true,
    data: {
      id: webhook.RefWebhook,
      url: webhook.Url,
      events: webhook.Events,
      secret: secret, // Retourné une seule fois lors de la création
      is_active: webhook.IsActive,
      created_at: webhook.CreatedAt
    },
    message: 'Webhook created successfully. Store the secret securely, it will not be shown again.'
  });
});

/**
 * @swagger
 * /webhooks/{id}:
 *   get:
 *     summary: Détails d'un webhook spécifique
 *     tags: [Webhooks]
 *     security:
 *       - BearerAuth: []
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: number
 *         description: ID du webhook
 *     responses:
 *       200:
 *         description: Détails du webhook
 */
const getWebhook = asyncHandler(async (req: Request, res: Response) => {
  const partnerId = req.partnerContext?.partnerId;
  const webhookId = parseInt(req.params.id);

  if (!partnerId) {
    throw new ValidationError('Partner context not found');
  }

  if (!webhookId || isNaN(webhookId)) {
    throw new ValidationError('Invalid webhook ID');
  }

  const webhook = await prisma.partnerWebhook.findFirst({
    where: { 
      RefWebhook: webhookId,
      RefBanque: partnerId
    },
    select: {
      RefWebhook: true,
      Url: true,
      Events: true,
      IsActive: true,
      CreatedAt: true,
      UpdatedAt: true
    }
  });

  if (!webhook) {
    return res.status(404).json({
      success: false,
      error: 'Webhook not found'
    });
  }

  res.status(200).json({
    success: true,
    data: {
      id: webhook.RefWebhook,
      url: webhook.Url,
      events: webhook.Events,
      is_active: webhook.IsActive,
      created_at: webhook.CreatedAt,
      updated_at: webhook.UpdatedAt
    }
  });
});

/**
 * @swagger
 * /webhooks/{id}:
 *   put:
 *     summary: Mettre à jour un webhook
 *     tags: [Webhooks]
 *     security:
 *       - BearerAuth: []
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: number
 *         description: ID du webhook
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             properties:
 *               url:
 *                 type: string
 *                 format: uri
 *               events:
 *                 type: array
 *                 items:
 *                   type: string
 *                   enum: [operation_created, operation_approved, operation_rejected, operation_cancelled]
 *               is_active:
 *                 type: boolean
 *     responses:
 *       200:
 *         description: Webhook mis à jour
 */
const updateWebhook = asyncHandler(async (req: Request, res: Response) => {
  const partnerId = req.partnerContext?.partnerId;
  const webhookId = parseInt(req.params.id);

  if (!partnerId) {
    throw new ValidationError('Partner context not found');
  }

  if (!webhookId || isNaN(webhookId)) {
    throw new ValidationError('Invalid webhook ID');
  }

  const { error, value } = updateWebhookSchema.validate(req.body);
  if (error) {
    throw new ValidationError(error.details[0].message);
  }

  // Vérifier que le webhook existe et appartient au partenaire
  const existingWebhook = await prisma.partnerWebhook.findFirst({
    where: { 
      RefWebhook: webhookId,
      RefBanque: partnerId
    }
  });

  if (!existingWebhook) {
    return res.status(404).json({
      success: false,
      error: 'Webhook not found'
    });
  }

  // Préparer les données de mise à jour
  const updateData: any = {};
  if (value.url) updateData.Url = value.url;
  if (value.events) updateData.Events = value.events;
  if (value.is_active !== undefined) updateData.IsActive = value.is_active;

  const updatedWebhook = await prisma.partnerWebhook.update({
    where: { RefWebhook: webhookId },
    data: updateData,
    select: {
      RefWebhook: true,
      Url: true,
      Events: true,
      IsActive: true,
      UpdatedAt: true
    }
  });

  logger.info('Webhook updated', {
    webhookId: updatedWebhook.RefWebhook,
    partnerId,
    changes: Object.keys(updateData)
  });

  res.status(200).json({
    success: true,
    data: {
      id: updatedWebhook.RefWebhook,
      url: updatedWebhook.Url,
      events: updatedWebhook.Events,
      is_active: updatedWebhook.IsActive,
      updated_at: updatedWebhook.UpdatedAt
    },
    message: 'Webhook updated successfully'
  });
});

/**
 * @swagger
 * /webhooks/{id}:
 *   delete:
 *     summary: Supprimer un webhook
 *     tags: [Webhooks]
 *     security:
 *       - BearerAuth: []
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: number
 *         description: ID du webhook
 *     responses:
 *       200:
 *         description: Webhook supprimé
 */
const deleteWebhook = asyncHandler(async (req: Request, res: Response) => {
  const partnerId = req.partnerContext?.partnerId;
  const webhookId = parseInt(req.params.id);

  if (!partnerId) {
    throw new ValidationError('Partner context not found');
  }

  if (!webhookId || isNaN(webhookId)) {
    throw new ValidationError('Invalid webhook ID');
  }

  // Vérifier que le webhook existe et appartient au partenaire
  const existingWebhook = await prisma.partnerWebhook.findFirst({
    where: { 
      RefWebhook: webhookId,
      RefBanque: partnerId
    }
  });

  if (!existingWebhook) {
    return res.status(404).json({
      success: false,
      error: 'Webhook not found'
    });
  }

  await prisma.partnerWebhook.delete({
    where: { RefWebhook: webhookId }
  });

  logger.info('Webhook deleted', {
    webhookId,
    partnerId
  });

  res.status(200).json({
    success: true,
    message: 'Webhook deleted successfully'
  });
});

/**
 * @swagger
 * /webhooks/{id}/test:
 *   post:
 *     summary: Tester un webhook
 *     tags: [Webhooks]
 *     security:
 *       - BearerAuth: []
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: number
 *         description: ID du webhook
 *     responses:
 *       200:
 *         description: Test du webhook effectué
 */
const testWebhook = asyncHandler(async (req: Request, res: Response) => {
  const partnerId = req.partnerContext?.partnerId;
  const webhookId = parseInt(req.params.id);

  if (!partnerId) {
    throw new ValidationError('Partner context not found');
  }

  if (!webhookId || isNaN(webhookId)) {
    throw new ValidationError('Invalid webhook ID');
  }

  // Récupérer le webhook
  const webhook = await prisma.partnerWebhook.findFirst({
    where: { 
      RefWebhook: webhookId,
      RefBanque: partnerId
    }
  });

  if (!webhook) {
    return res.status(404).json({
      success: false,
      error: 'Webhook not found'
    });
  }

  // Créer un payload de test
  const testPayload = {
    event: 'test_event',
    data: {
      message: 'This is a test webhook from IOB Partner API',
      timestamp: new Date().toISOString(),
      partner_id: partnerId
    },
    timestamp: new Date().toISOString(),
    partner_id: partnerId
  };

  // Générer la signature
  const signature = crypto
    .createHmac('sha256', webhook.Secret)
    .update(JSON.stringify(testPayload))
    .digest('hex');

  try {
    // Envoyer le webhook de test
    const response = await fetch(webhook.Url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-IOB-Signature': signature,
        'X-IOB-Event': 'test_event',
        'User-Agent': 'IOB-Partner-API/1.0'
      },
      body: JSON.stringify(testPayload)
    });

    const testResult = {
      webhook_id: webhookId,
      url: webhook.Url,
      status_code: response.status,
      status_text: response.statusText,
      success: response.ok,
      response_time: 0, // À implémenter si nécessaire
      tested_at: new Date().toISOString()
    };

    logger.info('Webhook test completed', {
      webhookId,
      partnerId,
      statusCode: response.status,
      success: response.ok
    });

    res.status(200).json({
      success: true,
      data: testResult,
      message: `Webhook test ${response.ok ? 'successful' : 'failed'}`
    });

  } catch (error) {
    logger.error('Webhook test failed', {
      webhookId,
      partnerId,
      error: error instanceof Error ? error.message : 'Unknown error'
    });

    res.status(200).json({
      success: false,
      data: {
        webhook_id: webhookId,
        url: webhook.Url,
        success: false,
        error: error instanceof Error ? error.message : 'Unknown error',
        tested_at: new Date().toISOString()
      },
      message: 'Webhook test failed'
    });
  }
});

// Routes avec middleware de permissions
router.get('/', requirePermission(PARTNER_PERMISSIONS.API_ACCESS), getWebhooks);
router.post('/', requirePermission(PARTNER_PERMISSIONS.API_ACCESS), createWebhook);
router.get('/:id', requirePermission(PARTNER_PERMISSIONS.API_ACCESS), getWebhook);
router.put('/:id', requirePermission(PARTNER_PERMISSIONS.API_ACCESS), updateWebhook);
router.delete('/:id', requirePermission(PARTNER_PERMISSIONS.API_ACCESS), deleteWebhook);
router.post('/:id/test', requirePermission(PARTNER_PERMISSIONS.API_ACCESS), testWebhook);

export default router;
import { Router } from 'express';
import { Request, Response } from 'express';
import { PrismaClient } from '@prisma/client';
import { PartnerService } from '@/services/PartnerService';
import { asyncHandler, ValidationError } from '@/middleware/errorHandler';
import { PARTNER_PERMISSIONS } from '@/types/partner';

const router = Router();
const prisma = new PrismaClient();
const partnerService = new PartnerService(prisma);

/**
 * @swagger
 * tags:
 *   name: Agencies
 *   description: Gestion des agences partenaires
 */

/**
 * @swagger
 * /agencies:
 *   get:
 *     summary: Liste des agences du partenaire
 *     tags: [Agencies]
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     responses:
 *       200:
 *         description: Liste des agences récupérée
 */
const getAgencies = asyncHandler(async (req: Request, res: Response) => {
  const partnerId = req.partnerContext?.partnerId;
  const permissions = req.partnerContext?.permissions || [];

  if (!partnerId) {
    throw new ValidationError('Partner context not found');
  }

  if (!permissions.includes(PARTNER_PERMISSIONS.VIEW_AGENCIES)) {
    return res.status(403).json({
      success: false,
      error: 'View agencies permission required'
    });
  }

  const agencies = await partnerService.getPartnerAgencies(partnerId);

  res.status(200).json({
    success: true,
    data: agencies
  });
});

/**
 * @swagger
 * /agencies/{id}:
 *   get:
 *     summary: Détails d'une agence spécifique
 *     tags: [Agencies]
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: number
 *         description: ID de l'agence
 *     responses:
 *       200:
 *         description: Détails de l'agence
 */
const getAgency = asyncHandler(async (req: Request, res: Response) => {
  const partnerId = req.partnerContext?.partnerId;
  const permissions = req.partnerContext?.permissions || [];
  const agencyId = parseInt(req.params.id);

  if (!partnerId) {
    throw new ValidationError('Partner context not found');
  }

  if (!permissions.includes(PARTNER_PERMISSIONS.VIEW_AGENCIES)) {
    return res.status(403).json({
      success: false,
      error: 'View agencies permission required'
    });
  }

  if (!agencyId || isNaN(agencyId)) {
    throw new ValidationError('Invalid agency ID');
  }

  // Récupérer l'agence avec ses statistiques
  const agencies = await partnerService.getPartnerAgencies(partnerId);
  const agency = agencies.find(a => a.RefAgence === agencyId);

  if (!agency) {
    return res.status(404).json({
      success: false,
      error: 'Agency not found'
    });
  }

  res.status(200).json({
    success: true,
    data: agency
  });
});

/**
 * @swagger
 * /agencies/{id}/operations:
 *   get:
 *     summary: Opérations d'une agence spécifique
 *     tags: [Agencies]
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: number
 *         description: ID de l'agence
 *       - in: query
 *         name: page
 *         schema:
 *           type: number
 *           default: 1
 *       - in: query
 *         name: limit
 *         schema:
 *           type: number
 *           default: 20
 *     responses:
 *       200:
 *         description: Opérations de l'agence
 */
const getAgencyOperations = asyncHandler(async (req: Request, res: Response) => {
  const partnerId = req.partnerContext?.partnerId;
  const permissions = req.partnerContext?.permissions || [];
  const agencyId = parseInt(req.params.id);
  const page = parseInt(req.query.page as string) || 1;
  const limit = Math.min(parseInt(req.query.limit as string) || 20, 100);

  if (!partnerId) {
    throw new ValidationError('Partner context not found');
  }

  if (!permissions.includes(PARTNER_PERMISSIONS.VIEW_OPERATIONS)) {
    return res.status(403).json({
      success: false,
      error: 'View operations permission required'
    });
  }

  if (!agencyId || isNaN(agencyId)) {
    throw new ValidationError('Invalid agency ID');
  }

  const operations = await partnerService.getPartnerOperations(partnerId, {
    agency_id: agencyId,
    page,
    limit
  });

  res.status(200).json({
    success: true,
    data: operations.data,
    pagination: operations.pagination
  });
});

/**
 * @swagger
 * /agencies/{id}/stats:
 *   get:
 *     summary: Statistiques d'une agence
 *     tags: [Agencies]
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: number
 *         description: ID de l'agence
 *       - in: query
 *         name: date_from
 *         schema:
 *           type: string
 *           format: date
 *       - in: query
 *         name: date_to
 *         schema:
 *           type: string
 *           format: date
 *     responses:
 *       200:
 *         description: Statistiques de l'agence
 */
const getAgencyStats = asyncHandler(async (req: Request, res: Response) => {
  const partnerId = req.partnerContext?.partnerId;
  const permissions = req.partnerContext?.permissions || [];
  const agencyId = parseInt(req.params.id);

  if (!partnerId) {
    throw new ValidationError('Partner context not found');
  }

  if (!permissions.includes(PARTNER_PERMISSIONS.VIEW_ANALYTICS)) {
    return res.status(403).json({
      success: false,
      error: 'View analytics permission required'
    });
  }

  if (!agencyId || isNaN(agencyId)) {
    throw new ValidationError('Invalid agency ID');
  }

  // Construction de la plage de dates
  let dateRange;
  if (req.query.date_from || req.query.date_to) {
    dateRange = {
      start: req.query.date_from ? new Date(req.query.date_from as string) : new Date(0),
      end: req.query.date_to ? new Date(req.query.date_to as string) : new Date()
    };
  }

  // Récupérer les opérations de l'agence pour calculer les stats
  const operations = await partnerService.getPartnerOperations(partnerId, {
    agency_id: agencyId,
    date_from: dateRange?.start.toISOString(),
    date_to: dateRange?.end.toISOString(),
    limit: 10000 // Toutes les opérations pour les stats
  });

  // Calculer les statistiques
  const stats = {
    operations_count: operations.data.length,
    total_volume: operations.data.reduce((sum, op) => sum + Number(op.Amount), 0),
    total_commissions: operations.data.reduce((sum, op) => sum + Number(op.Commission), 0),
    average_operation_value: operations.data.length > 0 
      ? operations.data.reduce((sum, op) => sum + Number(op.Amount), 0) / operations.data.length 
      : 0,
    operations_by_status: operations.data.reduce((acc, op) => {
      acc[op.Status] = (acc[op.Status] || 0) + 1;
      return acc;
    }, {} as { [key: string]: number }),
    operations_by_product: operations.data.reduce((acc, op) => {
      const productName = op.product?.NameProduit || 'Unknown';
      acc[productName] = (acc[productName] || 0) + 1;
      return acc;
    }, {} as { [key: string]: number })
  };

  res.status(200).json({
    success: true,
    data: stats
  });
});

// Routes
router.get('/', getAgencies);
router.get('/:id', getAgency);
router.get('/:id/operations', getAgencyOperations);
router.get('/:id/stats', getAgencyStats);

export default router;
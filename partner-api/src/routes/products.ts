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
 *   name: Products
 *   description: Gestion des produits partenaires
 */

/**
 * @swagger
 * /products:
 *   get:
 *     summary: Liste des produits du partenaire
 *     tags: [Products]
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     responses:
 *       200:
 *         description: Liste des produits récupérée
 */
const getProducts = asyncHandler(async (req: Request, res: Response) => {
  const partnerId = req.partnerContext?.partnerId;
  const permissions = req.partnerContext?.permissions || [];

  if (!partnerId) {
    throw new ValidationError('Partner context not found');
  }

  if (!permissions.includes(PARTNER_PERMISSIONS.VIEW_OPERATIONS)) {
    return res.status(403).json({
      success: false,
      error: 'View operations permission required'
    });
  }

  const products = await partnerService.getPartnerProducts(partnerId);

  res.status(200).json({
    success: true,
    data: products
  });
});

/**
 * @swagger
 * /products/{id}/operations:
 *   get:
 *     summary: Opérations d'un produit spécifique
 *     tags: [Products]
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: number
 *         description: ID du produit
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
 *         description: Opérations du produit
 */
const getProductOperations = asyncHandler(async (req: Request, res: Response) => {
  const partnerId = req.partnerContext?.partnerId;
  const permissions = req.partnerContext?.permissions || [];
  const productId = parseInt(req.params.id);
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

  if (!productId || isNaN(productId)) {
    throw new ValidationError('Invalid product ID');
  }

  const operations = await partnerService.getPartnerOperations(partnerId, {
    product_id: productId,
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
 * /products/{id}/performance:
 *   get:
 *     summary: Performance d'un produit
 *     tags: [Products]
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: number
 *         description: ID du produit
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
 *         description: Performance du produit
 */
const getProductPerformance = asyncHandler(async (req: Request, res: Response) => {
  const partnerId = req.partnerContext?.partnerId;
  const permissions = req.partnerContext?.permissions || [];
  const productId = parseInt(req.params.id);

  if (!partnerId) {
    throw new ValidationError('Partner context not found');
  }

  if (!permissions.includes(PARTNER_PERMISSIONS.VIEW_ANALYTICS)) {
    return res.status(403).json({
      success: false,
      error: 'View analytics permission required'
    });
  }

  if (!productId || isNaN(productId)) {
    throw new ValidationError('Invalid product ID');
  }

  // Construction de la plage de dates
  let dateRange;
  if (req.query.date_from || req.query.date_to) {
    dateRange = {
      start: req.query.date_from ? new Date(req.query.date_from as string) : new Date(0),
      end: req.query.date_to ? new Date(req.query.date_to as string) : new Date()
    };
  }

  // Récupérer les opérations du produit pour calculer la performance
  const operations = await partnerService.getPartnerOperations(partnerId, {
    product_id: productId,
    date_from: dateRange?.start.toISOString(),
    date_to: dateRange?.end.toISOString(),
    limit: 10000 // Toutes les opérations pour les stats
  });

  // Calculer la performance
  const performance = {
    operations_count: operations.data.length,
    total_volume: operations.data.reduce((sum, op) => sum + Number(op.Amount), 0),
    total_commissions: operations.data.reduce((sum, op) => sum + Number(op.Commission), 0),
    average_operation_value: operations.data.length > 0 
      ? operations.data.reduce((sum, op) => sum + Number(op.Amount), 0) / operations.data.length 
      : 0,
    success_rate: operations.data.length > 0
      ? (operations.data.filter(op => op.Status === 'approved').length / operations.data.length) * 100
      : 0,
    operations_by_status: operations.data.reduce((acc, op) => {
      acc[op.Status] = (acc[op.Status] || 0) + 1;
      return acc;
    }, {} as { [key: string]: number }),
    monthly_trend: await getMonthlyTrend(partnerId, productId, dateRange)
  };

  res.status(200).json({
    success: true,
    data: performance
  });
});

// Fonction utilitaire pour calculer la tendance mensuelle
async function getMonthlyTrend(partnerId: number, productId: number, dateRange?: { start: Date; end: Date }) {
  if (!dateRange) return [];

  const monthlyData = [];
  const startDate = new Date(dateRange.start);
  const endDate = new Date(dateRange.end);
  
  const currentDate = new Date(startDate.getFullYear(), startDate.getMonth(), 1);
  
  while (currentDate <= endDate) {
    const monthStart = new Date(currentDate);
    const monthEnd = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
    
    const monthOperations = await partnerService.getPartnerOperations(partnerId, {
      product_id: productId,
      date_from: monthStart.toISOString(),
      date_to: monthEnd.toISOString(),
      limit: 10000
    });

    monthlyData.push({
      month: currentDate.toISOString().substring(0, 7), // YYYY-MM
      operations_count: monthOperations.data.length,
      total_volume: monthOperations.data.reduce((sum, op) => sum + Number(op.Amount), 0),
      total_commissions: monthOperations.data.reduce((sum, op) => sum + Number(op.Commission), 0)
    });

    currentDate.setMonth(currentDate.getMonth() + 1);
  }

  return monthlyData;
}

// Routes
router.get('/', getProducts);
router.get('/:id/operations', getProductOperations);
router.get('/:id/performance', getProductPerformance);

export default router;
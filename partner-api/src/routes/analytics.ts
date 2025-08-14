import { Router } from 'express';
import { Request, Response } from 'express';
import { PrismaClient } from '@prisma/client';
import Joi from 'joi';
import { PartnerService } from '@/services/PartnerService';
import { ExportService } from '@/services/ExportService';
import { asyncHandler, ValidationError } from '@/middleware/errorHandler';
import { PARTNER_PERMISSIONS } from '@/types/partner';

const router = Router();
const prisma = new PrismaClient();
const partnerService = new PartnerService(prisma);
const exportService = new ExportService(prisma);

// Schémas de validation
const analyticsSchema = Joi.object({
  date_from: Joi.string().isoDate().required(),
  date_to: Joi.string().isoDate().required(),
  granularity: Joi.string().valid('day', 'week', 'month').default('day')
});

/**
 * @swagger
 * tags:
 *   name: Analytics
 *   description: Analytics avancés pour partenaires
 */

/**
 * @swagger
 * /analytics/operations:
 *   get:
 *     summary: Analytics des opérations
 *     tags: [Analytics]
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     parameters:
 *       - in: query
 *         name: date_from
 *         required: true
 *         schema:
 *           type: string
 *           format: date
 *       - in: query
 *         name: date_to
 *         required: true
 *         schema:
 *           type: string
 *           format: date
 *       - in: query
 *         name: granularity
 *         schema:
 *           type: string
 *           enum: [day, week, month]
 *           default: day
 *     responses:
 *       200:
 *         description: Analytics des opérations
 */
const getOperationsAnalytics = asyncHandler(async (req: Request, res: Response) => {
  const partnerId = req.partnerContext?.partnerId;
  const permissions = req.partnerContext?.permissions || [];

  if (!partnerId) {
    throw new ValidationError('Partner context not found');
  }

  if (!permissions.includes(PARTNER_PERMISSIONS.VIEW_ANALYTICS)) {
    return res.status(403).json({
      success: false,
      error: 'View analytics permission required'
    });
  }

  const { error, value } = analyticsSchema.validate(req.query);
  if (error) {
    throw new ValidationError(error.details[0].message);
  }

  const dateRange = {
    start: new Date(value.date_from),
    end: new Date(value.date_to)
  };

  const analytics = await partnerService.getPartnerAnalytics(partnerId, dateRange);

  res.status(200).json({
    success: true,
    data: {
      period: {
        start: dateRange.start,
        end: dateRange.end,
        granularity: value.granularity
      },
      ...analytics
    }
  });
});

/**
 * @swagger
 * /analytics/commissions:
 *   get:
 *     summary: Analytics des commissions
 *     tags: [Analytics]
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     parameters:
 *       - in: query
 *         name: date_from
 *         required: true
 *         schema:
 *           type: string
 *           format: date
 *       - in: query
 *         name: date_to
 *         required: true
 *         schema:
 *           type: string
 *           format: date
 *     responses:
 *       200:
 *         description: Analytics des commissions
 */
const getCommissionsAnalytics = asyncHandler(async (req: Request, res: Response) => {
  const partnerId = req.partnerContext?.partnerId;
  const permissions = req.partnerContext?.permissions || [];

  if (!partnerId) {
    throw new ValidationError('Partner context not found');
  }

  if (!permissions.includes(PARTNER_PERMISSIONS.VIEW_ANALYTICS)) {
    return res.status(403).json({
      success: false,
      error: 'View analytics permission required'
    });
  }

  const { error, value } = analyticsSchema.validate(req.query);
  if (error) {
    throw new ValidationError(error.details[0].message);
  }

  const dateRange = {
    start: new Date(value.date_from),
    end: new Date(value.date_to)
  };

  // Récupérer toutes les opérations pour l'analyse des commissions
  const operations = await partnerService.getPartnerOperations(partnerId, {
    date_from: value.date_from,
    date_to: value.date_to,
    limit: 10000
  });

  // Analyser les commissions
  const commissionsAnalytics = {
    total_commissions: operations.data.reduce((sum, op) => sum + Number(op.Commission), 0),
    average_commission_rate: operations.data.length > 0 
      ? (operations.data.reduce((sum, op) => sum + Number(op.Commission), 0) / 
         operations.data.reduce((sum, op) => sum + Number(op.Amount), 0)) * 100
      : 0,
    commissions_by_product: operations.data.reduce((acc, op) => {
      const productName = op.product?.NameProduit || 'Unknown';
      if (!acc[productName]) {
        acc[productName] = { count: 0, total_commission: 0, total_volume: 0 };
      }
      acc[productName].count += 1;
      acc[productName].total_commission += Number(op.Commission);
      acc[productName].total_volume += Number(op.Amount);
      return acc;
    }, {} as { [key: string]: { count: number; total_commission: number; total_volume: number } }),
    commissions_by_agency: operations.data.reduce((acc, op) => {
      const agencyName = op.cashRegister?.agency?.NameAgence || 'Unknown';
      if (!acc[agencyName]) {
        acc[agencyName] = { count: 0, total_commission: 0, total_volume: 0 };
      }
      acc[agencyName].count += 1;
      acc[agencyName].total_commission += Number(op.Commission);
      acc[agencyName].total_volume += Number(op.Amount);
      return acc;
    }, {} as { [key: string]: { count: number; total_commission: number; total_volume: number } }),
    top_commission_operations: operations.data
      .sort((a, b) => Number(b.Commission) - Number(a.Commission))
      .slice(0, 10)
      .map(op => ({
        id: op.RefOperation,
        reference: op.Reference,
        client: op.ClientName,
        amount: Number(op.Amount),
        commission: Number(op.Commission),
        commission_rate: (Number(op.Commission) / Number(op.Amount)) * 100,
        date: op.Insert_Time
      }))
  };

  res.status(200).json({
    success: true,
    data: commissionsAnalytics
  });
});

/**
 * @swagger
 * /analytics/volumes:
 *   get:
 *     summary: Analytics des volumes
 *     tags: [Analytics]
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     parameters:
 *       - in: query
 *         name: date_from
 *         required: true
 *         schema:
 *           type: string
 *           format: date
 *       - in: query
 *         name: date_to
 *         required: true
 *         schema:
 *           type: string
 *           format: date
 *     responses:
 *       200:
 *         description: Analytics des volumes
 */
const getVolumesAnalytics = asyncHandler(async (req: Request, res: Response) => {
  const partnerId = req.partnerContext?.partnerId;
  const permissions = req.partnerContext?.permissions || [];

  if (!partnerId) {
    throw new ValidationError('Partner context not found');
  }

  if (!permissions.includes(PARTNER_PERMISSIONS.VIEW_ANALYTICS)) {
    return res.status(403).json({
      success: false,
      error: 'View analytics permission required'
    });
  }

  const { error, value } = analyticsSchema.validate(req.query);
  if (error) {
    throw new ValidationError(error.details[0].message);
  }

  const dateRange = {
    start: new Date(value.date_from),
    end: new Date(value.date_to)
  };

  const analytics = await partnerService.getPartnerAnalytics(partnerId, dateRange);

  // Calculs supplémentaires pour les volumes
  const operations = await partnerService.getPartnerOperations(partnerId, {
    date_from: value.date_from,
    date_to: value.date_to,
    limit: 10000
  });

  const volumesAnalytics = {
    daily_volumes: analytics.volume_by_day,
    total_volume: operations.data.reduce((sum, op) => sum + Number(op.Amount), 0),
    average_operation_value: operations.data.length > 0 
      ? operations.data.reduce((sum, op) => sum + Number(op.Amount), 0) / operations.data.length 
      : 0,
    median_operation_value: calculateMedian(operations.data.map(op => Number(op.Amount))),
    volume_distribution: {
      small: operations.data.filter(op => Number(op.Amount) < 100).length,
      medium: operations.data.filter(op => Number(op.Amount) >= 100 && Number(op.Amount) < 1000).length,
      large: operations.data.filter(op => Number(op.Amount) >= 1000).length
    },
    peak_days: analytics.volume_by_day
      .sort((a: any, b: any) => b.volume - a.volume)
      .slice(0, 5),
    volume_trends: calculateVolumeTrends(analytics.volume_by_day as any[])
  };

  res.status(200).json({
    success: true,
    data: volumesAnalytics
  });
});

/**
 * @swagger
 * /analytics/export:
 *   post:
 *     summary: Exporter les analytics
 *     tags: [Analytics]
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
 *               - format
 *               - date_from
 *               - date_to
 *             properties:
 *               format:
 *                 type: string
 *                 enum: [pdf, excel]
 *               date_from:
 *                 type: string
 *                 format: date
 *               date_to:
 *                 type: string
 *                 format: date
 *               include_charts:
 *                 type: boolean
 *                 default: true
 *     responses:
 *       200:
 *         description: Rapport d'analytics généré
 */
const exportAnalytics = asyncHandler(async (req: Request, res: Response) => {
  const partnerId = req.partnerContext?.partnerId;
  const permissions = req.partnerContext?.permissions || [];

  if (!partnerId) {
    throw new ValidationError('Partner context not found');
  }

  if (!permissions.includes(PARTNER_PERMISSIONS.EXPORT_DATA)) {
    return res.status(403).json({
      success: false,
      error: 'Export data permission required'
    });
  }

  const { format, date_from, date_to, include_charts = true } = req.body;

  if (!format || !date_from || !date_to) {
    throw new ValidationError('Format, date_from and date_to are required');
  }

  const dateRange = {
    start: new Date(date_from),
    end: new Date(date_to)
  };

  // Récupérer toutes les données analytics
  const [stats, analytics, operations] = await Promise.all([
    partnerService.getPartnerStats(partnerId, dateRange),
    partnerService.getPartnerAnalytics(partnerId, dateRange),
    partnerService.getPartnerOperations(partnerId, {
      date_from,
      date_to,
      limit: 10000
    })
  ]);

  // Générer le rapport selon le format
  const reportData = {
    partner_id: partnerId,
    period: { start: dateRange.start, end: dateRange.end },
    summary: stats,
    analytics,
    operations: operations.data.slice(0, 100), // Limiter pour le rapport
    include_charts
  };

  // Pour cet exemple, on utilise l'ExportService existant
  const exportResult = await exportService.exportOperations(
    operations.data,
    { format: format as 'pdf' | 'excel' }
  );

  const filename = `analytics_report_${partnerId}_${new Date().toISOString().split('T')[0]}.${format}`;
  
  res.setHeader('Content-Disposition', `attachment; filename="${filename}"`);
  res.setHeader('Content-Type', format === 'pdf' ? 'application/pdf' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  res.send(exportResult.buffer);
});

// Fonctions utilitaires
function calculateMedian(values: number[]): number {
  if (values.length === 0) return 0;
  
  const sorted = values.sort((a, b) => a - b);
  const mid = Math.floor(sorted.length / 2);
  
  return sorted.length % 2 !== 0 
    ? sorted[mid] 
    : (sorted[mid - 1] + sorted[mid]) / 2;
}

function calculateVolumeTrends(volumeData: Array<{ date: string; volume: number }>): any {
  if (volumeData.length < 2) return { trend: 'stable', growth_rate: 0 };
  
  const firstHalf = volumeData.slice(0, Math.floor(volumeData.length / 2));
  const secondHalf = volumeData.slice(Math.floor(volumeData.length / 2));
  
  const firstHalfAvg = firstHalf.reduce((sum, item) => sum + item.volume, 0) / firstHalf.length;
  const secondHalfAvg = secondHalf.reduce((sum, item) => sum + item.volume, 0) / secondHalf.length;
  
  const growthRate = firstHalfAvg === 0 ? 0 : ((secondHalfAvg - firstHalfAvg) / firstHalfAvg) * 100;
  
  return {
    trend: growthRate > 5 ? 'growing' : growthRate < -5 ? 'declining' : 'stable',
    growth_rate: Math.round(growthRate * 100) / 100,
    first_half_avg: Math.round(firstHalfAvg),
    second_half_avg: Math.round(secondHalfAvg)
  };
}

// Routes
router.get('/operations', getOperationsAnalytics);
router.get('/commissions', getCommissionsAnalytics);
router.get('/volumes', getVolumesAnalytics);
router.post('/export', exportAnalytics);

export default router;
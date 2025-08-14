import { Request, Response } from 'express';
import { PrismaClient } from '@prisma/client';
import Joi from 'joi';
import { PartnerService } from '@/services/PartnerService';
import { asyncHandler, ValidationError } from '@/middleware/errorHandler';
import { PARTNER_PERMISSIONS } from '@/types/partner';
import { logger } from '@/utils/logger';

const prisma = new PrismaClient();
const partnerService = new PartnerService(prisma);

// Schémas de validation
const dateRangeSchema = Joi.object({
  date_from: Joi.string().isoDate().optional(),
  date_to: Joi.string().isoDate().optional()
});

export class DashboardController {
  /**
   * @swagger
   * /dashboard/stats:
   *   get:
   *     summary: Statistiques du dashboard partenaire
   *     tags: [Dashboard]
   *     security:
   *       - BearerAuth: []
   *       - ApiKeyAuth: []
   *     parameters:
   *       - in: query
   *         name: date_from
   *         schema:
   *           type: string
   *           format: date
   *         description: Date de début (ISO format)
   *       - in: query
   *         name: date_to
   *         schema:
   *           type: string
   *           format: date
   *         description: Date de fin (ISO format)
   *     responses:
   *       200:
   *         description: Statistiques récupérées avec succès
   *         content:
   *           application/json:
   *             schema:
   *               type: object
   *               properties:
   *                 success:
   *                   type: boolean
   *                 data:
   *                   type: object
   *                   properties:
   *                     operations_count:
   *                       type: number
   *                     total_volume:
   *                       type: number
   *                     commissions:
   *                       type: number
   *                     agencies_count:
   *                       type: number
   *                     active_users:
   *                       type: number
   *                     operations_trend:
   *                       type: number
   *                       description: Pourcentage de changement par rapport à la période précédente
   *                     volume_trend:
   *                       type: number
   *                     commission_trend:
   *                       type: number
   */
  static getStats = asyncHandler(async (req: Request, res: Response) => {
    const partnerId = req.partnerContext?.partnerId;
    
    if (!partnerId) {
      throw new ValidationError('Partner context not found');
    }

    // Validation des paramètres de date
    const { error, value } = dateRangeSchema.validate(req.query);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    // Construction de la plage de dates
    let dateRange;
    if (value.date_from || value.date_to) {
      dateRange = {
        start: value.date_from ? new Date(value.date_from) : new Date(0),
        end: value.date_to ? new Date(value.date_to) : new Date()
      };
    }

    const stats = await partnerService.getPartnerStats(partnerId, dateRange);

    res.status(200).json({
      success: true,
      data: stats
    });
  });

  /**
   * @swagger
   * /dashboard/operations/recent:
   *   get:
   *     summary: Opérations récentes du partenaire
   *     tags: [Dashboard]
   *     security:
   *       - BearerAuth: []
   *       - ApiKeyAuth: []
   *     parameters:
   *       - in: query
   *         name: limit
   *         schema:
   *           type: number
   *           minimum: 1
   *           maximum: 100
   *           default: 10
   *         description: Nombre d'opérations à récupérer
   *     responses:
   *       200:
   *         description: Opérations récentes récupérées
   */
  static getRecentOperations = asyncHandler(async (req: Request, res: Response) => {
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

    const limit = Math.min(parseInt(req.query.limit as string) || 10, 100);

    const result = await partnerService.getPartnerOperations(partnerId, {
      limit,
      sort: 'Insert_Time',
      order: 'desc'
    });

    res.status(200).json({
      success: true,
      data: result.data,
      pagination: result.pagination
    });
  });

  /**
   * @swagger
   * /dashboard/agencies:
   *   get:
   *     summary: Agences du partenaire avec statistiques
   *     tags: [Dashboard]
   *     security:
   *       - BearerAuth: []
   *       - ApiKeyAuth: []
   *     responses:
   *       200:
   *         description: Agences récupérées avec succès
   */
  static getAgencies = asyncHandler(async (req: Request, res: Response) => {
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
   * /dashboard/performance:
   *   get:
   *     summary: Indicateurs de performance du partenaire
   *     tags: [Dashboard]
   *     security:
   *       - BearerAuth: []
   *       - ApiKeyAuth: []
   *     parameters:
   *       - in: query
   *         name: period
   *         schema:
   *           type: string
   *           enum: [today, week, month, quarter, year]
   *           default: month
   *         description: Période d'analyse
   *     responses:
   *       200:
   *         description: Indicateurs de performance
   */
  static getPerformance = asyncHandler(async (req: Request, res: Response) => {
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

    const period = req.query.period as string || 'month';
    const dateRange = DashboardController.getDateRangeForPeriod(period);

    const [stats, analytics] = await Promise.all([
      partnerService.getPartnerStats(partnerId, dateRange),
      partnerService.getPartnerAnalytics(partnerId, dateRange)
    ]);

    // Calcul d'indicateurs de performance
    const averageOperationValue = stats.operations_count > 0 
      ? stats.total_volume / stats.operations_count 
      : 0;

    const averageCommissionRate = stats.total_volume > 0 
      ? (stats.commissions / stats.total_volume) * 100 
      : 0;

    const performance = {
      period,
      date_range: dateRange,
      kpis: {
        total_operations: stats.operations_count,
        total_volume: stats.total_volume,
        total_commissions: stats.commissions,
        average_operation_value: Math.round(averageOperationValue * 100) / 100,
        average_commission_rate: Math.round(averageCommissionRate * 100) / 100,
        active_agencies: stats.agencies_count,
        active_users: stats.active_users
      },
      trends: {
        operations_trend: stats.operations_trend || 0,
        volume_trend: stats.volume_trend || 0,
        commission_trend: stats.commission_trend || 0
      },
      analytics: {
        operations_by_status: analytics.operations_by_status,
        top_products: analytics.operations_by_product.slice(0, 5),
        top_agencies: analytics.operations_by_agency.slice(0, 5)
      }
    };

    res.status(200).json({
      success: true,
      data: performance
    });
  });

  /**
   * @swagger
   * /dashboard/summary:
   *   get:
   *     summary: Résumé complet du dashboard partenaire
   *     tags: [Dashboard]
   *     security:
   *       - BearerAuth: []
   *       - ApiKeyAuth: []
   *     responses:
   *       200:
   *         description: Résumé du dashboard
   */
  static getSummary = asyncHandler(async (req: Request, res: Response) => {
    const partnerId = req.partnerContext?.partnerId;

    if (!partnerId) {
      throw new ValidationError('Partner context not found');
    }

    // Récupération des données pour différentes périodes
    const today = DashboardController.getDateRangeForPeriod('today');
    const thisMonth = DashboardController.getDateRangeForPeriod('month');

    const [
      todayStats,
      monthStats,
      recentOperations,
      agencies,
      products
    ] = await Promise.all([
      partnerService.getPartnerStats(partnerId, today),
      partnerService.getPartnerStats(partnerId, thisMonth),
      partnerService.getPartnerOperations(partnerId, { limit: 5, sort: 'Insert_Time', order: 'desc' }),
      partnerService.getPartnerAgencies(partnerId),
      partnerService.getPartnerProducts(partnerId)
    ]);

    const summary = {
      today: {
        operations: todayStats.operations_count,
        volume: todayStats.total_volume,
        commissions: todayStats.commissions
      },
      this_month: {
        operations: monthStats.operations_count,
        volume: monthStats.total_volume,
        commissions: monthStats.commissions,
        trends: {
          operations_trend: monthStats.operations_trend || 0,
          volume_trend: monthStats.volume_trend || 0,
          commission_trend: monthStats.commission_trend || 0
        }
      },
      resources: {
        agencies_count: agencies.length,
        active_products: products.filter(p => p.IsActive).length,
        total_products: products.length,
        active_users: monthStats.active_users
      },
      recent_operations: recentOperations.data,
      top_agencies: agencies
        .sort((a, b) => b.stats.total_volume - a.stats.total_volume)
        .slice(0, 3)
        .map(agency => ({
          id: agency.RefAgence,
          name: agency.NameAgence,
          country: agency.country.NamePays,
          stats: agency.stats
        }))
    };

    res.status(200).json({
      success: true,
      data: summary
    });
  });

  // ===== MÉTHODES UTILITAIRES =====

  private static getDateRangeForPeriod(period: string): { start: Date; end: Date } {
    const now = new Date();
    const start = new Date();
    
    switch (period) {
      case 'today':
        start.setHours(0, 0, 0, 0);
        break;
      case 'week':
        start.setDate(now.getDate() - 7);
        break;
      case 'month':
        start.setMonth(now.getMonth() - 1);
        break;
      case 'quarter':
        start.setMonth(now.getMonth() - 3);
        break;
      case 'year':
        start.setFullYear(now.getFullYear() - 1);
        break;
      default:
        start.setMonth(now.getMonth() - 1);
    }

    return { start, end: now };
  }
}
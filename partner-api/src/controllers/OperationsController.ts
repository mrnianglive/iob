import { Request, Response } from 'express';
import { PrismaClient } from '@prisma/client';
import Joi from 'joi';
import { PartnerService } from '@/services/PartnerService';
import { ExportService } from '@/services/ExportService';
import { asyncHandler, ValidationError, NotFoundError } from '@/middleware/errorHandler';
import { PARTNER_PERMISSIONS } from '@/types/partner';
import { logger } from '@/utils/logger';

const prisma = new PrismaClient();
const partnerService = new PartnerService(prisma);
const exportService = new ExportService(prisma);

// Schémas de validation
const operationFiltersSchema = Joi.object({
  date_from: Joi.string().isoDate().optional(),
  date_to: Joi.string().isoDate().optional(),
  status: Joi.string().valid('pending', 'approved', 'rejected', 'cancelled').optional(),
  agency_id: Joi.number().integer().positive().optional(),
  product_id: Joi.number().integer().positive().optional(),
  amount_min: Joi.number().positive().optional(),
  amount_max: Joi.number().positive().optional(),
  client_name: Joi.string().optional(),
  reference: Joi.string().optional(),
  page: Joi.number().integer().min(1).default(1),
  limit: Joi.number().integer().min(1).max(100).default(20),
  sort: Joi.string().valid('Insert_Time', 'Amount', 'Commission', 'Status', 'ClientName').default('Insert_Time'),
  order: Joi.string().valid('asc', 'desc').default('desc')
});

const exportSchema = Joi.object({
  format: Joi.string().valid('pdf', 'excel', 'csv').required(),
  filters: operationFiltersSchema.optional(),
  columns: Joi.array().items(Joi.string()).optional(),
  template: Joi.string().optional()
});

export class OperationsController {
  /**
   * @swagger
   * /operations:
   *   get:
   *     summary: Liste des opérations du partenaire
   *     tags: [Operations]
   *     security:
   *       - BearerAuth: []
   *       - ApiKeyAuth: []
   *     parameters:
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
   *       - in: query
   *         name: status
   *         schema:
   *           type: string
   *           enum: [pending, approved, rejected, cancelled]
   *       - in: query
   *         name: agency_id
   *         schema:
   *           type: number
   *       - in: query
   *         name: product_id
   *         schema:
   *           type: number
   *       - in: query
   *         name: amount_min
   *         schema:
   *           type: number
   *       - in: query
   *         name: amount_max
   *         schema:
   *           type: number
   *       - in: query
   *         name: client_name
   *         schema:
   *           type: string
   *       - in: query
   *         name: reference
   *         schema:
   *           type: string
   *       - in: query
   *         name: page
   *         schema:
   *           type: number
   *           minimum: 1
   *           default: 1
   *       - in: query
   *         name: limit
   *         schema:
   *           type: number
   *           minimum: 1
   *           maximum: 100
   *           default: 20
   *       - in: query
   *         name: sort
   *         schema:
   *           type: string
   *           enum: [Insert_Time, Amount, Commission, Status, ClientName]
   *           default: Insert_Time
   *       - in: query
   *         name: order
   *         schema:
   *           type: string
   *           enum: [asc, desc]
   *           default: desc
   *     responses:
   *       200:
   *         description: Liste des opérations récupérée
   *         content:
   *           application/json:
   *             schema:
   *               type: object
   *               properties:
   *                 success:
   *                   type: boolean
   *                 data:
   *                   type: array
   *                   items:
   *                     $ref: '#/components/schemas/Operation'
   *                 pagination:
   *                   $ref: '#/components/schemas/Pagination'
   */
  static getOperations = asyncHandler(async (req: Request, res: Response) => {
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

    // Validation des filtres
    const { error, value } = operationFiltersSchema.validate(req.query);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const result = await partnerService.getPartnerOperations(partnerId, value);

    res.status(200).json({
      success: true,
      data: result.data,
      pagination: result.pagination
    });
  });

  /**
   * @swagger
   * /operations/{id}:
   *   get:
   *     summary: Détails d'une opération spécifique
   *     tags: [Operations]
   *     security:
   *       - BearerAuth: []
   *       - ApiKeyAuth: []
   *     parameters:
   *       - in: path
   *         name: id
   *         required: true
   *         schema:
   *           type: number
   *         description: ID de l'opération
   *     responses:
   *       200:
   *         description: Détails de l'opération
   *       404:
   *         description: Opération non trouvée
   */
  static getOperation = asyncHandler(async (req: Request, res: Response) => {
    const partnerId = req.partnerContext?.partnerId;
    const permissions = req.partnerContext?.permissions || [];
    const operationId = parseInt(req.params.id);

    if (!partnerId) {
      throw new ValidationError('Partner context not found');
    }

    if (!permissions.includes(PARTNER_PERMISSIONS.VIEW_OPERATIONS)) {
      return res.status(403).json({
        success: false,
        error: 'View operations permission required'
      });
    }

    if (!operationId || isNaN(operationId)) {
      throw new ValidationError('Invalid operation ID');
    }

    const operation = await partnerService.getPartnerOperation(partnerId, operationId);

    if (!operation) {
      throw new NotFoundError('Operation not found');
    }

    res.status(200).json({
      success: true,
      data: operation
    });
  });

  /**
   * @swagger
   * /operations/stats:
   *   get:
   *     summary: Statistiques des opérations
   *     tags: [Operations]
   *     security:
   *       - BearerAuth: []
   *       - ApiKeyAuth: []
   *     parameters:
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
   *         description: Statistiques des opérations
   */
  static getOperationStats = asyncHandler(async (req: Request, res: Response) => {
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

    // Construction de la plage de dates
    let dateRange;
    if (req.query.date_from || req.query.date_to) {
      dateRange = {
        start: req.query.date_from ? new Date(req.query.date_from as string) : new Date(0),
        end: req.query.date_to ? new Date(req.query.date_to as string) : new Date()
      };
    }

    const [stats, analytics] = await Promise.all([
      partnerService.getPartnerStats(partnerId, dateRange),
      dateRange ? partnerService.getPartnerAnalytics(partnerId, dateRange) : null
    ]);

    const result = {
      ...stats,
      ...(analytics && { analytics })
    };

    res.status(200).json({
      success: true,
      data: result
    });
  });

  /**
   * @swagger
   * /operations/export:
   *   post:
   *     summary: Exporter les opérations
   *     tags: [Operations]
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
   *             properties:
   *               format:
   *                 type: string
   *                 enum: [pdf, excel, csv]
   *               filters:
   *                 type: object
   *                 description: Filtres à appliquer aux opérations
   *               columns:
   *                 type: array
   *                 items:
   *                   type: string
   *                 description: Colonnes à inclure dans l'export
   *               template:
   *                 type: string
   *                 description: Template à utiliser pour l'export
   *     responses:
   *       200:
   *         description: Fichier d'export généré
   *         content:
   *           application/octet-stream:
   *             schema:
   *               type: string
   *               format: binary
   */
  static exportOperations = asyncHandler(async (req: Request, res: Response) => {
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

    // Validation des données d'export
    const { error, value } = exportSchema.validate(req.body);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const { format, filters = {}, columns, template } = value;

    // Récupération des opérations à exporter
    const operations = await partnerService.getPartnerOperations(partnerId, {
      ...filters,
      limit: 10000 // Limite élevée pour l'export
    });

    // Génération du fichier d'export
    const exportResult = await exportService.exportOperations(
      operations.data,
      { format, columns, template }
    );

    // Log de l'export
    logger.info('Operations export generated', {
      partnerId,
      userId: req.partnerContext?.userId,
      format,
      operationsCount: operations.data.length
    });

    // Configuration de la réponse selon le format
    const filename = `operations_${partnerId}_${new Date().toISOString().split('T')[0]}.${format}`;
    
    res.setHeader('Content-Disposition', `attachment; filename="${filename}"`);
    
    switch (format) {
      case 'pdf':
        res.setHeader('Content-Type', 'application/pdf');
        break;
      case 'excel':
        res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        break;
      case 'csv':
        res.setHeader('Content-Type', 'text/csv');
        break;
    }

    res.send(exportResult.buffer);
  });

  /**
   * @swagger
   * /operations/summary:
   *   get:
   *     summary: Résumé des opérations par période
   *     tags: [Operations]
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
   *     responses:
   *       200:
   *         description: Résumé des opérations
   */
  static getOperationsSummary = asyncHandler(async (req: Request, res: Response) => {
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

    const period = req.query.period as string || 'month';
    
    // Calcul des plages de dates
    const now = new Date();
    const dateRanges = {
      current: OperationsController.getDateRangeForPeriod(period),
      previous: OperationsController.getPreviousDateRange(period)
    };

    // Récupération des statistiques pour les deux périodes
    const [currentStats, previousStats] = await Promise.all([
      partnerService.getPartnerStats(partnerId, dateRanges.current),
      partnerService.getPartnerStats(partnerId, dateRanges.previous)
    ]);

    // Calcul des variations
    const summary = {
      period,
      current_period: {
        ...currentStats,
        date_range: dateRanges.current
      },
      previous_period: {
        ...previousStats,
        date_range: dateRanges.previous
      },
      variations: {
        operations: OperationsController.calculateVariation(
          previousStats.operations_count,
          currentStats.operations_count
        ),
        volume: OperationsController.calculateVariation(
          previousStats.total_volume,
          currentStats.total_volume
        ),
        commissions: OperationsController.calculateVariation(
          previousStats.commissions,
          currentStats.commissions
        )
      }
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

  private static getPreviousDateRange(period: string): { start: Date; end: Date } {
    const currentRange = OperationsController.getDateRangeForPeriod(period);
    const duration = currentRange.end.getTime() - currentRange.start.getTime();
    
    return {
      start: new Date(currentRange.start.getTime() - duration),
      end: new Date(currentRange.end.getTime() - duration)
    };
  }

  private static calculateVariation(previous: number, current: number): {
    absolute: number;
    percentage: number;
    trend: 'up' | 'down' | 'stable';
  } {
    const absolute = current - previous;
    const percentage = previous === 0 ? (current > 0 ? 100 : 0) : (absolute / previous) * 100;
    
    let trend: 'up' | 'down' | 'stable' = 'stable';
    if (percentage > 0) trend = 'up';
    else if (percentage < 0) trend = 'down';

    return {
      absolute,
      percentage: Math.round(percentage * 100) / 100,
      trend
    };
  }
}
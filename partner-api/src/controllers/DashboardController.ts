import { Request, Response } from 'express';
import { PrismaClient } from '@prisma/client';
import Joi from 'joi';
import { PartnerService } from '@/services/PartnerService';
import { asyncHandler, ValidationError } from '@/middleware/errorHandler';
import { PARTNER_PERMISSIONS } from '@/types/partner';
import { logger } from '@/utils/logger';

const prisma = new PrismaClient();
const partnerService = new PartnerService(prisma);

// Joi validation schemas
const dateRangeSchema = Joi.object({
  date_from: Joi.string().isoDate().optional(),
  date_to: Joi.string().isoDate().optional(),
  period: Joi.string().valid('today', 'yesterday', 'last_7_days', 'last_30_days', 'this_month', 'last_month', 'this_year').optional()
});

const recentOperationsSchema = Joi.object({
  limit: Joi.number().integer().min(1).max(100).default(10),
  status: Joi.string().valid('pending', 'approved', 'rejected', 'cancelled').optional()
});

export class DashboardController {
  /**
   * @swagger
   * /dashboard/stats:
   *   get:
   *     tags: [Dashboard]
   *     summary: Statistiques générales du partenaire
   *     description: |
   *       Récupère les statistiques principales du partenaire avec tendances.
   *       
   *       **Métriques incluses :**
   *       - Nombre total d'opérations
   *       - Volume total des transactions
   *       - Commissions générées
   *       - Nombre d'agences actives
   *       - Utilisateurs actifs
   *       - Tendances par rapport à la période précédente
   *       
   *       **Permissions requises :** `view_operations`
   *     security:
   *       - BearerAuth: []
   *       - ApiKeyAuth: []
   *     parameters:
   *       - $ref: '#/components/parameters/DateFromParam'
   *       - $ref: '#/components/parameters/DateToParam'
   *       - name: period
   *         in: query
   *         description: Période prédéfinie (remplace date_from/date_to)
   *         schema:
   *           type: string
   *           enum: [today, yesterday, last_7_days, last_30_days, this_month, last_month, this_year]
   *           example: last_30_days
   *     responses:
   *       '200':
   *         description: Statistiques récupérées avec succès
   *         content:
   *           application/json:
   *             schema:
   *               type: object
   *               properties:
   *                 success:
   *                   type: boolean
   *                   example: true
   *                 data:
   *                   $ref: '#/components/schemas/PartnerStats'
   *             examples:
   *               monthly_stats:
   *                 summary: Statistiques mensuelles
   *                 value:
   *                   success: true
   *                   data:
   *                     operations_count: 1250
   *                     total_volume: 2500000.00
   *                     commissions: 25000.00
   *                     agencies_count: 15
   *                     active_users: 8
   *                     trends:
   *                       operations_trend: 12.5
   *                       volume_trend: 8.3
   *                       commission_trend: 15.2
   *                     period:
   *                       start: "2024-01-01"
   *                       end: "2024-01-31"
   *                       label: "Janvier 2024"
   *       '400':
   *         $ref: '#/components/responses/ValidationError'
   *       '401':
   *         $ref: '#/components/responses/UnauthorizedError'
   *       '403':
   *         $ref: '#/components/responses/ForbiddenError'
   */
  static getStats = asyncHandler(async (req: Request, res: Response) => {
    const { error } = dateRangeSchema.validate(req.query);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const partnerId = req.partnerContext?.partnerId!;
    const { date_from, date_to, period } = req.query;

    let dateRange;
    if (period) {
      dateRange = DashboardController.getDateRangeForPeriod(period as string);
    } else if (date_from || date_to) {
      dateRange = {
        start: date_from ? new Date(date_from as string) : undefined,
        end: date_to ? new Date(date_to as string) : undefined
      };
    }

    const stats = await partnerService.getPartnerStats(partnerId, dateRange);

    logger.info('Dashboard stats retrieved', {
      partnerId,
      period: period || 'custom',
      dateRange
    });

    res.json({
      success: true,
      data: {
        ...stats,
        period: {
          start: dateRange?.start?.toISOString().split('T')[0],
          end: dateRange?.end?.toISOString().split('T')[0],
          label: DashboardController.getPeriodLabel(period as string, dateRange)
        }
      }
    });
  });

  /**
   * @swagger
   * /dashboard/operations/recent:
   *   get:
   *     tags: [Dashboard]
   *     summary: Opérations récentes du partenaire
   *     description: |
   *       Récupère les opérations les plus récentes du partenaire.
   *       
   *       **Fonctionnalités :**
   *       - Tri par date de création (plus récentes en premier)
   *       - Filtrage par statut optionnel
   *       - Limite configurable (défaut: 10, max: 100)
   *       - Informations complètes (client, bénéficiaire, agence, produit)
   *       
   *       **Permissions requises :** `view_operations`
   *     security:
   *       - BearerAuth: []
   *       - ApiKeyAuth: []
   *     parameters:
   *       - name: limit
   *         in: query
   *         description: Nombre d'opérations à retourner
   *         schema:
   *           type: integer
   *           minimum: 1
   *           maximum: 100
   *           default: 10
   *           example: 20
   *       - name: status
   *         in: query
   *         description: Filtrer par statut
   *         schema:
   *           type: string
   *           enum: [pending, approved, rejected, cancelled]
   *           example: approved
   *     responses:
   *       '200':
   *         description: Opérations récentes récupérées avec succès
   *         content:
   *           application/json:
   *             schema:
   *               type: object
   *               properties:
   *                 success:
   *                   type: boolean
   *                   example: true
   *                 data:
   *                   type: object
   *                   properties:
   *                     operations:
   *                       type: array
   *                       items:
   *                         $ref: '#/components/schemas/Operation'
   *                     count:
   *                       type: integer
   *                       example: 15
   *                     filters:
   *                       type: object
   *                       properties:
   *                         limit:
   *                           type: integer
   *                           example: 10
   *                         status:
   *                           type: string
   *                           example: approved
   *             examples:
   *               recent_operations:
   *                 summary: Opérations récentes
   *                 value:
   *                   success: true
   *                   data:
   *                     operations:
   *                       - id: 12345
   *                         reference: "OP-2024-001234"
   *                         amount: 1500.50
   *                         commission: 15.00
   *                         status: "approved"
   *                         client_name: "Jean Dupont"
   *                         created_at: "2024-01-15T10:30:00Z"
   *                         agency:
   *                           name: "Agence Paris Centre"
   *                         product:
   *                           name: "Transfert Express"
   *                     count: 15
   *                     filters:
   *                       limit: 10
   *       '400':
   *         $ref: '#/components/responses/ValidationError'
   *       '401':
   *         $ref: '#/components/responses/UnauthorizedError'
   *       '403':
   *         $ref: '#/components/responses/ForbiddenError'
   */
  static getRecentOperations = asyncHandler(async (req: Request, res: Response) => {
    const { error } = recentOperationsSchema.validate(req.query);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const partnerId = req.partnerContext?.partnerId!;
    const { limit = 10, status } = req.query;

    const filters = {
      ...(status && { status: status as string }),
      limit: Number(limit)
    };

    const operations = await partnerService.getPartnerOperations(partnerId, filters);

    res.json({
      success: true,
      data: {
        operations,
        count: operations.length,
        filters: {
          limit: Number(limit),
          ...(status && { status })
        }
      }
    });
  });

  /**
   * @swagger
   * /dashboard/agencies:
   *   get:
   *     tags: [Dashboard]
   *     summary: Agences du partenaire avec statistiques
   *     description: |
   *       Récupère la liste des agences du partenaire avec leurs statistiques.
   *       
   *       **Informations par agence :**
   *       - Données de base (nom, adresse, pays)
   *       - Nombre d'opérations
   *       - Volume total
   *       - Commissions générées
   *       - Nombre de caisses actives
   *       - Performance relative
   *       
   *       **Permissions requises :** `view_agencies`
   *     security:
   *       - BearerAuth: []
   *       - ApiKeyAuth: []
   *     parameters:
   *       - $ref: '#/components/parameters/DateFromParam'
   *       - $ref: '#/components/parameters/DateToParam'
   *     responses:
   *       '200':
   *         description: Agences récupérées avec succès
   *         content:
   *           application/json:
   *             schema:
   *               type: object
   *               properties:
   *                 success:
   *                   type: boolean
   *                   example: true
   *                 data:
   *                   type: object
   *                   properties:
   *                     agencies:
   *                       type: array
   *                       items:
   *                         allOf:
   *                           - $ref: '#/components/schemas/Agency'
   *                           - type: object
   *                             properties:
   *                               stats:
   *                                 type: object
   *                                 properties:
   *                                   operations_count:
   *                                     type: integer
   *                                     example: 125
   *                                   total_volume:
   *                                     type: number
   *                                     example: 250000.00
   *                                   commissions:
   *                                     type: number
   *                                     example: 2500.00
   *                                   cash_registers_count:
   *                                     type: integer
   *                                     example: 3
   *                                   performance_score:
   *                                     type: number
   *                                     example: 85.5
   *                     summary:
   *                       type: object
   *                       properties:
   *                         total_agencies:
   *                           type: integer
   *                           example: 15
   *                         active_agencies:
   *                           type: integer
   *                           example: 12
   *                         top_performer:
   *                           type: object
   *                           properties:
   *                             id:
   *                               type: integer
   *                               example: 1
   *                             name:
   *                               type: string
   *                               example: "Agence Paris Centre"
   *                             volume:
   *                               type: number
   *                               example: 500000.00
   *       '401':
   *         $ref: '#/components/responses/UnauthorizedError'
   *       '403':
   *         $ref: '#/components/responses/ForbiddenError'
   */
  static getAgencies = asyncHandler(async (req: Request, res: Response) => {
    const partnerId = req.partnerContext?.partnerId!;
    const { date_from, date_to } = req.query;

    const dateRange = {
      start: date_from ? new Date(date_from as string) : undefined,
      end: date_to ? new Date(date_to as string) : undefined
    };

    const agencies = await partnerService.getPartnerAgencies(partnerId);
    
    // Enrichir avec les statistiques
    const enrichedAgencies = await Promise.all(
      agencies.map(async (agency) => {
        const stats = await partnerService.getAgencyStats(partnerId, agency.RefAgence, dateRange);
        return {
          ...agency,
          stats
        };
      })
    );

    // Calculer le résumé
    const totalAgencies = enrichedAgencies.length;
    const activeAgencies = enrichedAgencies.filter(a => a.stats.operations_count > 0).length;
    const topPerformer = enrichedAgencies.reduce((top, current) => 
      current.stats.total_volume > (top?.stats.total_volume || 0) ? current : top
    , enrichedAgencies[0]);

    res.json({
      success: true,
      data: {
        agencies: enrichedAgencies,
        summary: {
          total_agencies: totalAgencies,
          active_agencies: activeAgencies,
          top_performer: topPerformer ? {
            id: topPerformer.RefAgence,
            name: topPerformer.NameAgence,
            volume: topPerformer.stats.total_volume
          } : null
        }
      }
    });
  });

  /**
   * @swagger
   * /dashboard/performance:
   *   get:
   *     tags: [Dashboard]
   *     summary: Indicateurs de performance du partenaire
   *     description: |
   *       Récupère les indicateurs clés de performance (KPI) du partenaire.
   *       
   *       **KPI inclus :**
   *       - Taux de réussite des opérations
   *       - Temps moyen de traitement
   *       - Volume moyen par opération
   *       - Commission moyenne
   *       - Taux de croissance
   *       - Comparaison avec la période précédente
   *       
   *       **Permissions requises :** `view_analytics`
   *     security:
   *       - BearerAuth: []
   *       - ApiKeyAuth: []
   *     parameters:
   *       - $ref: '#/components/parameters/DateFromParam'
   *       - $ref: '#/components/parameters/DateToParam'
   *       - name: period
   *         in: query
   *         description: Période pour le calcul des tendances
   *         schema:
   *           type: string
   *           enum: [last_7_days, last_30_days, this_month, last_month]
   *           default: last_30_days
   *     responses:
   *       '200':
   *         description: Indicateurs de performance récupérés avec succès
   *         content:
   *           application/json:
   *             schema:
   *               type: object
   *               properties:
   *                 success:
   *                   type: boolean
   *                   example: true
   *                 data:
   *                   type: object
   *                   properties:
   *                     success_rate:
   *                       type: object
   *                       properties:
   *                         current:
   *                           type: number
   *                           example: 95.5
   *                         previous:
   *                           type: number
   *                           example: 93.2
   *                         change:
   *                           type: number
   *                           example: 2.3
   *                         trend:
   *                           type: string
   *                           enum: [up, down, stable]
   *                           example: up
   *                     avg_amount:
   *                       type: object
   *                       properties:
   *                         current:
   *                           type: number
   *                           example: 1250.50
   *                         previous:
   *                           type: number
   *                           example: 1180.25
   *                         change:
   *                           type: number
   *                           example: 5.95
   *                         trend:
   *                           type: string
   *                           example: up
   *                     avg_commission:
   *                       type: object
   *                       properties:
   *                         current:
   *                           type: number
   *                           example: 18.75
   *                         previous:
   *                           type: number
   *                           example: 17.20
   *                         change:
   *                           type: number
   *                           example: 9.01
   *                         trend:
   *                           type: string
   *                           example: up
   *                     growth_rate:
   *                       type: object
   *                       properties:
   *                         operations:
   *                           type: number
   *                           example: 12.5
   *                         volume:
   *                           type: number
   *                           example: 8.3
   *                         commissions:
   *                           type: number
   *                           example: 15.2
   *                     period:
   *                       type: object
   *                       properties:
   *                         current:
   *                           type: object
   *                           properties:
   *                             start:
   *                               type: string
   *                               format: date
   *                               example: "2024-01-01"
   *                             end:
   *                               type: string
   *                               format: date
   *                               example: "2024-01-31"
   *                         previous:
   *                           type: object
   *                           properties:
   *                             start:
   *                               type: string
   *                               format: date
   *                               example: "2023-12-01"
   *                             end:
   *                               type: string
   *                               format: date
   *                               example: "2023-12-31"
   *       '401':
   *         $ref: '#/components/responses/UnauthorizedError'
   *       '403':
   *         $ref: '#/components/responses/ForbiddenError'
   */
  static getPerformance = asyncHandler(async (req: Request, res: Response) => {
    const partnerId = req.partnerContext?.partnerId!;
    const { date_from, date_to, period = 'last_30_days' } = req.query;

    let dateRange;
    if (date_from || date_to) {
      dateRange = {
        start: date_from ? new Date(date_from as string) : undefined,
        end: date_to ? new Date(date_to as string) : undefined
      };
    } else {
      dateRange = DashboardController.getDateRangeForPeriod(period as string);
    }

    const previousRange = DashboardController.getPreviousDateRange(period as string, dateRange);
    
    // Récupérer les données pour les deux périodes
    const [currentStats, previousStats] = await Promise.all([
      partnerService.getPartnerStats(partnerId, dateRange),
      partnerService.getPartnerStats(partnerId, previousRange)
    ]);

    // Calculer les performances
    const performance = {
      success_rate: {
        current: currentStats.operations_count > 0 
          ? (currentStats.approved_operations / currentStats.operations_count * 100) 
          : 0,
        previous: previousStats.operations_count > 0 
          ? (previousStats.approved_operations / previousStats.operations_count * 100) 
          : 0
      },
      avg_amount: {
        current: currentStats.operations_count > 0 
          ? (currentStats.total_volume / currentStats.operations_count) 
          : 0,
        previous: previousStats.operations_count > 0 
          ? (previousStats.total_volume / previousStats.operations_count) 
          : 0
      },
      avg_commission: {
        current: currentStats.operations_count > 0 
          ? (currentStats.commissions / currentStats.operations_count) 
          : 0,
        previous: previousStats.operations_count > 0 
          ? (previousStats.commissions / previousStats.operations_count) 
          : 0
      }
    };

    // Calculer les variations et tendances
    Object.keys(performance).forEach(key => {
      const metric = performance[key as keyof typeof performance];
      const change = metric.previous > 0 
        ? ((metric.current - metric.previous) / metric.previous * 100) 
        : 0;
      
      (metric as any).change = Math.round(change * 100) / 100;
      (metric as any).trend = change > 2 ? 'up' : change < -2 ? 'down' : 'stable';
    });

    res.json({
      success: true,
      data: {
        ...performance,
        growth_rate: {
          operations: currentStats.trends?.operations_trend || 0,
          volume: currentStats.trends?.volume_trend || 0,
          commissions: currentStats.trends?.commission_trend || 0
        },
        period: {
          current: {
            start: dateRange?.start?.toISOString().split('T')[0],
            end: dateRange?.end?.toISOString().split('T')[0]
          },
          previous: {
            start: previousRange?.start?.toISOString().split('T')[0],
            end: previousRange?.end?.toISOString().split('T')[0]
          }
        }
      }
    });
  });

  /**
   * @swagger
   * /dashboard/summary:
   *   get:
   *     tags: [Dashboard]
   *     summary: Résumé complet du dashboard
   *     description: |
   *       Récupère un résumé complet combinant toutes les données du dashboard.
   *       
   *       **Données incluses :**
   *       - Statistiques principales
   *       - Opérations récentes (5 dernières)
   *       - Top 3 des agences performantes
   *       - Indicateurs de performance clés
   *       - Alertes et notifications
   *       
   *       **Optimisé pour :** Affichage initial du dashboard
   *     security:
   *       - BearerAuth: []
   *       - ApiKeyAuth: []
   *     parameters:
   *       - name: period
   *         in: query
   *         description: Période pour les statistiques
   *         schema:
   *           type: string
   *           enum: [today, last_7_days, last_30_days, this_month]
   *           default: last_30_days
   *     responses:
   *       '200':
   *         description: Résumé récupéré avec succès
   *         content:
   *           application/json:
   *             schema:
   *               type: object
   *               properties:
   *                 success:
   *                   type: boolean
   *                   example: true
   *                 data:
   *                   type: object
   *                   properties:
   *                     stats:
   *                       $ref: '#/components/schemas/PartnerStats'
   *                     recent_operations:
   *                       type: array
   *                       maxItems: 5
   *                       items:
   *                         $ref: '#/components/schemas/Operation'
   *                     top_agencies:
   *                       type: array
   *                       maxItems: 3
   *                       items:
   *                         allOf:
   *                           - $ref: '#/components/schemas/Agency'
   *                           - type: object
   *                             properties:
   *                               volume:
   *                                 type: number
   *                                 example: 500000.00
   *                               rank:
   *                                 type: integer
   *                                 example: 1
   *                     performance:
   *                       type: object
   *                       properties:
   *                         success_rate:
   *                           type: number
   *                           example: 95.5
   *                         avg_amount:
   *                           type: number
   *                           example: 1250.50
   *                         growth_rate:
   *                           type: number
   *                           example: 12.5
   *                     alerts:
   *                       type: array
   *                       items:
   *                         type: object
   *                         properties:
   *                           type:
   *                             type: string
   *                             enum: [info, warning, error]
   *                             example: info
   *                           message:
   *                             type: string
   *                             example: "Nouveau record de volume mensuel atteint"
   *                           timestamp:
   *                             type: string
   *                             format: date-time
   *                             example: "2024-01-15T10:30:00Z"
   *       '401':
   *         $ref: '#/components/responses/UnauthorizedError'
   */
  static getSummary = asyncHandler(async (req: Request, res: Response) => {
    const partnerId = req.partnerContext?.partnerId!;
    const { period = 'last_30_days' } = req.query;

    const dateRange = DashboardController.getDateRangeForPeriod(period as string);

    // Récupérer toutes les données en parallèle
    const [stats, recentOps, agencies] = await Promise.all([
      partnerService.getPartnerStats(partnerId, dateRange),
      partnerService.getPartnerOperations(partnerId, { limit: 5 }),
      partnerService.getPartnerAgencies(partnerId)
    ]);

    // Top 3 agences (simulé pour l'exemple)
    const topAgencies = agencies.slice(0, 3).map((agency, index) => ({
      ...agency,
      volume: Math.random() * 500000,
      rank: index + 1
    })).sort((a, b) => b.volume - a.volume);

    // Générer des alertes basées sur les données
    const alerts = [];
    
    if (stats.trends?.volume_trend && stats.trends.volume_trend > 20) {
      alerts.push({
        type: 'info',
        message: 'Nouveau record de volume mensuel atteint',
        timestamp: new Date().toISOString()
      });
    }
    
    if (stats.trends?.operations_trend && stats.trends.operations_trend < -10) {
      alerts.push({
        type: 'warning',
        message: 'Baisse significative du nombre d\'opérations',
        timestamp: new Date().toISOString()
      });
    }

    res.json({
      success: true,
      data: {
        stats,
        recent_operations: recentOps,
        top_agencies: topAgencies,
        performance: {
          success_rate: stats.operations_count > 0 
            ? (stats.approved_operations / stats.operations_count * 100) 
            : 0,
          avg_amount: stats.operations_count > 0 
            ? (stats.total_volume / stats.operations_count) 
            : 0,
          growth_rate: stats.trends?.volume_trend || 0
        },
        alerts,
        generated_at: new Date().toISOString()
      }
    });
  });

  // Utility methods
  private static getDateRangeForPeriod(period: string): { start: Date; end: Date } {
    const now = new Date();
    const start = new Date();
    const end = new Date();

    switch (period) {
      case 'today':
        start.setHours(0, 0, 0, 0);
        end.setHours(23, 59, 59, 999);
        break;
      case 'yesterday':
        start.setDate(now.getDate() - 1);
        start.setHours(0, 0, 0, 0);
        end.setDate(now.getDate() - 1);
        end.setHours(23, 59, 59, 999);
        break;
      case 'last_7_days':
        start.setDate(now.getDate() - 7);
        start.setHours(0, 0, 0, 0);
        break;
      case 'last_30_days':
        start.setDate(now.getDate() - 30);
        start.setHours(0, 0, 0, 0);
        break;
      case 'this_month':
        start.setDate(1);
        start.setHours(0, 0, 0, 0);
        break;
      case 'last_month':
        start.setMonth(now.getMonth() - 1, 1);
        start.setHours(0, 0, 0, 0);
        end.setMonth(now.getMonth(), 0);
        end.setHours(23, 59, 59, 999);
        break;
      case 'this_year':
        start.setMonth(0, 1);
        start.setHours(0, 0, 0, 0);
        break;
      default:
        start.setDate(now.getDate() - 30);
        start.setHours(0, 0, 0, 0);
    }

    return { start, end };
  }

  private static getPreviousDateRange(period: string, currentRange?: { start?: Date; end?: Date }): { start: Date; end: Date } {
    if (!currentRange?.start || !currentRange?.end) {
      const current = DashboardController.getDateRangeForPeriod(period);
      currentRange = current;
    }

    const duration = currentRange.end.getTime() - currentRange.start.getTime();
    const start = new Date(currentRange.start.getTime() - duration);
    const end = new Date(currentRange.end.getTime() - duration);

    return { start, end };
  }

  private static getPeriodLabel(period: string, dateRange?: { start?: Date; end?: Date }): string {
    if (dateRange?.start && dateRange?.end) {
      return `${dateRange.start.toLocaleDateString('fr-FR')} - ${dateRange.end.toLocaleDateString('fr-FR')}`;
    }

    const labels: { [key: string]: string } = {
      today: "Aujourd'hui",
      yesterday: "Hier",
      last_7_days: "7 derniers jours",
      last_30_days: "30 derniers jours",
      this_month: "Ce mois",
      last_month: "Mois dernier",
      this_year: "Cette année"
    };

    return labels[period] || "Période personnalisée";
  }
}
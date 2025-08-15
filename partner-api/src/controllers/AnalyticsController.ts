import { Request, Response } from 'express';
import { PrismaClient } from '@prisma/client';
import Joi from 'joi';
import { asyncHandler, ValidationError } from '../middleware/errorHandler';
import { logger } from '../utils/logger';
import * as ExcelJS from 'exceljs';

const prisma = new PrismaClient();

// Validation schemas
const periodSchema = Joi.object({
  period: Joi.string().valid('7d', '30d', '90d', '1y').default('30d')
});

const kpiFiltersSchema = Joi.object({
  period: Joi.string().valid('7d', '30d', '90d', '1y').default('30d'),
  agencyId: Joi.number().integer().optional(),
  productId: Joi.number().integer().optional()
});

export class AnalyticsController {
  /**
   * Get KPIs for analytics dashboard
   */
  static getKPIs = asyncHandler(async (req: Request, res: Response) => {
    const { error, value } = kpiFiltersSchema.validate(req.query);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const user = (req as any).user;
    const { period, agencyId, productId } = value;

    // Calculate date range
    const endDate = new Date();
    const startDate = new Date();
    
    switch (period) {
      case '7d':
        startDate.setDate(endDate.getDate() - 7);
        break;
      case '30d':
        startDate.setDate(endDate.getDate() - 30);
        break;
      case '90d':
        startDate.setDate(endDate.getDate() - 90);
        break;
      case '1y':
        startDate.setFullYear(endDate.getFullYear() - 1);
        break;
    }

    // Build where clause
    const whereClause: any = {
      RefPays: user.RefPays,
      Insert_Time: {
        gte: startDate,
        lte: endDate
      }
    };

    if (agencyId) {
      whereClause.TbleCaisse = {
        RefAgency: agencyId
      };
    }

    if (productId) {
      whereClause.RefProduit = productId;
    }

    // Get operations data
    const [operations, totalVolume, successfulOps] = await Promise.all([
      prisma.tbleOperations.count({ where: whereClause }),
      prisma.tbleOperations.aggregate({
        where: whereClause,
        _sum: { MontantVersement: true }
      }),
      prisma.tbleOperations.count({
        where: {
          ...whereClause,
          Status: 1 // Validated operations
        }
      })
    ]);

    const kpis = {
      totalVolume: totalVolume._sum.MontantVersement || 0,
      totalOperations: operations,
      averageAmount: operations > 0 ? (totalVolume._sum.MontantVersement || 0) / operations : 0,
      successRate: operations > 0 ? Math.round((successfulOps / operations) * 100) : 0
    };

    res.json(kpis);
  });

  /**
   * Get volume chart data
   */
  static getVolumeChart = asyncHandler(async (req: Request, res: Response) => {
    const { error, value } = periodSchema.validate(req.query);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const user = (req as any).user;
    const { period } = value;

    // Calculate date range and grouping
    const endDate = new Date();
    const startDate = new Date();
    let dateFormat = '%Y-%m-%d';
    let groupBy = 'day';

    switch (period) {
      case '7d':
        startDate.setDate(endDate.getDate() - 7);
        break;
      case '30d':
        startDate.setDate(endDate.getDate() - 30);
        break;
      case '90d':
        startDate.setDate(endDate.getDate() - 90);
        dateFormat = '%Y-%m-%d';
        break;
      case '1y':
        startDate.setFullYear(endDate.getFullYear() - 1);
        dateFormat = '%Y-%m';
        groupBy = 'month';
        break;
    }

    // Raw SQL query for better performance with date grouping
    const volumeData = await prisma.$queryRaw`
      SELECT 
        DATE_FORMAT(Insert_Time, ${dateFormat}) as period,
        SUM(MontantVersement) as volume,
        COUNT(*) as operations
      FROM TbleOperations 
      WHERE RefPays = ${user.RefPays}
        AND Insert_Time >= ${startDate}
        AND Insert_Time <= ${endDate}
      GROUP BY DATE_FORMAT(Insert_Time, ${dateFormat})
      ORDER BY period ASC
    ` as any[];

    const chartData = {
      labels: volumeData.map(item => item.period),
      datasets: [
        {
          label: 'Volume (€)',
          data: volumeData.map(item => parseFloat(item.volume) || 0),
          borderColor: 'rgb(59, 130, 246)',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          tension: 0.1
        }
      ]
    };

    res.json(chartData);
  });

  /**
   * Get operations type distribution chart
   */
  static getTypeChart = asyncHandler(async (req: Request, res: Response) => {
    const { error, value } = periodSchema.validate(req.query);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const user = (req as any).user;
    const { period } = value;

    const endDate = new Date();
    const startDate = new Date();
    
    switch (period) {
      case '7d':
        startDate.setDate(endDate.getDate() - 7);
        break;
      case '30d':
        startDate.setDate(endDate.getDate() - 30);
        break;
      case '90d':
        startDate.setDate(endDate.getDate() - 90);
        break;
      case '1y':
        startDate.setFullYear(endDate.getFullYear() - 1);
        break;
    }

    const typeData = await prisma.$queryRaw`
      SELECT 
        RefType,
        COUNT(*) as count,
        SUM(MontantVersement) as volume
      FROM TbleOperations 
      WHERE RefPays = ${user.RefPays}
        AND Insert_Time >= ${startDate}
        AND Insert_Time <= ${endDate}
      GROUP BY RefType
      ORDER BY count DESC
    ` as any[];

    const typeLabels = {
      1: 'Dépôts',
      2: 'Retraits',
      3: 'Transferts entrants',
      4: 'Transferts sortants'
    };

    const chartData = {
      labels: typeData.map(item => typeLabels[item.RefType as keyof typeof typeLabels] || `Type ${item.RefType}`),
      datasets: [
        {
          data: typeData.map(item => parseInt(item.count)),
          backgroundColor: [
            'rgba(34, 197, 94, 0.8)',
            'rgba(239, 68, 68, 0.8)',
            'rgba(59, 130, 246, 0.8)',
            'rgba(245, 158, 11, 0.8)'
          ],
          borderColor: [
            'rgba(34, 197, 94, 1)',
            'rgba(239, 68, 68, 1)',
            'rgba(59, 130, 246, 1)',
            'rgba(245, 158, 11, 1)'
          ],
          borderWidth: 1
        }
      ]
    };

    res.json(chartData);
  });

  /**
   * Get analytics report data
   */
  static getReport = asyncHandler(async (req: Request, res: Response) => {
    const { error, value } = periodSchema.validate(req.query);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const user = (req as any).user;
    const { period } = value;

    const endDate = new Date();
    const startDate = new Date();
    
    switch (period) {
      case '7d':
        startDate.setDate(endDate.getDate() - 7);
        break;
      case '30d':
        startDate.setDate(endDate.getDate() - 30);
        break;
      case '90d':
        startDate.setDate(endDate.getDate() - 90);
        break;
      case '1y':
        startDate.setFullYear(endDate.getFullYear() - 1);
        break;
    }

    // Get daily aggregated data
    const reportData = await prisma.$queryRaw`
      SELECT 
        DATE(Insert_Time) as period,
        SUM(MontantVersement) as volume,
        COUNT(*) as operations,
        COUNT(CASE WHEN Status = 1 THEN 1 END) as successful_operations,
        ROUND(COUNT(CASE WHEN Status = 1 THEN 1 END) * 100.0 / COUNT(*), 1) as success_rate
      FROM TbleOperations 
      WHERE RefPays = ${user.RefPays}
        AND Insert_Time >= ${startDate}
        AND Insert_Time <= ${endDate}
      GROUP BY DATE(Insert_Time)
      ORDER BY period DESC
      LIMIT 30
    ` as any[];

    const formattedData = reportData.map((row, index) => {
      const prevRow = reportData[index + 1];
      let trend = 'stable';
      let trendValue = 0;

      if (prevRow) {
        const currentVolume = parseFloat(row.volume) || 0;
        const prevVolume = parseFloat(prevRow.volume) || 0;
        
        if (prevVolume > 0) {
          trendValue = Math.round(((currentVolume - prevVolume) / prevVolume) * 100);
          trend = trendValue > 0 ? 'up' : trendValue < 0 ? 'down' : 'stable';
        }
      }

      return {
        period: row.period,
        volume: parseFloat(row.volume) || 0,
        operations: parseInt(row.operations),
        successRate: parseFloat(row.success_rate) || 0,
        trend,
        trendValue: Math.abs(trendValue)
      };
    });

    res.json(formattedData);
  });

  /**
   * Export analytics data to Excel
   */
  static exportData = asyncHandler(async (req: Request, res: Response) => {
    const { error, value } = periodSchema.validate(req.query);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const user = (req as any).user;
    const { period } = value;

    const endDate = new Date();
    const startDate = new Date();
    
    switch (period) {
      case '7d':
        startDate.setDate(endDate.getDate() - 7);
        break;
      case '30d':
        startDate.setDate(endDate.getDate() - 30);
        break;
      case '90d':
        startDate.setDate(endDate.getDate() - 90);
        break;
      case '1y':
        startDate.setFullYear(endDate.getFullYear() - 1);
        break;
    }

    // Get detailed operations data
    const operations = await prisma.tbleOperations.findMany({
      where: {
        RefPays: user.RefPays,
        Insert_Time: {
          gte: startDate,
          lte: endDate
        }
      },
      include: {
        TbleCaisse: {
          include: {
            TbleAgency: true
          }
        },
        TbleUsers: true,
        TbleProduit: true
      },
      orderBy: {
        Insert_Time: 'desc'
      }
    });

    // Create Excel workbook
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Analytics Export');

    // Add headers
    worksheet.columns = [
      { header: 'Date', key: 'date', width: 15 },
      { header: 'Référence', key: 'reference', width: 20 },
      { header: 'Type', key: 'type', width: 15 },
      { header: 'Montant', key: 'amount', width: 15 },
      { header: 'Client', key: 'client', width: 25 },
      { header: 'Caisse', key: 'caisse', width: 20 },
      { header: 'Agence', key: 'agency', width: 25 },
      { header: 'Utilisateur', key: 'user', width: 20 },
      { header: 'Produit', key: 'product', width: 20 },
      { header: 'Statut', key: 'status', width: 15 }
    ];

    // Add data
    operations.forEach(op => {
      const typeLabels = {
        1: 'Dépôt',
        2: 'Retrait',
        3: 'Transfert entrant',
        4: 'Transfert sortant'
      };

      worksheet.addRow({
        date: op.Insert_Time,
        reference: op.UniqueId,
        type: typeLabels[op.RefType as keyof typeof typeLabels] || `Type ${op.RefType}`,
        amount: op.MontantVersement,
        client: op.NameClient,
        caisse: op.TbleCaisse?.NameCaisse,
        agency: op.TbleCaisse?.TbleAgency?.NameAgency,
        user: op.TbleUsers?.Name,
        product: op.TbleProduit?.NameProduit,
        status: op.Status === 1 ? 'Validé' : op.Status === 0 ? 'En attente' : 'Annulé'
      });
    });

    // Style the header row
    worksheet.getRow(1).font = { bold: true };
    worksheet.getRow(1).fill = {
      type: 'pattern',
      pattern: 'solid',
      fgColor: { argb: 'FFE2E8F0' }
    };

    // Set response headers
    res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    res.setHeader('Content-Disposition', `attachment; filename=analytics-${period}.xlsx`);

    // Write to response
    await workbook.xlsx.write(res);
    res.end();

    logger.info(`Analytics data exported for period ${period}`, {
      userId: user.RefUser,
      partnerId: user.RefBanque,
      recordCount: operations.length
    });
  });

  /**
   * Generate analytics report
   */
  static generateReport = asyncHandler(async (req: Request, res: Response) => {
    const user = (req as any).user;
    const { period } = req.body;

    // This would typically trigger a background job to generate a comprehensive report
    // For now, we'll return a simple response
    
    logger.info(`Analytics report generation requested for period ${period}`, {
      userId: user.RefUser,
      partnerId: user.RefBanque
    });

    res.json({
      message: 'Génération du rapport en cours',
      reportId: `RPT-${Date.now()}`,
      estimatedTime: '2-3 minutes',
      status: 'processing'
    });
  });
}

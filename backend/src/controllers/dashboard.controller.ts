import { Request, Response } from 'express';
import { DashboardService } from '../services/dashboard.service';
import { logger } from '../config/logger';

export class DashboardController {
  private dashboardService: DashboardService;

  constructor() {
    this.dashboardService = new DashboardService();
  }

  getStats = async (req: Request, res: Response) => {
    try {
      if (!req.tenantContext) {
        return res.status(401).json({
          success: false,
          error: 'Authentication required',
        });
      }

      const filters = {
        countryId: req.query.countryId ? parseInt(req.query.countryId as string) : req.tenantContext.countryId,
        agencyId: req.query.agencyId ? parseInt(req.query.agencyId as string) : undefined,
        cashRegisterId: req.query.cashRegisterId ? parseInt(req.query.cashRegisterId as string) : undefined,
        dateFrom: req.query.dateFrom as string,
        dateTo: req.query.dateTo as string,
      };

      const stats = await this.dashboardService.getStats(filters, req.tenantContext);

      res.json({
        success: true,
        stats,
      });
    } catch (error: any) {
      logger.error('Get dashboard stats error:', error);
      res.status(500).json({
        success: false,
        error: error.message || 'Failed to get dashboard stats',
      });
    }
  };

  getRecentOperations = async (req: Request, res: Response) => {
    try {
      if (!req.tenantContext) {
        return res.status(401).json({
          success: false,
          error: 'Authentication required',
        });
      }

      const limit = req.query.limit ? parseInt(req.query.limit as string) : 10;
      const operations = await this.dashboardService.getRecentOperations(
        req.tenantContext,
        limit
      );

      res.json({
        success: true,
        operations,
      });
    } catch (error: any) {
      logger.error('Get recent operations error:', error);
      res.status(500).json({
        success: false,
        error: error.message || 'Failed to get recent operations',
      });
    }
  };

  getAlerts = async (req: Request, res: Response) => {
    try {
      if (!req.tenantContext) {
        return res.status(401).json({
          success: false,
          error: 'Authentication required',
        });
      }

      const alerts = await this.dashboardService.getAlerts(req.tenantContext);

      res.json({
        success: true,
        alerts,
      });
    } catch (error: any) {
      logger.error('Get alerts error:', error);
      res.status(500).json({
        success: false,
        error: error.message || 'Failed to get alerts',
      });
    }
  };

  getQuickLinks = async (req: Request, res: Response) => {
    try {
      if (!req.tenantContext) {
        return res.status(401).json({
          success: false,
          error: 'Authentication required',
        });
      }

      const quickLinks = await this.dashboardService.getQuickLinks(
        req.tenantContext.countryId
      );

      res.json({
        success: true,
        quickLinks,
      });
    } catch (error: any) {
      logger.error('Get quick links error:', error);
      res.status(500).json({
        success: false,
        error: error.message || 'Failed to get quick links',
      });
    }
  };

  getBalanceSummary = async (req: Request, res: Response) => {
    try {
      if (!req.tenantContext) {
        return res.status(401).json({
          success: false,
          error: 'Authentication required',
        });
      }

      const balanceSummary = await this.dashboardService.getBalanceSummary(
        req.tenantContext
      );

      res.json({
        success: true,
        balanceSummary,
      });
    } catch (error: any) {
      logger.error('Get balance summary error:', error);
      res.status(500).json({
        success: false,
        error: error.message || 'Failed to get balance summary',
      });
    }
  };
}
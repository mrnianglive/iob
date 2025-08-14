import { Request, Response } from 'express';
import { OperationService } from '../services/operation.service';
import { logger } from '../config/logger';

export class OperationController {
  private operationService: OperationService;

  constructor() {
    this.operationService = new OperationService();
  }

  getOperations = async (req: Request, res: Response) => {
    try {
      if (!req.user || !req.tenantContext) {
        return res.status(401).json({
          success: false,
          error: 'Authentication required',
        });
      }

      const filters = {
        cashRegisterId: req.query.cashRegisterId ? parseInt(req.query.cashRegisterId as string) : undefined,
        dateFrom: req.query.dateFrom as string,
        dateTo: req.query.dateTo as string,
        type: req.query.type as string,
        status: req.query.status as string,
        page: req.query.page ? parseInt(req.query.page as string) : 1,
        limit: req.query.limit ? parseInt(req.query.limit as string) : 50,
      };

      const result = await this.operationService.getOperations(
        filters,
        req.user.RefUser,
        req.tenantContext
      );

      res.json(result);
    } catch (error: any) {
      logger.error('Get operations error:', error);
      res.status(500).json({
        success: false,
        error: error.message || 'Failed to get operations',
      });
    }
  };

  createOperation = async (req: Request, res: Response) => {
    try {
      if (!req.user || !req.tenantContext) {
        return res.status(401).json({
          success: false,
          error: 'Authentication required',
        });
      }

      const result = await this.operationService.createOperation(
        req.body,
        req.user.RefUser,
        req.tenantContext.countryId
      );

      res.status(201).json(result);
    } catch (error: any) {
      logger.error('Create operation error:', error);
      res.status(400).json({
        success: false,
        error: error.message || 'Failed to create operation',
      });
    }
  };

  getOperationById = async (req: Request, res: Response) => {
    try {
      if (!req.tenantContext) {
        return res.status(401).json({
          success: false,
          error: 'Authentication required',
        });
      }

      const operationId = parseInt(req.params.id);
      const result = await this.operationService.getOperationById(
        operationId,
        req.tenantContext
      );

      res.json(result);
    } catch (error: any) {
      logger.error('Get operation by ID error:', error);
      res.status(404).json({
        success: false,
        error: error.message || 'Operation not found',
      });
    }
  };

  updateOperation = async (req: Request, res: Response) => {
    try {
      // TODO: Implement update operation logic
      res.json({
        success: true,
        message: 'Operation update not yet implemented',
      });
    } catch (error: any) {
      logger.error('Update operation error:', error);
      res.status(500).json({
        success: false,
        error: error.message || 'Failed to update operation',
      });
    }
  };

  deleteOperation = async (req: Request, res: Response) => {
    try {
      if (!req.user) {
        return res.status(401).json({
          success: false,
          error: 'Authentication required',
        });
      }

      const operationId = parseInt(req.params.id);
      const result = await this.operationService.deleteOperation(
        operationId,
        req.user.RefUser
      );

      res.json(result);
    } catch (error: any) {
      logger.error('Delete operation error:', error);
      res.status(400).json({
        success: false,
        error: error.message || 'Failed to delete operation',
      });
    }
  };

  approveOperation = async (req: Request, res: Response) => {
    try {
      if (!req.user || !req.tenantContext) {
        return res.status(401).json({
          success: false,
          error: 'Authentication required',
        });
      }

      const operationId = parseInt(req.params.id);
      const { level } = req.body;

      const result = await this.operationService.approveOperation(
        operationId,
        level,
        req.user.RefUser,
        req.tenantContext
      );

      res.json(result);
    } catch (error: any) {
      logger.error('Approve operation error:', error);
      res.status(400).json({
        success: false,
        error: error.message || 'Failed to approve operation',
      });
    }
  };

  validateOperation = async (req: Request, res: Response) => {
    try {
      if (!req.user) {
        return res.status(401).json({
          success: false,
          error: 'Authentication required',
        });
      }

      const operationId = parseInt(req.params.id);
      const { sourceAgencyId } = req.body;

      const result = await this.operationService.validateOperation(
        operationId,
        req.user.RefUser,
        sourceAgencyId
      );

      res.json(result);
    } catch (error: any) {
      logger.error('Validate operation error:', error);
      res.status(400).json({
        success: false,
        error: error.message || 'Failed to validate operation',
      });
    }
  };

  cancelValidation = async (req: Request, res: Response) => {
    try {
      if (!req.user) {
        return res.status(401).json({
          success: false,
          error: 'Authentication required',
        });
      }

      const operationId = parseInt(req.params.id);
      const result = await this.operationService.cancelValidation(
        operationId,
        req.user.RefUser
      );

      res.json(result);
    } catch (error: any) {
      logger.error('Cancel validation error:', error);
      res.status(400).json({
        success: false,
        error: error.message || 'Failed to cancel validation',
      });
    }
  };

  validateBalance = async (req: Request, res: Response) => {
    try {
      const { cashRegisterId, amount, type } = req.query;

      if (!cashRegisterId || !amount || !type) {
        return res.status(400).json({
          success: false,
          error: 'Missing required parameters',
        });
      }

      const isValid = await this.operationService.validateBalance(
        parseInt(cashRegisterId as string),
        parseFloat(amount as string),
        type as string
      );

      res.json({
        success: true,
        valid: isValid,
      });
    } catch (error: any) {
      logger.error('Validate balance error:', error);
      res.status(500).json({
        success: false,
        error: error.message || 'Failed to validate balance',
      });
    }
  };

  getBillBreakdown = async (req: Request, res: Response) => {
    try {
      // TODO: Implement get bill breakdown logic
      res.json({
        success: true,
        message: 'Bill breakdown retrieval not yet implemented',
      });
    } catch (error: any) {
      logger.error('Get bill breakdown error:', error);
      res.status(500).json({
        success: false,
        error: error.message || 'Failed to get bill breakdown',
      });
    }
  };
}
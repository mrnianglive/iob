import { prisma } from '../config/database';
import { logger } from '../config/logger';

export enum AuditAction {
  CREATE = 'CREATE',
  UPDATE = 'UPDATE',
  DELETE = 'DELETE',
  VALIDATE = 'VALIDATE',
  APPROVE = 'APPROVE',
  REJECT = 'REJECT',
  LOGIN = 'LOGIN',
  LOGOUT = 'LOGOUT',
  EXPORT = 'EXPORT',
  TRANSFER = 'TRANSFER',
  CLOSE = 'CLOSE',
}

export enum AuditEntity {
  OPERATION = 'OPERATION',
  USER = 'USER',
  CASH_REGISTER = 'CASH_REGISTER',
  REMITTANCE = 'REMITTANCE',
  AGENCY = 'AGENCY',
  PRODUCT = 'PRODUCT',
  PARTNER = 'PARTNER',
  COUNTRY = 'COUNTRY',
}

interface AuditLogData {
  userId: number;
  action: AuditAction;
  entity: AuditEntity;
  entityId: number;
  countryId: number;
  partnerId?: number;
  details?: any;
  ipAddress?: string;
  userAgent?: string;
}

export class AuditService {
  /**
   * Create an audit log entry
   */
  async log(data: AuditLogData): Promise<void> {
    try {
      await prisma.auditLog.create({
        data: {
          userId: data.userId,
          action: data.action,
          entity: data.entity,
          entityId: data.entityId,
          countryId: data.countryId,
          partnerId: data.partnerId,
          details: JSON.stringify(data.details || {}),
          ipAddress: data.ipAddress,
          userAgent: data.userAgent,
          timestamp: new Date(),
        },
      });

      logger.info('Audit log created', {
        userId: data.userId,
        action: data.action,
        entity: data.entity,
        entityId: data.entityId,
      });
    } catch (error) {
      logger.error('Failed to create audit log:', error);
      // Don't throw - audit logging should not break the main flow
    }
  }

  /**
   * Log operation creation
   */
  async logOperationCreated(
    operation: any,
    userId: number,
    ipAddress?: string
  ): Promise<void> {
    await this.log({
      userId,
      action: AuditAction.CREATE,
      entity: AuditEntity.OPERATION,
      entityId: operation.RefOperations,
      countryId: operation.RefPays,
      details: {
        type: operation.RefType,
        amount: operation.MontantVersement,
        cashRegister: operation.RefCaisse,
      },
      ipAddress,
    });
  }

  /**
   * Log operation validation
   */
  async logOperationValidated(
    operationId: number,
    userId: number,
    countryId: number,
    ipAddress?: string
  ): Promise<void> {
    await this.log({
      userId,
      action: AuditAction.VALIDATE,
      entity: AuditEntity.OPERATION,
      entityId: operationId,
      countryId,
      ipAddress,
    });
  }

  /**
   * Log operation approval
   */
  async logOperationApproved(
    operationId: number,
    userId: number,
    countryId: number,
    level: number,
    ipAddress?: string
  ): Promise<void> {
    await this.log({
      userId,
      action: AuditAction.APPROVE,
      entity: AuditEntity.OPERATION,
      entityId: operationId,
      countryId,
      details: { approvalLevel: level },
      ipAddress,
    });
  }

  /**
   * Log user login
   */
  async logUserLogin(
    userId: number,
    countryId: number,
    ipAddress?: string,
    userAgent?: string
  ): Promise<void> {
    await this.log({
      userId,
      action: AuditAction.LOGIN,
      entity: AuditEntity.USER,
      entityId: userId,
      countryId,
      ipAddress,
      userAgent,
    });

    // Also update the login connection log
    await prisma.logConnection.create({
      data: {
        RefUser: userId,
        LoginTime: new Date(),
        IpAddress: ipAddress || '',
        UserAgent: userAgent || '',
      },
    });
  }

  /**
   * Log user logout
   */
  async logUserLogout(
    userId: number,
    countryId: number,
    ipAddress?: string
  ): Promise<void> {
    await this.log({
      userId,
      action: AuditAction.LOGOUT,
      entity: AuditEntity.USER,
      entityId: userId,
      countryId,
      ipAddress,
    });
  }

  /**
   * Log cash register closure
   */
  async logCashRegisterClosure(
    cashRegisterId: number,
    userId: number,
    countryId: number,
    closureDetails: any,
    ipAddress?: string
  ): Promise<void> {
    await this.log({
      userId,
      action: AuditAction.CLOSE,
      entity: AuditEntity.CASH_REGISTER,
      entityId: cashRegisterId,
      countryId,
      details: closureDetails,
      ipAddress,
    });
  }

  /**
   * Log fund transfer
   */
  async logFundTransfer(
    transferData: any,
    userId: number,
    countryId: number,
    ipAddress?: string
  ): Promise<void> {
    await this.log({
      userId,
      action: AuditAction.TRANSFER,
      entity: AuditEntity.CASH_REGISTER,
      entityId: transferData.sourceCashRegisterId,
      countryId,
      details: {
        sourceCashRegister: transferData.sourceCashRegisterId,
        targetCashRegister: transferData.targetCashRegisterId,
        amount: transferData.amount,
        reason: transferData.reason,
      },
      ipAddress,
    });
  }

  /**
   * Log data export
   */
  async logDataExport(
    exportType: string,
    userId: number,
    countryId: number,
    filters: any,
    ipAddress?: string
  ): Promise<void> {
    await this.log({
      userId,
      action: AuditAction.EXPORT,
      entity: AuditEntity.OPERATION,
      entityId: 0, // No specific entity
      countryId,
      details: {
        exportType,
        filters,
        timestamp: new Date(),
      },
      ipAddress,
    });
  }

  /**
   * Get audit logs for an entity
   */
  async getAuditLogs(
    entity: AuditEntity,
    entityId: number,
    limit: number = 50
  ): Promise<any[]> {
    try {
      const logs = await prisma.auditLog.findMany({
        where: {
          entity,
          entityId,
        },
        include: {
          user: {
            select: {
              Name: true,
              Login: true,
            },
          },
        },
        orderBy: {
          timestamp: 'desc',
        },
        take: limit,
      });

      return logs.map(log => ({
        id: log.id,
        action: log.action,
        userId: log.userId,
        userName: log.user.Name,
        userLogin: log.user.Login,
        details: JSON.parse(log.details || '{}'),
        timestamp: log.timestamp,
        ipAddress: log.ipAddress,
      }));
    } catch (error) {
      logger.error('Failed to get audit logs:', error);
      throw error;
    }
  }

  /**
   * Get user activity logs
   */
  async getUserActivityLogs(
    userId: number,
    dateFrom?: Date,
    dateTo?: Date,
    limit: number = 100
  ): Promise<any[]> {
    try {
      const where: any = {
        userId,
      };

      if (dateFrom || dateTo) {
        where.timestamp = {};
        if (dateFrom) where.timestamp.gte = dateFrom;
        if (dateTo) where.timestamp.lte = dateTo;
      }

      const logs = await prisma.auditLog.findMany({
        where,
        orderBy: {
          timestamp: 'desc',
        },
        take: limit,
      });

      return logs.map(log => ({
        id: log.id,
        action: log.action,
        entity: log.entity,
        entityId: log.entityId,
        details: JSON.parse(log.details || '{}'),
        timestamp: log.timestamp,
        ipAddress: log.ipAddress,
      }));
    } catch (error) {
      logger.error('Failed to get user activity logs:', error);
      throw error;
    }
  }

  /**
   * Get login history for a user
   */
  async getUserLoginHistory(
    userId: number,
    limit: number = 50
  ): Promise<any[]> {
    try {
      const logs = await prisma.logConnection.findMany({
        where: {
          RefUser: userId,
        },
        orderBy: {
          LoginTime: 'desc',
        },
        take: limit,
      });

      return logs.map(log => ({
        id: log.RefLog,
        loginTime: log.LoginTime,
        logoutTime: log.LogoutTime,
        ipAddress: log.IpAddress,
        userAgent: log.UserAgent,
        duration: log.LogoutTime
          ? Math.floor(
              (log.LogoutTime.getTime() - log.LoginTime.getTime()) / 1000 / 60
            )
          : null,
      }));
    } catch (error) {
      logger.error('Failed to get user login history:', error);
      throw error;
    }
  }
}

// Export singleton instance
export const auditService = new AuditService();
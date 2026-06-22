import { prisma } from '../config/database';
import { logger } from '../config/logger';
import { io } from '../index';
import { broadcastOperationUpdate } from '../utils/socket.handlers';

interface CreateOperationData {
  type: 'deposit' | 'withdrawal' | 'transfer' | 'appro' | 'sortie';
  cashRegisterId: number;
  amount: number;
  clientName: string;
  accountNumber: string;
  depositorName?: string;
  depositorPhone?: string;
  productId?: number;
  notes?: string;
  billBreakdown?: BillBreakdownData;
}

interface BillBreakdownData {
  bills10000?: number;
  bills5000?: number;
  bills2000?: number;
  bills1000?: number;
  bills500?: number;
  bills250?: number;
  bills200?: number;
  bills100?: number;
  coins50?: number;
  coins25?: number;
  coins10?: number;
  coins5?: number;
  coins1?: number;
}

export class OperationService {
  /**
   * Create a new operation with bill breakdown
   */
  async createOperation(data: CreateOperationData, userId: number, countryId: number) {
    try {
      // Map operation type
      const typeMap = {
        'deposit': 1,
        'withdrawal': 2,
        'transfer': 3,
        'appro': 4,
        'sortie': 5,
      };
      const operationType = typeMap[data.type];

      // Validate cash register access
      const cashRegister = await prisma.cashRegister.findFirst({
        where: {
          RefCaisse: data.cashRegisterId,
          users: {
            some: {
              RefUsers: userId,
            },
          },
        },
        include: {
          agency: true,
        },
      });

      if (!cashRegister) {
        throw new Error('No access to this cash register');
      }

      // Validate balance for withdrawals
      if (data.type === 'withdrawal') {
        const balance = await this.getCashRegisterBalance(data.cashRegisterId);
        if (balance < data.amount) {
          throw new Error('Insufficient balance in cash register');
        }
      }

      // Generate unique ID
      const uniqid = `OP-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;

      // Create operation
      const operation = await prisma.operation.create({
        data: {
          RefCaisse: data.cashRegisterId,
          NumCompte: data.accountNumber,
          NameClient: data.clientName,
          MontantVersement: data.amount.toString(),
          Remarque: data.notes,
          Insert_Id: userId,
          Insert_Time: new Date(),
          NameDeposant: data.depositorName,
          TelDeposant: data.depositorPhone || '',
          RefType: operationType,
          RefProduit: data.productId,
          RefPays: countryId,
          uniqid,
          Validate: 1, // Pending validation
        },
        include: {
          cashRegister: true,
          type: true,
          product: true,
          user: {
            select: {
              Name: true,
              Email: true,
            },
          },
        },
      });

      // Create bill breakdown if provided
      if (data.billBreakdown) {
        await this.createBillBreakdown(operation.RefOperations, data.billBreakdown);
      }

      // Broadcast operation creation via WebSocket
      broadcastOperationUpdate(io, operation);

      logger.info(`Operation ${operation.RefOperations} created by user ${userId}`);

      return {
        success: true,
        operation,
      };
    } catch (error: any) {
      logger.error('Create operation error:', error);
      throw error;
    }
  }

  /**
   * Create bill breakdown for an operation
   */
  private async createBillBreakdown(operationId: number, breakdown: BillBreakdownData) {
    const billValues = {
      a1: '10000', a2: (breakdown.bills10000 || 0).toString(),
      b1: '5000', b2: (breakdown.bills5000 || 0).toString(),
      c1: '2000', c2: (breakdown.bills2000 || 0).toString(),
      d1: '1000', d2: (breakdown.bills1000 || 0).toString(),
      e1: '500', e2: (breakdown.bills500 || 0).toString(),
      f1: '250', f2: (breakdown.bills250 || 0).toString(),
      g1: '200', g2: (breakdown.bills200 || 0).toString(),
      h1: '100', h2: (breakdown.bills100 || 0).toString(),
      i1: '50', i2: (breakdown.coins50 || 0).toString(),
      j1: '25', j2: (breakdown.coins25 || 0).toString(),
      k1: '10', k2: (breakdown.coins10 || 0).toString(),
      l1: '5', l2: (breakdown.coins5 || 0).toString(),
      m1: '1', m2: (breakdown.coins1 || 0).toString(),
    };

    return await prisma.billBreakdown.create({
      data: {
        RefOperations: operationId,
        ...billValues,
      },
    });
  }

  /**
   * Get operations with filters
   */
  async getOperations(filters: any, userId: number, tenantContext: any) {
    const where: any = {
      RefPays: tenantContext.countryId,
    };

    // Apply filters
    if (filters.cashRegisterId) {
      where.RefCaisse = filters.cashRegisterId;
    }
    if (filters.dateFrom || filters.dateTo) {
      where.datePayement = {};
      if (filters.dateFrom) {
        where.datePayement.gte = new Date(filters.dateFrom);
      }
      if (filters.dateTo) {
        where.datePayement.lte = new Date(filters.dateTo);
      }
    }
    if (filters.type) {
      where.RefType = parseInt(filters.type);
    }
    if (filters.status) {
      where.Validate = parseInt(filters.status);
    }

    // Restrict to user's cash registers unless admin
    if (!tenantContext.permissions.includes('admin')) {
      where.RefCaisse = {
        in: tenantContext.cashRegisterIds,
      };
    }

    const operations = await prisma.operation.findMany({
      where,
      include: {
        cashRegister: {
          include: {
            agency: true,
          },
        },
        type: true,
        product: true,
        user: {
          select: {
            Name: true,
          },
        },
        billBreakdown: true,
      },
      orderBy: {
        datePayement: 'desc',
      },
      take: filters.limit || 50,
      skip: ((filters.page || 1) - 1) * (filters.limit || 50),
    });

    const total = await prisma.operation.count({ where });

    return {
      success: true,
      operations,
      pagination: {
        total,
        page: filters.page || 1,
        limit: filters.limit || 50,
        pages: Math.ceil(total / (filters.limit || 50)),
      },
    };
  }

  /**
   * Get single operation by ID
   */
  async getOperationById(operationId: number, tenantContext: any) {
    const operation = await prisma.operation.findFirst({
      where: {
        RefOperations: operationId,
        RefPays: tenantContext.countryId,
      },
      include: {
        cashRegister: {
          include: {
            agency: true,
          },
        },
        type: true,
        product: true,
        user: {
          select: {
            Name: true,
            Email: true,
          },
        },
        approver1: {
          select: {
            Name: true,
          },
        },
        approver2: {
          select: {
            Name: true,
          },
        },
        validator: {
          select: {
            Name: true,
          },
        },
        billBreakdown: true,
      },
    });

    if (!operation) {
      throw new Error('Operation not found');
    }

    // Check access rights
    if (!tenantContext.permissions.includes('admin')) {
      if (!tenantContext.cashRegisterIds.includes(operation.RefCaisse)) {
        throw new Error('No access to this operation');
      }
    }

    return {
      success: true,
      operation,
    };
  }

  /**
   * Approve operation (multi-level)
   */
  async approveOperation(operationId: number, level: '1' | '2', userId: number, tenantContext: any) {
    const operation = await prisma.operation.findFirst({
      where: {
        RefOperations: operationId,
        RefPays: tenantContext.countryId,
      },
    });

    if (!operation) {
      throw new Error('Operation not found');
    }

    // Check if user has approval rights
    const hasApprovalRights = await prisma.cashRegisterApproUser.findFirst({
      where: {
        RefCaisse: operation.RefCaisse,
        RefUsers: userId,
      },
    });

    if (!hasApprovalRights && !tenantContext.permissions.includes('admin')) {
      throw new Error('No approval rights for this cash register');
    }

    // Update approval based on level
    const updateData: any = {};
    if (level === '1') {
      if (operation.Approve1_Id) {
        throw new Error('Operation already approved at level 1');
      }
      updateData.Approve1_Id = userId;
      updateData.Approve1_Time = new Date();
    } else {
      if (!operation.Approve1_Id) {
        throw new Error('Level 1 approval required first');
      }
      if (operation.Approve2_Id) {
        throw new Error('Operation already approved at level 2');
      }
      updateData.Approve2_Id = userId;
      updateData.Approve2_Time = new Date();
      updateData.Validate = 2; // Mark as validated after level 2
    }

    const updatedOperation = await prisma.operation.update({
      where: { RefOperations: operationId },
      data: updateData,
      include: {
        cashRegister: true,
        type: true,
      },
    });

    // Broadcast update
    broadcastOperationUpdate(io, updatedOperation);

    logger.info(`Operation ${operationId} approved at level ${level} by user ${userId}`);

    return {
      success: true,
      operation: updatedOperation,
    };
  }

  /**
   * Validate operation
   */
  async validateOperation(operationId: number, userId: number, sourceAgencyId: number) {
    const operation = await prisma.operation.findUnique({
      where: { RefOperations: operationId },
    });

    if (!operation) {
      throw new Error('Operation not found');
    }

    if (operation.Validate === 2) {
      throw new Error('Operation already validated');
    }

    const updatedOperation = await prisma.operation.update({
      where: { RefOperations: operationId },
      data: {
        Validate: 2,
        ValidateDate: new Date(),
        RefValidate: userId,
        SentFromAgency: sourceAgencyId,
      },
      include: {
        cashRegister: true,
        type: true,
      },
    });

    // Broadcast update
    broadcastOperationUpdate(io, updatedOperation);

    logger.info(`Operation ${operationId} validated by user ${userId}`);

    return {
      success: true,
      operation: updatedOperation,
    };
  }

  /**
   * Cancel operation validation
   */
  async cancelValidation(operationId: number, userId: number) {
    const operation = await prisma.operation.findUnique({
      where: { RefOperations: operationId },
    });

    if (!operation) {
      throw new Error('Operation not found');
    }

    if (operation.Validate !== 2) {
      throw new Error('Operation is not validated');
    }

    const updatedOperation = await prisma.operation.update({
      where: { RefOperations: operationId },
      data: {
        Validate: 1,
        ValidateDate: null,
        RefValidate: null,
        Reset_Id: userId,
        Reset_At: new Date(),
      },
    });

    logger.info(`Operation ${operationId} validation cancelled by user ${userId}`);

    return {
      success: true,
      operation: updatedOperation,
    };
  }

  /**
   * Delete operation (soft delete by setting Validate to 0)
   */
  async deleteOperation(operationId: number, userId: number) {
    const operation = await prisma.operation.findUnique({
      where: { RefOperations: operationId },
    });

    if (!operation) {
      throw new Error('Operation not found');
    }

    if (operation.Validate === 2) {
      throw new Error('Cannot delete validated operation');
    }

    const updatedOperation = await prisma.operation.update({
      where: { RefOperations: operationId },
      data: {
        Validate: 0, // Soft delete
        Reset_Id: userId,
        Reset_At: new Date(),
      },
    });

    logger.info(`Operation ${operationId} deleted by user ${userId}`);

    return {
      success: true,
      message: 'Operation deleted successfully',
    };
  }

  /**
   * Get cash register balance
   */
  async getCashRegisterBalance(cashRegisterId: number): Promise<number> {
    const deposits = await prisma.operation.aggregate({
      where: {
        RefCaisse: cashRegisterId,
        RefType: 1, // Deposits
        Validate: 2, // Validated
      },
      _sum: {
        MontantVersement: true,
      },
    });

    const withdrawals = await prisma.operation.aggregate({
      where: {
        RefCaisse: cashRegisterId,
        RefType: 2, // Withdrawals
        Validate: 2, // Validated
      },
      _sum: {
        MontantVersement: true,
      },
    });

    return (
      parseFloat(deposits._sum.MontantVersement || '0') -
      parseFloat(withdrawals._sum.MontantVersement || '0')
    );
  }

  /**
   * Validate balance for operation
   */
  async validateBalance(cashRegisterId: number, amount: number, type: string): Promise<boolean> {
    if (type !== 'withdrawal') {
      return true;
    }

    const balance = await this.getCashRegisterBalance(cashRegisterId);
    return balance >= amount;
  }
}
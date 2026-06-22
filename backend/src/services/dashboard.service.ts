import { prisma } from '../config/database';
import { logger } from '../config/logger';

interface DashboardFilters {
  countryId: number;
  agencyId?: number;
  cashRegisterId?: number;
  dateFrom?: string;
  dateTo?: string;
}

export class DashboardService {
  /**
   * Get dashboard statistics
   */
  async getStats(filters: DashboardFilters, tenantContext: any) {
    try {
      const today = new Date();
      today.setHours(0, 0, 0, 0);

      const where: any = {
        RefPays: filters.countryId,
        datePayement: {
          gte: filters.dateFrom ? new Date(filters.dateFrom) : today,
          lte: filters.dateTo ? new Date(filters.dateTo) : new Date(),
        },
      };

      if (filters.agencyId) {
        where.cashRegister = {
          RefAgency: filters.agencyId,
        };
      }

      if (filters.cashRegisterId) {
        where.RefCaisse = filters.cashRegisterId;
      }

      // Apply tenant context restrictions
      if (!tenantContext.permissions.includes('admin')) {
        where.RefCaisse = {
          in: tenantContext.cashRegisterIds,
        };
      }

      // Total operations
      const totalOperations = await prisma.operation.count({ where });

      // Total amount
      const totalAmount = await prisma.operation.aggregate({
        where,
        _sum: {
          MontantVersement: true,
        },
      });

      // Deposits
      const deposits = await prisma.operation.aggregate({
        where: {
          ...where,
          RefType: 1,
        },
        _sum: {
          MontantVersement: true,
        },
        _count: true,
      });

      // Withdrawals
      const withdrawals = await prisma.operation.aggregate({
        where: {
          ...where,
          RefType: 2,
        },
        _sum: {
          MontantVersement: true,
        },
        _count: true,
      });

      // Pending validations
      const pendingValidations = await prisma.operation.count({
        where: {
          ...where,
          Validate: 1,
        },
      });

      // Cash registers balance
      const cashRegistersBalance = await this.getCashRegistersBalance(
        tenantContext.cashRegisterIds
      );

      return {
        totalOperations,
        totalAmount: parseFloat(totalAmount._sum.MontantVersement || '0'),
        deposits: {
          count: deposits._count,
          amount: parseFloat(deposits._sum.MontantVersement || '0'),
        },
        withdrawals: {
          count: withdrawals._count,
          amount: parseFloat(withdrawals._sum.MontantVersement || '0'),
        },
        pendingValidations,
        cashRegistersBalance,
        period: {
          from: filters.dateFrom || today.toISOString(),
          to: filters.dateTo || new Date().toISOString(),
        },
      };
    } catch (error) {
      logger.error('Dashboard stats error:', error);
      throw error;
    }
  }

  /**
   * Get recent operations
   */
  async getRecentOperations(tenantContext: any, limit: number = 10) {
    try {
      const where: any = {
        RefPays: tenantContext.countryId,
      };

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
        },
        orderBy: {
          datePayement: 'desc',
        },
        take: limit,
      });

      return operations.map(op => ({
        id: op.RefOperations,
        type: op.type.NameType,
        amount: parseFloat(op.MontantVersement),
        clientName: op.NameClient,
        accountNumber: op.NumCompte,
        cashRegister: op.cashRegister.NameCaisse,
        agency: op.cashRegister.agency.NameAgency,
        user: op.user.Name,
        status: this.getOperationStatus(op.Validate),
        date: op.datePayement,
      }));
    } catch (error) {
      logger.error('Get recent operations error:', error);
      throw error;
    }
  }

  /**
   * Get alerts for dashboard
   */
  async getAlerts(tenantContext: any) {
    try {
      const alerts = [];

      // Check low balance alerts
      for (const cashRegisterId of tenantContext.cashRegisterIds) {
        const balance = await this.getCashRegisterBalance(cashRegisterId);
        
        if (balance < 100000) { // Alert if balance below 100,000
          const cashRegister = await prisma.cashRegister.findUnique({
            where: { RefCaisse: cashRegisterId },
            include: { agency: true },
          });

          alerts.push({
            type: 'low_balance',
            severity: balance < 50000 ? 'critical' : 'warning',
            message: `Solde faible pour la caisse ${cashRegister?.NameCaisse}`,
            details: {
              cashRegister: cashRegister?.NameCaisse,
              agency: cashRegister?.agency.NameAgency,
              balance,
            },
            timestamp: new Date(),
          });
        }
      }

      // Check pending validations
      const pendingCount = await prisma.operation.count({
        where: {
          RefPays: tenantContext.countryId,
          RefCaisse: {
            in: tenantContext.cashRegisterIds,
          },
          Validate: 1,
          datePayement: {
            gte: new Date(new Date().setHours(0, 0, 0, 0)),
          },
        },
      });

      if (pendingCount > 5) {
        alerts.push({
          type: 'pending_validations',
          severity: 'info',
          message: `${pendingCount} opérations en attente de validation`,
          details: {
            count: pendingCount,
          },
          timestamp: new Date(),
        });
      }

      return alerts;
    } catch (error) {
      logger.error('Get alerts error:', error);
      throw error;
    }
  }

  /**
   * Get quick links for country
   */
  async getQuickLinks(countryId: number) {
    try {
      // This would typically come from a links table
      // For now, return default links
      return [
        {
          id: 1,
          title: 'Nouvelle Opération',
          url: '/operations/new',
          icon: 'PlusCircleIcon',
          color: 'primary',
        },
        {
          id: 2,
          title: 'Journal du Jour',
          url: '/journal',
          icon: 'BookOpenIcon',
          color: 'secondary',
        },
        {
          id: 3,
          title: 'Transfert de Fonds',
          url: '/cash-registers/transfer',
          icon: 'ArrowsRightLeftIcon',
          color: 'success',
        },
        {
          id: 4,
          title: 'Rapports',
          url: '/analytics',
          icon: 'ChartBarIcon',
          color: 'info',
        },
      ];
    } catch (error) {
      logger.error('Get quick links error:', error);
      throw error;
    }
  }

  /**
   * Get balance summary for user's cash registers
   */
  async getBalanceSummary(tenantContext: any) {
    try {
      const balances = [];

      for (const cashRegisterId of tenantContext.cashRegisterIds) {
        const cashRegister = await prisma.cashRegister.findUnique({
          where: { RefCaisse: cashRegisterId },
          include: { agency: true },
        });

        const balance = await this.getCashRegisterBalance(cashRegisterId);

        balances.push({
          cashRegisterId,
          cashRegisterName: cashRegister?.NameCaisse,
          agencyName: cashRegister?.agency.NameAgency,
          balance,
          lastUpdate: new Date(),
        });
      }

      const totalBalance = balances.reduce((sum, b) => sum + b.balance, 0);

      return {
        cashRegisters: balances,
        totalBalance,
        currency: 'XOF',
      };
    } catch (error) {
      logger.error('Get balance summary error:', error);
      throw error;
    }
  }

  /**
   * Get cash register balance
   */
  private async getCashRegisterBalance(cashRegisterId: number): Promise<number> {
    const deposits = await prisma.operation.aggregate({
      where: {
        RefCaisse: cashRegisterId,
        RefType: 1,
        Validate: 2,
      },
      _sum: {
        MontantVersement: true,
      },
    });

    const withdrawals = await prisma.operation.aggregate({
      where: {
        RefCaisse: cashRegisterId,
        RefType: 2,
        Validate: 2,
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
   * Get balances for multiple cash registers
   */
  private async getCashRegistersBalance(cashRegisterIds: number[]): Promise<number> {
    let totalBalance = 0;

    for (const id of cashRegisterIds) {
      const balance = await this.getCashRegisterBalance(id);
      totalBalance += balance;
    }

    return totalBalance;
  }

  /**
   * Get operation status label
   */
  private getOperationStatus(validate: number): string {
    switch (validate) {
      case 0:
        return 'Supprimé';
      case 1:
        return 'En attente';
      case 2:
        return 'Validé';
      default:
        return 'Inconnu';
    }
  }
}
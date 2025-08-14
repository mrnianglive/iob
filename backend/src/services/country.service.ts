import { prisma } from '../config/database';
import { logger } from '../config/logger';

export class CountryService {
  /**
   * Get list of accessible countries for user
   */
  async getCountries(tenantContext: any) {
    try {
      // Admin can see all countries
      if (tenantContext.permissions.includes('super_admin')) {
        return await prisma.country.findMany({
          include: {
            _count: {
              select: {
                agencies: true,
                partners: true,
                users: true,
              },
            },
          },
        });
      }

      // User can only see their country
      return await prisma.country.findMany({
        where: {
          RefPays: tenantContext.countryId,
        },
        include: {
          _count: {
            select: {
              agencies: true,
              partners: true,
              users: true,
            },
          },
        },
      });
    } catch (error) {
      logger.error('Get countries error:', error);
      throw error;
    }
  }

  /**
   * Get country details by ID
   */
  async getCountryById(countryId: number, tenantContext: any) {
    try {
      // Check access rights
      if (
        !tenantContext.permissions.includes('super_admin') &&
        tenantContext.countryId !== countryId
      ) {
        throw new Error('Access denied to this country');
      }

      const country = await prisma.country.findUnique({
        where: {
          RefPays: countryId,
        },
        include: {
          _count: {
            select: {
              agencies: true,
              partners: true,
              users: true,
              operations: true,
            },
          },
        },
      });

      if (!country) {
        return null;
      }

      // Get today's statistics
      const today = new Date();
      today.setHours(0, 0, 0, 0);

      const todayStats = await prisma.operation.aggregate({
        where: {
          RefPays: countryId,
          datePayement: {
            gte: today,
          },
        },
        _count: true,
        _sum: {
          MontantVersement: true,
        },
      });

      return {
        ...country,
        statistics: {
          totalOperationsToday: todayStats._count,
          totalVolumeToday: parseFloat(todayStats._sum.MontantVersement || '0'),
          agenciesCount: country._count.agencies,
          partnersCount: country._count.partners,
          usersCount: country._count.users,
        },
      };
    } catch (error) {
      logger.error('Get country by ID error:', error);
      throw error;
    }
  }

  /**
   * Update country configuration
   */
  async updateCountry(countryId: number, updates: any) {
    try {
      const country = await prisma.country.update({
        where: {
          RefPays: countryId,
        },
        data: {
          nomPays: updates.name,
          logo: updates.logo,
          EmailAlert: updates.alertEmail,
        },
      });

      return country;
    } catch (error) {
      logger.error('Update country error:', error);
      throw error;
    }
  }

  /**
   * Get agencies of a country
   */
  async getCountryAgencies(countryId: number, tenantContext: any) {
    try {
      // Check access rights
      if (
        !tenantContext.permissions.includes('admin') &&
        tenantContext.countryId !== countryId
      ) {
        throw new Error('Access denied to this country');
      }

      const agencies = await prisma.agency.findMany({
        where: {
          RefPays: countryId,
        },
        include: {
          cashRegisters: {
            include: {
              _count: {
                select: {
                  operations: true,
                },
              },
            },
          },
          _count: {
            select: {
              users: true,
            },
          },
        },
      });

      // Calculate balance for each agency
      const agenciesWithBalance = await Promise.all(
        agencies.map(async (agency) => {
          const balance = await this.calculateAgencyBalance(agency.RefAgency);
          return {
            ...agency,
            balance,
            cashRegistersCount: agency.cashRegisters.length,
            usersCount: agency._count.users,
          };
        })
      );

      return agenciesWithBalance;
    } catch (error) {
      logger.error('Get country agencies error:', error);
      throw error;
    }
  }

  /**
   * Get partners of a country
   */
  async getCountryPartners(countryId: number, tenantContext: any) {
    try {
      // Check access rights
      if (
        !tenantContext.permissions.includes('admin') &&
        tenantContext.countryId !== countryId
      ) {
        throw new Error('Access denied to this country');
      }

      const partners = await prisma.partner.findMany({
        where: {
          RefPays: countryId,
        },
        include: {
          products: {
            where: {
              status: 'active',
            },
          },
          _count: {
            select: {
              products: true,
            },
          },
        },
      });

      // Get statistics for each partner
      const partnersWithStats = await Promise.all(
        partners.map(async (partner) => {
          const today = new Date();
          today.setHours(0, 0, 0, 0);

          // Get products IDs for this partner
          const productIds = partner.products.map(p => p.RefProduit);

          const stats = await prisma.operation.aggregate({
            where: {
              RefPays: countryId,
              RefProduit: {
                in: productIds,
              },
              datePayement: {
                gte: today,
              },
            },
            _count: true,
            _sum: {
              MontantVersement: true,
              Commission: true,
            },
          });

          return {
            id: partner.RefBanque,
            name: partner.NameBanque,
            countryId: partner.RefPays,
            active: true,
            products: partner.products,
            statistics: {
              totalOperationsToday: stats._count,
              totalVolumeToday: parseFloat(stats._sum.MontantVersement || '0'),
              commissionEarnedToday: parseFloat(stats._sum.Commission || '0'),
              productsCount: partner._count.products,
            },
          };
        })
      );

      return partnersWithStats;
    } catch (error) {
      logger.error('Get country partners error:', error);
      throw error;
    }
  }

  /**
   * Get country statistics
   */
  async getCountryStatistics(
    countryId: number,
    tenantContext: any,
    filters: { dateFrom?: string; dateTo?: string }
  ) {
    try {
      // Check access rights
      if (
        !tenantContext.permissions.includes('admin') &&
        tenantContext.countryId !== countryId
      ) {
        throw new Error('Access denied to this country');
      }

      const dateFrom = filters.dateFrom ? new Date(filters.dateFrom) : new Date();
      dateFrom.setHours(0, 0, 0, 0);

      const dateTo = filters.dateTo ? new Date(filters.dateTo) : new Date();
      dateTo.setHours(23, 59, 59, 999);

      // Total operations
      const operations = await prisma.operation.aggregate({
        where: {
          RefPays: countryId,
          datePayement: {
            gte: dateFrom,
            lte: dateTo,
          },
        },
        _count: true,
        _sum: {
          MontantVersement: true,
          Commission: true,
        },
      });

      // Operations by type
      const operationsByType = await prisma.operation.groupBy({
        by: ['RefType'],
        where: {
          RefPays: countryId,
          datePayement: {
            gte: dateFrom,
            lte: dateTo,
          },
        },
        _count: true,
        _sum: {
          MontantVersement: true,
        },
      });

      // Operations by agency
      const operationsByAgency = await prisma.operation.groupBy({
        by: ['RefCaisse'],
        where: {
          RefPays: countryId,
          datePayement: {
            gte: dateFrom,
            lte: dateTo,
          },
        },
        _count: true,
        _sum: {
          MontantVersement: true,
        },
      });

      // Active users
      const activeUsers = await prisma.user.count({
        where: {
          RefPays: countryId,
          LogStatus: 1,
        },
      });

      // Active cash registers
      const activeCashRegisters = await prisma.cashRegister.count({
        where: {
          agency: {
            RefPays: countryId,
          },
        },
      });

      return {
        countryId,
        period: {
          from: dateFrom,
          to: dateTo,
        },
        totalOperations: operations._count,
        totalVolume: parseFloat(operations._sum.MontantVersement || '0'),
        totalCommissions: parseFloat(operations._sum.Commission || '0'),
        operationsByType: operationsByType.map(item => ({
          type: item.RefType,
          count: item._count,
          volume: parseFloat(item._sum.MontantVersement || '0'),
        })),
        activeUsers,
        activeCashRegisters,
        currency: 'XOF',
        timezone: 'GMT+0',
      };
    } catch (error) {
      logger.error('Get country statistics error:', error);
      throw error;
    }
  }

  /**
   * Get users of a country
   */
  async getCountryUsers(countryId: number) {
    try {
      const users = await prisma.user.findMany({
        where: {
          RefPays: countryId,
        },
        include: {
          country: true,
          permissions: true,
          cashRegisterPermissions: true,
        },
        orderBy: {
          Name: 'asc',
        },
      });

      return users.map(user => ({
        id: user.RefUser,
        login: user.Login,
        name: user.Name,
        email: user.Email,
        roleId: user.RefRole,
        countryId: user.RefPays,
        status: user.LogStatus === 1 ? 'active' : 'inactive',
        lastLogin: user.LastLogin,
        permissions: user.permissions,
        cashRegisters: user.cashRegisterPermissions.map(p => p.RefCaisse),
      }));
    } catch (error) {
      logger.error('Get country users error:', error);
      throw error;
    }
  }

  /**
   * Calculate total balance for an agency
   */
  private async calculateAgencyBalance(agencyId: number): Promise<number> {
    const cashRegisters = await prisma.cashRegister.findMany({
      where: {
        RefAgency: agencyId,
      },
    });

    let totalBalance = 0;

    for (const cashRegister of cashRegisters) {
      const deposits = await prisma.operation.aggregate({
        where: {
          RefCaisse: cashRegister.RefCaisse,
          RefType: 1, // Deposit
          Validate: 2, // Validated
        },
        _sum: {
          MontantVersement: true,
        },
      });

      const withdrawals = await prisma.operation.aggregate({
        where: {
          RefCaisse: cashRegister.RefCaisse,
          RefType: 2, // Withdrawal
          Validate: 2, // Validated
        },
        _sum: {
          MontantVersement: true,
        },
      });

      const balance =
        parseFloat(deposits._sum.MontantVersement || '0') -
        parseFloat(withdrawals._sum.MontantVersement || '0');

      totalBalance += balance;
    }

    return totalBalance;
  }
}
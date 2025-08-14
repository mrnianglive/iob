import { PrismaClient } from '@prisma/client';
import { OperationFilters, DateRange, PartnerStats } from '@/types/partner';
import { logger } from '@/utils/logger';

export class PartnerService {
  constructor(private prisma: PrismaClient) {}

  /**
   * Récupérer les opérations d'un partenaire avec filtrage automatique
   */
  async getPartnerOperations(partnerId: number, filters: OperationFilters = {}) {
    const {
      date_from,
      date_to,
      status,
      agency_id,
      product_id,
      amount_min,
      amount_max,
      client_name,
      reference,
      page = 1,
      limit = 20,
      sort = 'Insert_Time',
      order = 'desc'
    } = filters;

    // Construction des conditions de filtrage
    const where: any = {
      RefBanque: partnerId, // Isolation automatique par partenaire
    };

    // Filtres de date
    if (date_from || date_to) {
      where.Insert_Time = {};
      if (date_from) where.Insert_Time.gte = new Date(date_from);
      if (date_to) where.Insert_Time.lte = new Date(date_to);
    }

    // Autres filtres
    if (status) where.Status = status;
    if (agency_id) {
      where.cashRegister = {
        RefAgence: agency_id
      };
    }
    if (product_id) where.RefProduit = product_id;
    if (amount_min || amount_max) {
      where.Amount = {};
      if (amount_min) where.Amount.gte = amount_min;
      if (amount_max) where.Amount.lte = amount_max;
    }
    if (client_name) {
      where.ClientName = {
        contains: client_name,
        mode: 'insensitive'
      };
    }
    if (reference) {
      where.Reference = {
        contains: reference,
        mode: 'insensitive'
      };
    }

    // Calcul de la pagination
    const skip = (page - 1) * limit;

    // Requête avec comptage total
    const [operations, total] = await Promise.all([
      this.prisma.operation.findMany({
        where,
        include: {
          cashRegister: {
            include: { 
              agency: {
                include: { country: true }
              }
            }
          },
          product: {
            select: {
              RefProduit: true,
              NameProduit: true,
              Commission: true
            }
          }
        },
        orderBy: { [sort]: order },
        skip,
        take: limit,
      }),
      this.prisma.operation.count({ where })
    ]);

    return {
      data: operations,
      pagination: {
        page,
        limit,
        total,
        pages: Math.ceil(total / limit)
      }
    };
  }

  /**
   * Récupérer une opération spécifique d'un partenaire
   */
  async getPartnerOperation(partnerId: number, operationId: number) {
    const operation = await this.prisma.operation.findFirst({
      where: {
        RefOperation: operationId,
        RefBanque: partnerId // Vérification de l'appartenance au partenaire
      },
      include: {
        cashRegister: {
          include: { 
            agency: {
              include: { country: true }
            }
          }
        },
        product: true
      }
    });

    return operation;
  }

  /**
   * Récupérer les statistiques d'un partenaire
   */
  async getPartnerStats(partnerId: number, dateRange?: DateRange): Promise<PartnerStats> {
    const where: any = { RefBanque: partnerId };
    
    if (dateRange) {
      where.Insert_Time = {
        gte: dateRange.start,
        lte: dateRange.end
      };
    }

    // Statistiques actuelles
    const [
      operationsCount,
      totalVolume,
      totalCommissions,
      agenciesCount,
      activeUsersCount
    ] = await Promise.all([
      this.getOperationsCount(partnerId, dateRange),
      this.getTotalVolume(partnerId, dateRange),
      this.getTotalCommissions(partnerId, dateRange),
      this.getAgenciesCount(partnerId),
      this.getActiveUsersCount(partnerId)
    ]);

    // Calcul des tendances (comparaison avec la période précédente)
    let trends = {};
    if (dateRange) {
      trends = await this.calculateTrends(partnerId, dateRange);
    }

    return {
      operations_count: operationsCount,
      total_volume: totalVolume,
      commissions: totalCommissions,
      agencies_count: agenciesCount,
      active_users: activeUsersCount,
      ...trends
    };
  }

  /**
   * Récupérer les agences d'un partenaire
   */
  async getPartnerAgencies(partnerId: number) {
    const agencies = await this.prisma.agency.findMany({
      where: {
        // Agences ayant des caisses avec des opérations du partenaire
        cashRegisters: {
          some: {
            operations: {
              some: { RefBanque: partnerId }
            }
          }
        }
      },
      include: {
        country: {
          select: {
            RefPays: true,
            NamePays: true,
            CodePays: true
          }
        },
        cashRegisters: {
          where: {
            operations: {
              some: { RefBanque: partnerId }
            }
          },
          include: {
            _count: {
              select: {
                operations: {
                  where: { RefBanque: partnerId }
                }
              }
            }
          }
        }
      }
    });

    // Enrichissement avec statistiques par agence
    const enrichedAgencies = await Promise.all(
      agencies.map(async (agency) => {
        const stats = await this.getAgencyStats(partnerId, agency.RefAgence);
        return {
          ...agency,
          stats
        };
      })
    );

    return enrichedAgencies;
  }

  /**
   * Récupérer les produits d'un partenaire
   */
  async getPartnerProducts(partnerId: number) {
    const products = await this.prisma.product.findMany({
      where: {
        RefBanque: partnerId,
        IsActive: true
      },
      include: {
        _count: {
          select: {
            operations: true
          }
        }
      }
    });

    return products;
  }

  /**
   * Statistiques d'analytics avancées
   */
  async getPartnerAnalytics(partnerId: number, dateRange: DateRange) {
    const [
      volumeByDay,
      operationsByStatus,
      operationsByProduct,
      operationsByAgency,
      commissionsByDay
    ] = await Promise.all([
      this.getVolumeByDay(partnerId, dateRange),
      this.getOperationsByStatus(partnerId, dateRange),
      this.getOperationsByProduct(partnerId, dateRange),
      this.getOperationsByAgency(partnerId, dateRange),
      this.getCommissionsByDay(partnerId, dateRange)
    ]);

    return {
      volume_by_day: volumeByDay,
      operations_by_status: operationsByStatus,
      operations_by_product: operationsByProduct,
      operations_by_agency: operationsByAgency,
      commissions_by_day: commissionsByDay
    };
  }

  // ===== MÉTHODES PRIVÉES =====

  private async getOperationsCount(partnerId: number, dateRange?: DateRange): Promise<number> {
    const where: any = { RefBanque: partnerId };
    if (dateRange) {
      where.Insert_Time = { gte: dateRange.start, lte: dateRange.end };
    }
    return this.prisma.operation.count({ where });
  }

  private async getTotalVolume(partnerId: number, dateRange?: DateRange): Promise<number> {
    const where: any = { RefBanque: partnerId };
    if (dateRange) {
      where.Insert_Time = { gte: dateRange.start, lte: dateRange.end };
    }
    
    const result = await this.prisma.operation.aggregate({
      where,
      _sum: { Amount: true }
    });
    
    return Number(result._sum.Amount) || 0;
  }

  private async getTotalCommissions(partnerId: number, dateRange?: DateRange): Promise<number> {
    const where: any = { RefBanque: partnerId };
    if (dateRange) {
      where.Insert_Time = { gte: dateRange.start, lte: dateRange.end };
    }
    
    const result = await this.prisma.operation.aggregate({
      where,
      _sum: { Commission: true }
    });
    
    return Number(result._sum.Commission) || 0;
  }

  private async getAgenciesCount(partnerId: number): Promise<number> {
    const agencies = await this.prisma.agency.findMany({
      where: {
        cashRegisters: {
          some: {
            operations: {
              some: { RefBanque: partnerId }
            }
          }
        }
      }
    });
    
    return agencies.length;
  }

  private async getActiveUsersCount(partnerId: number): Promise<number> {
    return this.prisma.partnerUser.count({
      where: {
        RefBanque: partnerId,
        IsActive: true
      }
    });
  }

  private async calculateTrends(partnerId: number, currentRange: DateRange) {
    const duration = currentRange.end.getTime() - currentRange.start.getTime();
    const previousStart = new Date(currentRange.start.getTime() - duration);
    const previousEnd = new Date(currentRange.end.getTime() - duration);
    
    const [currentStats, previousStats] = await Promise.all([
      {
        operations: await this.getOperationsCount(partnerId, currentRange),
        volume: await this.getTotalVolume(partnerId, currentRange),
        commissions: await this.getTotalCommissions(partnerId, currentRange)
      },
      {
        operations: await this.getOperationsCount(partnerId, { start: previousStart, end: previousEnd }),
        volume: await this.getTotalVolume(partnerId, { start: previousStart, end: previousEnd }),
        commissions: await this.getTotalCommissions(partnerId, { start: previousStart, end: previousEnd })
      }
    ]);

    return {
      operations_trend: this.calculatePercentageChange(previousStats.operations, currentStats.operations),
      volume_trend: this.calculatePercentageChange(previousStats.volume, currentStats.volume),
      commission_trend: this.calculatePercentageChange(previousStats.commissions, currentStats.commissions)
    };
  }

  private calculatePercentageChange(previous: number, current: number): number {
    if (previous === 0) return current > 0 ? 100 : 0;
    return Math.round(((current - previous) / previous) * 100);
  }

  private async getAgencyStats(partnerId: number, agencyId: number) {
    const where = {
      RefBanque: partnerId,
      cashRegister: { RefAgence: agencyId }
    };

    const [operationsCount, totalVolume, totalCommissions] = await Promise.all([
      this.prisma.operation.count({ where }),
      this.prisma.operation.aggregate({
        where,
        _sum: { Amount: true }
      }).then(result => Number(result._sum.Amount) || 0),
      this.prisma.operation.aggregate({
        where,
        _sum: { Commission: true }
      }).then(result => Number(result._sum.Commission) || 0)
    ]);

    return {
      operations_count: operationsCount,
      total_volume: totalVolume,
      total_commissions: totalCommissions
    };
  }

  private async getVolumeByDay(partnerId: number, dateRange: DateRange) {
    const result = await this.prisma.$queryRaw`
      SELECT 
        DATE(Insert_Time) as date,
        SUM(Amount) as volume,
        COUNT(*) as operations_count
      FROM TbleOperation 
      WHERE RefBanque = ${partnerId}
        AND Insert_Time >= ${dateRange.start}
        AND Insert_Time <= ${dateRange.end}
      GROUP BY DATE(Insert_Time)
      ORDER BY date
    `;
    
    return result;
  }

  private async getOperationsByStatus(partnerId: number, dateRange: DateRange) {
    const result = await this.prisma.operation.groupBy({
      by: ['Status'],
      where: {
        RefBanque: partnerId,
        Insert_Time: {
          gte: dateRange.start,
          lte: dateRange.end
        }
      },
      _count: true,
      _sum: { Amount: true }
    });

    return result.map(item => ({
      status: item.Status,
      count: item._count,
      volume: Number(item._sum.Amount) || 0
    }));
  }

  private async getOperationsByProduct(partnerId: number, dateRange: DateRange) {
    const result = await this.prisma.operation.groupBy({
      by: ['RefProduit'],
      where: {
        RefBanque: partnerId,
        Insert_Time: {
          gte: dateRange.start,
          lte: dateRange.end
        }
      },
      _count: true,
      _sum: { Amount: true, Commission: true }
    });

    // Enrichissement avec les noms de produits
    const enriched = await Promise.all(
      result.map(async (item) => {
        const product = await this.prisma.product.findUnique({
          where: { RefProduit: item.RefProduit },
          select: { NameProduit: true }
        });

        return {
          product_id: item.RefProduit,
          product_name: product?.NameProduit || 'Unknown',
          count: item._count,
          volume: Number(item._sum.Amount) || 0,
          commission: Number(item._sum.Commission) || 0
        };
      })
    );

    return enriched;
  }

  private async getOperationsByAgency(partnerId: number, dateRange: DateRange) {
    const result = await this.prisma.$queryRaw`
      SELECT 
        a.RefAgence as agency_id,
        a.NameAgence as agency_name,
        COUNT(o.RefOperation) as count,
        SUM(o.Amount) as volume,
        SUM(o.Commission) as commission
      FROM TbleOperation o
      JOIN TbleCaisse c ON o.RefCaisse = c.RefCaisse
      JOIN TbleAgence a ON c.RefAgence = a.RefAgence
      WHERE o.RefBanque = ${partnerId}
        AND o.Insert_Time >= ${dateRange.start}
        AND o.Insert_Time <= ${dateRange.end}
      GROUP BY a.RefAgence, a.NameAgence
      ORDER BY volume DESC
    `;

    return result;
  }

  private async getCommissionsByDay(partnerId: number, dateRange: DateRange) {
    const result = await this.prisma.$queryRaw`
      SELECT 
        DATE(Insert_Time) as date,
        SUM(Commission) as commission
      FROM TbleOperation 
      WHERE RefBanque = ${partnerId}
        AND Insert_Time >= ${dateRange.start}
        AND Insert_Time <= ${dateRange.end}
      GROUP BY DATE(Insert_Time)
      ORDER BY date
    `;
    
    return result;
  }
}
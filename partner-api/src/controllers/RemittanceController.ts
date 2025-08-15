import { Request, Response } from 'express';
import { PrismaClient } from '@prisma/client';
import Joi from 'joi';
import { asyncHandler, ValidationError } from '../middleware/errorHandler';
import { logger } from '../utils/logger';

const prisma = new PrismaClient();

// Validation schemas
const createRemittanceSchema = Joi.object({
  sourceAgencyId: Joi.number().integer().required(),
  destinationAgencyId: Joi.number().integer().required(),
  amount: Joi.number().positive().required(),
  currency: Joi.string().valid('EUR', 'USD', 'GBP', 'XOF', 'XAF').default('EUR'),
  description: Joi.string().max(500).optional()
});

const remittanceFiltersSchema = Joi.object({
  status: Joi.string().valid('pending', 'validated', 'cancelled').optional(),
  dateFrom: Joi.date().optional(),
  dateTo: Joi.date().optional(),
  sourceAgencyId: Joi.number().integer().optional(),
  destinationAgencyId: Joi.number().integer().optional()
});

export class RemittanceController {
  /**
   * Get remittances list with filters
   */
  static getRemittances = asyncHandler(async (req: Request, res: Response) => {
    const user = (req as any).user;
    const partnerId = user.RefBanque;
    
    const { error, value } = remittanceFiltersSchema.validate(req.query);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const { status, dateFrom, dateTo, sourceAgencyId, destinationAgencyId } = value;
    const page = parseInt(req.query.page as string) || 1;
    const limit = parseInt(req.query.limit as string) || 20;
    const offset = (page - 1) * limit;

    // Build where clause
    const whereClause: any = {
      RefPays: user.RefPays
    };

    if (status) {
      whereClause.Status = status === 'pending' ? 0 : status === 'validated' ? 1 : 2;
    }

    if (dateFrom || dateTo) {
      whereClause.Insert_Time = {};
      if (dateFrom) whereClause.Insert_Time.gte = new Date(dateFrom);
      if (dateTo) whereClause.Insert_Time.lte = new Date(dateTo);
    }

    if (sourceAgencyId) {
      whereClause.RefAgencyFrom = sourceAgencyId;
    }

    if (destinationAgencyId) {
      whereClause.RefAgencyTo = destinationAgencyId;
    }

    const [remittances, total] = await Promise.all([
      prisma.tbleRemittance.findMany({
        where: whereClause,
        include: {
          TbleAgency_TbleRemittance_RefAgencyFromToTbleAgency: {
            include: { TbleBanque: true }
          },
          TbleAgency_TbleRemittance_RefAgencyToToTbleAgency: {
            include: { TbleBanque: true }
          },
          TbleUsers_TbleRemittance_RefUserToTbleUsers: true,
          TbleUsers_TbleRemittance_RefUserValidatorToTbleUsers: true,
          TbleProduit: true
        },
        orderBy: { Insert_Time: 'desc' },
        skip: offset,
        take: limit
      }),
      prisma.tbleRemittance.count({ where: whereClause })
    ]);

    const formattedRemittances = remittances.map(remittance => ({
      id: remittance.RefRemittance,
      reference: `REM-${remittance.RefRemittance.toString().padStart(6, '0')}`,
      sourceAgency: remittance.TbleAgency_TbleRemittance_RefAgencyFromToTbleAgency?.NameAgency,
      destinationAgency: remittance.TbleAgency_TbleRemittance_RefAgencyToToTbleAgency?.NameAgency,
      amount: remittance.Montant,
      currency: 'EUR', // Default currency
      status: remittance.Status === 0 ? 'pending' : remittance.Status === 1 ? 'validated' : 'cancelled',
      createdAt: remittance.Insert_Time,
      validatedAt: remittance.DateValidation,
      creator: remittance.TbleUsers_TbleRemittance_RefUserToTbleUsers?.Name,
      validator: remittance.TbleUsers_TbleRemittance_RefUserValidatorToTbleUsers?.Name,
      product: remittance.TbleProduit?.NameProduit,
      phoneNumber: remittance.NumTel,
      fullName: remittance.FullName
    }));

    res.json({
      remittances: formattedRemittances,
      pagination: {
        page,
        limit,
        total,
        totalPages: Math.ceil(total / limit)
      }
    });
  });

  /**
   * Create new remittance
   */
  static createRemittance = asyncHandler(async (req: Request, res: Response) => {
    const { error, value } = createRemittanceSchema.validate(req.body);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const user = (req as any).user;
    const { sourceAgencyId, destinationAgencyId, amount, currency, description } = value;

    // Verify agencies exist and belong to accessible countries
    const [sourceAgency, destinationAgency] = await Promise.all([
      prisma.tbleAgency.findUnique({
        where: { RefAgency: sourceAgencyId },
        include: { TbleBanque: true }
      }),
      prisma.tbleAgency.findUnique({
        where: { RefAgency: destinationAgencyId },
        include: { TbleBanque: true }
      })
    ]);

    if (!sourceAgency || !destinationAgency) {
      return res.status(404).json({ error: 'Agence source ou destination non trouvée' });
    }

    if (sourceAgencyId === destinationAgencyId) {
      return res.status(400).json({ error: 'Les agences source et destination doivent être différentes' });
    }

    // Get default product for remittance
    const product = await prisma.tbleProduit.findFirst({
      where: {
        RefBanque: user.RefBanque,
        NameProduit: { contains: 'Remise' }
      }
    });

    // Create remittance
    const remittance = await prisma.tbleRemittance.create({
      data: {
        RefCaisse: 1, // Default caisse
        RefProduit: product?.RefProduit || 1,
        RefType: 1, // Deposit type
        NumTel: '',
        FullName: `Remise ${sourceAgency.NameAgency} -> ${destinationAgency.NameAgency}`,
        Montant: amount,
        RefUser: user.RefUser,
        Insert_Time: new Date(),
        Status: 0, // Pending
        RefPays: user.RefPays,
        RefAgencyFrom: sourceAgencyId,
        RefAgencyTo: destinationAgencyId,
        Remarque: description
      },
      include: {
        TbleAgency_TbleRemittance_RefAgencyFromToTbleAgency: true,
        TbleAgency_TbleRemittance_RefAgencyToToTbleAgency: true,
        TbleProduit: true
      }
    });

    logger.info(`Remittance created: ${amount} from agency ${sourceAgencyId} to ${destinationAgencyId}`, {
      userId: user.RefUser,
      partnerId: user.RefBanque,
      remittanceId: remittance.RefRemittance
    });

    res.status(201).json({
      id: remittance.RefRemittance,
      reference: `REM-${remittance.RefRemittance.toString().padStart(6, '0')}`,
      sourceAgency: remittance.TbleAgency_TbleRemittance_RefAgencyFromToTbleAgency?.NameAgency,
      destinationAgency: remittance.TbleAgency_TbleRemittance_RefAgencyToToTbleAgency?.NameAgency,
      amount: remittance.Montant,
      status: 'pending',
      createdAt: remittance.Insert_Time
    });
  });

  /**
   * Get remittance statistics
   */
  static getStats = asyncHandler(async (req: Request, res: Response) => {
    const user = (req as any).user;
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const [pending, totalAmount, todayCount] = await Promise.all([
      prisma.tbleRemittance.count({
        where: {
          RefPays: user.RefPays,
          Status: 0
        }
      }),
      prisma.tbleRemittance.aggregate({
        where: {
          RefPays: user.RefPays,
          Status: 1 // Validated only
        },
        _sum: {
          Montant: true
        }
      }),
      prisma.tbleRemittance.count({
        where: {
          RefPays: user.RefPays,
          Insert_Time: {
            gte: today
          }
        }
      })
    ]);

    res.json({
      pending,
      totalAmount: totalAmount._sum.Montant || 0,
      today: todayCount
    });
  });

  /**
   * Validate remittance
   */
  static validateRemittance = asyncHandler(async (req: Request, res: Response) => {
    const user = (req as any).user;
    const remittanceId = parseInt(req.params.id);

    const remittance = await prisma.tbleRemittance.findUnique({
      where: { RefRemittance: remittanceId },
      include: {
        TbleAgency_TbleRemittance_RefAgencyFromToTbleAgency: true,
        TbleAgency_TbleRemittance_RefAgencyToToTbleAgency: true
      }
    });

    if (!remittance) {
      return res.status(404).json({ error: 'Remise non trouvée' });
    }

    if (remittance.RefPays !== user.RefPays) {
      return res.status(403).json({ error: 'Accès non autorisé' });
    }

    if (remittance.Status !== 0) {
      return res.status(400).json({ error: 'Cette remise a déjà été traitée' });
    }

    // Update remittance status
    const updatedRemittance = await prisma.tbleRemittance.update({
      where: { RefRemittance: remittanceId },
      data: {
        Status: 1, // Validated
        RefUserValidator: user.RefUser,
        DateValidation: new Date()
      }
    });

    logger.info(`Remittance validated: ${remittanceId}`, {
      userId: user.RefUser,
      partnerId: user.RefBanque,
      remittanceId
    });

    res.json({
      message: 'Remise validée avec succès',
      id: updatedRemittance.RefRemittance,
      status: 'validated',
      validatedAt: updatedRemittance.DateValidation
    });
  });

  /**
   * Cancel remittance
   */
  static cancelRemittance = asyncHandler(async (req: Request, res: Response) => {
    const user = (req as any).user;
    const remittanceId = parseInt(req.params.id);

    const remittance = await prisma.tbleRemittance.findUnique({
      where: { RefRemittance: remittanceId }
    });

    if (!remittance) {
      return res.status(404).json({ error: 'Remise non trouvée' });
    }

    if (remittance.RefPays !== user.RefPays) {
      return res.status(403).json({ error: 'Accès non autorisé' });
    }

    if (remittance.Status !== 0) {
      return res.status(400).json({ error: 'Cette remise ne peut plus être annulée' });
    }

    // Update remittance status
    const updatedRemittance = await prisma.tbleRemittance.update({
      where: { RefRemittance: remittanceId },
      data: {
        Status: 2, // Cancelled
        RefUserValidator: user.RefUser,
        DateValidation: new Date()
      }
    });

    logger.info(`Remittance cancelled: ${remittanceId}`, {
      userId: user.RefUser,
      partnerId: user.RefBanque,
      remittanceId
    });

    res.json({
      message: 'Remise annulée avec succès',
      id: updatedRemittance.RefRemittance,
      status: 'cancelled'
    });
  });

  /**
   * Get remittance details
   */
  static getRemittanceDetails = asyncHandler(async (req: Request, res: Response) => {
    const user = (req as any).user;
    const remittanceId = parseInt(req.params.id);

    const remittance = await prisma.tbleRemittance.findUnique({
      where: { RefRemittance: remittanceId },
      include: {
        TbleAgency_TbleRemittance_RefAgencyFromToTbleAgency: {
          include: { TbleBanque: true }
        },
        TbleAgency_TbleRemittance_RefAgencyToToTbleAgency: {
          include: { TbleBanque: true }
        },
        TbleUsers_TbleRemittance_RefUserToTbleUsers: true,
        TbleUsers_TbleRemittance_RefUserValidatorToTbleUsers: true,
        TbleProduit: true
      }
    });

    if (!remittance) {
      return res.status(404).json({ error: 'Remise non trouvée' });
    }

    if (remittance.RefPays !== user.RefPays) {
      return res.status(403).json({ error: 'Accès non autorisé' });
    }

    const formattedRemittance = {
      id: remittance.RefRemittance,
      reference: `REM-${remittance.RefRemittance.toString().padStart(6, '0')}`,
      sourceAgency: {
        id: remittance.RefAgencyFrom,
        name: remittance.TbleAgency_TbleRemittance_RefAgencyFromToTbleAgency?.NameAgency,
        partner: remittance.TbleAgency_TbleRemittance_RefAgencyFromToTbleAgency?.TbleBanque?.NameBanque
      },
      destinationAgency: {
        id: remittance.RefAgencyTo,
        name: remittance.TbleAgency_TbleRemittance_RefAgencyToToTbleAgency?.NameAgency,
        partner: remittance.TbleAgency_TbleRemittance_RefAgencyToToTbleAgency?.TbleBanque?.NameBanque
      },
      amount: remittance.Montant,
      currency: 'EUR',
      status: remittance.Status === 0 ? 'pending' : remittance.Status === 1 ? 'validated' : 'cancelled',
      createdAt: remittance.Insert_Time,
      validatedAt: remittance.DateValidation,
      creator: {
        id: remittance.RefUser,
        name: remittance.TbleUsers_TbleRemittance_RefUserToTbleUsers?.Name
      },
      validator: remittance.RefUserValidator ? {
        id: remittance.RefUserValidator,
        name: remittance.TbleUsers_TbleRemittance_RefUserValidatorToTbleUsers?.Name
      } : null,
      product: {
        id: remittance.RefProduit,
        name: remittance.TbleProduit?.NameProduit
      },
      phoneNumber: remittance.NumTel,
      fullName: remittance.FullName,
      description: remittance.Remarque
    };

    res.json(formattedRemittance);
  });
}

import { Request, Response } from 'express';
import { PrismaClient } from '@prisma/client';
import Joi from 'joi';
import { asyncHandler, ValidationError } from '../middleware/errorHandler';
import { logger } from '../utils/logger';

const prisma = new PrismaClient();

// Validation schemas
const transferSchema = Joi.object({
  destinationCaisseId: Joi.number().integer().required(),
  amount: Joi.number().positive().required(),
  reason: Joi.string().min(3).max(500).required()
});

const depositSchema = Joi.object({
  amount: Joi.number().positive().required(),
  source: Joi.string().valid('bank_transfer', 'cash_delivery', 'other').required(),
  description: Joi.string().min(3).max(500).required()
});

export class CaisseController {
  /**
   * Get cash register balance
   */
  static getBalance = asyncHandler(async (req: Request, res: Response) => {
    const user = (req as any).user;
    const partnerId = user.RefBanque;

    // Get all cash registers for this partner
    const caisses = await prisma.tbleCaisse.findMany({
      where: {
        TbleAgency: {
          RefPays: user.RefPays,
          TbleBanque: {
            RefBanque: partnerId
          }
        }
      },
      include: {
        TbleAgency: {
          include: {
            TbleBanque: true
          }
        }
      }
    });

    // Calculate balances
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const balanceData = {
      current: 0,
      todayIn: 0,
      todayOut: 0
    };

    for (const caisse of caisses) {
      // Get current balance from TbleSolde
      const latestBalance = await prisma.tbleSolde.findFirst({
        where: { RefCaisse: caisse.RefCaisse },
        orderBy: { Insert_Time: 'desc' }
      });

      if (latestBalance) {
        balanceData.current += latestBalance.Solde;
      }

      // Get today's movements
      const todayOperations = await prisma.tbleOperations.findMany({
        where: {
          RefCaisse: caisse.RefCaisse,
          Insert_Time: {
            gte: today
          }
        }
      });

      todayOperations.forEach(op => {
        if (op.RefType === 1) { // Deposit
          balanceData.todayIn += op.MontantVersement;
        } else if (op.RefType === 2) { // Withdrawal
          balanceData.todayOut += op.MontantVersement;
        }
      });
    }

    res.json(balanceData);
  });

  /**
   * Get cash register movements
   */
  static getMovements = asyncHandler(async (req: Request, res: Response) => {
    const user = (req as any).user;
    const partnerId = user.RefBanque;
    const limit = parseInt(req.query.limit as string) || 20;

    const movements = await prisma.tbleOperations.findMany({
      where: {
        TbleCaisse: {
          TbleAgency: {
            RefPays: user.RefPays,
            TbleBanque: {
              RefBanque: partnerId
            }
          }
        }
      },
      include: {
        TbleCaisse: {
          include: {
            TbleAgency: true
          }
        },
        TbleUsers: true
      },
      orderBy: {
        Insert_Time: 'desc'
      },
      take: limit
    });

    const formattedMovements = movements.map(movement => ({
      id: movement.RefOperations,
      type: movement.RefType === 1 ? 'deposit' : 
            movement.RefType === 2 ? 'withdrawal' : 
            movement.RefType === 3 ? 'transfer_in' : 'transfer_out',
      amount: movement.MontantVersement,
      description: `${movement.RefType === 1 ? 'Dépôt' : 'Retrait'} - ${movement.NameClient}`,
      reference: movement.UniqueId,
      createdAt: movement.Insert_Time,
      cashRegister: movement.TbleCaisse?.NameCaisse,
      agency: movement.TbleCaisse?.TbleAgency?.NameAgency,
      user: movement.TbleUsers?.Name
    }));

    res.json(formattedMovements);
  });

  /**
   * Transfer between cash registers
   */
  static transfer = asyncHandler(async (req: Request, res: Response) => {
    const { error, value } = transferSchema.validate(req.body);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const user = (req as any).user;
    const { destinationCaisseId, amount, reason } = value;

    // Verify source caisse belongs to partner
    const sourceCaisse = await prisma.tbleCaisse.findFirst({
      where: {
        TbleAgency: {
          RefPays: user.RefPays,
          TbleBanque: {
            RefBanque: user.RefBanque
          }
        }
      },
      include: {
        TbleAgency: true
      }
    });

    if (!sourceCaisse) {
      return res.status(404).json({ error: 'Caisse source non trouvée' });
    }

    // Verify destination caisse exists
    const destinationCaisse = await prisma.tbleCaisse.findUnique({
      where: { RefCaisse: destinationCaisseId },
      include: { TbleAgency: true }
    });

    if (!destinationCaisse) {
      return res.status(404).json({ error: 'Caisse destination non trouvée' });
    }

    // Check balance
    const currentBalance = await prisma.tbleSolde.findFirst({
      where: { RefCaisse: sourceCaisse.RefCaisse },
      orderBy: { Insert_Time: 'desc' }
    });

    if (!currentBalance || currentBalance.Solde < amount) {
      return res.status(400).json({ error: 'Solde insuffisant' });
    }

    // Create transfer operations
    const transferOut = await prisma.tbleOperations.create({
      data: {
        RefType: 4, // Transfer out
        MontantVersement: amount,
        NameClient: `Transfert vers ${destinationCaisse.NameCaisse}`,
        RefCaisse: sourceCaisse.RefCaisse,
        RefUser: user.RefUser,
        RefPays: user.RefPays,
        UniqueId: `TRF-OUT-${Date.now()}`,
        Insert_Time: new Date(),
        Remarque: reason
      }
    });

    const transferIn = await prisma.tbleOperations.create({
      data: {
        RefType: 3, // Transfer in
        MontantVersement: amount,
        NameClient: `Transfert depuis ${sourceCaisse.NameCaisse}`,
        RefCaisse: destinationCaisseId,
        RefUser: user.RefUser,
        RefPays: user.RefPays,
        UniqueId: `TRF-IN-${Date.now()}`,
        Insert_Time: new Date(),
        Remarque: reason
      }
    });

    // Update balances
    await prisma.tbleSolde.create({
      data: {
        RefCaisse: sourceCaisse.RefCaisse,
        Solde: currentBalance.Solde - amount,
        Insert_Time: new Date()
      }
    });

    const destBalance = await prisma.tbleSolde.findFirst({
      where: { RefCaisse: destinationCaisseId },
      orderBy: { Insert_Time: 'desc' }
    });

    await prisma.tbleSolde.create({
      data: {
        RefCaisse: destinationCaisseId,
        Solde: (destBalance?.Solde || 0) + amount,
        Insert_Time: new Date()
      }
    });

    logger.info(`Transfer completed: ${amount} from caisse ${sourceCaisse.RefCaisse} to ${destinationCaisseId}`, {
      userId: user.RefUser,
      partnerId: user.RefBanque,
      transferOutId: transferOut.RefOperations,
      transferInId: transferIn.RefOperations
    });

    res.status(201).json({
      message: 'Transfert effectué avec succès',
      transferOut: transferOut.RefOperations,
      transferIn: transferIn.RefOperations
    });
  });

  /**
   * Make a deposit
   */
  static deposit = asyncHandler(async (req: Request, res: Response) => {
    const { error, value } = depositSchema.validate(req.body);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const user = (req as any).user;
    const { amount, source, description } = value;

    // Get user's default caisse
    const caisse = await prisma.tbleCaisse.findFirst({
      where: {
        TbleAgency: {
          RefPays: user.RefPays,
          TbleBanque: {
            RefBanque: user.RefBanque
          }
        }
      }
    });

    if (!caisse) {
      return res.status(404).json({ error: 'Aucune caisse disponible' });
    }

    // Create deposit operation
    const deposit = await prisma.tbleOperations.create({
      data: {
        RefType: 1, // Deposit
        MontantVersement: amount,
        NameClient: `Dépôt ${source}`,
        RefCaisse: caisse.RefCaisse,
        RefUser: user.RefUser,
        RefPays: user.RefPays,
        UniqueId: `DEP-${Date.now()}`,
        Insert_Time: new Date(),
        Remarque: description
      }
    });

    // Update balance
    const currentBalance = await prisma.tbleSolde.findFirst({
      where: { RefCaisse: caisse.RefCaisse },
      orderBy: { Insert_Time: 'desc' }
    });

    await prisma.tbleSolde.create({
      data: {
        RefCaisse: caisse.RefCaisse,
        Solde: (currentBalance?.Solde || 0) + amount,
        Insert_Time: new Date()
      }
    });

    logger.info(`Deposit completed: ${amount} to caisse ${caisse.RefCaisse}`, {
      userId: user.RefUser,
      partnerId: user.RefBanque,
      operationId: deposit.RefOperations
    });

    res.status(201).json({
      message: 'Dépôt effectué avec succès',
      operation: deposit.RefOperations
    });
  });

  /**
   * Get available cash registers for transfer
   */
  static getAvailableCaisses = asyncHandler(async (req: Request, res: Response) => {
    const user = (req as any).user;

    const caisses = await prisma.tbleCaisse.findMany({
      where: {
        TbleAgency: {
          RefPays: user.RefPays
        }
      },
      include: {
        TbleAgency: {
          include: {
            TbleBanque: true
          }
        }
      }
    });

    const formattedCaisses = caisses.map(caisse => ({
      id: caisse.RefCaisse,
      name: `${caisse.NameCaisse} - ${caisse.TbleAgency?.NameAgency}`,
      agency: caisse.TbleAgency?.NameAgency,
      partner: caisse.TbleAgency?.TbleBanque?.NameBanque
    }));

    res.json(formattedCaisses);
  });
}

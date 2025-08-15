import { Request, Response } from 'express';
import { PrismaClient } from '@prisma/client';
import Joi from 'joi';
import { asyncHandler, ValidationError } from '../middleware/errorHandler';
import { logger } from '../utils/logger';
import bcrypt from 'bcrypt';

const prisma = new PrismaClient();

// Validation schemas
const createUserSchema = Joi.object({
  name: Joi.string().min(2).max(100).required(),
  email: Joi.string().email().required(),
  password: Joi.string().min(8).required(),
  role: Joi.string().valid('admin', 'operator', 'viewer').default('operator'),
  agencyId: Joi.number().integer().optional(),
  permissions: Joi.array().items(Joi.string()).optional()
});

const updateUserSchema = Joi.object({
  name: Joi.string().min(2).max(100).optional(),
  email: Joi.string().email().optional(),
  role: Joi.string().valid('admin', 'operator', 'viewer').optional(),
  agencyId: Joi.number().integer().optional(),
  isActive: Joi.boolean().optional(),
  permissions: Joi.array().items(Joi.string()).optional()
});

const createAgencySchema = Joi.object({
  name: Joi.string().min(2).max(100).required(),
  address: Joi.string().max(255).optional(),
  phone: Joi.string().max(20).optional(),
  email: Joi.string().email().optional(),
  isActive: Joi.boolean().default(true)
});

const createProductSchema = Joi.object({
  name: Joi.string().min(2).max(100).required(),
  description: Joi.string().max(500).optional(),
  commissionRate: Joi.number().min(0).max(100).required(),
  minAmount: Joi.number().min(0).optional(),
  maxAmount: Joi.number().min(0).optional(),
  isActive: Joi.boolean().default(true)
});

export class AdminController {
  /**
   * Get users list
   */
  static getUsers = asyncHandler(async (req: Request, res: Response) => {
    const user = (req as any).user;
    const page = parseInt(req.query.page as string) || 1;
    const limit = parseInt(req.query.limit as string) || 20;
    const offset = (page - 1) * limit;

    const whereClause: any = {
      RefPays: user.RefPays
    };

    // Filter by partner if user is not super admin
    if (user.RefBanque) {
      whereClause.RefBanque = user.RefBanque;
    }

    const [users, total] = await Promise.all([
      prisma.tbleUsers.findMany({
        where: whereClause,
        include: {
          TbleAgency: true,
          TbleBanque: true
        },
        orderBy: { Name: 'asc' },
        skip: offset,
        take: limit
      }),
      prisma.tbleUsers.count({ where: whereClause })
    ]);

    const formattedUsers = users.map(user => ({
      id: user.RefUser,
      name: user.Name,
      email: user.Email,
      role: user.Role || 'operator',
      agency: user.TbleAgency ? {
        id: user.TbleAgency.RefAgency,
        name: user.TbleAgency.NameAgency
      } : null,
      partner: user.TbleBanque ? {
        id: user.TbleBanque.RefBanque,
        name: user.TbleBanque.NameBanque
      } : null,
      isActive: user.IsActive === 1,
      lastLogin: user.LastLogin,
      createdAt: user.CreatedAt
    }));

    res.json({
      users: formattedUsers,
      pagination: {
        page,
        limit,
        total,
        totalPages: Math.ceil(total / limit)
      }
    });
  });

  /**
   * Create new user
   */
  static createUser = asyncHandler(async (req: Request, res: Response) => {
    const { error, value } = createUserSchema.validate(req.body);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const currentUser = (req as any).user;
    const { name, email, password, role, agencyId, permissions } = value;

    // Check if email already exists
    const existingUser = await prisma.tbleUsers.findFirst({
      where: { Email: email }
    });

    if (existingUser) {
      return res.status(400).json({ error: 'Un utilisateur avec cet email existe déjà' });
    }

    // Hash password
    const hashedPassword = await bcrypt.hash(password, 12);

    // Create user
    const newUser = await prisma.tbleUsers.create({
      data: {
        Name: name,
        Email: email,
        Password: hashedPassword,
        Role: role,
        RefPays: currentUser.RefPays,
        RefBanque: currentUser.RefBanque,
        RefAgency: agencyId,
        IsActive: 1,
        CreatedAt: new Date()
      },
      include: {
        TbleAgency: true,
        TbleBanque: true
      }
    });

    logger.info(`User created: ${email}`, {
      userId: currentUser.RefUser,
      partnerId: currentUser.RefBanque,
      newUserId: newUser.RefUser
    });

    res.status(201).json({
      id: newUser.RefUser,
      name: newUser.Name,
      email: newUser.Email,
      role: newUser.Role,
      agency: newUser.TbleAgency ? {
        id: newUser.TbleAgency.RefAgency,
        name: newUser.TbleAgency.NameAgency
      } : null,
      isActive: true,
      createdAt: newUser.CreatedAt
    });
  });

  /**
   * Update user
   */
  static updateUser = asyncHandler(async (req: Request, res: Response) => {
    const { error, value } = updateUserSchema.validate(req.body);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const currentUser = (req as any).user;
    const userId = parseInt(req.params.id);
    const { name, email, role, agencyId, isActive, permissions } = value;

    // Check if user exists and belongs to same partner
    const existingUser = await prisma.tbleUsers.findFirst({
      where: {
        RefUser: userId,
        RefPays: currentUser.RefPays,
        ...(currentUser.RefBanque && { RefBanque: currentUser.RefBanque })
      }
    });

    if (!existingUser) {
      return res.status(404).json({ error: 'Utilisateur non trouvé' });
    }

    // Check email uniqueness if email is being changed
    if (email && email !== existingUser.Email) {
      const emailExists = await prisma.tbleUsers.findFirst({
        where: { 
          Email: email,
          RefUser: { not: userId }
        }
      });

      if (emailExists) {
        return res.status(400).json({ error: 'Un utilisateur avec cet email existe déjà' });
      }
    }

    // Update user
    const updatedUser = await prisma.tbleUsers.update({
      where: { RefUser: userId },
      data: {
        ...(name && { Name: name }),
        ...(email && { Email: email }),
        ...(role && { Role: role }),
        ...(agencyId !== undefined && { RefAgency: agencyId }),
        ...(isActive !== undefined && { IsActive: isActive ? 1 : 0 }),
        UpdatedAt: new Date()
      },
      include: {
        TbleAgency: true,
        TbleBanque: true
      }
    });

    logger.info(`User updated: ${userId}`, {
      userId: currentUser.RefUser,
      partnerId: currentUser.RefBanque,
      updatedUserId: userId
    });

    res.json({
      id: updatedUser.RefUser,
      name: updatedUser.Name,
      email: updatedUser.Email,
      role: updatedUser.Role,
      agency: updatedUser.TbleAgency ? {
        id: updatedUser.TbleAgency.RefAgency,
        name: updatedUser.TbleAgency.NameAgency
      } : null,
      isActive: updatedUser.IsActive === 1,
      updatedAt: updatedUser.UpdatedAt
    });
  });

  /**
   * Delete user
   */
  static deleteUser = asyncHandler(async (req: Request, res: Response) => {
    const currentUser = (req as any).user;
    const userId = parseInt(req.params.id);

    // Check if user exists and belongs to same partner
    const existingUser = await prisma.tbleUsers.findFirst({
      where: {
        RefUser: userId,
        RefPays: currentUser.RefPays,
        ...(currentUser.RefBanque && { RefBanque: currentUser.RefBanque })
      }
    });

    if (!existingUser) {
      return res.status(404).json({ error: 'Utilisateur non trouvé' });
    }

    // Prevent self-deletion
    if (userId === currentUser.RefUser) {
      return res.status(400).json({ error: 'Vous ne pouvez pas supprimer votre propre compte' });
    }

    // Soft delete by deactivating
    await prisma.tbleUsers.update({
      where: { RefUser: userId },
      data: {
        IsActive: 0,
        UpdatedAt: new Date()
      }
    });

    logger.info(`User deleted: ${userId}`, {
      userId: currentUser.RefUser,
      partnerId: currentUser.RefBanque,
      deletedUserId: userId
    });

    res.json({ message: 'Utilisateur supprimé avec succès' });
  });

  /**
   * Get agencies list
   */
  static getAgencies = asyncHandler(async (req: Request, res: Response) => {
    const user = (req as any).user;
    const page = parseInt(req.query.page as string) || 1;
    const limit = parseInt(req.query.limit as string) || 20;
    const offset = (page - 1) * limit;

    const whereClause: any = {
      RefPays: user.RefPays
    };

    const [agencies, total] = await Promise.all([
      prisma.tbleAgency.findMany({
        where: whereClause,
        include: {
          TbleBanque: true,
          _count: {
            select: {
              TbleUsers: true,
              TbleCaisse: true
            }
          }
        },
        orderBy: { NameAgency: 'asc' },
        skip: offset,
        take: limit
      }),
      prisma.tbleAgency.count({ where: whereClause })
    ]);

    const formattedAgencies = agencies.map(agency => ({
      id: agency.RefAgency,
      name: agency.NameAgency,
      address: agency.AdresseAgency,
      phone: agency.TelAgency,
      email: agency.EmailAgency,
      partner: agency.TbleBanque ? {
        id: agency.TbleBanque.RefBanque,
        name: agency.TbleBanque.NameBanque
      } : null,
      usersCount: agency._count.TbleUsers,
      caissesCount: agency._count.TbleCaisse,
      isActive: agency.IsActive === 1,
      createdAt: agency.CreatedAt
    }));

    res.json({
      agencies: formattedAgencies,
      pagination: {
        page,
        limit,
        total,
        totalPages: Math.ceil(total / limit)
      }
    });
  });

  /**
   * Create new agency
   */
  static createAgency = asyncHandler(async (req: Request, res: Response) => {
    const { error, value } = createAgencySchema.validate(req.body);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const currentUser = (req as any).user;
    const { name, address, phone, email, isActive } = value;

    // Create agency
    const newAgency = await prisma.tbleAgency.create({
      data: {
        NameAgency: name,
        AdresseAgency: address,
        TelAgency: phone,
        EmailAgency: email,
        RefPays: currentUser.RefPays,
        RefBanque: currentUser.RefBanque,
        IsActive: isActive ? 1 : 0,
        CreatedAt: new Date()
      },
      include: {
        TbleBanque: true
      }
    });

    logger.info(`Agency created: ${name}`, {
      userId: currentUser.RefUser,
      partnerId: currentUser.RefBanque,
      agencyId: newAgency.RefAgency
    });

    res.status(201).json({
      id: newAgency.RefAgency,
      name: newAgency.NameAgency,
      address: newAgency.AdresseAgency,
      phone: newAgency.TelAgency,
      email: newAgency.EmailAgency,
      isActive: newAgency.IsActive === 1,
      createdAt: newAgency.CreatedAt
    });
  });

  /**
   * Get products list
   */
  static getProducts = asyncHandler(async (req: Request, res: Response) => {
    const user = (req as any).user;
    const page = parseInt(req.query.page as string) || 1;
    const limit = parseInt(req.query.limit as string) || 20;
    const offset = (page - 1) * limit;

    const whereClause: any = {};

    // Filter by partner if user has one
    if (user.RefBanque) {
      whereClause.RefBanque = user.RefBanque;
    }

    const [products, total] = await Promise.all([
      prisma.tbleProduit.findMany({
        where: whereClause,
        include: {
          TbleBanque: true,
          _count: {
            select: {
              TbleOperations: true
            }
          }
        },
        orderBy: { NameProduit: 'asc' },
        skip: offset,
        take: limit
      }),
      prisma.tbleProduit.count({ where: whereClause })
    ]);

    const formattedProducts = products.map(product => ({
      id: product.RefProduit,
      name: product.NameProduit,
      description: product.Description,
      commissionRate: product.Commission,
      minAmount: product.MinAmount,
      maxAmount: product.MaxAmount,
      partner: product.TbleBanque ? {
        id: product.TbleBanque.RefBanque,
        name: product.TbleBanque.NameBanque
      } : null,
      operationsCount: product._count.TbleOperations,
      isActive: product.IsActive === 1,
      createdAt: product.CreatedAt
    }));

    res.json({
      products: formattedProducts,
      pagination: {
        page,
        limit,
        total,
        totalPages: Math.ceil(total / limit)
      }
    });
  });

  /**
   * Create new product
   */
  static createProduct = asyncHandler(async (req: Request, res: Response) => {
    const { error, value } = createProductSchema.validate(req.body);
    if (error) {
      throw new ValidationError(error.details[0].message);
    }

    const currentUser = (req as any).user;
    const { name, description, commissionRate, minAmount, maxAmount, isActive } = value;

    // Create product
    const newProduct = await prisma.tbleProduit.create({
      data: {
        NameProduit: name,
        Description: description,
        Commission: commissionRate,
        MinAmount: minAmount,
        MaxAmount: maxAmount,
        RefBanque: currentUser.RefBanque,
        IsActive: isActive ? 1 : 0,
        CreatedAt: new Date()
      },
      include: {
        TbleBanque: true
      }
    });

    logger.info(`Product created: ${name}`, {
      userId: currentUser.RefUser,
      partnerId: currentUser.RefBanque,
      productId: newProduct.RefProduit
    });

    res.status(201).json({
      id: newProduct.RefProduit,
      name: newProduct.NameProduit,
      description: newProduct.Description,
      commissionRate: newProduct.Commission,
      minAmount: newProduct.MinAmount,
      maxAmount: newProduct.MaxAmount,
      isActive: newProduct.IsActive === 1,
      createdAt: newProduct.CreatedAt
    });
  });

  /**
   * Get permissions list
   */
  static getPermissions = asyncHandler(async (req: Request, res: Response) => {
    const availablePermissions = [
      {
        id: 'view_dashboard',
        name: 'Voir le dashboard',
        description: 'Accès au tableau de bord principal'
      },
      {
        id: 'view_operations',
        name: 'Voir les opérations',
        description: 'Consulter la liste des opérations'
      },
      {
        id: 'create_operations',
        name: 'Créer des opérations',
        description: 'Créer de nouvelles opérations'
      },
      {
        id: 'approve_operations',
        name: 'Approuver les opérations',
        description: 'Valider ou rejeter les opérations'
      },
      {
        id: 'view_analytics',
        name: 'Voir les analytics',
        description: 'Accès aux rapports et statistiques'
      },
      {
        id: 'export_data',
        name: 'Exporter les données',
        description: 'Exporter les données au format Excel/PDF'
      },
      {
        id: 'manage_users',
        name: 'Gérer les utilisateurs',
        description: 'Créer, modifier et supprimer des utilisateurs'
      },
      {
        id: 'manage_agencies',
        name: 'Gérer les agences',
        description: 'Créer et modifier des agences'
      },
      {
        id: 'manage_products',
        name: 'Gérer les produits',
        description: 'Créer et modifier des produits'
      },
      {
        id: 'view_caisse',
        name: 'Voir la caisse',
        description: 'Consulter les soldes et mouvements de caisse'
      },
      {
        id: 'manage_caisse',
        name: 'Gérer la caisse',
        description: 'Effectuer des transferts et dépôts'
      },
      {
        id: 'view_remittance',
        name: 'Voir les remises',
        description: 'Consulter les remises inter-agences'
      },
      {
        id: 'manage_remittance',
        name: 'Gérer les remises',
        description: 'Créer et valider des remises'
      }
    ];

    res.json({ permissions: availablePermissions });
  });
}

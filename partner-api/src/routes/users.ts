import { Router } from 'express';
import { Request, Response } from 'express';
import { PrismaClient } from '@prisma/client';
import Joi from 'joi';
import { AuthService } from '@/services/AuthService';
import { asyncHandler, ValidationError } from '@/middleware/errorHandler';
import { PARTNER_PERMISSIONS } from '@/types/partner';
import { requirePermission } from '@/middleware/partnerAuth';

const router = Router();
const prisma = new PrismaClient();
const authService = new AuthService(prisma);

// Schémas de validation
const updateUserSchema = Joi.object({
  name: Joi.string().min(2).optional(),
  email: Joi.string().email().optional(),
  role: Joi.string().valid('partner_admin', 'partner_viewer', 'partner_operator').optional(),
  is_active: Joi.boolean().optional(),
  permissions: Joi.object({
    viewOperations: Joi.boolean().optional(),
    viewAnalytics: Joi.boolean().optional(),
    viewAgencies: Joi.boolean().optional(),
    exportData: Joi.boolean().optional(),
    manageUsers: Joi.boolean().optional(),
    apiAccess: Joi.boolean().optional()
  }).optional()
});

/**
 * @swagger
 * tags:
 *   name: Users
 *   description: Gestion des utilisateurs partenaires
 */

/**
 * @swagger
 * /users:
 *   get:
 *     summary: Liste des utilisateurs du partenaire
 *     tags: [Users]
 *     security:
 *       - BearerAuth: []
 *     responses:
 *       200:
 *         description: Liste des utilisateurs récupérée
 */
const getUsers = asyncHandler(async (req: Request, res: Response) => {
  const partnerId = req.partnerContext?.partnerId;

  if (!partnerId) {
    throw new ValidationError('Partner context not found');
  }

  const users = await prisma.partnerUser.findMany({
    where: { RefBanque: partnerId },
    select: {
      RefUser: true,
      Name: true,
      Email: true,
      Role: true,
      IsActive: true,
      LastLogin: true,
      CreatedAt: true,
      ViewOperations: true,
      ViewAnalytics: true,
      ViewAgencies: true,
      ExportData: true,
      ManageUsers: true,
      ApiAccess: true
    },
    orderBy: { CreatedAt: 'desc' }
  });

  const formattedUsers = users.map(user => ({
    id: user.RefUser,
    name: user.Name,
    email: user.Email,
    role: user.Role,
    is_active: user.IsActive,
    last_login: user.LastLogin,
    created_at: user.CreatedAt,
    permissions: {
      view_operations: user.ViewOperations,
      view_analytics: user.ViewAnalytics,
      view_agencies: user.ViewAgencies,
      export_data: user.ExportData,
      manage_users: user.ManageUsers,
      api_access: user.ApiAccess
    }
  }));

  res.status(200).json({
    success: true,
    data: formattedUsers
  });
});

/**
 * @swagger
 * /users/{id}:
 *   get:
 *     summary: Détails d'un utilisateur spécifique
 *     tags: [Users]
 *     security:
 *       - BearerAuth: []
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: number
 *         description: ID de l'utilisateur
 *     responses:
 *       200:
 *         description: Détails de l'utilisateur
 */
const getUser = asyncHandler(async (req: Request, res: Response) => {
  const partnerId = req.partnerContext?.partnerId;
  const userId = parseInt(req.params.id);

  if (!partnerId) {
    throw new ValidationError('Partner context not found');
  }

  if (!userId || isNaN(userId)) {
    throw new ValidationError('Invalid user ID');
  }

  const user = await prisma.partnerUser.findFirst({
    where: { 
      RefUser: userId,
      RefBanque: partnerId // S'assurer que l'utilisateur appartient au partenaire
    },
    select: {
      RefUser: true,
      Name: true,
      Email: true,
      Login: true,
      Role: true,
      IsActive: true,
      LastLogin: true,
      CreatedAt: true,
      UpdatedAt: true,
      ViewOperations: true,
      ViewAnalytics: true,
      ViewAgencies: true,
      ExportData: true,
      ManageUsers: true,
      ApiAccess: true
    }
  });

  if (!user) {
    return res.status(404).json({
      success: false,
      error: 'User not found'
    });
  }

  const formattedUser = {
    id: user.RefUser,
    name: user.Name,
    email: user.Email,
    login: user.Login,
    role: user.Role,
    is_active: user.IsActive,
    last_login: user.LastLogin,
    created_at: user.CreatedAt,
    updated_at: user.UpdatedAt,
    permissions: {
      view_operations: user.ViewOperations,
      view_analytics: user.ViewAnalytics,
      view_agencies: user.ViewAgencies,
      export_data: user.ExportData,
      manage_users: user.ManageUsers,
      api_access: user.ApiAccess
    }
  };

  res.status(200).json({
    success: true,
    data: formattedUser
  });
});

/**
 * @swagger
 * /users/{id}:
 *   put:
 *     summary: Mettre à jour un utilisateur
 *     tags: [Users]
 *     security:
 *       - BearerAuth: []
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: number
 *         description: ID de l'utilisateur
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             properties:
 *               name:
 *                 type: string
 *               email:
 *                 type: string
 *                 format: email
 *               role:
 *                 type: string
 *                 enum: [partner_admin, partner_viewer, partner_operator]
 *               is_active:
 *                 type: boolean
 *               permissions:
 *                 type: object
 *                 properties:
 *                   viewOperations:
 *                     type: boolean
 *                   viewAnalytics:
 *                     type: boolean
 *                   viewAgencies:
 *                     type: boolean
 *                   exportData:
 *                     type: boolean
 *                   manageUsers:
 *                     type: boolean
 *                   apiAccess:
 *                     type: boolean
 *     responses:
 *       200:
 *         description: Utilisateur mis à jour
 */
const updateUser = asyncHandler(async (req: Request, res: Response) => {
  const partnerId = req.partnerContext?.partnerId;
  const userId = parseInt(req.params.id);

  if (!partnerId) {
    throw new ValidationError('Partner context not found');
  }

  if (!userId || isNaN(userId)) {
    throw new ValidationError('Invalid user ID');
  }

  // Validation des données
  const { error, value } = updateUserSchema.validate(req.body);
  if (error) {
    throw new ValidationError(error.details[0].message);
  }

  // Vérifier que l'utilisateur existe et appartient au partenaire
  const existingUser = await prisma.partnerUser.findFirst({
    where: { 
      RefUser: userId,
      RefBanque: partnerId
    }
  });

  if (!existingUser) {
    return res.status(404).json({
      success: false,
      error: 'User not found'
    });
  }

  // Préparer les données de mise à jour
  const updateData: any = {};
  
  if (value.name) updateData.Name = value.name;
  if (value.email) updateData.Email = value.email;
  if (value.role) updateData.Role = value.role;
  if (value.is_active !== undefined) updateData.IsActive = value.is_active;

  // Permissions
  if (value.permissions) {
    if (value.permissions.viewOperations !== undefined) 
      updateData.ViewOperations = value.permissions.viewOperations;
    if (value.permissions.viewAnalytics !== undefined) 
      updateData.ViewAnalytics = value.permissions.viewAnalytics;
    if (value.permissions.viewAgencies !== undefined) 
      updateData.ViewAgencies = value.permissions.viewAgencies;
    if (value.permissions.exportData !== undefined) 
      updateData.ExportData = value.permissions.exportData;
    if (value.permissions.manageUsers !== undefined) 
      updateData.ManageUsers = value.permissions.manageUsers;
    if (value.permissions.apiAccess !== undefined) 
      updateData.ApiAccess = value.permissions.apiAccess;
  }

  // Mettre à jour l'utilisateur
  const updatedUser = await prisma.partnerUser.update({
    where: { RefUser: userId },
    data: updateData,
    select: {
      RefUser: true,
      Name: true,
      Email: true,
      Role: true,
      IsActive: true,
      UpdatedAt: true,
      ViewOperations: true,
      ViewAnalytics: true,
      ViewAgencies: true,
      ExportData: true,
      ManageUsers: true,
      ApiAccess: true
    }
  });

  res.status(200).json({
    success: true,
    data: {
      id: updatedUser.RefUser,
      name: updatedUser.Name,
      email: updatedUser.Email,
      role: updatedUser.Role,
      is_active: updatedUser.IsActive,
      updated_at: updatedUser.UpdatedAt,
      permissions: {
        view_operations: updatedUser.ViewOperations,
        view_analytics: updatedUser.ViewAnalytics,
        view_agencies: updatedUser.ViewAgencies,
        export_data: updatedUser.ExportData,
        manage_users: updatedUser.ManageUsers,
        api_access: updatedUser.ApiAccess
      }
    },
    message: 'User updated successfully'
  });
});

/**
 * @swagger
 * /users/{id}/activity:
 *   get:
 *     summary: Activité d'un utilisateur
 *     tags: [Users]
 *     security:
 *       - BearerAuth: []
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: number
 *         description: ID de l'utilisateur
 *       - in: query
 *         name: limit
 *         schema:
 *           type: number
 *           default: 50
 *     responses:
 *       200:
 *         description: Activité de l'utilisateur
 */
const getUserActivity = asyncHandler(async (req: Request, res: Response) => {
  const partnerId = req.partnerContext?.partnerId;
  const userId = parseInt(req.params.id);
  const limit = Math.min(parseInt(req.query.limit as string) || 50, 100);

  if (!partnerId) {
    throw new ValidationError('Partner context not found');
  }

  if (!userId || isNaN(userId)) {
    throw new ValidationError('Invalid user ID');
  }

  // Vérifier que l'utilisateur appartient au partenaire
  const user = await prisma.partnerUser.findFirst({
    where: { 
      RefUser: userId,
      RefBanque: partnerId
    },
    select: { RefUser: true, Name: true, Email: true }
  });

  if (!user) {
    return res.status(404).json({
      success: false,
      error: 'User not found'
    });
  }

  // Récupérer les logs d'API pour cet utilisateur (si disponible)
  const apiLogs = await prisma.partnerApiLog.findMany({
    where: { 
      RefBanque: partnerId,
      // Note: Il faudrait ajouter un champ RefUser dans PartnerApiLog pour traquer par utilisateur
    },
    orderBy: { CreatedAt: 'desc' },
    take: limit,
    select: {
      RefLog: true,
      Endpoint: true,
      Method: true,
      StatusCode: true,
      ResponseTime: true,
      CreatedAt: true,
      IpAddress: true,
      UserAgent: true
    }
  });

  // Pour cet exemple, on simule l'activité avec les logs API généraux
  const activity = apiLogs.map(log => ({
    id: log.RefLog,
    type: 'api_call',
    description: `${log.Method} ${log.Endpoint}`,
    status: log.StatusCode < 400 ? 'success' : 'error',
    response_time: log.ResponseTime,
    ip_address: log.IpAddress,
    user_agent: log.UserAgent,
    timestamp: log.CreatedAt
  }));

  res.status(200).json({
    success: true,
    data: {
      user: {
        id: user.RefUser,
        name: user.Name,
        email: user.Email
      },
      activity,
      total_activities: activity.length
    }
  });
});

// Routes avec middleware de permissions
router.get('/', requirePermission(PARTNER_PERMISSIONS.MANAGE_USERS), getUsers);
router.get('/:id', requirePermission(PARTNER_PERMISSIONS.MANAGE_USERS), getUser);
router.put('/:id', requirePermission(PARTNER_PERMISSIONS.MANAGE_USERS), updateUser);
router.get('/:id/activity', requirePermission(PARTNER_PERMISSIONS.MANAGE_USERS), getUserActivity);

export default router;
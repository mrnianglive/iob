import { Request, Response, NextFunction } from 'express';
import jwt from 'jsonwebtoken';
import { prisma } from '../config/database';
import { logger } from '../config/logger';
import { TenantContext } from '../types/express';

interface JWTPayload {
  userId: number;
  countryId: number;
  partnerId?: number;
  permissions: string[];
  cashRegisterIds: number[];
  iat: number;
  exp: number;
}

export const authenticateToken = async (
  req: Request,
  res: Response,
  next: NextFunction
) => {
  try {
    // Extract token from Authorization header
    const authHeader = req.headers.authorization;
    const token = authHeader?.split(' ')[1];

    if (!token) {
      return res.status(401).json({
        success: false,
        error: 'Access token required',
      });
    }

    // Verify JWT token
    const payload = jwt.verify(
      token,
      process.env.JWT_SECRET || 'default-secret'
    ) as JWTPayload;

    // Fetch user with relations
    const user = await prisma.user.findUnique({
      where: { RefUser: payload.userId },
      include: {
        country: true,
        partner: true,
        permissions: true,
        cashRegisters: {
          include: {
            cashRegister: true,
          },
        },
      },
    });

    if (!user) {
      return res.status(401).json({
        success: false,
        error: 'User not found',
      });
    }

    if (!user.IsActive) {
      return res.status(401).json({
        success: false,
        error: 'Account is deactivated',
      });
    }

    // Build tenant context for multi-tenant isolation
    const tenantContext: TenantContext = {
      countryId: user.RefPays,
      partnerId: user.RefBanque || undefined,
      permissions: payload.permissions,
      cashRegisterIds: user.cashRegisters.map(cr => cr.RefCaisse),
    };

    // Attach user and context to request
    req.user = user;
    req.tenantContext = tenantContext;

    // Log successful authentication
    logger.debug(`User ${user.Login} authenticated successfully`, {
      userId: user.RefUser,
      countryId: tenantContext.countryId,
      partnerId: tenantContext.partnerId,
    });

    next();
  } catch (error) {
    if (error instanceof jwt.TokenExpiredError) {
      return res.status(401).json({
        success: false,
        error: 'Token expired',
      });
    }

    if (error instanceof jwt.JsonWebTokenError) {
      return res.status(401).json({
        success: false,
        error: 'Invalid token',
      });
    }

    logger.error('Authentication error:', error);
    return res.status(500).json({
      success: false,
      error: 'Authentication failed',
    });
  }
};

// Middleware to check specific permissions
export const requirePermission = (permission: string) => {
  return (req: Request, res: Response, next: NextFunction) => {
    if (!req.tenantContext) {
      return res.status(403).json({
        success: false,
        error: 'No tenant context',
      });
    }

    if (!req.tenantContext.permissions.includes(permission)) {
      logger.warn(`User ${req.user?.RefUser} denied access - missing permission: ${permission}`);
      return res.status(403).json({
        success: false,
        error: 'Insufficient permissions',
      });
    }

    next();
  };
};

// Middleware to check if user has access to a specific cash register
export const requireCashRegisterAccess = (cashRegisterIdParam: string = 'cashRegisterId') => {
  return (req: Request, res: Response, next: NextFunction) => {
    const cashRegisterId = parseInt(req.params[cashRegisterIdParam] || req.body.cashRegisterId);
    
    if (!cashRegisterId) {
      return res.status(400).json({
        success: false,
        error: 'Cash register ID required',
      });
    }

    if (!req.tenantContext?.cashRegisterIds.includes(cashRegisterId)) {
      logger.warn(`User ${req.user?.RefUser} denied access to cash register ${cashRegisterId}`);
      return res.status(403).json({
        success: false,
        error: 'No access to this cash register',
      });
    }

    next();
  };
};

// Middleware for admin-only routes
export const requireAdmin = (req: Request, res: Response, next: NextFunction) => {
  const adminPermissions = ['admin', 'super_admin'];
  const hasAdminPermission = req.tenantContext?.permissions.some(p => 
    adminPermissions.includes(p)
  );

  if (!hasAdminPermission) {
    logger.warn(`User ${req.user?.RefUser} denied admin access`);
    return res.status(403).json({
      success: false,
      error: 'Admin access required',
    });
  }

  next();
};
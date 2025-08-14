import { Request, Response, NextFunction } from 'express';
import { logger } from '@/utils/logger';

export interface ApiError extends Error {
  statusCode?: number;
  isOperational?: boolean;
}

export class CustomError extends Error implements ApiError {
  public statusCode: number;
  public isOperational: boolean;

  constructor(message: string, statusCode: number = 500, isOperational: boolean = true) {
    super(message);
    this.statusCode = statusCode;
    this.isOperational = isOperational;
    this.name = this.constructor.name;

    Error.captureStackTrace(this, this.constructor);
  }
}

export class ValidationError extends CustomError {
  constructor(message: string) {
    super(message, 400);
  }
}

export class NotFoundError extends CustomError {
  constructor(message: string = 'Resource not found') {
    super(message, 404);
  }
}

export class UnauthorizedError extends CustomError {
  constructor(message: string = 'Unauthorized') {
    super(message, 401);
  }
}

export class ForbiddenError extends CustomError {
  constructor(message: string = 'Forbidden') {
    super(message, 403);
  }
}

export class ConflictError extends CustomError {
  constructor(message: string = 'Conflict') {
    super(message, 409);
  }
}

export class RateLimitError extends CustomError {
  constructor(message: string = 'Rate limit exceeded') {
    super(message, 429);
  }
}

/**
 * Middleware de gestion des erreurs globales
 */
export const errorHandler = (
  err: ApiError,
  req: Request,
  res: Response,
  next: NextFunction
) => {
  let error = { ...err };
  error.message = err.message;

  // Log de l'erreur
  logger.error(`Error ${error.statusCode || 500}: ${error.message}`, {
    url: req.originalUrl,
    method: req.method,
    ip: req.ip,
    userAgent: req.get('User-Agent'),
    partnerId: req.partnerContext?.partnerId,
    userId: req.partnerContext?.userId,
    stack: error.stack,
  });

  // Erreur de validation Mongoose/Prisma
  if (err.name === 'ValidationError') {
    const message = 'Validation Error';
    error = new ValidationError(message);
  }

  // Erreur de duplication (clé unique)
  if (err.name === 'MongoError' && (err as any).code === 11000) {
    const message = 'Duplicate field value entered';
    error = new ConflictError(message);
  }

  // Erreur JWT
  if (err.name === 'JsonWebTokenError') {
    const message = 'Invalid token';
    error = new UnauthorizedError(message);
  }

  if (err.name === 'TokenExpiredError') {
    const message = 'Token expired';
    error = new UnauthorizedError(message);
  }

  // Erreur Prisma
  if (err.name === 'PrismaClientKnownRequestError') {
    error = handlePrismaError(err as any);
  }

  // Réponse d'erreur
  res.status(error.statusCode || 500).json({
    success: false,
    error: error.message || 'Server Error',
    ...(process.env.NODE_ENV === 'development' && {
      stack: error.stack,
    }),
  });
};

/**
 * Gestion des erreurs Prisma spécifiques
 */
function handlePrismaError(err: any): CustomError {
  switch (err.code) {
    case 'P2002':
      return new ConflictError(`Duplicate field value: ${err.meta?.target}`);
    case 'P2014':
      return new ValidationError(`Invalid ID: ${err.meta?.target}`);
    case 'P2003':
      return new ValidationError(`Invalid input data: ${err.meta?.target}`);
    case 'P2025':
      return new NotFoundError('Record not found');
    default:
      return new CustomError('Database error', 500);
  }
}

/**
 * Wrapper pour les fonctions async pour capturer les erreurs
 */
export const asyncHandler = (fn: Function) => (req: Request, res: Response, next: NextFunction) =>
  Promise.resolve(fn(req, res, next)).catch(next);

/**
 * Middleware pour les erreurs 404
 */
export const notFound = (req: Request, res: Response, next: NextFunction) => {
  const error = new NotFoundError(`Not found - ${req.originalUrl}`);
  next(error);
};
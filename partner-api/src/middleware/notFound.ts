import { Request, Response, NextFunction } from 'express';
import { logger } from '@/utils/logger';

/**
 * Middleware pour gérer les routes non trouvées (404)
 */
export const notFound = (req: Request, res: Response, next: NextFunction) => {
  const error = {
    message: `Route not found - ${req.method} ${req.originalUrl}`,
    statusCode: 404,
  };

  // Log de la route non trouvée
  logger.warn('Route not found', {
    method: req.method,
    url: req.originalUrl,
    ip: req.ip,
    userAgent: req.get('User-Agent'),
    partnerId: req.partnerContext?.partnerId,
    userId: req.partnerContext?.userId,
  });

  res.status(404).json({
    success: false,
    error: error.message,
    available_endpoints: {
      auth: [
        'POST /partner-api/auth/login',
        'POST /partner-api/auth/refresh',
      ],
      dashboard: [
        'GET /partner-api/dashboard/stats',
        'GET /partner-api/dashboard/operations/recent',
      ],
      operations: [
        'GET /partner-api/operations',
        'GET /partner-api/operations/:id',
      ],
      documentation: [
        'GET /docs - API Documentation',
        'GET /health - Health Check',
      ],
    },
  });
};
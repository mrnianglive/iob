import { Request, Response, NextFunction } from 'express';
import { logger } from '@/utils/logger';

export interface RequestLog {
  method: string;
  url: string;
  ip: string;
  userAgent?: string;
  partnerId?: number;
  userId?: number;
  apiKey?: string;
  responseTime: number;
  statusCode: number;
  timestamp: Date;
}

/**
 * Middleware de logging des requêtes pour l'API partenaire
 */
export const requestLogger = (req: Request, res: Response, next: NextFunction) => {
  const startTime = Date.now();
  
  // Capture des informations de base
  const requestInfo = {
    method: req.method,
    url: req.originalUrl,
    ip: req.ip || req.connection.remoteAddress || 'unknown',
    userAgent: req.get('User-Agent'),
    timestamp: new Date(),
  };

  // Override de la méthode send pour capturer la réponse
  const originalSend = res.send;
  res.send = function(data) {
    const responseTime = Date.now() - startTime;
    
    // Informations complètes du log
    const logInfo: RequestLog = {
      ...requestInfo,
      partnerId: req.partnerContext?.partnerId,
      userId: req.partnerContext?.userId,
      apiKey: req.partnerContext?.apiKey ? 'present' : undefined,
      responseTime,
      statusCode: res.statusCode,
    };

    // Log conditionnel selon le niveau
    if (res.statusCode >= 500) {
      logger.error('Request failed', logInfo);
    } else if (res.statusCode >= 400) {
      logger.warn('Request error', logInfo);
    } else if (process.env.NODE_ENV === 'development') {
      logger.info('Request completed', {
        method: logInfo.method,
        url: logInfo.url,
        statusCode: logInfo.statusCode,
        responseTime: logInfo.responseTime,
        partnerId: logInfo.partnerId,
      });
    }

    return originalSend.call(this, data);
  };

  next();
};

/**
 * Middleware spécialisé pour les endpoints sensibles
 */
export const sensitiveEndpointLogger = (endpointName: string) => {
  return (req: Request, res: Response, next: NextFunction) => {
    logger.info(`Sensitive endpoint accessed: ${endpointName}`, {
      partnerId: req.partnerContext?.partnerId,
      userId: req.partnerContext?.userId,
      ip: req.ip,
      userAgent: req.get('User-Agent'),
      timestamp: new Date(),
    });
    next();
  };
};
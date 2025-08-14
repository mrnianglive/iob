import 'dotenv/config';
import express from 'express';
import cors from 'cors';
import helmet from 'helmet';
import compression from 'compression';
import morgan from 'morgan';
import rateLimit from 'express-rate-limit';
import swaggerJsdoc from 'swagger-jsdoc';
import swaggerUi from 'swagger-ui-express';

import { PrismaClient } from '@prisma/client';
import { logger } from '@/utils/logger';
import { errorHandler } from '@/middleware/errorHandler';
import { notFound } from '@/middleware/notFound';
import { partnerAuthMiddleware } from '@/middleware/partnerAuth';
import { requestLogger } from '@/middleware/requestLogger';

// Routes
import authRoutes from '@/routes/auth';
import dashboardRoutes from '@/routes/dashboard';
import operationsRoutes from '@/routes/operations';
import agenciesRoutes from '@/routes/agencies';
import productsRoutes from '@/routes/products';
import analyticsRoutes from '@/routes/analytics';
import usersRoutes from '@/routes/users';
import webhooksRoutes from '@/routes/webhooks';

const app = express();
const PORT = process.env.PORT || 3001;
const API_PREFIX = process.env.API_PREFIX || '/partner-api';

// Initialize Prisma
export const prisma = new PrismaClient({
  log: process.env.NODE_ENV === 'development' ? ['query', 'error', 'warn'] : ['error'],
});

// Swagger configuration
const swaggerOptions = {
  definition: {
    openapi: '3.0.0',
    info: {
      title: 'IOB Partner API',
      version: '1.0.0',
      description: 'API dédiée aux partenaires bancaires IOB',
      contact: {
        name: 'IOB Support',
        email: 'support@iob.com',
      },
    },
    servers: [
      {
        url: `http://localhost:${PORT}${API_PREFIX}`,
        description: 'Development server',
      },
      {
        url: `https://api.iob.com${API_PREFIX}`,
        description: 'Production server',
      },
    ],
    components: {
      securitySchemes: {
        BearerAuth: {
          type: 'http',
          scheme: 'bearer',
          bearerFormat: 'JWT',
        },
        ApiKeyAuth: {
          type: 'apiKey',
          in: 'header',
          name: 'X-API-Key',
        },
      },
    },
    security: [
      {
        BearerAuth: [],
      },
      {
        ApiKeyAuth: [],
      },
    ],
  },
  apis: ['./src/routes/*.ts', './src/controllers/*.ts'],
};

const specs = swaggerJsdoc(swaggerOptions);

// Security middleware
app.use(helmet({
  contentSecurityPolicy: {
    directives: {
      defaultSrc: ["'self'"],
      styleSrc: ["'self'", "'unsafe-inline'"],
      scriptSrc: ["'self'"],
      imgSrc: ["'self'", "data:", "https:"],
    },
  },
}));

// CORS configuration
app.use(cors({
  origin: process.env.CORS_ORIGIN?.split(',') || ['http://localhost:8081'],
  credentials: process.env.CORS_CREDENTIALS === 'true',
  methods: ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization', 'X-API-Key', 'X-Signature', 'X-Timestamp'],
}));

// Rate limiting
const limiter = rateLimit({
  windowMs: parseInt(process.env.API_WINDOW_MS || '900000'), // 15 minutes
  max: parseInt(process.env.API_RATE_LIMIT || '1000'),
  message: {
    error: 'Too many requests from this IP, please try again later.',
  },
  standardHeaders: true,
  legacyHeaders: false,
});

app.use(limiter);

// Body parsing middleware
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// Compression
app.use(compression());

// HTTP request logging
if (process.env.NODE_ENV !== 'test') {
  app.use(morgan('combined', {
    stream: {
      write: (message: string) => logger.info(message.trim()),
    },
  }));
}

// Custom request logging for partner API
app.use(requestLogger);

// Health check endpoint
app.get('/health', (req, res) => {
  res.status(200).json({
    status: 'OK',
    timestamp: new Date().toISOString(),
    service: 'IOB Partner API',
    version: '1.0.0',
  });
});

// API Documentation
if (process.env.SWAGGER_ENABLED === 'true') {
  app.use('/docs', swaggerUi.serve, swaggerUi.setup(specs, {
    explorer: true,
    customCss: '.swagger-ui .topbar { display: none }',
    customSiteTitle: 'IOB Partner API Documentation',
  }));
}

// Routes
app.use(`${API_PREFIX}/auth`, authRoutes);

// Protected routes (require authentication)
app.use(`${API_PREFIX}/dashboard`, partnerAuthMiddleware, dashboardRoutes);
app.use(`${API_PREFIX}/operations`, partnerAuthMiddleware, operationsRoutes);
app.use(`${API_PREFIX}/agencies`, partnerAuthMiddleware, agenciesRoutes);
app.use(`${API_PREFIX}/products`, partnerAuthMiddleware, productsRoutes);
app.use(`${API_PREFIX}/analytics`, partnerAuthMiddleware, analyticsRoutes);
app.use(`${API_PREFIX}/users`, partnerAuthMiddleware, usersRoutes);
app.use(`${API_PREFIX}/webhooks`, partnerAuthMiddleware, webhooksRoutes);

// Error handling middleware
app.use(notFound);
app.use(errorHandler);

// Graceful shutdown
process.on('SIGTERM', async () => {
  logger.info('SIGTERM received, shutting down gracefully...');
  await prisma.$disconnect();
  process.exit(0);
});

process.on('SIGINT', async () => {
  logger.info('SIGINT received, shutting down gracefully...');
  await prisma.$disconnect();
  process.exit(0);
});

// Start server
const server = app.listen(PORT, () => {
  logger.info(`🚀 IOB Partner API server running on port ${PORT}`);
  logger.info(`📚 API Documentation available at http://localhost:${PORT}/docs`);
  logger.info(`🏥 Health check available at http://localhost:${PORT}/health`);
});

export default app;
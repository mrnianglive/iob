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
      description: `
# IOB Partner API

API dédiée aux partenaires bancaires IOB pour consulter et gérer leurs opérations.

## Authentification

Cette API supporte deux méthodes d'authentification :

### 1. JWT Bearer Token (Interface Web)
- Utilisée par l'interface web partenaire
- Token obtenu via \`POST /auth/login\`
- Header: \`Authorization: Bearer <token>\`

### 2. API Key + HMAC (Intégrations Système)
- Utilisée pour les intégrations système-à-système
- Headers requis :
  - \`X-API-Key: <your-api-key>\`
  - \`X-Signature: <hmac-signature>\`
  - \`X-Timestamp: <unix-timestamp>\`

## Isolation des Données

Toutes les données sont automatiquement filtrées par partenaire (RefBanque).
Chaque partenaire ne peut accéder qu'à ses propres données.

## Formats de Réponse

Toutes les réponses suivent le format standard :
\`\`\`json
{
  "success": true,
  "data": {...},
  "pagination": {...} // Si applicable
}
\`\`\`

## Gestion d'Erreurs

Les erreurs suivent le format :
\`\`\`json
{
  "success": false,
  "error": "Message d'erreur",
  "code": "ERROR_CODE"
}
\`\`\`

## Rate Limiting

- 1000 requêtes par minute par défaut
- Configurable par partenaire
- Headers de réponse : \`X-RateLimit-*\`
      `,
      contact: {
        name: 'IOB Support',
        email: 'support@iob.com',
        url: 'https://support.iob.com'
      },
      license: {
        name: 'Proprietary',
        url: 'https://iob.com/license'
      }
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
      {
        url: `https://staging-api.iob.com${API_PREFIX}`,
        description: 'Staging server',
      },
    ],
    components: {
      securitySchemes: {
        BearerAuth: {
          type: 'http',
          scheme: 'bearer',
          bearerFormat: 'JWT',
          description: 'JWT token obtenu via /auth/login'
        },
        ApiKeyAuth: {
          type: 'apiKey',
          in: 'header',
          name: 'X-API-Key',
          description: 'Clé API partenaire'
        },
        HmacAuth: {
          type: 'apiKey',
          in: 'header',
          name: 'X-Signature',
          description: 'Signature HMAC pour validation des requêtes'
        }
      },
      schemas: {
        // Réponses standard
        SuccessResponse: {
          type: 'object',
          properties: {
            success: {
              type: 'boolean',
              example: true
            },
            data: {
              type: 'object'
            }
          }
        },
        ErrorResponse: {
          type: 'object',
          properties: {
            success: {
              type: 'boolean',
              example: false
            },
            error: {
              type: 'string',
              example: 'Message d\'erreur'
            },
            code: {
              type: 'string',
              example: 'VALIDATION_ERROR'
            }
          }
        },
        PaginationInfo: {
          type: 'object',
          properties: {
            page: {
              type: 'integer',
              example: 1
            },
            limit: {
              type: 'integer',
              example: 20
            },
            total: {
              type: 'integer',
              example: 150
            },
            pages: {
              type: 'integer',
              example: 8
            }
          }
        },
        // Authentification
        PartnerLoginRequest: {
          type: 'object',
          required: ['email', 'password', 'partner_code'],
          properties: {
            email: {
              type: 'string',
              format: 'email',
              example: 'admin@partner.com'
            },
            password: {
              type: 'string',
              example: 'SecurePassword123!'
            },
            partner_code: {
              type: 'string',
              example: 'PARTNER_001'
            }
          }
        },
        PartnerLoginResponse: {
          type: 'object',
          properties: {
            success: {
              type: 'boolean',
              example: true
            },
            data: {
              type: 'object',
              properties: {
                access_token: {
                  type: 'string',
                  example: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...'
                },
                refresh_token: {
                  type: 'string',
                  example: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...'
                },
                partner: {
                  $ref: '#/components/schemas/Partner'
                },
                expires_in: {
                  type: 'integer',
                  example: 86400
                }
              }
            }
          }
        },
        // Entités principales
        Partner: {
          type: 'object',
          properties: {
            id: {
              type: 'integer',
              example: 1
            },
            name: {
              type: 'string',
              example: 'Banque Partenaire XYZ'
            },
            country: {
              type: 'string',
              example: 'France'
            },
            logo: {
              type: 'string',
              format: 'uri',
              example: 'https://example.com/logo.png'
            },
            contact_email: {
              type: 'string',
              format: 'email',
              example: 'contact@partner.com'
            },
            is_active: {
              type: 'boolean',
              example: true
            },
            permissions: {
              type: 'array',
              items: {
                type: 'string'
              },
              example: ['view_operations', 'view_analytics', 'export_data']
            }
          }
        },
        Operation: {
          type: 'object',
          properties: {
            id: {
              type: 'integer',
              example: 12345
            },
            reference: {
              type: 'string',
              example: 'OP-2024-001234'
            },
            amount: {
              type: 'number',
              format: 'decimal',
              example: 1500.50
            },
            commission: {
              type: 'number',
              format: 'decimal',
              example: 15.00
            },
            status: {
              type: 'string',
              enum: ['pending', 'approved', 'rejected', 'cancelled'],
              example: 'approved'
            },
            client_name: {
              type: 'string',
              example: 'Jean Dupont'
            },
            client_phone: {
              type: 'string',
              example: '+33123456789'
            },
            beneficiary_name: {
              type: 'string',
              example: 'Marie Martin'
            },
            beneficiary_phone: {
              type: 'string',
              example: '+33987654321'
            },
            beneficiary_country: {
              type: 'string',
              example: 'Sénégal'
            },
            created_at: {
              type: 'string',
              format: 'date-time',
              example: '2024-01-15T10:30:00Z'
            },
            updated_at: {
              type: 'string',
              format: 'date-time',
              example: '2024-01-15T10:35:00Z'
            },
            agency: {
              $ref: '#/components/schemas/Agency'
            },
            product: {
              $ref: '#/components/schemas/Product'
            }
          }
        },
        Agency: {
          type: 'object',
          properties: {
            id: {
              type: 'integer',
              example: 1
            },
            name: {
              type: 'string',
              example: 'Agence Paris Centre'
            },
            address: {
              type: 'string',
              example: '123 Rue de Rivoli, 75001 Paris'
            },
            phone: {
              type: 'string',
              example: '+33140000000'
            },
            country: {
              type: 'object',
              properties: {
                name: {
                  type: 'string',
                  example: 'France'
                },
                code: {
                  type: 'string',
                  example: 'FR'
                }
              }
            },
            is_active: {
              type: 'boolean',
              example: true
            }
          }
        },
        Product: {
          type: 'object',
          properties: {
            id: {
              type: 'integer',
              example: 1
            },
            name: {
              type: 'string',
              example: 'Transfert Express'
            },
            description: {
              type: 'string',
              example: 'Transfert d\'argent rapide vers l\'Afrique'
            },
            commission_rate: {
              type: 'number',
              format: 'decimal',
              example: 2.5
            },
            is_active: {
              type: 'boolean',
              example: true
            }
          }
        },
        // Statistiques
        PartnerStats: {
          type: 'object',
          properties: {
            operations_count: {
              type: 'integer',
              example: 1250
            },
            total_volume: {
              type: 'number',
              format: 'decimal',
              example: 2500000.00
            },
            commissions: {
              type: 'number',
              format: 'decimal',
              example: 25000.00
            },
            agencies_count: {
              type: 'integer',
              example: 15
            },
            active_users: {
              type: 'integer',
              example: 8
            },
            trends: {
              type: 'object',
              properties: {
                operations_trend: {
                  type: 'number',
                  example: 12.5
                },
                volume_trend: {
                  type: 'number',
                  example: 8.3
                },
                commission_trend: {
                  type: 'number',
                  example: 15.2
                }
              }
            }
          }
        },
        // Filtres
        OperationFilters: {
          type: 'object',
          properties: {
            status: {
              type: 'string',
              enum: ['pending', 'approved', 'rejected', 'cancelled']
            },
            date_from: {
              type: 'string',
              format: 'date',
              example: '2024-01-01'
            },
            date_to: {
              type: 'string',
              format: 'date',
              example: '2024-01-31'
            },
            agency_id: {
              type: 'integer',
              example: 1
            },
            product_id: {
              type: 'integer',
              example: 1
            },
            min_amount: {
              type: 'number',
              example: 100
            },
            max_amount: {
              type: 'number',
              example: 5000
            },
            client_name: {
              type: 'string',
              example: 'Jean'
            },
            page: {
              type: 'integer',
              minimum: 1,
              example: 1
            },
            limit: {
              type: 'integer',
              minimum: 1,
              maximum: 100,
              example: 20
            }
          }
        },
        // Webhooks
        WebhookConfig: {
          type: 'object',
          required: ['url', 'events'],
          properties: {
            url: {
              type: 'string',
              format: 'uri',
              example: 'https://partner.com/webhooks/iob'
            },
            events: {
              type: 'array',
              items: {
                type: 'string',
                enum: ['operation_created', 'operation_approved', 'operation_rejected', 'operation_cancelled']
              },
              example: ['operation_created', 'operation_approved']
            },
            secret: {
              type: 'string',
              example: 'webhook_secret_key'
            },
            is_active: {
              type: 'boolean',
              example: true
            }
          }
        }
      },
      responses: {
        UnauthorizedError: {
          description: 'Token d\'authentification invalide ou manquant',
          content: {
            'application/json': {
              schema: {
                $ref: '#/components/schemas/ErrorResponse'
              },
              example: {
                success: false,
                error: 'Token d\'authentification requis',
                code: 'UNAUTHORIZED'
              }
            }
          }
        },
        ForbiddenError: {
          description: 'Permissions insuffisantes',
          content: {
            'application/json': {
              schema: {
                $ref: '#/components/schemas/ErrorResponse'
              },
              example: {
                success: false,
                error: 'Permission view_operations requise',
                code: 'FORBIDDEN'
              }
            }
          }
        },
        NotFoundError: {
          description: 'Ressource non trouvée',
          content: {
            'application/json': {
              schema: {
                $ref: '#/components/schemas/ErrorResponse'
              },
              example: {
                success: false,
                error: 'Opération non trouvée',
                code: 'NOT_FOUND'
              }
            }
          }
        },
        ValidationError: {
          description: 'Données de requête invalides',
          content: {
            'application/json': {
              schema: {
                $ref: '#/components/schemas/ErrorResponse'
              },
              example: {
                success: false,
                error: 'Email requis',
                code: 'VALIDATION_ERROR'
              }
            }
          }
        },
        RateLimitError: {
          description: 'Limite de taux dépassée',
          content: {
            'application/json': {
              schema: {
                $ref: '#/components/schemas/ErrorResponse'
              },
              example: {
                success: false,
                error: 'Trop de requêtes, réessayez dans 60 secondes',
                code: 'RATE_LIMIT_EXCEEDED'
              }
            }
          }
        }
      },
      parameters: {
        PageParam: {
          name: 'page',
          in: 'query',
          description: 'Numéro de page (commence à 1)',
          schema: {
            type: 'integer',
            minimum: 1,
            default: 1
          }
        },
        LimitParam: {
          name: 'limit',
          in: 'query',
          description: 'Nombre d\'éléments par page',
          schema: {
            type: 'integer',
            minimum: 1,
            maximum: 100,
            default: 20
          }
        },
        DateFromParam: {
          name: 'date_from',
          in: 'query',
          description: 'Date de début (YYYY-MM-DD)',
          schema: {
            type: 'string',
            format: 'date'
          }
        },
        DateToParam: {
          name: 'date_to',
          in: 'query',
          description: 'Date de fin (YYYY-MM-DD)',
          schema: {
            type: 'string',
            format: 'date'
          }
        }
      }
    },
    security: [
      {
        BearerAuth: [],
      },
      {
        ApiKeyAuth: [],
        HmacAuth: []
      },
    ],
    tags: [
      {
        name: 'Authentication',
        description: 'Authentification et gestion des tokens'
      },
      {
        name: 'Dashboard',
        description: 'Endpoints du dashboard partenaire'
      },
      {
        name: 'Operations',
        description: 'Gestion des opérations partenaires'
      },
      {
        name: 'Agencies',
        description: 'Gestion des agences partenaires'
      },
      {
        name: 'Products',
        description: 'Gestion des produits partenaires'
      },
      {
        name: 'Analytics',
        description: 'Analytics et rapports avancés'
      },
      {
        name: 'Users',
        description: 'Gestion des utilisateurs partenaires'
      },
      {
        name: 'Webhooks',
        description: 'Configuration des webhooks temps réel'
      }
    ]
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
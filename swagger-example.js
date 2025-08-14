const express = require('express');
const swaggerJsdoc = require('swagger-jsdoc');
const swaggerUi = require('swagger-ui-express');

const app = express();
const PORT = 3002;

// Configuration Swagger
const swaggerOptions = {
  definition: {
    openapi: '3.0.0',
    info: {
      title: 'IOB Partner API',
      version: '1.0.0',
      description: `
# IOB Partner API - Documentation Complète

API dédiée aux partenaires bancaires IOB pour consulter et gérer leurs opérations.

## 🔐 Authentification

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

## 🔒 Isolation des Données

Toutes les données sont automatiquement filtrées par partenaire (RefBanque).
Chaque partenaire ne peut accéder qu'à ses propres données.

## 📊 Formats de Réponse

Toutes les réponses suivent le format standard :
\`\`\`json
{
  "success": true,
  "data": {...},
  "pagination": {...} // Si applicable
}
\`\`\`

## ⚠️ Gestion d'Erreurs

Les erreurs suivent le format :
\`\`\`json
{
  "success": false,
  "error": "Message d'erreur",
  "code": "ERROR_CODE"
}
\`\`\`

## 🚦 Rate Limiting

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
        url: 'http://localhost:3001/partner-api',
        description: 'Development server'
      },
      {
        url: 'https://staging-api.iob.com/partner-api',
        description: 'Staging server'
      },
      {
        url: 'https://api.iob.com/partner-api',
        description: 'Production server'
      }
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
            success: { type: 'boolean', example: true },
            data: { type: 'object' }
          }
        },
        ErrorResponse: {
          type: 'object',
          properties: {
            success: { type: 'boolean', example: false },
            error: { type: 'string', example: 'Message d\'erreur' },
            code: { type: 'string', example: 'VALIDATION_ERROR' }
          }
        },
        PaginationInfo: {
          type: 'object',
          properties: {
            page: { type: 'integer', example: 1 },
            limit: { type: 'integer', example: 20 },
            total: { type: 'integer', example: 150 },
            pages: { type: 'integer', example: 8 }
          }
        },
        // Authentification
        PartnerLoginRequest: {
          type: 'object',
          required: ['email', 'password', 'partner_code'],
          properties: {
            email: { type: 'string', format: 'email', example: 'admin@partner.com' },
            password: { type: 'string', example: 'SecurePassword123!' },
            partner_code: { type: 'string', example: 'PARTNER_001' }
          }
        },
        PartnerLoginResponse: {
          type: 'object',
          properties: {
            success: { type: 'boolean', example: true },
            data: {
              type: 'object',
              properties: {
                access_token: { type: 'string', example: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...' },
                refresh_token: { type: 'string', example: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...' },
                partner: { $ref: '#/components/schemas/Partner' },
                expires_in: { type: 'integer', example: 86400 }
              }
            }
          }
        },
        // Entités principales
        Partner: {
          type: 'object',
          properties: {
            id: { type: 'integer', example: 1 },
            name: { type: 'string', example: 'Banque Partenaire XYZ' },
            country: { type: 'string', example: 'France' },
            logo: { type: 'string', format: 'uri', example: 'https://example.com/logo.png' },
            contact_email: { type: 'string', format: 'email', example: 'contact@partner.com' },
            is_active: { type: 'boolean', example: true },
            permissions: {
              type: 'array',
              items: { type: 'string' },
              example: ['view_operations', 'view_analytics', 'export_data']
            }
          }
        },
        Operation: {
          type: 'object',
          properties: {
            id: { type: 'integer', example: 12345 },
            reference: { type: 'string', example: 'OP-2024-001234' },
            amount: { type: 'number', format: 'decimal', example: 1500.50 },
            commission: { type: 'number', format: 'decimal', example: 15.00 },
            status: {
              type: 'string',
              enum: ['pending', 'approved', 'rejected', 'cancelled'],
              example: 'approved'
            },
            client_name: { type: 'string', example: 'Jean Dupont' },
            client_phone: { type: 'string', example: '+33123456789' },
            beneficiary_name: { type: 'string', example: 'Marie Martin' },
            beneficiary_phone: { type: 'string', example: '+33987654321' },
            beneficiary_country: { type: 'string', example: 'Sénégal' },
            created_at: { type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z' },
            updated_at: { type: 'string', format: 'date-time', example: '2024-01-15T10:35:00Z' },
            agency: { $ref: '#/components/schemas/Agency' },
            product: { $ref: '#/components/schemas/Product' }
          }
        },
        Agency: {
          type: 'object',
          properties: {
            id: { type: 'integer', example: 1 },
            name: { type: 'string', example: 'Agence Paris Centre' },
            address: { type: 'string', example: '123 Rue de Rivoli, 75001 Paris' },
            phone: { type: 'string', example: '+33140000000' },
            country: {
              type: 'object',
              properties: {
                name: { type: 'string', example: 'France' },
                code: { type: 'string', example: 'FR' }
              }
            },
            is_active: { type: 'boolean', example: true }
          }
        },
        Product: {
          type: 'object',
          properties: {
            id: { type: 'integer', example: 1 },
            name: { type: 'string', example: 'Transfert Express' },
            description: { type: 'string', example: 'Transfert d\'argent rapide vers l\'Afrique' },
            commission_rate: { type: 'number', format: 'decimal', example: 2.5 },
            is_active: { type: 'boolean', example: true }
          }
        },
        // Statistiques
        PartnerStats: {
          type: 'object',
          properties: {
            operations_count: { type: 'integer', example: 1250 },
            total_volume: { type: 'number', format: 'decimal', example: 2500000.00 },
            commissions: { type: 'number', format: 'decimal', example: 25000.00 },
            agencies_count: { type: 'integer', example: 15 },
            active_users: { type: 'integer', example: 8 },
            trends: {
              type: 'object',
              properties: {
                operations_trend: { type: 'number', example: 12.5 },
                volume_trend: { type: 'number', example: 8.3 },
                commission_trend: { type: 'number', example: 15.2 }
              }
            }
          }
        }
      },
      responses: {
        UnauthorizedError: {
          description: 'Token d\'authentification invalide ou manquant',
          content: {
            'application/json': {
              schema: { $ref: '#/components/schemas/ErrorResponse' },
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
              schema: { $ref: '#/components/schemas/ErrorResponse' },
              example: {
                success: false,
                error: 'Permission view_operations requise',
                code: 'FORBIDDEN'
              }
            }
          }
        },
        ValidationError: {
          description: 'Données de requête invalides',
          content: {
            'application/json': {
              schema: { $ref: '#/components/schemas/ErrorResponse' },
              example: {
                success: false,
                error: 'Email requis',
                code: 'VALIDATION_ERROR'
              }
            }
          }
        }
      }
    },
    security: [
      { BearerAuth: [] },
      { ApiKeyAuth: [], HmacAuth: [] }
    ],
    tags: [
      { name: 'Authentication', description: 'Authentification et gestion des tokens' },
      { name: 'Dashboard', description: 'Endpoints du dashboard partenaire' },
      { name: 'Operations', description: 'Gestion des opérations partenaires' },
      { name: 'Analytics', description: 'Analytics et rapports avancés' },
      { name: 'Webhooks', description: 'Configuration des webhooks temps réel' }
    ]
  },
  apis: ['./swagger-example.js'] // Ce fichier même
};

const specs = swaggerJsdoc(swaggerOptions);

// Middleware
app.use(express.json());

/**
 * @swagger
 * /auth/login:
 *   post:
 *     tags: [Authentication]
 *     summary: Authentification partenaire
 *     description: |
 *       Authentifie un utilisateur partenaire et retourne les tokens JWT.
 *       
 *       **Note importante :** Le `partner_code` correspond au nom de la banque (NameBanque) dans la base de données.
 *       
 *       **Exemple de flux :**
 *       1. L'utilisateur saisit ses identifiants + code partenaire
 *       2. Le système valide les informations
 *       3. Génération des tokens JWT (access + refresh)
 *       4. Retour des informations partenaire et permissions
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             $ref: '#/components/schemas/PartnerLoginRequest'
 *           examples:
 *             exemple_standard:
 *               summary: Connexion standard
 *               value:
 *                 email: admin@partner.com
 *                 password: SecurePassword123!
 *                 partner_code: Banque Partenaire XYZ
 *     responses:
 *       '200':
 *         description: Authentification réussie
 *         content:
 *           application/json:
 *             schema:
 *               $ref: '#/components/schemas/PartnerLoginResponse'
 *       '400':
 *         $ref: '#/components/responses/ValidationError'
 *       '401':
 *         description: Identifiants invalides
 *         content:
 *           application/json:
 *             schema:
 *               $ref: '#/components/schemas/ErrorResponse'
 */
app.post('/auth/login', (req, res) => {
  res.json({
    success: true,
    data: {
      access_token: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...',
      refresh_token: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...',
      partner: {
        id: 1,
        name: 'Banque Partenaire XYZ',
        country: 'France',
        permissions: ['view_operations', 'view_analytics']
      },
      expires_in: 86400
    }
  });
});

/**
 * @swagger
 * /dashboard/stats:
 *   get:
 *     tags: [Dashboard]
 *     summary: Statistiques générales du partenaire
 *     description: |
 *       Récupère les statistiques principales du partenaire avec tendances.
 *       
 *       **Métriques incluses :**
 *       - Nombre total d'opérations
 *       - Volume total des transactions
 *       - Commissions générées
 *       - Nombre d'agences actives
 *       - Utilisateurs actifs
 *       - Tendances par rapport à la période précédente
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     parameters:
 *       - name: date_from
 *         in: query
 *         description: Date de début (YYYY-MM-DD)
 *         schema:
 *           type: string
 *           format: date
 *           example: '2024-01-01'
 *       - name: date_to
 *         in: query
 *         description: Date de fin (YYYY-MM-DD)
 *         schema:
 *           type: string
 *           format: date
 *           example: '2024-01-31'
 *       - name: period
 *         in: query
 *         description: Période prédéfinie (remplace date_from/date_to)
 *         schema:
 *           type: string
 *           enum: [today, yesterday, last_7_days, last_30_days, this_month, last_month, this_year]
 *           example: last_30_days
 *     responses:
 *       '200':
 *         description: Statistiques récupérées avec succès
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 success:
 *                   type: boolean
 *                   example: true
 *                 data:
 *                   $ref: '#/components/schemas/PartnerStats'
 *       '401':
 *         $ref: '#/components/responses/UnauthorizedError'
 */
app.get('/dashboard/stats', (req, res) => {
  res.json({
    success: true,
    data: {
      operations_count: 1250,
      total_volume: 2500000.00,
      commissions: 25000.00,
      agencies_count: 15,
      active_users: 8,
      trends: {
        operations_trend: 12.5,
        volume_trend: 8.3,
        commission_trend: 15.2
      }
    }
  });
});

/**
 * @swagger
 * /operations:
 *   get:
 *     tags: [Operations]
 *     summary: Liste des opérations du partenaire
 *     description: |
 *       Récupère la liste des opérations du partenaire avec filtres et pagination.
 *       
 *       **Fonctionnalités :**
 *       - Filtrage par statut, dates, agence, produit
 *       - Recherche par nom de client
 *       - Tri et pagination
 *       - Informations complètes (client, bénéficiaire, agence, produit)
 *     security:
 *       - BearerAuth: []
 *       - ApiKeyAuth: []
 *     parameters:
 *       - name: status
 *         in: query
 *         description: Filtrer par statut
 *         schema:
 *           type: string
 *           enum: [pending, approved, rejected, cancelled]
 *       - name: date_from
 *         in: query
 *         description: Date de début (YYYY-MM-DD)
 *         schema:
 *           type: string
 *           format: date
 *       - name: date_to
 *         in: query
 *         description: Date de fin (YYYY-MM-DD)
 *         schema:
 *           type: string
 *           format: date
 *       - name: page
 *         in: query
 *         description: Numéro de page
 *         schema:
 *           type: integer
 *           minimum: 1
 *           default: 1
 *       - name: limit
 *         in: query
 *         description: Nombre d'éléments par page
 *         schema:
 *           type: integer
 *           minimum: 1
 *           maximum: 100
 *           default: 20
 *     responses:
 *       '200':
 *         description: Liste des opérations récupérée avec succès
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 success:
 *                   type: boolean
 *                   example: true
 *                 data:
 *                   type: object
 *                   properties:
 *                     operations:
 *                       type: array
 *                       items:
 *                         $ref: '#/components/schemas/Operation'
 *                     pagination:
 *                       $ref: '#/components/schemas/PaginationInfo'
 *       '401':
 *         $ref: '#/components/responses/UnauthorizedError'
 */
app.get('/operations', (req, res) => {
  res.json({
    success: true,
    data: {
      operations: [
        {
          id: 12345,
          reference: 'OP-2024-001234',
          amount: 1500.50,
          commission: 15.00,
          status: 'approved',
          client_name: 'Jean Dupont',
          client_phone: '+33123456789',
          beneficiary_name: 'Marie Martin',
          beneficiary_country: 'Sénégal',
          created_at: '2024-01-15T10:30:00Z',
          agency: { id: 1, name: 'Agence Paris Centre' },
          product: { id: 1, name: 'Transfert Express' }
        }
      ],
      pagination: {
        page: 1,
        limit: 20,
        total: 150,
        pages: 8
      }
    }
  });
});

// Documentation Swagger
app.use('/docs', swaggerUi.serve, swaggerUi.setup(specs, {
  explorer: true,
  customCss: '.swagger-ui .topbar { display: none }',
  customSiteTitle: 'IOB Partner API Documentation'
}));

// Health check
app.get('/health', (req, res) => {
  res.json({ status: 'OK', timestamp: new Date().toISOString() });
});

// Démarrage du serveur
app.listen(PORT, () => {
  console.log('🚀 IOB Partner API Documentation');
  console.log('='.repeat(40));
  console.log(`📖 Swagger UI: http://localhost:${PORT}/docs`);
  console.log(`🔍 OpenAPI JSON: http://localhost:${PORT}/docs.json`);
  console.log(`❤️  Health Check: http://localhost:${PORT}/health`);
  console.log(`🖥️  Server running on port ${PORT}`);
  console.log('');
  console.log('📋 Endpoints de test disponibles :');
  console.log('• POST /auth/login - Authentification');
  console.log('• GET /dashboard/stats - Statistiques');
  console.log('• GET /operations - Liste des opérations');
  console.log('');
  console.log('🎯 Cette documentation montre la structure complète de l\'API IOB Partner');
});

module.exports = app;
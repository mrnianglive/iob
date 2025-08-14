# Interface Partenaire IOB - API & Dashboard Dédiés

## 1. Contexte et Objectifs

### 1.1 Vision Partenaire
Créer une **interface dédiée aux partenaires bancaires** permettant de consulter et gérer leurs opérations IOB via une API moderne et un dashboard personnalisé.

### 1.2 Objectifs Spécifiques
- **API REST** dédiée avec authentification partenaire
- **Dashboard web** avec données filtrées par partenaire
- **Isolation complète** des données par `RefBanque`
- **Autonomie** : Interface indépendante du système principal
- **Intégration** : Possibilité d'intégrer l'API dans les systèmes partenaires

## 2. Architecture Technique Partenaire

### 2.1 Stack Technologique
```
┌─────────────────────────────────────────────────────────────┐
│                 Interface Partenaire                       │
├─────────────────────────────────────────────────────────────┤
│  Frontend Vue.js 3    │  Backend Node.js + TypeScript     │
│  + TypeScript         │  + Express.js                     │
│  + Tailwind CSS       │  + Prisma ORM                     │
│  + Chart.js           │  + JWT Auth                       │
├─────────────────────────────────────────────────────────────┤
│                    MySQL Database                          │
│              (Même base, filtrage RefBanque)               │
└─────────────────────────────────────────────────────────────┘
```

### 2.2 Architecture de Données Partenaire
```typescript
// Modèle Prisma spécifique partenaire
model Partner {
  RefBanque    Int      @id @map("RefBanque")
  NameBanque   String
  RefPays      Int
  LogoBanque   String?
  ContactEmail String?
  ApiKey       String   @unique
  IsActive     Boolean  @default(true)
  
  // Relations filtrées
  users        User[]
  operations   Operation[]
  products     Product[]
  agencies     Agency[]
  
  @@map("TbleBanque")
}

model PartnerUser {
  RefUser      Int      @id @map("RefUser")
  Login        String   @unique
  Password     String
  Name         String
  Email        String
  RefBanque    Int      // OBLIGATOIRE pour partenaire
  Role         String   // partner_admin, partner_viewer
  ApiAccess    Boolean  @default(false)
  
  partner      Partner  @relation(fields: [RefBanque], references: [RefBanque])
  
  @@map("TbleUsers")
}
```

## 3. API Partenaire

### 3.1 Authentification Partenaire
```typescript
// POST /partner-api/auth/login
interface PartnerLoginRequest {
  email: string;
  password: string;
  partner_code: string; // Code unique partenaire
}

interface PartnerLoginResponse {
  access_token: string;
  refresh_token: string;
  partner: {
    id: number;
    name: string;
    country: string;
    permissions: string[];
  };
  expires_in: number;
}

// Middleware authentification partenaire
class PartnerAuthMiddleware {
  async validatePartnerToken(req: Request) {
    const token = req.headers.authorization?.split(' ')[1];
    const payload = jwt.verify(token, process.env.PARTNER_JWT_SECRET!);
    
    // Validation que l'utilisateur appartient au partenaire
    const user = await prisma.user.findUnique({
      where: { RefUser: payload.userId },
      include: { partner: true }
    });
    
    if (!user?.RefBanque) {
      throw new UnauthorizedException('Partner access required');
    }
    
    req.partnerContext = {
      partnerId: user.RefBanque,
      userId: user.RefUser,
      permissions: payload.permissions
    };
  }
}
```

### 3.2 Endpoints API Partenaire
```typescript
// === DASHBOARD PARTENAIRE ===
GET    /partner-api/dashboard/stats
GET    /partner-api/dashboard/operations/recent
GET    /partner-api/dashboard/agencies
GET    /partner-api/dashboard/performance

// === OPÉRATIONS PARTENAIRE ===
GET    /partner-api/operations
GET    /partner-api/operations/:id
GET    /partner-api/operations/export
GET    /partner-api/operations/stats

// === AGENCES PARTENAIRE ===
GET    /partner-api/agencies
GET    /partner-api/agencies/:id/operations
GET    /partner-api/agencies/:id/stats
GET    /partner-api/agencies/:id/cash-registers

// === PRODUITS PARTENAIRE ===
GET    /partner-api/products
GET    /partner-api/products/:id/operations
GET    /partner-api/products/:id/performance

// === ANALYTICS PARTENAIRE ===
GET    /partner-api/analytics/operations
GET    /partner-api/analytics/commissions
GET    /partner-api/analytics/volumes
POST   /partner-api/analytics/export

// === UTILISATEURS PARTENAIRE ===
GET    /partner-api/users
POST   /partner-api/users
PUT    /partner-api/users/:id
GET    /partner-api/users/:id/activity
```

### 3.3 Services Partenaire
```typescript
// src/services/PartnerService.ts
export class PartnerService {
  constructor(private prisma: PrismaClient) {}
  
  async getPartnerOperations(partnerId: number, filters: OperationFilters) {
    return this.prisma.operation.findMany({
      where: {
        RefBanque: partnerId, // Filtrage automatique par partenaire
        ...filters
      },
      include: {
        cashRegister: {
          include: { agency: true }
        },
        user: {
          select: { Name: true, Email: true }
        },
        product: true
      },
      orderBy: { Insert_Time: 'desc' }
    });
  }
  
  async getPartnerStats(partnerId: number, dateRange: DateRange) {
    const [operations, totalVolume, commissions] = await Promise.all([
      this.getOperationsCount(partnerId, dateRange),
      this.getTotalVolume(partnerId, dateRange),
      this.getCommissions(partnerId, dateRange)
    ]);
    
    return {
      operations_count: operations,
      total_volume: totalVolume,
      commissions: commissions,
      agencies_count: await this.getAgenciesCount(partnerId),
      active_users: await this.getActiveUsersCount(partnerId)
    };
  }
  
  async getPartnerAgencies(partnerId: number) {
    return this.prisma.agency.findMany({
      where: {
        // Agences ayant des caisses avec des opérations du partenaire
        cashRegisters: {
          some: {
            operations: {
              some: { RefBanque: partnerId }
            }
          }
        }
      },
      include: {
        country: true,
        cashRegisters: {
          where: {
            operations: {
              some: { RefBanque: partnerId }
            }
          }
        }
      }
    });
  }
}
```

## 4. Interface Web Partenaire

### 4.1 Dashboard Partenaire
```vue
<!-- src/views/PartnerDashboard.vue -->
<template>
  <div class="partner-dashboard">
    <!-- Header Partenaire -->
    <div class="dashboard-header">
      <div class="partner-info">
        <img :src="partner.logo" :alt="partner.name" class="partner-logo">
        <h1>{{ partner.name }} - Dashboard</h1>
        <span class="country-badge">{{ partner.country }}</span>
      </div>
      <div class="date-selector">
        <DateRangePicker v-model="dateRange" @change="refreshData" />
      </div>
    </div>
    
    <!-- Statistiques Partenaire -->
    <div class="stats-grid">
      <StatsCard 
        title="Opérations Totales"
        :value="stats.operations_count"
        :trend="stats.operations_trend"
        icon="CurrencyDollarIcon"
        color="blue"
      />
      <StatsCard 
        title="Volume Total"
        :value="formatCurrency(stats.total_volume)"
        :trend="stats.volume_trend"
        icon="TrendingUpIcon"
        color="green"
      />
      <StatsCard 
        title="Commissions"
        :value="formatCurrency(stats.commissions)"
        :trend="stats.commission_trend"
        icon="BanknotesIcon"
        color="purple"
      />
      <StatsCard 
        title="Agences Actives"
        :value="stats.agencies_count"
        icon="BuildingOfficeIcon"
        color="orange"
      />
    </div>
    
    <!-- Graphiques Performance -->
    <div class="charts-section">
      <div class="chart-container">
        <h3>Volume d'Opérations</h3>
        <LineChart 
          :data="volumeChartData"
          :options="chartOptions"
        />
      </div>
      <div class="chart-container">
        <h3>Répartition par Agence</h3>
        <DoughnutChart 
          :data="agencyChartData"
          :options="doughnutOptions"
        />
      </div>
    </div>
    
    <!-- Opérations Récentes -->
    <div class="recent-operations">
      <h3>Opérations Récentes</h3>
      <PartnerOperationsTable 
        :operations="recentOperations"
        :loading="loading"
        @view-details="viewOperationDetails"
      />
    </div>
    
    <!-- Agences Performance -->
    <div class="agencies-performance">
      <h3>Performance par Agence</h3>
      <AgencyPerformanceGrid 
        :agencies="agencies"
        @view-agency="viewAgencyDetails"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { usePartnerStore } from '@/stores/partner';
import { formatCurrency } from '@/utils/currency';

const partnerStore = usePartnerStore();
const dateRange = ref({ start: new Date(), end: new Date() });
const loading = ref(false);

const stats = computed(() => partnerStore.stats);
const recentOperations = computed(() => partnerStore.recentOperations);
const agencies = computed(() => partnerStore.agencies);
const partner = computed(() => partnerStore.currentPartner);

onMounted(async () => {
  await refreshData();
});

const refreshData = async () => {
  loading.value = true;
  try {
    await Promise.all([
      partnerStore.fetchStats(dateRange.value),
      partnerStore.fetchRecentOperations(),
      partnerStore.fetchAgencies()
    ]);
  } finally {
    loading.value = false;
  }
};
</script>
```

### 4.2 Composants Partenaire Spécifiques
```vue
<!-- src/components/partner/PartnerOperationsTable.vue -->
<template>
  <div class="partner-operations-table">
    <div class="table-header">
      <div class="filters">
        <select v-model="filters.agency" @change="applyFilters">
          <option value="">Toutes les agences</option>
          <option v-for="agency in agencies" :key="agency.id" :value="agency.id">
            {{ agency.name }}
          </option>
        </select>
        <select v-model="filters.product" @change="applyFilters">
          <option value="">Tous les produits</option>
          <option v-for="product in products" :key="product.id" :value="product.id">
            {{ product.name }}
          </option>
        </select>
      </div>
      <button @click="exportOperations" class="export-btn">
        <DownloadIcon class="w-4 h-4" />
        Exporter
      </button>
    </div>
    
    <DataTable 
      :columns="columns"
      :data="operations"
      :loading="loading"
      :pagination="pagination"
      @page-change="handlePageChange"
      @sort="handleSort"
    >
      <template #cell-amount="{ value }">
        <span class="font-semibold text-green-600">
          {{ formatCurrency(value) }}
        </span>
      </template>
      
      <template #cell-status="{ value }">
        <StatusBadge :status="value" />
      </template>
      
      <template #cell-actions="{ row }">
        <button 
          @click="viewDetails(row)"
          class="text-blue-600 hover:text-blue-800"
        >
          Voir détails
        </button>
      </template>
    </DataTable>
  </div>
</template>
```

### 4.3 Store Pinia Partenaire
```typescript
// src/stores/partner.ts
import { defineStore } from 'pinia';
import { PartnerApiService } from '@/services/PartnerApiService';

export const usePartnerStore = defineStore('partner', () => {
  const currentPartner = ref<Partner | null>(null);
  const stats = ref<PartnerStats>({});
  const operations = ref<Operation[]>([]);
  const agencies = ref<Agency[]>([]);
  const loading = ref(false);
  
  const partnerApi = new PartnerApiService();
  
  const fetchStats = async (dateRange: DateRange) => {
    loading.value = true;
    try {
      stats.value = await partnerApi.getStats(dateRange);
    } catch (error) {
      console.error('Error fetching partner stats:', error);
    } finally {
      loading.value = false;
    }
  };
  
  const fetchOperations = async (filters: OperationFilters = {}) => {
    loading.value = true;
    try {
      const response = await partnerApi.getOperations(filters);
      operations.value = response.data;
      return response;
    } catch (error) {
      console.error('Error fetching operations:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  };
  
  const fetchAgencies = async () => {
    try {
      agencies.value = await partnerApi.getAgencies();
    } catch (error) {
      console.error('Error fetching agencies:', error);
    }
  };
  
  const exportOperations = async (filters: OperationFilters, format: 'pdf' | 'excel') => {
    try {
      const blob = await partnerApi.exportOperations(filters, format);
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `operations_${format}_${new Date().toISOString().split('T')[0]}.${format}`;
      a.click();
      window.URL.revokeObjectURL(url);
    } catch (error) {
      console.error('Error exporting operations:', error);
    }
  };
  
  return {
    currentPartner: readonly(currentPartner),
    stats: readonly(stats),
    operations: readonly(operations),
    agencies: readonly(agencies),
    loading: readonly(loading),
    fetchStats,
    fetchOperations,
    fetchAgencies,
    exportOperations
  };
});
```

## 5. Sécurité et Authentification

### 5.1 Authentification Multi-Niveau
```typescript
// Authentification partenaire avec API Key
interface PartnerAuth {
  // Niveau 1: Authentification utilisateur partenaire
  user_auth: {
    email: string;
    password: string;
    partner_code: string;
  };
  
  // Niveau 2: API Key pour intégrations
  api_key_auth: {
    api_key: string;
    signature: string; // HMAC des données
    timestamp: number;
  };
}

// Middleware validation API Key
class PartnerApiKeyMiddleware {
  async validateApiKey(req: Request) {
    const apiKey = req.headers['x-api-key'];
    const signature = req.headers['x-signature'];
    const timestamp = req.headers['x-timestamp'];
    
    const partner = await prisma.partner.findUnique({
      where: { ApiKey: apiKey }
    });
    
    if (!partner || !partner.IsActive) {
      throw new UnauthorizedException('Invalid API key');
    }
    
    // Validation signature HMAC
    const expectedSignature = this.generateSignature(
      req.body, 
      partner.ApiSecret, 
      timestamp
    );
    
    if (signature !== expectedSignature) {
      throw new UnauthorizedException('Invalid signature');
    }
    
    req.partnerContext = { partnerId: partner.RefBanque };
  }
}
```

### 5.2 Permissions Partenaire
```typescript
// Système de permissions granulaires
enum PartnerPermission {
  VIEW_OPERATIONS = 'view_operations',
  VIEW_ANALYTICS = 'view_analytics',
  VIEW_AGENCIES = 'view_agencies',
  EXPORT_DATA = 'export_data',
  MANAGE_USERS = 'manage_users',
  API_ACCESS = 'api_access'
}

// Décorateur validation permissions
function RequirePartnerPermission(permission: PartnerPermission) {
  return function(target: any, propertyName: string, descriptor: PropertyDescriptor) {
    const method = descriptor.value;
    descriptor.value = async function(...args: any[]) {
      const req = args[0] as Request;
      const userPermissions = req.partnerContext?.permissions || [];
      
      if (!userPermissions.includes(permission)) {
        throw new ForbiddenException(`Permission ${permission} required`);
      }
      
      return method.apply(this, args);
    };
  };
}
```

## 6. Intégration API Partenaire

### 6.1 SDK JavaScript pour Partenaires
```typescript
// SDK pour intégration dans les systèmes partenaires
class IOBPartnerSDK {
  constructor(
    private apiKey: string,
    private apiSecret: string,
    private baseUrl: string = 'https://api.iob.com/partner-api'
  ) {}
  
  async getOperations(filters?: OperationFilters): Promise<Operation[]> {
    const response = await this.makeAuthenticatedRequest('GET', '/operations', filters);
    return response.data;
  }
  
  async getStats(dateRange: DateRange): Promise<PartnerStats> {
    const response = await this.makeAuthenticatedRequest('GET', '/dashboard/stats', dateRange);
    return response.data;
  }
  
  async exportOperations(filters: OperationFilters, format: 'pdf' | 'excel'): Promise<Blob> {
    const response = await this.makeAuthenticatedRequest(
      'POST', 
      '/analytics/export', 
      { ...filters, format }
    );
    return response.blob();
  }
  
  private async makeAuthenticatedRequest(method: string, endpoint: string, data?: any) {
    const timestamp = Date.now();
    const signature = this.generateSignature(data, timestamp);
    
    const headers = {
      'X-API-Key': this.apiKey,
      'X-Signature': signature,
      'X-Timestamp': timestamp.toString(),
      'Content-Type': 'application/json'
    };
    
    const response = await fetch(`${this.baseUrl}${endpoint}`, {
      method,
      headers,
      body: data ? JSON.stringify(data) : undefined
    });
    
    if (!response.ok) {
      throw new Error(`API Error: ${response.statusText}`);
    }
    
    return response;
  }
  
  private generateSignature(data: any, timestamp: number): string {
    const payload = JSON.stringify(data) + timestamp;
    return crypto.createHmac('sha256', this.apiSecret).update(payload).digest('hex');
  }
}

// Utilisation du SDK
const iobSDK = new IOBPartnerSDK('your-api-key', 'your-api-secret');

// Récupérer les opérations du jour
const todayOperations = await iobSDK.getOperations({
  date_from: new Date().toISOString().split('T')[0],
  date_to: new Date().toISOString().split('T')[0]
});

// Exporter les données
const exportBlob = await iobSDK.exportOperations({
  date_from: '2024-01-01',
  date_to: '2024-01-31'
}, 'excel');
```

### 6.2 Webhooks Partenaire
```typescript
// Système de webhooks pour notifications temps réel
interface PartnerWebhook {
  RefWebhook: number;
  RefBanque: number;
  Url: string;
  Events: string[]; // operation_created, operation_approved, etc.
  Secret: string;
  IsActive: boolean;
}

class PartnerWebhookService {
  async sendWebhook(partnerId: number, event: string, data: any) {
    const webhooks = await prisma.partnerWebhook.findMany({
      where: {
        RefBanque: partnerId,
        Events: { has: event },
        IsActive: true
      }
    });
    
    for (const webhook of webhooks) {
      await this.sendWebhookNotification(webhook, event, data);
    }
  }
  
  private async sendWebhookNotification(webhook: PartnerWebhook, event: string, data: any) {
    const payload = {
      event,
      data,
      timestamp: new Date().toISOString(),
      partner_id: webhook.RefBanque
    };
    
    const signature = crypto
      .createHmac('sha256', webhook.Secret)
      .update(JSON.stringify(payload))
      .digest('hex');
    
    try {
      await fetch(webhook.Url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-IOB-Signature': signature,
          'X-IOB-Event': event
        },
        body: JSON.stringify(payload)
      });
    } catch (error) {
      console.error(`Webhook failed for partner ${webhook.RefBanque}:`, error);
    }
  }
}
```

## 7. Déploiement et Configuration

### 7.1 Configuration Partenaire
```typescript
// Configuration spécifique par partenaire
interface PartnerConfig {
  partner_id: number;
  name: string;
  country: string;
  
  // Branding
  logo_url: string;
  primary_color: string;
  secondary_color: string;
  
  // API Configuration
  api_key: string;
  api_secret: string;
  rate_limit: number; // requêtes par minute
  
  // Features
  features: {
    analytics: boolean;
    export: boolean;
    webhooks: boolean;
    api_access: boolean;
  };
  
  // Notifications
  notification_email: string;
  webhook_urls: string[];
}
```

### 7.2 Docker Deployment
```dockerfile
# Dockerfile.partner-api
FROM node:18-alpine

WORKDIR /app

# Installation dépendances
COPY package*.json ./
RUN npm ci --only=production

# Copie du code
COPY . .

# Build TypeScript
RUN npm run build

# Variables d'environnement
ENV NODE_ENV=production
ENV PORT=3001

EXPOSE 3001

CMD ["npm", "start"]
```

```yaml
# docker-compose.partner.yml
version: '3.8'
services:
  partner-api:
    build:
      context: .
      dockerfile: Dockerfile.partner-api
    ports:
      - "3001:3001"
    environment:
      - DATABASE_URL=mysql://user:password@mysql:3306/iob
      - PARTNER_JWT_SECRET=your-partner-jwt-secret
      - REDIS_URL=redis://redis:6379
    depends_on:
      - mysql
      - redis
    
  partner-frontend:
    build:
      context: ./frontend
      dockerfile: Dockerfile.partner
    ports:
      - "8081:80"
    environment:
      - VITE_API_URL=http://localhost:3001
```

## 8. Documentation API

### 8.1 OpenAPI Specification
```yaml
# partner-api.openapi.yml
openapi: 3.0.0
info:
  title: IOB Partner API
  version: 1.0.0
  description: API dédiée aux partenaires bancaires IOB

servers:
  - url: https://api.iob.com/partner-api
    description: Production
  - url: http://localhost:3001
    description: Development

security:
  - ApiKeyAuth: []
  - BearerAuth: []

paths:
  /auth/login:
    post:
      summary: Authentification partenaire
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/PartnerLoginRequest'
      responses:
        '200':
          description: Authentification réussie
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/PartnerLoginResponse'
  
  /dashboard/stats:
    get:
      summary: Statistiques dashboard partenaire
      parameters:
        - name: date_from
          in: query
          schema:
            type: string
            format: date
        - name: date_to
          in: query
          schema:
            type: string
            format: date
      responses:
        '200':
          description: Statistiques récupérées
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/PartnerStats'

components:
  securitySchemes:
    ApiKeyAuth:
      type: apiKey
      in: header
      name: X-API-Key
    BearerAuth:
      type: http
      scheme: bearer
      bearerFormat: JWT
  
  schemas:
    PartnerLoginRequest:
      type: object
      required:
        - email
        - password
        - partner_code
      properties:
        email:
          type: string
          format: email
        password:
          type: string
        partner_code:
          type: string
```

## Conclusion

Cette interface partenaire offre :
- **API REST complète** avec authentification sécurisée
- **Dashboard web personnalisé** par partenaire
- **SDK JavaScript** pour intégrations faciles
- **Webhooks** pour notifications temps réel
- **Isolation complète** des données par partenaire
- **Documentation API** complète avec OpenAPI

**Durée de développement** : 3-4 mois
**Équipe** : 3-4 développeurs (1 Backend, 1 Frontend, 1 DevOps, 1 QA)
**Budget estimé** : 150K€ développement + 30K€/an maintenance

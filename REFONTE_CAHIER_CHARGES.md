# Cahier des Charges - Refonte IOB avec Vue.js et Backend Moderne

## 1. Contexte et Objectifs

### 1.1 Contexte Actuel
- **Application existante** : IOB en PHP MVC avec MySQL
- **Fonctionnalités** : Système de gestion de caisse multi-agence, multi-pays
- **Architecture actuelle** : Monolithe PHP avec vues server-side
- **Base de données** : MySQL avec structure multi-tenant existante

### 1.2 Objectifs de la Refonte
- **Moderniser l'interface utilisateur** avec Vue.js 3 + Composition API
- **Créer un backend API moderne** avec Node.js/TypeScript
- **Conserver la base MySQL** existante avec ORM moderne (Prisma/TypeORM)
- **Améliorer l'expérience utilisateur** avec une interface responsive et intuitive
- **Préparer l'évolution** vers l'architecture microservices future

## 2. Architecture Technique

### 2.1 Stack Technologique Recommandée

#### **Frontend**
```
┌─────────────────────────────────────────────────────────────┐
│                    Vue.js 3 + TypeScript                   │
├─────────────────────────────────────────────────────────────┤
│  Vue Router 4    │  Pinia (State)   │  Vite (Build Tool)  │
├─────────────────────────────────────────────────────────────┤
│  Tailwind CSS    │  Headless UI     │  Chart.js/D3.js    │
├─────────────────────────────────────────────────────────────┤
│  Axios (HTTP)    │  Socket.io       │  Vue i18n           │
└─────────────────────────────────────────────────────────────┘
```

#### **Backend**
```
┌─────────────────────────────────────────────────────────────┐
│                 Node.js + TypeScript                       │
├─────────────────────────────────────────────────────────────┤
│  Express.js      │  Prisma ORM      │  JWT Auth           │
├─────────────────────────────────────────────────────────────┤
│  Zod Validation  │  Winston Logs    │  Redis Cache        │
├─────────────────────────────────────────────────────────────┤
│  Socket.io       │  Nodemailer      │  Sharp (Images)     │
└─────────────────────────────────────────────────────────────┘
```

#### **Base de Données**
- **MySQL 8.0+** : Conservation de la structure existante
- **Prisma** : ORM moderne avec type safety
- **Redis** : Cache pour sessions et données fréquentes

### 2.2 Architecture de l'Application

```
┌─────────────────────────────────────────────────────────────┐
│                    Frontend (Vue.js)                       │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────────────┐   │
│  │  Dashboard  │ │  Operations │ │  Administration     │   │
│  │  Module     │ │  Module     │ │  Module            │   │
│  └─────────────┘ └─────────────┘ └─────────────────────┘   │
└─────────────────────┬───────────────────────────────────────┘
                      │ HTTP/WebSocket
┌─────────────────────▼───────────────────────────────────────┐
│                 Backend API (Node.js)                      │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────────────┐   │
│  │ Auth        │ │ Operations  │ │ Analytics           │   │
│  │ Controller  │ │ Controller  │ │ Controller         │   │
│  └─────────────┘ └─────────────┘ └─────────────────────┘   │
└─────────────────────┬───────────────────────────────────────┘
                      │ Prisma ORM
┌─────────────────────▼───────────────────────────────────────┐
│                   MySQL Database                           │
│         (Structure existante conservée)                    │
└─────────────────────────────────────────────────────────────┘
```

## 3. Modules et Fonctionnalités

### 3.1 Module Authentification
#### **Fonctionnalités**
- Connexion avec email/mot de passe
- Authentification à deux facteurs (2FA)
- Gestion des sessions JWT
- Récupération de mot de passe
- Intégration Microsoft OAuth (existant)

#### **Interface Vue.js**
```vue
<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="max-w-md w-full space-y-8">
      <LoginForm 
        @submit="handleLogin"
        :loading="isLoading"
        :error="loginError"
      />
      <TwoFactorModal 
        v-if="showTwoFactor"
        @verify="handleTwoFactor"
        @close="closeTwoFactor"
      />
    </div>
  </div>
</template>
```

#### **API Endpoints**
```typescript
// POST /api/auth/login
interface LoginRequest {
  email: string;
  password: string;
}

// POST /api/auth/verify-2fa
interface TwoFactorRequest {
  token: string;
  code: string;
}

// GET /api/auth/me
interface UserProfile {
  id: number;
  name: string;
  email: string;
  country_id: number;
  partner_id?: number;
  permissions: string[];
  cash_registers: CashRegister[];
}
```

### 3.2 Module Dashboard
#### **Fonctionnalités**
- Vue d'ensemble des activités du jour
- Statistiques en temps réel
- Graphiques de performance
- Notifications et alertes
- Raccourcis vers actions fréquentes

#### **Interface Vue.js**
```vue
<template>
  <div class="dashboard-grid">
    <StatsCard 
      title="Opérations du jour"
      :value="todayStats.operations"
      :trend="todayStats.operationsTrend"
      icon="CurrencyDollarIcon"
    />
    <RealtimeChart 
      :data="chartData"
      type="operations"
      :height="300"
    />
    <RecentOperations 
      :operations="recentOperations"
      @view-details="viewOperationDetails"
    />
    <QuickActions 
      :actions="availableActions"
      @action="handleQuickAction"
    />
  </div>
</template>
```

### 3.3 Module Opérations (Billetage)
#### **Fonctionnalités**
- Création d'opérations (dépôt, retrait, transfert)
- Validation en temps réel des soldes
- Gestion des coupures de billets
- Workflow d'approbation multi-niveau
- Impression de reçus avec QR code

#### **Interface Vue.js**
```vue
<template>
  <div class="operations-container">
    <OperationForm 
      v-model="operationData"
      :cash-registers="availableCashRegisters"
      :products="availableProducts"
      @submit="createOperation"
      @validate="validateOperation"
    />
    <BillBreakdown 
      v-if="showBillBreakdown"
      v-model="billBreakdown"
      :amount="operationData.amount"
    />
    <ApprovalWorkflow 
      v-if="requiresApproval"
      :operation="pendingOperation"
      :approvers="availableApprovers"
      @approve="handleApproval"
    />
  </div>
</template>
```

#### **API Endpoints**
```typescript
// POST /api/operations
interface CreateOperationRequest {
  type: 'deposit' | 'withdrawal' | 'transfer';
  amount: number;
  cash_register_id: number;
  client_name: string;
  account_number: string;
  product_id: number;
  bill_breakdown?: BillBreakdown;
  notes?: string;
}

// GET /api/operations
interface GetOperationsQuery {
  page: number;
  limit: number;
  date_from?: string;
  date_to?: string;
  type?: string;
  cash_register_id?: number;
  status?: string;
}
```

### 3.4 Module Caisse
#### **Fonctionnalités**
- Gestion des caisses par agence
- Transferts de fonds entre caisses
- Suivi des soldes en temps réel
- Historique des mouvements
- Gestion des réserves

### 3.5 Module Remittance
#### **Fonctionnalités**
- Transferts inter-agences
- Transferts internationaux
- Suivi des transferts en cours
- Validation et approbation
- Gestion des devises

### 3.6 Module Analytics
#### **Fonctionnalités**
- Rapports de performance par période
- Graphiques interactifs
- Calcul des commissions
- Export des données (PDF, Excel)
- Comparaisons multi-périodes

### 3.7 Module Administration
#### **Fonctionnalités**
- Gestion des utilisateurs et rôles
- Configuration des agences
- Gestion des produits financiers
- Configuration des permissions
- Paramètres système

## 4. Architecture de Données avec Prisma

### 4.1 Configuration Prisma
```typescript
// prisma/schema.prisma
generator client {
  provider = "prisma-client-js"
}

datasource db {
  provider = "mysql"
  url      = env("DATABASE_URL")
}

model User {
  RefUser      Int      @id @map("RefUser")
  Login        String   @unique
  Password     String
  Name         String
  Email        String
  RefPays      Int
  RefBanque    Int?
  IsActive     Boolean  @default(true)
  Insert_Time  DateTime @default(now())
  
  // Relations
  country      Country  @relation(fields: [RefPays], references: [RefPays])
  partner      Partner? @relation(fields: [RefBanque], references: [RefBanque])
  operations   Operation[]
  permissions  Permission[]
  
  @@map("TbleUsers")
}

model Operation {
  RefOperations    Int      @id @map("RefOperations")
  RefCaisse       Int
  NumCompte       String
  NameClient      String
  MontantVersement Decimal
  RefPays         Int
  RefBanque       Int?
  Insert_Time     DateTime @default(now())
  Insert_Id       Int
  
  // Relations
  cashRegister    CashRegister @relation(fields: [RefCaisse], references: [RefCaisse])
  country         Country      @relation(fields: [RefPays], references: [RefPays])
  partner         Partner?     @relation(fields: [RefBanque], references: [RefBanque])
  user            User         @relation(fields: [Insert_Id], references: [RefUser])
  
  @@map("TbleOperations")
}
```

### 4.2 Services de Données
```typescript
// src/services/OperationService.ts
import { PrismaClient } from '@prisma/client';

export class OperationService {
  constructor(private prisma: PrismaClient) {}
  
  async createOperation(data: CreateOperationData, userId: number) {
    return this.prisma.operation.create({
      data: {
        ...data,
        Insert_Id: userId,
        Insert_Time: new Date()
      },
      include: {
        cashRegister: true,
        country: true,
        partner: true,
        user: {
          select: {
            Name: true,
            Email: true
          }
        }
      }
    });
  }
  
  async getOperations(filters: OperationFilters, userId: number) {
    const userContext = await this.getUserContext(userId);
    
    return this.prisma.operation.findMany({
      where: {
        RefPays: userContext.countryId,
        RefBanque: userContext.partnerId,
        ...filters
      },
      include: {
        cashRegister: true,
        user: {
          select: { Name: true }
        }
      },
      orderBy: {
        Insert_Time: 'desc'
      }
    });
  }
}
```

## 5. Sécurité et Authentification

### 5.1 JWT avec Contexte Multi-Tenant
```typescript
// src/middleware/auth.ts
interface JWTPayload {
  userId: number;
  countryId: number;
  partnerId?: number;
  permissions: string[];
  cashRegisterIds: number[];
  iat: number;
  exp: number;
}

export const authenticateToken = async (req: Request, res: Response, next: NextFunction) => {
  const token = req.headers.authorization?.split(' ')[1];
  
  if (!token) {
    return res.status(401).json({ error: 'Token required' });
  }
  
  try {
    const payload = jwt.verify(token, process.env.JWT_SECRET!) as JWTPayload;
    
    // Validation du contexte utilisateur
    const user = await prisma.user.findUnique({
      where: { RefUser: payload.userId },
      include: {
        country: true,
        partner: true,
        permissions: true
      }
    });
    
    if (!user || !user.IsActive) {
      return res.status(401).json({ error: 'Invalid user' });
    }
    
    // Injection du contexte dans la requête
    req.user = user;
    req.tenantContext = {
      countryId: payload.countryId,
      partnerId: payload.partnerId,
      permissions: payload.permissions,
      cashRegisterIds: payload.cashRegisterIds
    };
    
    next();
  } catch (error) {
    return res.status(401).json({ error: 'Invalid token' });
  }
};
```

## 6. Planning de Développement

### 6.1 Phase 1 : Fondations (4-6 semaines)
#### **Backend**
- Configuration du projet Node.js/TypeScript
- Setup Prisma avec base MySQL existante
- Authentification JWT multi-tenant
- Middleware de sécurité et permissions
- Tests unitaires de base

#### **Frontend**
- Configuration Vue.js 3 + Vite + TypeScript
- Setup Tailwind CSS et design system
- Composants UI de base
- Store Pinia pour authentification
- Routing et guards

### 6.2 Phase 2 : Modules Core (6-8 semaines)
#### **Module Authentification**
- Interface de connexion moderne
- 2FA avec QR code
- Gestion des sessions
- Récupération de mot de passe

#### **Module Dashboard**
- Vue d'ensemble temps réel
- Graphiques interactifs
- Notifications push
- Raccourcis actions

#### **Module Opérations**
- Formulaire de création d'opérations
- Validation temps réel
- Workflow d'approbation
- Impression reçus

### 6.3 Phase 3 : Modules Avancés (6-8 semaines)
#### **Module Caisse**
- Gestion des caisses
- Transferts de fonds
- Suivi des soldes
- Historique des mouvements

#### **Module Remittance**
- Transferts inter-agences
- Gestion des devises
- Suivi des transferts
- Validation multi-niveau

#### **Module Analytics**
- Rapports interactifs
- Export de données
- Graphiques avancés
- Comparaisons temporelles

### 6.4 Phase 4 : Administration et Finalisation (4-6 semaines)
#### **Module Administration**
- Gestion des utilisateurs
- Configuration des agences
- Gestion des produits
- Paramètres système

#### **Finalisation**
- Tests d'intégration complets
- Optimisation des performances
- Documentation utilisateur
- Formation des équipes

## 7. Ressources Nécessaires

### 7.1 Équipe de Développement
- **1 Tech Lead** : Architecture et coordination
- **2 Développeurs Backend** : Node.js/TypeScript/Prisma
- **2 Développeurs Frontend** : Vue.js/TypeScript
- **1 UI/UX Designer** : Interface utilisateur moderne
- **1 DevOps** : Déploiement et infrastructure
- **1 QA** : Tests et validation

### 7.2 Infrastructure
- **Serveur de développement** : Node.js + MySQL + Redis
- **Serveur de staging** : Environnement de test
- **Outils de développement** : VS Code, Git, Docker
- **Monitoring** : Sentry pour erreurs, Analytics

## 8. Livrables

### 8.1 Code Source
- **Frontend Vue.js** : Application SPA complète
- **Backend Node.js** : API REST avec WebSockets
- **Base de données** : Scripts de migration Prisma
- **Tests** : Couverture > 80%

### 8.2 Documentation
- **Documentation technique** : Architecture et APIs
- **Guide utilisateur** : Manuel d'utilisation
- **Guide d'installation** : Déploiement
- **Formation** : Sessions pour les équipes

## 9. Budget Estimé

### 9.1 Développement (6-8 mois)
- **Équipe de développement** : 7 personnes × 7 mois = 350K€
- **Infrastructure et outils** : 20K€
- **Formation et accompagnement** : 30K€
- **Total développement** : 400K€

### 9.2 Maintenance (Annuelle)
- **Support technique** : 50K€/an
- **Évolutions fonctionnelles** : 30K€/an
- **Infrastructure** : 20K€/an
- **Total maintenance** : 100K€/an

## 10. Risques et Mitigation

### 10.1 Risques Techniques
- **Migration des données** : Tests approfondis requis
- **Performance** : Optimisation continue nécessaire
- **Sécurité** : Audit de sécurité obligatoire

### 10.2 Risques Projet
- **Délais** : Planning avec buffer de 20%
- **Ressources** : Équipe dédiée à temps plein
- **Formation** : Accompagnement utilisateurs

## 11. Tâches Détaillées par Module

### 11.1 Module Authentification

#### **Écrans à Développer**
1. **Page de Connexion** (`/Applications/App/Modules/Connexion/Views/index.php`)
   - Formulaire email/mot de passe
   - Validation côté client avec Zod
   - Gestion des erreurs en temps réel
   - Intégration Microsoft OAuth

2. **Page 2FA** (`/Applications/App/Modules/Connexion/Views/doubleauth.php`)
   - Interface de saisie du code 2FA
   - Génération QR code pour configuration
   - Timer de validité du code
   - Option "Se souvenir de cet appareil"

#### **Tâches Backend**
- **API POST /auth/login** : Validation credentials + génération JWT
- **API POST /auth/verify-2fa** : Vérification code TOTP
- **API GET /auth/me** : Récupération profil utilisateur avec contexte
- **Middleware JWT** : Validation token + injection contexte tenant
- **Service AuthService** : Logique métier authentification

#### **Tâches Frontend**
- **Composant LoginForm.vue** : Formulaire avec validation
- **Composant TwoFactorModal.vue** : Modal 2FA
- **Store auth.ts** : Gestion état authentification
- **Guard router** : Protection routes authentifiées
- **Service AuthService.ts** : Appels API auth

### 11.2 Module Dashboard (Bielletage Index)

#### **Écrans à Développer**
1. **Dashboard Principal** (`/Applications/App/Modules/Bielletage/Views/index.php`)
   - Filtres Pays/Agence/Caisse (pour admin/superadmin)
   - 4 cartes statistiques : Dépôt, Retrait, Solde, Horloge
   - Tableau opérations du jour avec pagination
   - Liens rapides configurés par pays
   - Alertes soldes agences en temps réel

#### **Tâches Backend**
- **API GET /dashboard/stats** : Statistiques du jour par contexte
- **API GET /dashboard/operations** : Opérations récentes paginées
- **API GET /dashboard/alerts** : Alertes soldes agences
- **WebSocket events** : Mise à jour temps réel stats
- **Service DashboardService** : Agrégation données multi-tenant

#### **Tâches Frontend**
- **Page DashboardView.vue** : Layout principal
- **Composant StatsCards.vue** : 4 cartes avec animations
- **Composant OperationsTable.vue** : Tableau avec actions
- **Composant CountryAgencyFilter.vue** : Filtres hiérarchiques
- **Composant QuickLinks.vue** : Liens configurables
- **Store dashboard.ts** : Gestion état + WebSocket

### 11.3 Module Opérations (Billetage)

#### **Écrans à Développer**
1. **Formulaire Opérations** (`/Applications/App/Modules/Bielletage/Views/bielletage.php`)
   - Wizard 4 étapes : Billets, Pièces, Infos Client, Produit
   - Calculateur automatique montants par coupure
   - Sélection produit dynamique par caisse
   - Validation soldes en temps réel
   - Gestion des 5 types : Versement, Retrait, Appro, Sortie, Transfert

2. **Interface Billetage Détaillé**
   - Grille coupures : 10000, 5000, 2000, 1000, 500, 250, 200, 100
   - Grille pièces : 50, 25, 10, 5, 1
   - Calcul automatique total par ligne
   - Validation cohérence montant saisi/billetage

#### **Tâches Backend**
- **API POST /operations** : Création opération avec billetage
- **API GET /operations/validate-balance** : Vérification solde temps réel
- **API GET /cash-registers/products** : Produits disponibles par caisse
- **API POST /operations/approve** : Workflow approbation multi-niveau
- **Service OperationService** : Logique métier + validation
- **Service BillBreakdownService** : Gestion détail coupures

#### **Tâches Frontend**
- **Page OperationWizard.vue** : Wizard 4 étapes
- **Composant BillBreakdownGrid.vue** : Grille saisie coupures
- **Composant OperationTypeSelector.vue** : Sélection type opération
- **Composant ProductSelector.vue** : Sélection produit dynamique
- **Composant ApprovalWorkflow.vue** : Interface approbation
- **Store operations.ts** : Gestion état + validation

### 11.4 Module Journal

#### **Écrans à Développer**
1. **Journal des Opérations** (`/Applications/App/Modules/Journal/Views/index.php`)
   - Filtres : Agence, Dates, Produit
   - Statistiques période : Total Dépôt/Retrait
   - Tableau avec statut validation (couleurs)
   - Actions : Valider, Annuler validation, Supprimer, Imprimer
   - Modal validation avec sélection agence source

#### **Tâches Backend**
- **API GET /journal/operations** : Liste avec filtres + pagination
- **API POST /journal/validate** : Validation opération
- **API POST /journal/cancel-validation** : Annulation validation
- **API DELETE /journal/operations/:id** : Suppression opération
- **API GET /journal/stats** : Statistiques période
- **Service JournalService** : Logique validation + audit

#### **Täches Frontend**
- **Page JournalView.vue** : Interface principale
- **Composant JournalFilters.vue** : Filtres avancés
- **Composant JournalTable.vue** : Tableau avec actions
- **Composant ValidationModal.vue** : Modal validation
- **Composant PeriodStats.vue** : Statistiques période
- **Store journal.ts** : Gestion état + filtres

### 11.5 Module Analytics

#### **Écrans à Développer**
1. **Rapports Analytics** (`/Applications/App/Modules/Analytics/Views/index.php`)
   - Sélecteur période (du/au)
   - Statistiques : Total Dépôt/Retrait + Commissions
   - Tableau détaillé opérations avec export
   - Graphiques interactifs par période

2. **Graphiques Performance** (`/Applications/App/Modules/Analytics/Views/chart.php`)
   - Graphiques temporels volumes
   - Répartition par agence/produit
   - Comparaisons périodes

#### **Tâches Backend**
- **API GET /analytics/operations** : Données avec filtres temporels
- **API GET /analytics/stats** : Statistiques agrégées
- **API GET /analytics/charts** : Données graphiques
- **API POST /analytics/export** : Export PDF/Excel
- **Service AnalyticsService** : Calculs commissions + agrégations

#### **Tâches Frontend**
- **Page AnalyticsView.vue** : Interface rapports
- **Composant DateRangePicker.vue** : Sélecteur période
- **Composant AnalyticsCharts.vue** : Graphiques Chart.js
- **Composant ExportTools.vue** : Outils export
- **Composant StatsGrid.vue** : Grille statistiques
- **Store analytics.ts** : Gestion données + cache

### 11.6 Module Caisse

#### **Écrans à Développer**
1. **Gestion Caisses** (`/Applications/App/Modules/Caisse/Views/index.php`)
   - Sélecteur caisse utilisateur
   - Affichage solde temps réel
   - Historique opérations caisse
   - Actions : Transfert fonds, Appro caisse

2. **Transfert de Fonds** (`/Applications/App/Modules/Caisse/Views/transfertfond.php`)
   - Sélection caisse source/destination
   - Saisie montant avec validation solde
   - Workflow approbation

#### **Tâches Backend**
- **API GET /cash-registers** : Liste caisses utilisateur
- **API GET /cash-registers/:id/balance** : Solde temps réel
- **API POST /cash-registers/transfer** : Transfert entre caisses
- **API GET /cash-registers/:id/operations** : Historique caisse
- **Service CashRegisterService** : Gestion soldes + transferts

#### **Tâches Frontend**
- **Page CashRegisterView.vue** : Interface principale
- **Composant CashRegisterSelector.vue** : Sélecteur caisse
- **Composant BalanceDisplay.vue** : Affichage solde animé
- **Composant FundTransferModal.vue** : Modal transfert
- **Store cashRegister.ts** : Gestion état + WebSocket soldes

### 11.7 Module Remittance

#### **Écrans à Développer**
1. **Transferts Inter-Agences**
   - Formulaire transfert avec agences source/destination
   - Calcul frais automatique
   - Suivi statut transferts
   - Gestion devises multiples

#### **Tâches Backend**
- **API POST /remittances** : Création transfert inter-agence
- **API GET /remittances** : Liste transferts avec suivi
- **API GET /remittances/fees** : Calcul frais transfert
- **API POST /remittances/:id/approve** : Approbation transfert
- **Service RemittanceService** : Logique transferts + devises

#### **Tâches Frontend**
- **Page RemittanceView.vue** : Interface transferts
- **Composant RemittanceForm.vue** : Formulaire transfert
- **Composant TransferTracker.vue** : Suivi transferts
- **Composant ExchangeRates.vue** : Taux de change
- **Store remittance.ts** : Gestion transferts

### 11.8 Module Administration (Pannel)

#### **Écrans à Développer**
1. **Gestion Utilisateurs** (`/Applications/App/Modules/Users/Views/index.php`)
   - CRUD utilisateurs avec rôles
   - Gestion permissions granulaires
   - Configuration 2FA
   - Historique connexions

2. **Gestion Agences** (`/Applications/App/Modules/Pannel/Views/listeagence.php`)
   - CRUD agences par pays
   - Configuration caisses par agence
   - Gestion liens rapides

3. **Gestion Produits** (`/Applications/App/Modules/Pannel/Views/listeproduit.php`)
   - CRUD produits par partenaire
   - Configuration permissions produits/caisse
   - Paramétrage commissions

#### **Tâches Backend**
- **API CRUD /users** : Gestion utilisateurs complète
- **API CRUD /agencies** : Gestion agences
- **API CRUD /products** : Gestion produits
- **API GET/POST /permissions** : Gestion permissions granulaires
- **Service AdminService** : Logique administration

#### **Tâches Frontend**
- **Page AdminPanel.vue** : Interface onglets
- **Composant UserManagement.vue** : Gestion utilisateurs
- **Composant AgencyManagement.vue** : Gestion agences
- **Composant ProductManagement.vue** : Gestion produits
- **Composant PermissionMatrix.vue** : Matrice permissions
- **Store admin.ts** : Gestion état admin

## 12. Composants UI Réutilisables

### 12.1 Composants de Base
```typescript
// Composants fondamentaux à développer

1. **DataTable.vue** - Tableau avec tri, filtres, pagination
2. **FormInput.vue** - Input avec validation intégrée
3. **SelectDropdown.vue** - Select avec recherche
4. **DatePicker.vue** - Sélecteur de date
5. **Modal.vue** - Modal réutilisable
6. **Button.vue** - Boutons avec variants
7. **Card.vue** - Cartes avec header/footer
8. **Tabs.vue** - Onglets navigables
9. **Pagination.vue** - Pagination avec infos
10. **LoadingSpinner.vue** - Indicateurs chargement
11. **Toast.vue** - Notifications toast
12. **ConfirmDialog.vue** - Dialogues confirmation
13. **NumberInput.vue** - Input numérique formaté
14. **CurrencyDisplay.vue** - Affichage montants
15. **StatusBadge.vue** - Badges statut colorés
```

### 12.2 Composants Métier
```typescript
// Composants spécifiques IOB

1. **CountrySelector.vue** - Sélecteur pays multi-tenant
2. **AgencySelector.vue** - Sélecteur agence hiérarchique
3. **CashRegisterSelector.vue** - Sélecteur caisse
4. **ProductSelector.vue** - Sélecteur produit dynamique
5. **BillBreakdownGrid.vue** - Grille billetage
6. **OperationStatusBadge.vue** - Statut opérations
7. **ApprovalWorkflow.vue** - Workflow approbation
8. **BalanceDisplay.vue** - Affichage solde animé
9. **ReceiptPrinter.vue** - Générateur reçus
10. **QRCodeGenerator.vue** - Générateur QR codes
```

## 13. Spécifications Techniques Détaillées

### 13.1 Structure Base de Données Prisma
```typescript
// Schema Prisma complet pour toutes les tables existantes

model User {
  RefUser      Int      @id @map("RefUser")
  Login        String   @unique
  Password     String
  Name         String
  Email        String
  RefPays      Int
  RefBanque    Int?
  IsActive     Boolean  @default(true)
  Insert_Time  DateTime @default(now())
  
  // Relations
  country      Country  @relation(fields: [RefPays], references: [RefPays])
  partner      Partner? @relation(fields: [RefBanque], references: [RefBanque])
  operations   Operation[]
  permissions  Permission[]
  
  @@map("TbleUsers")
}

model Operation {
  RefOperations    Int      @id @map("RefOperations")
  RefCaisse       Int
  NumCompte       String
  NameClient      String
  MontantVersement Decimal
  RefPays         Int
  RefBanque       Int?
  Insert_Time     DateTime @default(now())
  Insert_Id       Int
  Validate        Int      @default(1)
  Approve1_Id     Int?
  Approve2_Id     Int?
  
  // Relations
  cashRegister    CashRegister @relation(fields: [RefCaisse], references: [RefCaisse])
  country         Country      @relation(fields: [RefPays], references: [RefPays])
  partner         Partner?     @relation(fields: [RefBanque], references: [RefBanque])
  user            User         @relation(fields: [Insert_Id], references: [RefUser])
  billBreakdown   BillBreakdown?
  
  @@map("TbleOperations")
}

model BillBreakdown {
  RefBielletage   Int @id @map("RefBielletage")
  RefOperations   Int @unique
  // Billets
  a2 Int? // Quantité billets 10000
  b2 Int? // Quantité billets 5000
  c2 Int? // Quantité billets 2000
  d2 Int? // Quantité billets 1000
  e2 Int? // Quantité billets 500
  f2 Int? // Quantité billets 250
  g2 Int? // Quantité billets 200
  h2 Int? // Quantité billets 100
  // Pièces
  i2 Int? // Quantité pièces 50
  j2 Int? // Quantité pièces 25
  k2 Int? // Quantité pièces 10
  l2 Int? // Quantité pièces 5
  m2 Int? // Quantité pièces 1
  
  operation Operation @relation(fields: [RefOperations], references: [RefOperations])
  
  @@map("TbleBielletage")
}
```

### 13.2 API Endpoints Complets
```typescript
// Spécification complète de tous les endpoints

// AUTH
POST   /api/auth/login
POST   /api/auth/verify-2fa
POST   /api/auth/refresh
POST   /api/auth/logout
GET    /api/auth/me

// DASHBOARD
GET    /api/dashboard/stats
GET    /api/dashboard/operations
GET    /api/dashboard/alerts

// OPERATIONS
GET    /api/operations
POST   /api/operations
GET    /api/operations/:id
PUT    /api/operations/:id
DELETE /api/operations/:id
POST   /api/operations/:id/approve
GET    /api/operations/validate-balance

// CASH REGISTERS
GET    /api/cash-registers
GET    /api/cash-registers/:id
GET    /api/cash-registers/:id/balance
GET    /api/cash-registers/:id/operations
POST   /api/cash-registers/transfer
GET    /api/cash-registers/:id/products

// JOURNAL
GET    /api/journal/operations
POST   /api/journal/validate
POST   /api/journal/cancel-validation
GET    /api/journal/stats

// ANALYTICS
GET    /api/analytics/operations
GET    /api/analytics/stats
GET    /api/analytics/charts
POST   /api/analytics/export

// REMITTANCES
GET    /api/remittances
POST   /api/remittances
GET    /api/remittances/:id
POST   /api/remittances/:id/approve
GET    /api/remittances/fees

// ADMIN
GET    /api/users
POST   /api/users
GET    /api/users/:id
PUT    /api/users/:id
DELETE /api/users/:id
GET    /api/agencies
POST   /api/agencies
GET    /api/products
POST   /api/products
GET    /api/permissions
POST   /api/permissions
```

### 13.3 Planning Détaillé par Sprint

#### **Sprint 1-2 (Semaines 1-4) : Fondations**
- Setup projet Node.js + TypeScript + Prisma
- Configuration Vue.js 3 + Vite + Tailwind
- Authentification JWT + 2FA
- Composants UI de base (DataTable, Modal, Form)
- Middleware sécurité multi-tenant

#### **Sprint 3-4 (Semaines 5-8) : Dashboard + Opérations**
- Page Dashboard avec stats temps réel
- Module Opérations avec wizard billetage
- WebSocket pour mises à jour temps réel
- Validation soldes et workflow approbation

#### **Sprint 5-6 (Semaines 9-12) : Journal + Caisse**
- Module Journal avec filtres avancés
- Validation opérations et audit trail
- Module Caisse avec transferts
- Gestion soldes temps réel

#### **Sprint 7-8 (Semaines 13-16) : Analytics + Remittance**
- Module Analytics avec graphiques
- Export PDF/Excel des rapports
- Module Remittance inter-agences
- Gestion devises multiples

#### **Sprint 9-10 (Semaines 17-20) : Administration**
- Module Administration complet
- Gestion utilisateurs et permissions
- Configuration agences et produits
- Interface permissions granulaires

#### **Sprint 11-12 (Semaines 21-24) : Tests + Déploiement**
- Tests d'intégration complets
- Optimisation performances
- Documentation utilisateur
- Formation équipes et mise en production

## Conclusion

Cette refonte transformera IOB en application moderne avec :
- **Interface utilisateur** intuitive et responsive
- **Performance** optimisée avec cache Redis
- **Sécurité** renforcée avec JWT multi-tenant
- **Évolutivité** préparée pour croissance future

**Durée totale** : 6-8 mois (24 semaines)  
**Budget** : 400K€ développement + 100K€/an maintenance  
**ROI** : Amélioration productivité 40%, réduction coûts support 60%  
**Livrables** : 50+ composants Vue.js, 40+ endpoints API, Documentation complète

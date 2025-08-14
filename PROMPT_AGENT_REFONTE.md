# Prompt pour Agent IA - Refonte IOB Vue.js + Node.js

## Mission

Tu es un développeur expert chargé de moderniser l'application IOB (système de gestion de caisse multi-tenant) en migrant de PHP vers une architecture moderne Vue.js 3 + Node.js/TypeScript + Prisma, tout en conservant la base MySQL existante.

## Contexte du Projet

**Application actuelle** : IOB - Système de gestion de caisse bancaire multi-pays/multi-partenaires
- **Architecture existante** : PHP MVC monolithique avec vues server-side
- **Base de données** : MySQL avec structure multi-tenant complexe
- **Utilisateurs** : Caissiers, chefs de caisse, administrateurs dans plusieurs pays d'Afrique
- **Fonctionnalités** : Opérations bancaires, billetage, transferts, analytics, administration

## Objectif de la Refonte

Créer une application moderne avec :
- **Frontend** : Vue.js 3 + TypeScript + Composition API
- **Backend** : Node.js + TypeScript + Express + Prisma ORM
- **Base de données** : MySQL existante (CONSERVÉE)
- **Architecture** : SPA avec API REST + WebSockets temps réel
- **Sécurité** : JWT multi-tenant avec 2FA

## Fichiers Clés à Analyser

### 1. Documentation Projet
- `REFONTE_CAHIER_CHARGES.md` - **FICHIER PRINCIPAL** avec toutes les spécifications
- `API_ARCHITECTURE.md` - Architecture microservices future (référence)
- `db_structure.sql` - Structure complète base de données MySQL

### 2. Contrôleurs PHP Existants (logique métier à porter)
- `Applications/App/Modules/Bielletage/BielletageController.class.php` - Opérations principales
- `Applications/App/Modules/Connexion/ConnexionController.class.php` - Authentification
- `Applications/App/Modules/Journal/JournalController.class.php` - Journal des opérations
- `Applications/App/Modules/Analytics/AnalyticsController.class.php` - Rapports
- `Applications/App/Modules/Caisse/CaisseController.class.php` - Gestion caisses
- `Applications/App/Modules/Pannel/PannelController.class.php` - Administration
- `Applications/App/Modules/Users/UsersController.class.php` - Gestion utilisateurs
- `Applications/App/Modules/Remittance/RemittanceController.class.php` - Transferts

### 3. Vues PHP Existantes (interfaces à recréer en Vue.js)
- `Applications/App/Modules/Bielletage/Views/index.php` - Dashboard principal
- `Applications/App/Modules/Bielletage/Views/bielletage.php` - Formulaire opérations complexe
- `Applications/App/Modules/Journal/Views/index.php` - Journal avec validation
- `Applications/App/Modules/Analytics/Views/index.php` - Rapports analytics
- `Applications/App/Modules/Caisse/Views/index.php` - Interface caisses
- `Applications/App/Modules/Connexion/Views/index.php` - Page de connexion
- `Applications/App/Modules/Connexion/Views/doubleauth.php` - Interface 2FA

### 4. Managers/Services PHP (logique à porter)
- `Library/Models/` - Tous les managers de données existants
- `Library/Entities/User.class.php` - Entité utilisateur

## Étapes de Travail Prioritaires

### Phase 1 : Setup et Fondations (Semaines 1-4)

#### 1.1 Analyse Approfondie
- **Lire intégralement** `REFONTE_CAHIER_CHARGES.md`
- **Analyser** `db_structure.sql` pour comprendre le modèle multi-tenant
- **Examiner** les contrôleurs PHP pour extraire la logique métier
- **Étudier** les vues PHP pour comprendre les interfaces utilisateur

#### 1.2 Setup Backend Node.js
```bash
# Structure à créer
backend/
├── src/
│   ├── controllers/
│   ├── services/
│   ├── middleware/
│   ├── routes/
│   ├── types/
│   └── utils/
├── prisma/
│   ├── schema.prisma
│   └── migrations/
├── package.json
└── tsconfig.json
```

**Technologies requises :**
- Node.js 18+ + TypeScript
- Express.js pour l'API REST
- Prisma ORM pour MySQL
- JWT + bcrypt pour l'authentification
- Socket.io pour temps réel
- Zod pour validation
- Winston pour logs

#### 1.3 Setup Frontend Vue.js
```bash
# Structure à créer
frontend/
├── src/
│   ├── components/
│   │   ├── ui/          # Composants réutilisables
│   │   └── business/    # Composants métier
│   ├── views/           # Pages principales
│   ├── stores/          # Pinia stores
│   ├── services/        # Services API
│   ├── types/           # Types TypeScript
│   └── utils/
├── package.json
└── vite.config.ts
```

**Technologies requises :**
- Vue.js 3 + TypeScript + Composition API
- Vite pour le build
- Vue Router 4 pour navigation
- Pinia pour state management
- Tailwind CSS pour styling
- Headless UI pour composants
- Axios pour HTTP
- Socket.io-client pour WebSocket

### Phase 2 : Modules Core (Semaines 5-12)

#### 2.1 Module Authentification
- **Backend** : API `/auth/login`, `/auth/verify-2fa`, middleware JWT
- **Frontend** : LoginForm.vue, TwoFactorModal.vue, auth store
- **Référence** : `ConnexionController.class.php` + vues connexion

#### 2.2 Module Dashboard
- **Backend** : API `/dashboard/stats`, `/dashboard/operations`
- **Frontend** : DashboardView.vue, StatsCards.vue, OperationsTable.vue
- **Référence** : `BielletageController.executeIndex()` + `Bielletage/Views/index.php`

#### 2.3 Module Opérations (Billetage)
- **Backend** : API `/operations` CRUD + validation soldes
- **Frontend** : OperationWizard.vue, BillBreakdownGrid.vue (13 coupures)
- **Référence** : `BielletageController` + `Bielletage/Views/bielletage.php`

### Phase 3 : Modules Avancés (Semaines 13-20)

#### 3.1 Module Journal
- **Backend** : API `/journal/operations` avec filtres + validation
- **Frontend** : JournalView.vue, ValidationModal.vue
- **Référence** : `JournalController` + `Journal/Views/index.php`

#### 3.2 Module Analytics
- **Backend** : API `/analytics/stats` + `/analytics/export`
- **Frontend** : AnalyticsView.vue, Charts avec Chart.js
- **Référence** : `AnalyticsController` + `Analytics/Views/index.php`

#### 3.3 Module Administration
- **Backend** : API CRUD `/users`, `/agencies`, `/products`
- **Frontend** : AdminPanel.vue, UserManagement.vue
- **Référence** : `PannelController` + `UsersController`

## Contraintes Techniques Critiques

### Architecture Multi-Tenant
- **OBLIGATOIRE** : Isolation complète des données par `RefPays` (pays)
- **JWT** doit contenir `country_id`, `partner_id`, `permissions[]`
- **Tous les endpoints** doivent filtrer automatiquement par contexte tenant
- **Middleware** d'injection contexte tenant sur chaque requête

### Gestion du Billetage
- **13 coupures** : 10000, 5000, 2000, 1000, 500, 250, 200, 100, 50, 25, 10, 5, 1
- **Calcul automatique** : quantité × valeur = montant par ligne
- **Validation** : cohérence entre montant saisi et total billetage
- **Stockage** : table `TbleBielletage` avec colonnes a2, b2, c2... m2

### Workflow Approbation
- **Multi-niveau** : `Approve1_Id`, `Approve2_Id` dans `TbleOperations`
- **Statuts** : En attente (1), Validé (2), Rejeté (3)
- **Permissions** : Vérification granulaire par caisse/produit

### Temps Réel
- **WebSocket** pour : soldes caisses, nouvelles opérations, alertes
- **Events** : `operation_created`, `balance_updated`, `approval_required`

## Modèle de Données Prisma

```typescript
// Schema de base à implémenter (extrait du cahier des charges)
model User {
  RefUser      Int      @id @map("RefUser")
  Login        String   @unique
  Password     String
  Name         String
  Email        String
  RefPays      Int      // CRITIQUE : isolation par pays
  RefBanque    Int?     // CRITIQUE : isolation par partenaire
  IsActive     Boolean  @default(true)
  
  country      Country  @relation(fields: [RefPays], references: [RefPays])
  partner      Partner? @relation(fields: [RefBanque], references: [RefBanque])
  operations   Operation[]
  
  @@map("TbleUsers")
}

model Operation {
  RefOperations    Int      @id @map("RefOperations")
  RefCaisse       Int
  NumCompte       String
  NameClient      String
  MontantVersement Decimal
  RefPays         Int      // CRITIQUE : isolation par pays
  RefBanque       Int?
  Validate        Int      @default(1)
  Approve1_Id     Int?
  Approve2_Id     Int?
  
  billBreakdown   BillBreakdown?
  
  @@map("TbleOperations")
}
```

## Composants Vue.js Prioritaires

### Composants UI de Base
1. **DataTable.vue** - Tableau avec tri/filtres/pagination
2. **FormInput.vue** - Input avec validation Zod
3. **Modal.vue** - Modal réutilisable
4. **Button.vue** - Boutons avec variants Tailwind
5. **DatePicker.vue** - Sélecteur de date

### Composants Métier IOB
1. **BillBreakdownGrid.vue** - Grille 13 coupures avec calcul auto
2. **CountryAgencyFilter.vue** - Filtres hiérarchiques pays/agence
3. **OperationStatusBadge.vue** - Badge statut avec couleurs
4. **ApprovalWorkflow.vue** - Interface workflow approbation
5. **BalanceDisplay.vue** - Affichage solde animé temps réel

## API Endpoints Prioritaires

```typescript
// Authentification
POST /api/auth/login
POST /api/auth/verify-2fa
GET  /api/auth/me

// Dashboard
GET  /api/dashboard/stats
GET  /api/dashboard/operations

// Opérations
GET    /api/operations
POST   /api/operations
POST   /api/operations/:id/approve
GET    /api/operations/validate-balance

// Journal
GET  /api/journal/operations
POST /api/journal/validate
GET  /api/journal/stats
```

## Critères de Réussite

### Fonctionnel
- ✅ **Toutes les interfaces** PHP recréées en Vue.js
- ✅ **Toute la logique métier** portée en Node.js/TypeScript
- ✅ **Multi-tenant** : isolation complète par pays/partenaire
- ✅ **Temps réel** : WebSocket pour soldes et notifications
- ✅ **Billetage** : Grille 13 coupures fonctionnelle
- ✅ **Workflow** : Approbation multi-niveau opérationnel

### Technique
- ✅ **Type Safety** : TypeScript strict frontend + backend
- ✅ **Performance** : <200ms latence API, cache Redis
- ✅ **Sécurité** : JWT + 2FA + validation Zod
- ✅ **Tests** : Couverture >80% backend, tests E2E frontend

### Qualité Code
- ✅ **Architecture** : Clean code, SOLID principles
- ✅ **Documentation** : README, API docs, composants Storybook
- ✅ **Git** : Commits atomiques, branches par feature
- ✅ **CI/CD** : Tests automatisés, déploiement Docker

## Instructions de Démarrage

1. **COMMENCE PAR** lire intégralement `REFONTE_CAHIER_CHARGES.md`
2. **ANALYSE** `db_structure.sql` pour comprendre le modèle multi-tenant
3. **EXAMINE** `BielletageController.class.php` pour la logique principale
4. **ÉTUDIE** `Bielletage/Views/bielletage.php` pour le formulaire complexe
5. **SETUP** l'environnement Node.js + Vue.js selon les spécifications
6. **DÉVELOPPE** module par module selon le planning défini

## Ressources et Support

- **Cahier des charges complet** : `REFONTE_CAHIER_CHARGES.md`
- **Architecture future** : `API_ARCHITECTURE.md`
- **Base de données** : `db_structure.sql`
- **Code existant** : Tous les contrôleurs et vues PHP

**Durée estimée** : 6-8 mois (24 semaines)
**Budget** : 400K€ développement
**Équipe** : 7 personnes (1 Tech Lead, 2 Backend, 2 Frontend, 1 UI/UX, 1 DevOps)

---

**🚀 Tu as maintenant toutes les informations pour démarrer la refonte IOB. Commence par analyser les fichiers mentionnés, puis setup l'environnement de développement selon les spécifications du cahier des charges.**

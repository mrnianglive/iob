# IOB Partner API

Interface dédiée aux partenaires bancaires IOB avec API REST moderne et dashboard personnalisé.

## 🏗️ Architecture

### Structure du Projet
```
iob/
├── partner-api/           # API Backend Node.js + TypeScript
│   ├── src/
│   │   ├── controllers/   # Contrôleurs API
│   │   ├── middleware/    # Middlewares d'authentification
│   │   ├── routes/        # Routes API
│   │   ├── services/      # Services métier
│   │   ├── types/         # Types TypeScript
│   │   └── utils/         # Utilitaires
│   ├── prisma/           # Schéma et migrations Prisma
│   └── package.json      # Dépendances Node.js
├── partner-sdk/          # SDK JavaScript pour partenaires
└── docs/                 # Documentation API
```

### Technologies Utilisées
- **Backend**: Node.js + TypeScript + Express
- **ORM**: Prisma avec MySQL
- **Authentification**: JWT + API Keys + HMAC
- **Documentation**: Swagger/OpenAPI 3.0
- **Sécurité**: Helmet, CORS, Rate Limiting

## 🚀 Fonctionnalités

### 1. API REST Complète
- **8 modules API** : Auth, Dashboard, Operations, Agencies, Products, Analytics, Users, Webhooks
- **Authentification multi-niveau** : JWT Bearer + API Key + HMAC
- **Isolation des données** par partenaire (RefBanque)
- **Documentation interactive** Swagger

### 2. Dashboard Partenaire
- **Statistiques en temps réel** : opérations, volumes, commissions
- **Graphiques interactifs** : performance par agence
- **Filtrage avancé** par date, statut, montant
- **Export PDF/Excel** des données

### 3. Gestion des Opérations
- **Consultation des transactions** filtrées par partenaire
- **Détails complets** : client, bénéficiaire, statut
- **Recherche avancée** et pagination
- **Suivi en temps réel** des statuts

### 4. Analytics Avancées
- **Rapports de performance** par période
- **Calcul des commissions** automatique
- **Analyse des volumes** par produit/agence
- **Tendances et comparaisons**

### 5. Webhooks Temps Réel
- **Notifications automatiques** des événements
- **Signature HMAC** pour sécurité
- **Retry automatique** en cas d'échec
- **Configuration flexible** des événements

## 🔐 Sécurité

### Authentification Multi-Niveau
- **JWT Bearer Tokens** pour interface web
- **API Keys + HMAC** pour intégrations système
- **Validation des signatures** avec timestamp
- **Permissions granulaires** par partenaire

### Isolation des Données
- **Filtrage automatique** par RefBanque
- **Accès restreint** aux données du partenaire
- **Logs d'accès** détaillés
- **Rate limiting** configurable

## 🌐 API Endpoints

### Authentification
```
POST /partner-api/auth/login          # Connexion partenaire
POST /partner-api/auth/refresh        # Renouvellement token
```

### Dashboard
```
GET  /partner-api/dashboard/stats     # Statistiques générales
GET  /partner-api/dashboard/operations # Opérations récentes
GET  /partner-api/dashboard/agencies  # Performance agences
```

### Opérations
```
GET  /partner-api/operations          # Liste des opérations
GET  /partner-api/operations/:id      # Détail opération
GET  /partner-api/operations/export   # Export données
```

### Analytics
```
GET  /partner-api/analytics/operations # Analytics opérations
GET  /partner-api/analytics/commissions # Calcul commissions
POST /partner-api/analytics/export    # Export rapports
```

## 🛠️ Installation

### Prérequis
- Node.js 18+
- MySQL 8.0+
- Redis (optionnel, pour cache)

### Installation
```bash
cd partner-api
npm install
cp .env.example .env
# Configurer DATABASE_URL dans .env
npx prisma generate
npm run dev
```

### Configuration
```env
DATABASE_URL="mysql://user:password@localhost:3306/iob"
PARTNER_JWT_SECRET="your-secret-key"
PORT=3001
```

## 📊 Base de Données

### Modèles Principaux
- **Partner** : Partenaires bancaires avec configuration
- **PartnerUser** : Utilisateurs partenaires avec permissions
- **Operation** : Transactions filtrées par RefBanque
- **PartnerWebhook** : Configuration webhooks
- **PartnerApiLog** : Logs d'accès API

### Relations Clés
- Isolation par `RefBanque` (partenaire)
- Permissions granulaires par utilisateur
- Logs complets des accès API

## 🚀 Démarrage Rapide

### 1. Démarrer l'API
```bash
cd partner-api
npm run dev
```

### 2. Accéder à la Documentation
- **API Docs** : http://localhost:3001/docs
- **Health Check** : http://localhost:3001/health

### 3. Tester l'API
```bash
# Test de santé
curl http://localhost:3001/health

# Documentation interactive
open http://localhost:3001/docs
```

## 📈 Monitoring

### Métriques Disponibles
- **Temps de réponse** par endpoint
- **Taux d'erreur** par partenaire
- **Volume d'utilisation** API
- **Performance** des requêtes

### Logs Structurés
- **Accès API** avec détails complets
- **Erreurs** avec stack traces
- **Performance** des requêtes DB
- **Sécurité** et tentatives d'accès

## 🔧 Développement

### Scripts Disponibles
```bash
npm run dev          # Développement avec hot-reload
npm run build        # Build production
npm run start        # Démarrage production
npm run test         # Tests unitaires
npm run lint         # Vérification code
```

### Structure des Types
- Types TypeScript complets
- Validation Joi des requêtes
- Réponses API standardisées
- Gestion d'erreurs centralisée

---

**IOB Partner API** - Interface moderne et sécurisée pour partenaires bancaires.

# 🏦 IOB Partner Interface - Architecture Complète

Interface dédiée aux partenaires bancaires IOB avec API REST, dashboard web personnalisé, SDK JavaScript et système de webhooks temps réel.

## 🎯 Vue d'ensemble

Cette solution complète permet aux partenaires bancaires de :
- **Consulter** leurs opérations IOB via une API sécurisée
- **Analyser** leurs performances avec un dashboard personnalisé
- **Intégrer** facilement via un SDK JavaScript
- **Recevoir** des notifications temps réel par webhooks
- **Exporter** leurs données en PDF/Excel/CSV

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                 Interface Partenaire IOB                   │
├─────────────────────────────────────────────────────────────┤
│  Frontend Vue.js 3    │  Backend Node.js + TypeScript     │
│  + TypeScript         │  + Express.js                     │
│  + Tailwind CSS       │  + Prisma ORM                     │
│  + Chart.js           │  + JWT Auth + API Key             │
├─────────────────────────────────────────────────────────────┤
│                    MySQL Database                          │
│              (Isolation par RefBanque)                     │
├─────────────────────────────────────────────────────────────┤
│  Redis Cache    │  Nginx Proxy    │  Monitoring           │
└─────────────────────────────────────────────────────────────┘
```

## 🚀 Démarrage rapide

### Prérequis

- Docker & Docker Compose
- Node.js 18+ (pour le développement)
- MySQL 8.0+
- Redis (optionnel, pour le cache)

### Installation avec Docker

1. **Cloner le projet**
```bash
git clone https://github.com/iob/partner-interface.git
cd partner-interface
```

2. **Configurer l'environnement**
```bash
cp .env.example .env
# Éditer .env avec vos paramètres
```

3. **Lancer les services**
```bash
# Services principaux
docker-compose -f docker-compose.partner.yml up -d

# Avec monitoring (optionnel)
docker-compose -f docker-compose.partner.yml --profile monitoring up -d
```

4. **Initialiser la base de données**
```bash
# Les migrations Prisma se lancent automatiquement
# Ou manuellement :
docker-compose exec partner-api npm run prisma:migrate
```

### Accès aux services

- **API Partner** : http://localhost:3001
- **Documentation API** : http://localhost:3001/docs
- **Dashboard Partner** : http://localhost:8081
- **Monitoring** : http://localhost:3000 (Grafana)

## 📁 Structure du projet

```
├── partner-api/              # Backend Node.js + TypeScript
│   ├── src/
│   │   ├── controllers/      # Contrôleurs API
│   │   ├── services/         # Services métier
│   │   ├── middleware/       # Middlewares (auth, logs, etc.)
│   │   ├── routes/          # Routes API
│   │   ├── types/           # Types TypeScript
│   │   └── utils/           # Utilitaires
│   ├── prisma/              # Schéma et migrations
│   └── Dockerfile
│
├── partner-frontend/         # Frontend Vue.js 3 (à créer)
│   ├── src/
│   │   ├── components/      # Composants Vue
│   │   ├── views/           # Pages/Vues
│   │   ├── stores/          # Stores Pinia
│   │   └── services/        # Services API
│   └── Dockerfile
│
├── partner-sdk/             # SDK JavaScript
│   ├── src/
│   │   └── index.ts         # SDK principal
│   ├── dist/                # Build du SDK
│   └── README.md
│
├── nginx/                   # Configuration Nginx
├── monitoring/              # Configuration monitoring
├── scripts/                 # Scripts utilitaires
└── docker-compose.partner.yml
```

## 🔐 Sécurité

### Authentification Multi-Niveau

1. **JWT Token** pour les utilisateurs partenaires
2. **API Key + HMAC** pour les intégrations système
3. **Permissions granulaires** par utilisateur
4. **Isolation automatique** par `RefBanque`

### Configuration sécurisée

```typescript
// Exemple de middleware d'authentification
class PartnerAuthMiddleware {
  static async validatePartnerToken(req: Request) {
    const payload = jwt.verify(token, process.env.PARTNER_JWT_SECRET!);
    
    // Vérification appartenance au partenaire
    const user = await prisma.user.findUnique({
      where: { RefUser: payload.userId },
      include: { partner: true }
    });
    
    req.partnerContext = {
      partnerId: user.RefBanque,
      permissions: payload.permissions
    };
  }
}
```

## 📊 API REST Complète

### Endpoints principaux

```bash
# Authentification
POST   /partner-api/auth/login
POST   /partner-api/auth/refresh
GET    /partner-api/auth/me

# Dashboard
GET    /partner-api/dashboard/stats
GET    /partner-api/dashboard/operations/recent
GET    /partner-api/dashboard/agencies

# Opérations
GET    /partner-api/operations
GET    /partner-api/operations/:id
POST   /partner-api/operations/export

# Analytics
GET    /partner-api/analytics/operations
GET    /partner-api/analytics/commissions
GET    /partner-api/analytics/volumes

# Webhooks
GET    /partner-api/webhooks
POST   /partner-api/webhooks
PUT    /partner-api/webhooks/:id
DELETE /partner-api/webhooks/:id
```

### Exemple d'utilisation

```javascript
// Récupérer les statistiques
const response = await fetch('/partner-api/dashboard/stats', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
});

const stats = await response.json();
console.log('Opérations:', stats.data.operations_count);
```

## 🛠️ SDK JavaScript

### Installation

```bash
npm install @iob/partner-sdk
```

### Utilisation

```javascript
import IOBPartnerSDK from '@iob/partner-sdk';

const sdk = new IOBPartnerSDK({
  apiKey: 'your-api-key',
  apiSecret: 'your-api-secret'
});

// Récupérer les opérations
const { operations } = await sdk.getOperations({
  date_from: '2024-01-01',
  status: 'approved'
});

// Export Excel
const blob = await sdk.exportOperations({
  format: 'excel',
  filters: { status: 'approved' }
});
```

## 🔔 Système de Webhooks

### Configuration

```javascript
// Créer un webhook
const webhook = await sdk.createWebhook({
  url: 'https://your-api.com/webhooks/iob',
  events: ['operation_created', 'operation_approved'],
  is_active: true
});

// Validation des signatures
const isValid = sdk.validateWebhookSignature(
  payload, 
  signature, 
  webhookSecret
);
```

### Événements disponibles

- `operation_created` : Nouvelle opération
- `operation_approved` : Opération approuvée
- `operation_rejected` : Opération rejetée
- `operation_cancelled` : Opération annulée

## 📈 Dashboard Partenaire

### Fonctionnalités

- **Statistiques temps réel** : Volume, commissions, nombre d'opérations
- **Graphiques interactifs** : Évolution temporelle, répartition par agence
- **Filtres avancés** : Date, statut, agence, produit
- **Export de données** : PDF, Excel, CSV
- **Gestion d'utilisateurs** : Permissions granulaires

### Composants Vue.js

```vue
<template>
  <PartnerDashboard>
    <StatsGrid :stats="stats" />
    <ChartsSection :data="analyticsData" />
    <OperationsTable :operations="operations" />
    <AgencyPerformance :agencies="agencies" />
  </PartnerDashboard>
</template>
```

## 🐳 Déploiement Docker

### Production

```bash
# Build et déploiement
docker-compose -f docker-compose.partner.yml up -d --build

# Scaling
docker-compose -f docker-compose.partner.yml up -d --scale partner-api=3

# Logs
docker-compose -f docker-compose.partner.yml logs -f partner-api
```

### Configuration Nginx

```nginx
upstream partner_api {
    server partner-api:3001;
}

server {
    listen 443 ssl;
    server_name partner.iob.com;
    
    location /partner-api/ {
        proxy_pass http://partner_api;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

## 📊 Monitoring

### Métriques disponibles

- **Performance API** : Temps de réponse, taux d'erreur
- **Utilisation** : Requêtes par partenaire, endpoints populaires
- **Système** : CPU, mémoire, disque
- **Base de données** : Connexions, requêtes lentes

### Dashboards Grafana

- Vue d'ensemble système
- Performance API par partenaire
- Monitoring des webhooks
- Alertes automatiques

## 🧪 Tests et Qualité

```bash
# Tests unitaires
npm test

# Tests d'intégration
npm run test:integration

# Couverture de code
npm run test:coverage

# Linting
npm run lint

# Type checking
npm run type-check
```

## 🔄 CI/CD

### Pipeline GitHub Actions

```yaml
name: IOB Partner API CI/CD

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '18'
      - run: npm ci
      - run: npm run test
      - run: npm run build

  deploy:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    steps:
      - name: Deploy to production
        run: |
          docker-compose -f docker-compose.partner.yml up -d --build
```

## 📋 Roadmap

### Version 1.1 (Q2 2024)
- [ ] Interface mobile responsive
- [ ] Notifications push
- [ ] API GraphQL
- [ ] Multi-langue (FR/EN/ES)

### Version 1.2 (Q3 2024)
- [ ] Analytics prédictifs
- [ ] Intégration BI
- [ ] API de réconciliation
- [ ] Audit trail complet

### Version 2.0 (Q4 2024)
- [ ] Architecture microservices
- [ ] Support multi-tenant
- [ ] Machine Learning insights
- [ ] Blockchain integration

## 🤝 Contribution

1. Fork le projet
2. Créez votre branche feature (`git checkout -b feature/AmazingFeature`)
3. Commitez vos changements (`git commit -m 'Add AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

## 📝 Documentation

- **API Documentation** : http://localhost:3001/docs
- **SDK Documentation** : [partner-sdk/README.md](partner-sdk/README.md)
- **Architecture Decision Records** : [docs/adr/](docs/adr/)
- **Deployment Guide** : [docs/deployment.md](docs/deployment.md)

## 🆘 Support

- **Documentation** : https://docs.iob.com/partner-api
- **Support technique** : support@iob.com
- **Issues** : https://github.com/iob/partner-interface/issues
- **Slack** : #iob-partner-support

## 📄 Licence

Ce projet est sous licence MIT. Voir [LICENSE](LICENSE) pour plus de détails.

## 🏆 Estimation Projet

- **Durée** : 3-4 mois de développement
- **Équipe** : 4 développeurs (1 Backend, 1 Frontend, 1 DevOps, 1 QA)
- **Budget** : 150K€ développement + 30K€/an maintenance
- **ROI** : Interface moderne + intégrations API + satisfaction partenaires

---

**IOB Partner Interface** - Solution complète pour l'intégration partenaires bancaires 🚀
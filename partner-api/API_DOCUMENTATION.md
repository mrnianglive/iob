# 📚 IOB Partner API - Documentation Complète

## 🎯 Vue d'ensemble

L'API IOB Partner permet aux partenaires bancaires d'accéder à leurs données opérationnelles via une interface REST sécurisée. Cette API supporte deux méthodes d'authentification et offre des fonctionnalités complètes pour la consultation, l'analyse et l'export des données.

## 🔗 URLs de Base

| Environnement | URL de Base |
|---------------|-------------|
| **Production** | `https://api.iob.com/partner-api` |
| **Staging** | `https://staging-api.iob.com/partner-api` |
| **Development** | `http://localhost:3001/partner-api` |

## 📖 Documentation Interactive

- **Swagger UI** : `{BASE_URL}/docs`
- **OpenAPI Spec** : `{BASE_URL}/docs.json`

## 🔐 Authentification

### 1. JWT Bearer Token (Interface Web)

**Utilisation :** Interface web partenaire, applications mobiles

```http
POST /auth/login
Content-Type: application/json

{
  "email": "admin@partner.com",
  "password": "SecurePassword123!",
  "partner_code": "Banque Partenaire XYZ"
}
```

**Réponse :**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "partner": {
      "id": 1,
      "name": "Banque Partenaire XYZ",
      "country": "France",
      "permissions": ["view_operations", "view_analytics", "export_data"]
    },
    "expires_in": 86400
  }
}
```

**Utilisation du token :**
```http
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### 2. API Key + HMAC (Intégrations Système)

**Utilisation :** Intégrations serveur-à-serveur, SDK

**Headers requis :**
```http
X-API-Key: pk_live_1234567890abcdef
X-Signature: sha256=abc123def456...
X-Timestamp: 1642234567
Content-Type: application/json
```

**Génération de la signature HMAC :**
```javascript
const crypto = require('crypto');

function generateSignature(data, apiSecret, timestamp) {
  const payload = JSON.stringify(data) + timestamp;
  return crypto.createHmac('sha256', apiSecret)
    .update(payload)
    .digest('hex');
}

// Exemple
const data = { date_from: '2024-01-01', date_to: '2024-01-31' };
const timestamp = Date.now();
const signature = generateSignature(data, 'sk_live_abcdef1234567890', timestamp);
```

## 📊 Endpoints Principaux

### Authentication

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/auth/login` | POST | Authentification partenaire |
| `/auth/refresh` | POST | Renouvellement du token |
| `/auth/me` | GET | Profil utilisateur |
| `/auth/logout` | POST | Déconnexion |
| `/auth/api-key` | POST | Générer API Key |
| `/auth/api-key` | DELETE | Révoquer API Key |

### Dashboard

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/dashboard/stats` | GET | Statistiques générales |
| `/dashboard/operations/recent` | GET | Opérations récentes |
| `/dashboard/agencies` | GET | Agences avec stats |
| `/dashboard/performance` | GET | Indicateurs de performance |
| `/dashboard/summary` | GET | Résumé complet |

### Opérations

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/operations` | GET | Liste des opérations |
| `/operations/:id` | GET | Détails d'une opération |
| `/operations/stats` | GET | Statistiques des opérations |
| `/operations/summary` | GET | Résumé par période |
| `/operations/export` | POST | Export des opérations |

### Analytics

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/analytics/operations` | GET | Analytics des opérations |
| `/analytics/commissions` | GET | Analytics des commissions |
| `/analytics/volumes` | GET | Analytics des volumes |
| `/analytics/export` | POST | Export des analytics |

### Agences

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/agencies` | GET | Liste des agences |
| `/agencies/:id` | GET | Détails d'une agence |
| `/agencies/:id/operations` | GET | Opérations par agence |
| `/agencies/:id/stats` | GET | Statistiques d'une agence |

### Produits

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/products` | GET | Liste des produits |
| `/products/:id/operations` | GET | Opérations par produit |
| `/products/:id/performance` | GET | Performance d'un produit |

### Webhooks

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/webhooks` | GET | Liste des webhooks |
| `/webhooks` | POST | Créer un webhook |
| `/webhooks/:id` | GET | Détails d'un webhook |
| `/webhooks/:id` | PUT | Modifier un webhook |
| `/webhooks/:id` | DELETE | Supprimer un webhook |
| `/webhooks/:id/test` | POST | Tester un webhook |

## 🚀 Exemples d'Utilisation

### 1. Récupérer les Statistiques du Mois

```bash
curl -X GET "https://api.iob.com/partner-api/dashboard/stats?period=this_month" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json"
```

**Réponse :**
```json
{
  "success": true,
  "data": {
    "operations_count": 1250,
    "total_volume": 2500000.00,
    "commissions": 25000.00,
    "agencies_count": 15,
    "active_users": 8,
    "trends": {
      "operations_trend": 12.5,
      "volume_trend": 8.3,
      "commission_trend": 15.2
    },
    "period": {
      "start": "2024-01-01",
      "end": "2024-01-31",
      "label": "Janvier 2024"
    }
  }
}
```

### 2. Lister les Opérations avec Filtres

```bash
curl -X GET "https://api.iob.com/partner-api/operations?status=approved&date_from=2024-01-01&date_to=2024-01-31&limit=20" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

**Réponse :**
```json
{
  "success": true,
  "data": {
    "operations": [
      {
        "id": 12345,
        "reference": "OP-2024-001234",
        "amount": 1500.50,
        "commission": 15.00,
        "status": "approved",
        "client_name": "Jean Dupont",
        "client_phone": "+33123456789",
        "beneficiary_name": "Marie Martin",
        "beneficiary_country": "Sénégal",
        "created_at": "2024-01-15T10:30:00Z",
        "agency": {
          "id": 1,
          "name": "Agence Paris Centre"
        },
        "product": {
          "id": 1,
          "name": "Transfert Express"
        }
      }
    ],
    "pagination": {
      "page": 1,
      "limit": 20,
      "total": 150,
      "pages": 8
    }
  }
}
```

### 3. Exporter des Données en Excel

```bash
curl -X POST "https://api.iob.com/partner-api/operations/export" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "format": "excel",
    "filters": {
      "date_from": "2024-01-01",
      "date_to": "2024-01-31",
      "status": "approved"
    },
    "columns": ["reference", "amount", "client_name", "status", "created_at"]
  }' \
  --output operations_janvier_2024.xlsx
```

### 4. Créer un Webhook

```bash
curl -X POST "https://api.iob.com/partner-api/webhooks" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://partner.com/webhooks/iob",
    "events": ["operation_created", "operation_approved"],
    "secret": "webhook_secret_key_123",
    "is_active": true
  }'
```

### 5. Authentification avec API Key (HMAC)

```javascript
const crypto = require('crypto');
const axios = require('axios');

class IOBPartnerClient {
  constructor(apiKey, apiSecret, baseUrl = 'https://api.iob.com/partner-api') {
    this.apiKey = apiKey;
    this.apiSecret = apiSecret;
    this.baseUrl = baseUrl;
  }

  generateSignature(data, timestamp) {
    const payload = JSON.stringify(data) + timestamp;
    return crypto.createHmac('sha256', this.apiSecret)
      .update(payload)
      .digest('hex');
  }

  async makeRequest(method, endpoint, data = {}) {
    const timestamp = Date.now();
    const signature = this.generateSignature(data, timestamp);

    const config = {
      method,
      url: `${this.baseUrl}${endpoint}`,
      headers: {
        'X-API-Key': this.apiKey,
        'X-Signature': signature,
        'X-Timestamp': timestamp.toString(),
        'Content-Type': 'application/json'
      }
    };

    if (method !== 'GET') {
      config.data = data;
    } else {
      config.params = data;
    }

    const response = await axios(config);
    return response.data;
  }

  async getStats(dateRange = {}) {
    return this.makeRequest('GET', '/dashboard/stats', dateRange);
  }

  async getOperations(filters = {}) {
    return this.makeRequest('GET', '/operations', filters);
  }
}

// Utilisation
const client = new IOBPartnerClient('pk_live_123', 'sk_live_abc');

// Récupérer les stats du mois
const stats = await client.getStats({ period: 'this_month' });
console.log('Stats:', stats);

// Récupérer les opérations approuvées
const operations = await client.getOperations({ 
  status: 'approved', 
  limit: 50 
});
console.log('Opérations:', operations);
```

## 📋 Filtres et Paramètres Communs

### Filtres de Date

| Paramètre | Type | Description | Exemple |
|-----------|------|-------------|---------|
| `date_from` | string | Date de début (YYYY-MM-DD) | `2024-01-01` |
| `date_to` | string | Date de fin (YYYY-MM-DD) | `2024-01-31` |
| `period` | string | Période prédéfinie | `last_30_days` |

**Périodes disponibles :**
- `today` - Aujourd'hui
- `yesterday` - Hier
- `last_7_days` - 7 derniers jours
- `last_30_days` - 30 derniers jours
- `this_month` - Ce mois
- `last_month` - Mois dernier
- `this_year` - Cette année

### Pagination

| Paramètre | Type | Description | Défaut | Max |
|-----------|------|-------------|---------|-----|
| `page` | integer | Numéro de page | 1 | - |
| `limit` | integer | Éléments par page | 20 | 100 |

### Filtres d'Opérations

| Paramètre | Type | Description | Valeurs |
|-----------|------|-------------|---------|
| `status` | string | Statut de l'opération | `pending`, `approved`, `rejected`, `cancelled` |
| `agency_id` | integer | ID de l'agence | - |
| `product_id` | integer | ID du produit | - |
| `min_amount` | number | Montant minimum | - |
| `max_amount` | number | Montant maximum | - |
| `client_name` | string | Nom du client (recherche) | - |

## 📤 Formats d'Export

### Formats Supportés

| Format | Extension | Content-Type | Description |
|--------|-----------|--------------|-------------|
| **PDF** | `.pdf` | `application/pdf` | Document formaté |
| **Excel** | `.xlsx` | `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet` | Feuille de calcul |
| **CSV** | `.csv` | `text/csv` | Valeurs séparées par virgules |

### Colonnes Disponibles

| Colonne | Description | Type |
|---------|-------------|------|
| `reference` | Référence unique | string |
| `amount` | Montant de l'opération | number |
| `commission` | Commission | number |
| `status` | Statut | string |
| `client_name` | Nom du client | string |
| `client_phone` | Téléphone du client | string |
| `beneficiary_name` | Nom du bénéficiaire | string |
| `beneficiary_phone` | Téléphone du bénéficiaire | string |
| `beneficiary_country` | Pays du bénéficiaire | string |
| `agency_name` | Nom de l'agence | string |
| `product_name` | Nom du produit | string |
| `created_at` | Date de création | datetime |
| `updated_at` | Date de modification | datetime |

## 🔔 Webhooks

### Événements Disponibles

| Événement | Description | Données incluses |
|-----------|-------------|------------------|
| `operation_created` | Nouvelle opération créée | Données complètes de l'opération |
| `operation_approved` | Opération approuvée | Données complètes + statut |
| `operation_rejected` | Opération rejetée | Données complètes + raison |
| `operation_cancelled` | Opération annulée | Données complètes + raison |

### Format des Notifications

```json
{
  "event": "operation_created",
  "data": {
    "id": 12345,
    "reference": "OP-2024-001234",
    "amount": 1500.50,
    "status": "pending",
    "client_name": "Jean Dupont",
    "created_at": "2024-01-15T10:30:00Z"
  },
  "timestamp": "2024-01-15T10:30:05Z",
  "partner_id": 1
}
```

### Validation des Webhooks

```javascript
function validateWebhookSignature(payload, signature, secret) {
  const expectedSignature = crypto
    .createHmac('sha256', secret)
    .update(payload)
    .digest('hex');
  
  return signature === expectedSignature;
}

// Utilisation dans Express.js
app.post('/webhooks/iob', (req, res) => {
  const signature = req.headers['x-iob-signature'];
  const payload = JSON.stringify(req.body);
  const secret = 'webhook_secret_key_123';
  
  if (!validateWebhookSignature(payload, signature, secret)) {
    return res.status(401).send('Invalid signature');
  }
  
  // Traiter l'événement
  console.log('Webhook reçu:', req.body);
  res.status(200).send('OK');
});
```

## 🎯 Permissions et Rôles

### Rôles Utilisateur

| Rôle | Description | Permissions par défaut |
|------|-------------|------------------------|
| `partner_admin` | Administrateur partenaire | Toutes les permissions |
| `partner_viewer` | Consultation seule | `view_operations`, `view_analytics`, `view_agencies` |
| `partner_operator` | Opérateur | `view_operations`, `view_agencies` |

### Permissions Disponibles

| Permission | Description | Endpoints concernés |
|------------|-------------|---------------------|
| `view_operations` | Consulter les opérations | `/operations/*`, `/dashboard/*` |
| `view_analytics` | Consulter les analytics | `/analytics/*`, `/dashboard/performance` |
| `view_agencies` | Consulter les agences | `/agencies/*`, `/dashboard/agencies` |
| `export_data` | Exporter les données | `*/export` |
| `manage_users` | Gérer les utilisateurs | `/users/*` |
| `api_access` | Accès API Key | `/auth/api-key`, `/webhooks/*` |

## ⚠️ Gestion d'Erreurs

### Codes d'Erreur HTTP

| Code | Description | Exemple |
|------|-------------|---------|
| `400` | Requête invalide | Paramètres manquants ou invalides |
| `401` | Non authentifié | Token manquant ou expiré |
| `403` | Permissions insuffisantes | Accès refusé à la ressource |
| `404` | Ressource non trouvée | Opération ou agence inexistante |
| `409` | Conflit | Utilisateur déjà existant |
| `429` | Trop de requêtes | Limite de taux dépassée |
| `500` | Erreur serveur | Erreur interne |

### Format des Erreurs

```json
{
  "success": false,
  "error": "Message d'erreur descriptif",
  "code": "ERROR_CODE",
  "details": {
    "field": "Détail spécifique"
  }
}
```

### Gestion des Erreurs en JavaScript

```javascript
async function handleApiCall() {
  try {
    const response = await fetch('/partner-api/operations', {
      headers: {
        'Authorization': 'Bearer ' + token
      }
    });
    
    const data = await response.json();
    
    if (!data.success) {
      throw new Error(`API Error: ${data.error} (${data.code})`);
    }
    
    return data.data;
  } catch (error) {
    console.error('Erreur API:', error.message);
    
    // Gestion spécifique par code d'erreur
    if (error.message.includes('401')) {
      // Rediriger vers la page de connexion
      window.location.href = '/login';
    } else if (error.message.includes('429')) {
      // Attendre avant de réessayer
      setTimeout(() => handleApiCall(), 60000);
    }
    
    throw error;
  }
}
```

## 🔒 Sécurité et Bonnes Pratiques

### Sécurité des Clés API

1. **Stockage sécurisé** : Utilisez des variables d'environnement
2. **Rotation régulière** : Changez les clés périodiquement
3. **Surveillance** : Surveillez l'utilisation des clés
4. **Révocation** : Révoquez immédiatement les clés compromises

### Rate Limiting

- **Limite par défaut** : 1000 requêtes/minute
- **Headers de réponse** :
  - `X-RateLimit-Limit` : Limite totale
  - `X-RateLimit-Remaining` : Requêtes restantes
  - `X-RateLimit-Reset` : Timestamp de reset

### HTTPS Obligatoire

Toutes les requêtes doivent utiliser HTTPS en production.

### Validation des Données

Toutes les données d'entrée sont validées côté serveur avec Joi.

## 📞 Support et Contact

- **Documentation** : `{BASE_URL}/docs`
- **Support technique** : support@iob.com
- **Status API** : https://status.iob.com
- **Changelog** : Disponible dans la documentation Swagger

## 🔄 Changelog

### Version 1.0.0 (2024-01-15)
- ✅ Lancement initial de l'API Partner
- ✅ Authentification JWT et API Key
- ✅ Endpoints Dashboard, Opérations, Analytics
- ✅ Système de webhooks
- ✅ Export PDF/Excel/CSV
- ✅ Documentation Swagger complète
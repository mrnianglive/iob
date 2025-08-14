# IOB Partner SDK

SDK JavaScript/TypeScript pour l'intégration avec l'API IOB Partner. Ce SDK permet aux partenaires bancaires d'intégrer facilement les services IOB dans leurs systèmes.

## 🚀 Installation

```bash
npm install @iob/partner-sdk
```

## 📖 Usage de base

```typescript
import IOBPartnerSDK from '@iob/partner-sdk';

const sdk = new IOBPartnerSDK({
  apiKey: 'your-api-key',
  apiSecret: 'your-api-secret',
  baseUrl: 'https://api.iob.com/partner-api' // optionnel
});
```

## 🔑 Authentification

Le SDK utilise l'authentification par API Key avec signature HMAC pour sécuriser les requêtes.

### Obtenir vos clés API

1. Connectez-vous au dashboard partenaire IOB
2. Allez dans **Paramètres** > **API Keys**
3. Générez une nouvelle paire API Key/Secret
4. Stockez le secret de manière sécurisée (il ne sera affiché qu'une fois)

## 📊 Exemples d'utilisation

### Récupérer les statistiques

```typescript
// Statistiques générales
const stats = await sdk.getStats();
console.log('Opérations:', stats.operations_count);
console.log('Volume total:', stats.total_volume);

// Statistiques pour une période
const monthlyStats = await sdk.getStats({
  date_from: '2024-01-01',
  date_to: '2024-01-31'
});
```

### Récupérer les opérations

```typescript
// Toutes les opérations récentes
const { operations, pagination } = await sdk.getOperations();

// Opérations avec filtres
const filteredOps = await sdk.getOperations({
  status: 'approved',
  date_from: '2024-01-01',
  amount_min: 100,
  limit: 50
});

// Opération spécifique
const operation = await sdk.getOperation(12345);
```

### Analytics avancés

```typescript
// Analytics généraux
const analytics = await sdk.getAnalytics({
  date_from: '2024-01-01',
  date_to: '2024-01-31'
});

// Analytics des commissions
const commissions = await sdk.getCommissionsAnalytics({
  date_from: '2024-01-01',
  date_to: '2024-01-31'
});

// Analytics des volumes
const volumes = await sdk.getVolumesAnalytics({
  date_from: '2024-01-01',
  date_to: '2024-01-31'
});
```

### Export de données

```typescript
// Export Excel
const excelBlob = await sdk.exportOperations({
  format: 'excel',
  filters: {
    date_from: '2024-01-01',
    status: 'approved'
  }
});

// Télécharger le fichier
const url = URL.createObjectURL(excelBlob);
const a = document.createElement('a');
a.href = url;
a.download = 'operations.xlsx';
a.click();
```

### Gestion des webhooks

```typescript
// Créer un webhook
const webhook = await sdk.createWebhook({
  url: 'https://your-api.com/webhooks/iob',
  events: ['operation_created', 'operation_approved'],
  is_active: true
});

console.log('Secret du webhook:', webhook.secret); // À stocker !

// Lister les webhooks
const webhooks = await sdk.getWebhooks();

// Tester un webhook
const testResult = await sdk.testWebhook(webhookId);
console.log('Test réussi:', testResult.success);
```

## 🔒 Validation des webhooks

```typescript
import { IOBPartnerSDK } from '@iob/partner-sdk';

// Dans votre endpoint webhook
app.post('/webhooks/iob', (req, res) => {
  const signature = req.headers['x-iob-signature'];
  const payload = JSON.stringify(req.body);
  const webhookSecret = 'your-webhook-secret';

  const sdk = new IOBPartnerSDK({ apiKey: '', apiSecret: '' });
  const isValid = sdk.validateWebhookSignature(payload, signature, webhookSecret);

  if (!isValid) {
    return res.status(401).send('Invalid signature');
  }

  // Traiter l'événement
  const { event, data } = req.body;
  console.log('Événement reçu:', event, data);

  res.status(200).send('OK');
});
```

## 📋 API Reference

### Configuration

```typescript
interface PartnerConfig {
  apiKey: string;        // Votre clé API
  apiSecret: string;     // Votre secret API
  baseUrl?: string;      // URL de base (défaut: production)
  timeout?: number;      // Timeout en ms (défaut: 30000)
}
```

### Méthodes principales

#### `getStats(dateRange?)`
Récupère les statistiques du partenaire.

#### `getOperations(filters?)`
Récupère les opérations avec pagination et filtres.

#### `getOperation(id)`
Récupère une opération spécifique.

#### `getAgencies()`
Récupère les agences du partenaire.

#### `getProducts()`
Récupère les produits du partenaire.

#### `exportOperations(options)`
Exporte les opérations dans différents formats.

#### `getAnalytics(dateRange)`
Récupère les analytics avancés.

#### `createWebhook(config)`
Crée un nouveau webhook.

#### `getWebhooks()`
Liste les webhooks configurés.

### Filtres d'opérations

```typescript
interface OperationFilters {
  date_from?: string;      // Date de début (ISO)
  date_to?: string;        // Date de fin (ISO)
  status?: string;         // Statut de l'opération
  agency_id?: number;      // ID de l'agence
  product_id?: number;     // ID du produit
  amount_min?: number;     // Montant minimum
  amount_max?: number;     // Montant maximum
  client_name?: string;    // Nom du client
  reference?: string;      // Référence de l'opération
  page?: number;           // Page (défaut: 1)
  limit?: number;          // Limite par page (défaut: 20, max: 100)
  sort?: string;           // Champ de tri
  order?: 'asc' | 'desc';  // Ordre de tri
}
```

## 🚨 Gestion d'erreurs

```typescript
import { IOBPartnerSDKError } from '@iob/partner-sdk';

try {
  const operations = await sdk.getOperations();
} catch (error) {
  if (error instanceof IOBPartnerSDKError) {
    console.error('Erreur API:', error.message);
    console.error('Code:', error.statusCode);
    console.error('Réponse:', error.response);
  } else {
    console.error('Erreur inconnue:', error);
  }
}
```

## 🔄 Retry et rate limiting

Le SDK gère automatiquement :
- Les timeouts de requête
- La signature HMAC des requêtes
- Les erreurs de réseau

Pour les applications critiques, implémentez votre propre logique de retry :

```typescript
async function withRetry<T>(operation: () => Promise<T>, maxRetries = 3): Promise<T> {
  for (let i = 0; i < maxRetries; i++) {
    try {
      return await operation();
    } catch (error) {
      if (i === maxRetries - 1) throw error;
      await new Promise(resolve => setTimeout(resolve, 1000 * (i + 1)));
    }
  }
  throw new Error('Max retries exceeded');
}

// Usage
const stats = await withRetry(() => sdk.getStats());
```

## 🌐 Environnements

### Production
```typescript
const sdk = new IOBPartnerSDK({
  apiKey: process.env.IOB_API_KEY,
  apiSecret: process.env.IOB_API_SECRET,
  baseUrl: 'https://api.iob.com/partner-api'
});
```

### Staging/Test
```typescript
const sdk = new IOBPartnerSDK({
  apiKey: process.env.IOB_TEST_API_KEY,
  apiSecret: process.env.IOB_TEST_API_SECRET,
  baseUrl: 'https://staging-api.iob.com/partner-api'
});
```

## 📝 TypeScript

Le SDK est entièrement typé et fournit une excellente expérience de développement avec IntelliSense.

```typescript
import IOBPartnerSDK, { OperationFilters, PartnerStats } from '@iob/partner-sdk';

const filters: OperationFilters = {
  status: 'approved', // Auto-complétion des valeurs
  limit: 50
};

const stats: PartnerStats = await sdk.getStats();
```

## 🤝 Support

- **Documentation API** : https://api.iob.com/docs
- **Support technique** : support@iob.com
- **Issues GitHub** : https://github.com/iob/partner-sdk/issues

## 📄 Licence

MIT License - voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 🔄 Changelog

### v1.0.0
- Version initiale du SDK
- Support complet de l'API Partner IOB
- Authentification HMAC
- Gestion des webhooks
- Export de données
- Analytics avancés
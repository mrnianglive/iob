# Architecture API pour l'Application IOB
## Système Multi-Partenaire Multi-Pays de Gestion de Caisse Bancaire

### Vue d'ensemble du Système

L'application IOB est un système complet de gestion de caisse pour institutions financières développé en PHP avec une architecture MVC personnalisée et une base de données MySQL. **Le système est conçu pour supporter multiple partenaires bancaires dans plusieurs pays avec une isolation complète des données.**

## 1. Architecture Multi-Tenant Actuelle

### Structure des Modules
```
Applications/App/Modules/
├── Analytics/          # Analyses et rapports de performance par pays/partenaire
├── Arreter/           # Clôture de caisse multi-agences
├── Bielletage/        # Opérations de caisse principales multi-devises
├── Caisse/            # Gestion des caisses par agence/pays
├── Connexion/         # Système d'authentification multi-tenant
├── Journal/           # Journal des transactions avec isolation pays
├── Pannel/            # Administration système multi-partenaire
├── Remittance/        # Transferts inter-agences/inter-pays
└── Users/             # Gestion des utilisateurs par pays/banque
```

### Architecture Multi-Pays et Multi-Partenaire

#### **Niveau Pays (Multi-Country)**
- **Table `tblpays`** : Gestion centralisée des pays
  - `RefPays` : Identifiant unique du pays
  - `nomPays` : Nom du pays
  - `logo` : Logo spécifique au pays
  - `EmailAlert` : Email d'alerte par pays

#### **Niveau Partenaire Bancaire (Multi-Partner)**
- **Table `TbleBanque`** : Partenaires bancaires par pays
  - `RefBanque` : Identifiant unique de la banque
  - `NameBanque` : Nom de la banque partenaire
  - `RefPays` : Lien vers le pays d'opération

#### **Isolation des Données**
Toutes les entités principales incluent `RefPays` pour l'isolation :
- `TbleOperations.RefPays` - Transactions par pays
- `TbleRemittance.RefPays` - Transferts par pays
- `TbleUsers.RefPays` - Utilisateurs par pays
- `TbleAgency.RefPays` - Agences par pays
- `tbllinks.RefPays` - Liens configurables par pays

### Base de Données Multi-Tenant Complète

#### **Tables Principales**
- `TbleOperations` - Transactions avec isolation pays/partenaire
- `TbleRemittance` - Transferts inter-agences/inter-pays
- `TbleUsers` - Utilisateurs avec affectation pays/banque
- `TbleAgency` - Agences par pays
- `TbleCaisse` - Caisses par agence
- `TbleProduit` - Produits financiers par banque partenaire
- `TbleBanque` - Partenaires bancaires par pays
- `tblpays` - Configuration pays

#### **Tables de Permissions et Configuration**
- `permissions` - Permissions granulaires par utilisateur
- `TbleChmod` - Permissions caisses par utilisateur
- `TbleChmodAppro` - Permissions approvisionnement
- `TbleChmodProduit` - Permissions produits par caisse
- `TbleOuverture` - Horaires d'ouverture par caisse
- `tbllinks` - Liens configurables par pays

#### **Tables de Suivi et Audit**
- `LogConnexion` - Logs de connexion avec IP tracking
- `TbleSolde` - Historique des soldes par caisse
- `TbleJobs` - Système de jobs asynchrones
- `TbleBilletage` - Détail des billetages par opération

## 2. Architecture API Proposée

### 2.1 Structure REST API Multi-Tenant

```
api/
├── v1/
│   ├── auth/              # Authentification multi-tenant
│   ├── countries/         # Gestion des pays
│   ├── partners/          # Partenaires bancaires
│   ├── operations/        # Opérations de caisse par pays
│   ├── cash-registers/    # Gestion des caisses par agence
│   ├── journals/          # Journal des transactions avec isolation
│   ├── remittances/       # Transferts inter-agences/inter-pays
│   ├── analytics/         # Analyses par pays/partenaire
│   ├── users/             # Utilisateurs avec contexte pays/banque
│   ├── agencies/          # Agences par pays
│   ├── products/          # Produits financiers par partenaire
│   └── admin/             # Administration multi-tenant
```

### 2.2 Endpoints Principaux

#### **Authentification Multi-Tenant** (`/api/v1/auth`)
```http
POST   /api/v1/auth/login                    # Login avec contexte pays/banque
POST   /api/v1/auth/logout                   # Logout avec log IP
POST   /api/v1/auth/refresh                  # Refresh token
POST   /api/v1/auth/verify-2fa               # Vérification 2FA
GET    /api/v1/auth/me                      # Profil avec contexte tenant
GET    /api/v1/auth/permissions             # Permissions utilisateur
POST   /api/v1/auth/switch-context          # Changer de contexte pays/banque
```

#### **Gestion des Pays** (`/api/v1/countries`)
```http
GET    /api/v1/countries                    # Liste des pays accessibles
GET    /api/v1/countries/{id}               # Détail d'un pays
PUT    /api/v1/countries/{id}               # Modifier configuration pays
GET    /api/v1/countries/{id}/agencies      # Agences d'un pays
GET    /api/v1/countries/{id}/partners      # Partenaires bancaires du pays
GET    /api/v1/countries/{id}/statistics    # Statistiques par pays
```

#### **Partenaires Bancaires** (`/api/v1/partners`)
```http
GET    /api/v1/partners                     # Liste des partenaires
POST   /api/v1/partners                     # Créer un partenaire
GET    /api/v1/partners/{id}                # Détail d'un partenaire
PUT    /api/v1/partners/{id}                # Modifier un partenaire
GET    /api/v1/partners/{id}/products       # Produits du partenaire
GET    /api/v1/partners/{id}/agencies       # Agences du partenaire
```

#### **Opérations de Caisse Multi-Tenant** (`/api/v1/operations`)
```http
GET    /api/v1/operations                    # Liste des opérations (filtrées par contexte)
POST   /api/v1/operations                    # Nouvelle opération avec validation multi-tenant
GET    /api/v1/operations/{id}               # Détail d'une opération
PUT    /api/v1/operations/{id}/validate      # Valider une opération (workflow approbation)
PUT    /api/v1/operations/{id}/cancel        # Annuler une opération
DELETE /api/v1/operations/{id}               # Supprimer une opération (avec audit)
GET    /api/v1/operations/{id}/receipt       # Bordereau/reçu avec QR code
GET    /api/v1/operations/{id}/billetage     # Détail du billetage
POST   /api/v1/operations/bulk               # Opérations en lot
GET    /api/v1/operations/pending-approval   # Opérations en attente d'approbation
```

#### **Caisses** (`/api/v1/cash-registers`)
```http
GET    /api/v1/cash-registers                # Liste des caisses
GET    /api/v1/cash-registers/{id}           # Détail d'une caisse
GET    /api/v1/cash-registers/{id}/balance   # Solde actuel
POST   /api/v1/cash-registers/{id}/close     # Clôturer une caisse
GET    /api/v1/cash-registers/{id}/operations # Opérations d'une caisse
```

#### **Journal** (`/api/v1/journals`)
```http
GET    /api/v1/journals                      # Journal des transactions
GET    /api/v1/journals/unverified          # Opérations non vérifiées
GET    /api/v1/journals/canceled            # Opérations annulées
GET    /api/v1/journals/daily-summary       # Résumé journalier
```

#### **Transferts Inter-Agences/Inter-Pays** (`/api/v1/remittances`)
```http
GET    /api/v1/remittances                  # Liste des transferts par contexte
POST   /api/v1/remittances                  # Nouveau transfert avec validation solde
GET    /api/v1/remittances/{id}             # Détail d'un transfert
PUT    /api/v1/remittances/{id}/validate    # Valider un transfert
PUT    /api/v1/remittances/{id}/cancel      # Annuler un transfert
GET    /api/v1/remittances/cross-country    # Transferts inter-pays
GET    /api/v1/remittances/by-product/{productId} # Transferts par produit
```

#### **Analyses Multi-Tenant** (`/api/v1/analytics`)
```http
GET    /api/v1/analytics/performance        # Performances par agence/pays
GET    /api/v1/analytics/operations         # Statistiques opérations par contexte
GET    /api/v1/analytics/commissions        # Calcul des commissions par partenaire
GET    /api/v1/analytics/charts             # Données pour graphiques multi-tenant
GET    /api/v1/analytics/country-comparison # Comparaison entre pays
GET    /api/v1/analytics/partner-performance # Performance par partenaire
GET    /api/v1/analytics/cross-border       # Analyses transferts inter-pays
```

#### **Utilisateurs Multi-Tenant** (`/api/v1/users`)
```http
GET    /api/v1/users                        # Liste des utilisateurs (par contexte)
POST   /api/v1/users                        # Créer un utilisateur avec contexte
GET    /api/v1/users/{id}                   # Profil utilisateur
PUT    /api/v1/users/{id}                   # Modifier un utilisateur
DELETE /api/v1/users/{id}                   # Supprimer un utilisateur
PUT    /api/v1/users/{id}/permissions       # Gérer les permissions granulaires
PUT    /api/v1/users/{id}/cash-registers    # Assigner des caisses
PUT    /api/v1/users/{id}/products          # Assigner des produits
POST   /api/v1/users/{id}/2fa               # Configurer 2FA
PUT    /api/v1/users/{id}/2fa/reset         # Réinitialiser 2FA
GET    /api/v1/users/{id}/activity-log      # Log d'activité utilisateur
```

#### **Agences par Pays** (`/api/v1/agencies`)
```http
GET    /api/v1/agencies                     # Liste des agences (par pays)
POST   /api/v1/agencies                     # Créer une agence
GET    /api/v1/agencies/{id}                # Détail d'une agence
PUT    /api/v1/agencies/{id}                # Modifier une agence
GET    /api/v1/agencies/{id}/cash-registers # Caisses d'une agence
GET    /api/v1/agencies/{id}/balance        # Solde global agence
GET    /api/v1/agencies/{id}/daily-closure  # Clôture journalière
POST   /api/v1/agencies/{id}/reserve        # Gérer la réserve
GET    /api/v1/agencies/{id}/performance    # Performance agence
```

#### **Produits Financiers** (`/api/v1/products`)
```http
GET    /api/v1/products                     # Liste des produits (par partenaire)
POST   /api/v1/products                     # Créer un produit
GET    /api/v1/products/{id}                # Détail d'un produit
PUT    /api/v1/products/{id}                # Modifier un produit
GET    /api/v1/products/{id}/permissions    # Permissions produit par caisse
PUT    /api/v1/products/{id}/permissions    # Modifier permissions produit
```

## 3. Modèles de Données API

### 3.1 Opération Multi-Tenant
```json
{
  "id": 12345,
  "type": "deposit|withdrawal|transfer|provision",
  "amount": 100000,
  "client_name": "John Doe",
  "account_number": "ACC123456",
  "cash_register_id": 1,
  "agency_id": 1,
  "country_id": 1,
  "partner_id": 2,
  "product_id": 3,
  "status": "pending|approved_level1|approved_level2|validated|canceled",
  "created_at": "2024-01-15T10:30:00Z",
  "approved_level1_at": "2024-01-15T10:32:00Z",
  "approved_level2_at": "2024-01-15T10:35:00Z",
  "validated_at": "2024-01-15T10:35:00Z",
  "creator_id": 3,
  "approver_level1_id": 4,
  "approver_level2_id": 5,
  "validator_id": 5,
  "receipt_number": "REC-2024-001",
  "unique_id": "OP-2024-001-12345",
  "commission": 2000,
  "stamp_fee": 500,
  "remarks": "Dépôt client",
  "depositor_name": "Jane Smith",
  "depositor_phone": "+223123456789",
  "sent_from_agency_id": null,
  "billetage": {
    "denominations": {
      "10000": 5,
      "5000": 10,
      "1000": 20
    }
  },
  "country": {
    "id": 1,
    "name": "Mali",
    "logo": "mali_logo.png"
  },
  "partner": {
    "id": 2,
    "name": "Orange Money",
    "country_id": 1
  }
}
```

### 3.2 Caisse Multi-Tenant
```json
{
  "id": 1,
  "name": "Caisse 01",
  "agency_id": 1,
  "account_number": "CAISSE-001-BAMAKO",
  "current_balance": 5000000,
  "daily_limit": 10000000,
  "status": "open|closed",
  "last_closure": "2024-01-14T18:00:00Z",
  "assigned_users": [1, 2, 3],
  "authorized_products": [1, 2, 3],
  "opening_hours": {
    "monday": {"start": "08:00", "end": "17:00"},
    "tuesday": {"start": "08:00", "end": "17:00"},
    "wednesday": {"start": "08:00", "end": "17:00"},
    "thursday": {"start": "08:00", "end": "17:00"},
    "friday": {"start": "08:00", "end": "17:00"},
    "saturday": {"start": "08:00", "end": "12:00"},
    "sunday": null
  },
  "agency": {
    "id": 1,
    "name": "Agence Bamako Centre",
    "country_id": 1,
    "phone": "+223123456789"
  },
  "country": {
    "id": 1,
    "name": "Mali",
    "logo": "mali_logo.png"
  }
}
```

### 3.3 Utilisateur Multi-Tenant
```json
{
  "id": 1,
  "login": "user123",
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@bank.com",
  "role_id": 2,
  "role_name": "Caissier",
  "country_id": 1,
  "partner_id": 2,
  "agency_ids": [1, 2],
  "permissions": [
    "create_operation",
    "validate_level1",
    "access_cash_register_1",
    "access_product_orange_money"
  ],
  "cash_register_permissions": {
    "1": ["operate", "provision"],
    "2": ["operate"]
  },
  "product_permissions": [1, 2, 3],
  "two_factor_enabled": true,
  "two_factor_secret": "ENCRYPTED_SECRET",
  "last_login": "2024-01-15T09:00:00Z",
  "last_login_ip": "192.168.1.100",
  "status": "active|inactive",
  "log_status": 1,
  "country": {
    "id": 1,
    "name": "Mali",
    "logo": "mali_logo.png"
  },
  "partner": {
    "id": 2,
    "name": "Orange Money",
    "country_id": 1
  }
}
```

## 4. Sécurité et Authentification

### 4.1 JWT Authentication Multi-Tenant
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "user": {
    "id": 1,
    "login": "user123",
    "role": "cashier",
    "country_id": 1,
    "partner_id": 2,
    "agency_ids": [1, 2],
    "permissions": ["create_operation", "validate_level1"],
    "context": {
      "current_country": {
        "id": 1,
        "name": "Mali",
        "logo": "mali_logo.png"
      },
      "current_partner": {
        "id": 2,
        "name": "Orange Money"
      },
      "accessible_countries": [1, 2],
      "accessible_partners": [1, 2, 3]
    }
  },
  "requires_2fa": false,
  "session_id": "sess_123456789"
}
```

### 4.2 Authentification à Deux Facteurs Multi-Tenant
```json
{
  "requires_2fa": true,
  "qr_code": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...",
  "backup_codes": ["123456", "789012", "345678"],
  "issuer": "IOB-Mali-OrangeMoney",
  "account_name": "user123@mali.iob.com",
  "secret_key": "ENCRYPTED_SECRET",
  "setup_complete": false
}

### 3.4 Pays (Country)
```json
{
  "id": 1,
  "name": "Mali",
  "logo": "mali_logo.png",
  "alert_email": "alerts@mali.iob.com",
  "currency": "XOF",
  "timezone": "GMT+0",
  "active": true,
  "agencies_count": 15,
  "partners_count": 3,
  "total_operations_today": 1250,
  "total_volume_today": 125000000
}
```

### 3.5 Partenaire Bancaire
```json
{
  "id": 2,
  "name": "Orange Money",
  "country_id": 1,
  "logo": "orange_money_logo.png",
  "active": true,
  "products": [
    {
      "id": 1,
      "name": "Dépôt Orange Money",
      "status": "active",
      "commission_rate": 0.02
    },
    {
      "id": 2,
      "name": "Retrait Orange Money",
      "status": "active",
      "commission_rate": 0.015
    }
  ],
  "statistics": {
    "total_operations_today": 450,
    "total_volume_today": 45000000,
    "commission_earned_today": 900000
  }
}
```

### 3.6 Transfert Inter-Agences/Inter-Pays
```json
{
  "id": 789,
  "cash_register_id": 1,
  "product_id": 3,
  "type": "deposit|withdrawal",
  "phone_number": "+223123456789",
  "full_name": "Amadou Traore",
  "amount": 50000,
  "creator_id": 1,
  "created_at": "2024-01-15T14:30:00Z",
  "status": "pending|validated|canceled",
  "validator_id": null,
  "validated_at": null,
  "country_id": 1,
  "sent_from_agency_id": 2,
  "destination_country_id": 1,
  "cross_border": false,
  "exchange_rate": null,
  "fees": 1000,
  "country": {
    "id": 1,
    "name": "Mali"
  },
  "product": {
    "id": 3,
    "name": "Transfert Western Union",
    "partner_name": "Western Union"
  }
}
```

## 5. Gestion des Erreurs

### 5.1 Format Standard des Erreurs Multi-Tenant
```json
{
  "error": {
    "code": "INSUFFICIENT_BALANCE",
    "message": "Solde insuffisant pour cette opération",
    "details": {
      "current_balance": 50000,
      "requested_amount": 100000,
      "cash_register_id": 1,
      "country_id": 1,
      "partner_id": 2
    },
    "timestamp": "2024-01-15T10:30:00Z",
    "trace_id": "trace_123456789",
    "user_context": {
      "user_id": 1,
      "country_name": "Mali",
      "partner_name": "Orange Money"
    }
  }
}
```

### 5.2 Codes d'Erreur Métier Multi-Tenant
- `INSUFFICIENT_BALANCE` - Solde insuffisant
- `CASH_REGISTER_CLOSED` - Caisse fermée
- `OPERATION_ALREADY_VALIDATED` - Opération déjà validée
- `DAILY_LIMIT_EXCEEDED` - Limite journalière dépassée
- `UNAUTHORIZED_OPERATION` - Opération non autorisée
- `INVALID_2FA_CODE` - Code 2FA invalide
- `COUNTRY_ACCESS_DENIED` - Accès au pays refusé
- `PARTNER_ACCESS_DENIED` - Accès au partenaire refusé
- `PRODUCT_NOT_AUTHORIZED` - Produit non autorisé pour cette caisse
- `CROSS_BORDER_NOT_ALLOWED` - Transfert inter-pays non autorisé
- `AGENCY_CLOSURE_REQUIRED` - Clôture d'agence requise
- `RESERVE_INSUFFICIENT` - Réserve insuffisante
- `APPROVAL_REQUIRED` - Approbation requise
- `INVALID_COUNTRY_CONTEXT` - Contexte pays invalide
- `PARTNER_INACTIVE` - Partenaire inactif
- `PRODUCT_SUSPENDED` - Produit suspendu

## 6. Pagination et Filtrage

### 6.1 Pagination
```http
GET /api/v1/operations?page=1&per_page=50&sort=created_at&order=desc
```

```json
{
  "data": [...],
  "pagination": {
    "current_page": 1,
    "per_page": 50,
    "total": 1250,
    "total_pages": 25,
    "has_next": true,
    "has_prev": false
  }
}
```

### 6.2 Filtrage
```http
GET /api/v1/operations?date_from=2024-01-01&date_to=2024-01-31&agency_id=1&type=deposit&status=validated
```

## 7. WebSockets pour Temps Réel

### 7.1 Événements en Temps Réel
```javascript
// Connexion WebSocket
const ws = new WebSocket('wss://api.iob.com/ws');

// Événements
ws.on('operation_created', (data) => {
  console.log('Nouvelle opération:', data);
});

ws.on('balance_updated', (data) => {
  console.log('Solde mis à jour:', data);
});

ws.on('cash_register_closed', (data) => {
  console.log('Caisse fermée:', data);
});
```

## 8. Implémentation Technique

### 8.1 Structure des Contrôleurs API
```php
<?php
namespace Api\V1\Controllers;

class OperationsController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $operations = $this->operationService->getOperations(
            $request->getFilters(),
            $request->getPagination()
        );
        
        return $this->success($operations);
    }
    
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'type' => 'required|in:deposit,withdrawal,transfer',
            'amount' => 'required|numeric|min:1',
            'cash_register_id' => 'required|exists:cash_registers,id'
        ]);
        
        $operation = $this->operationService->createOperation(
            $request->validated()
        );
        
        return $this->created($operation);
    }
}
```

### 8.2 Services Métier
```php
<?php
namespace Api\Services;

class OperationService
{
    public function createOperation(array $data): Operation
    {
        DB::beginTransaction();
        
        try {
            // Vérifier le solde
            $this->validateBalance($data['cash_register_id'], $data['amount']);
            
            // Créer l'opération
            $operation = Operation::create($data);
            
            // Mettre à jour le solde
            $this->updateCashRegisterBalance($data['cash_register_id'], $data['amount']);
            
            // Déclencher les événements
            event(new OperationCreated($operation));
            
            DB::commit();
            return $operation;
            
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
```

### 8.3 Middleware d'Authentification Multi-Tenant
```php
<?php
namespace Api\Middleware;

class JwtAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['error' => 'Token required'], 401);
        }
        
        try {
            $payload = JWT::decode($token, config('jwt.secret'), ['HS256']);
            $user = User::find($payload->sub);
            
            // Validation du contexte multi-tenant
            $this->validateTenantContext($user, $payload);
            
            // Injection du contexte dans la requête
            $request->setUser($user);
            $request->setTenantContext([
                'country_id' => $payload->country_id,
                'partner_id' => $payload->partner_id ?? null,
                'permissions' => $payload->permissions,
                'cash_register_ids' => $payload->cash_register_ids
            ]);
            
        } catch (Exception $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
        
        return $next($request);
    }
    
    private function validateTenantContext($user, $payload)
    {
        // Vérifier que l'utilisateur a accès au pays
        if ($user->RefPays !== $payload->country_id) {
            throw new UnauthorizedException('Country access denied');
        }
        
        // Vérifier l'accès au partenaire si spécifié
        if ($payload->partner_id && $user->RefBanque !== $payload->partner_id) {
            throw new UnauthorizedException('Partner access denied');
        }
    }
}
```

## 9. Tests et Documentation

### 9.1 Tests API
```php
<?php
class OperationApiTest extends TestCase
{
    public function test_create_operation()
    {
        $user = $this->createUser(['role' => 'cashier']);
        $cashRegister = $this->createCashRegister(['balance' => 1000000]);
        
        $response = $this->actingAs($user)
            ->postJson('/api/v1/operations', [
                'type' => 'deposit',
                'amount' => 50000,
                'cash_register_id' => $cashRegister->id,
                'client_name' => 'John Doe'
            ]);
        
        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'type', 'amount', 'status']);
    }
}
```

### 9.2 Documentation OpenAPI
```yaml
openapi: 3.0.0
info:
  title: IOB Cash Management API
  version: 1.0.0
  description: API pour le système de gestion de caisse IOB

paths:
  /api/v1/operations:
    post:
      summary: Créer une nouvelle opération
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/CreateOperation'
      responses:
        '201':
          description: Opération créée avec succès
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/Operation'
```

## 10. Déploiement et Monitoring

### 10.1 Configuration Docker
```dockerfile
FROM php:8.1-fpm

# Installation des extensions PHP
RUN docker-php-ext-install pdo pdo_mysql

# Configuration Nginx
COPY nginx.conf /etc/nginx/nginx.conf

# Variables d'environnement
ENV DB_HOST=mysql
ENV DB_DATABASE=iob
ENV JWT_SECRET=your-secret-key
```

### 10.2 Monitoring et Logs
```php
<?php
// Middleware de logging
class ApiLoggingMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);
        
        $response = $next($request);
        
        $duration = microtime(true) - $start;
        
        Log::info('API Request', [
            'method' => $request->method(),
            'url' => $request->url(),
            'user_id' => $request->user()?->id,
            'status' => $response->status(),
            'duration' => $duration
        ]);
        
        return $response;
    }
}
```

## 11. Migration de l'Existant

### 11.1 Stratégie de Migration
1. **Phase 1** : Créer l'API en parallèle de l'existant
2. **Phase 2** : Migrer module par module
3. **Phase 3** : Décommissionner l'ancienne interface

### 11.2 Compatibilité Descendante
```php
<?php
// Adapter pour l'ancien système
class LegacyOperationAdapter
{
    public function adaptToApi(array $legacyData): array
    {
        return [
            'id' => $legacyData['RefOperations'],
            'type' => $this->mapOperationType($legacyData['RefType']),
            'amount' => $legacyData['MontantVersement'],
            'client_name' => $legacyData['NameClient'],
            'account_number' => $legacyData['NumCompte']
        ];
    }
}
```

## 12. Recommandation d'Architecture Expert

### 12.1 Vision Architecturale Globale

**IOB doit évoluer vers une architecture de microservices multi-tenant avec isolation complète des données par pays/partenaire, supportant une croissance internationale massive.**

### 12.2 Stack Technologique Recommandée

#### **Backend API (Microservices)**
```
┌─────────────────────────────────────────────────────────────┐
│                    API Gateway (Kong/Traefik)              │
├─────────────────────────────────────────────────────────────┤
│  Auth Service    │  Country Service  │  Partner Service    │
│  (Node.js/Go)    │  (Node.js/PHP)    │  (Node.js/PHP)     │
├─────────────────────────────────────────────────────────────┤
│  Operations      │  Cash Registers   │  Analytics Service  │
│  Service         │  Service          │  (Python/Node.js)  │
│  (PHP/Node.js)   │  (PHP/Node.js)    │                     │
├─────────────────────────────────────────────────────────────┤
│  Remittance      │  Journal Service  │  Notification       │
│  Service         │  (PHP/Node.js)    │  Service (Node.js)  │
│  (PHP/Node.js)   │                   │                     │
└─────────────────────────────────────────────────────────────┘
```

#### **Technologies Principales**
- **API Gateway** : Kong ou Traefik (routing multi-tenant)
- **Backend Services** : Node.js + TypeScript (performance) ou PHP 8.3+ (continuité)
- **Base de Données** : MySQL 8.0+ avec partitioning par pays
- **Cache** : Redis Cluster (sessions, permissions, soldes)
- **Message Queue** : RabbitMQ ou Apache Kafka (événements)
- **Monitoring** : Prometheus + Grafana + ELK Stack

### 12.3 Architecture de Données Multi-Tenant

#### **Stratégie de Partitioning Recommandée**
```sql
-- Partitioning par pays pour isolation des données
CREATE TABLE TbleOperations (
    RefOperations INT AUTO_INCREMENT,
    RefPays INT NOT NULL,
    -- autres colonnes...
    PRIMARY KEY (RefOperations, RefPays)
) PARTITION BY HASH(RefPays) PARTITIONS 10;

-- Index composites pour performance multi-tenant
CREATE INDEX idx_operations_country_date 
ON TbleOperations(RefPays, Insert_Time, RefCaisse);
```

#### **Base de Données par Environnement**
- **Production** : MySQL Master-Slave par région géographique
- **Staging** : Réplique complète avec données anonymisées
- **Development** : MySQL local avec Docker

### 12.4 Architecture de Sécurité Multi-Niveau

#### **Authentification & Autorisation**
```typescript
// JWT avec contexte multi-tenant
interface JWTPayload {
  user_id: number;
  country_id: number;
  partner_id?: number;
  permissions: string[];
  cash_register_ids: number[];
  product_ids: number[];
  session_id: string;
  tenant_context: {
    country_code: string;
    partner_code?: string;
    timezone: string;
    currency: string;
  };
}
```

#### **Middleware de Sécurité**
```typescript
// Middleware d'isolation multi-tenant
class TenantIsolationMiddleware {
  async handle(request: Request, next: Function) {
    const token = this.extractToken(request);
    const context = this.validateTenantContext(token);
    
    // Injection automatique des filtres tenant
    request.tenantFilters = {
      country_id: context.country_id,
      partner_id: context.partner_id
    };
    
    return next(request);
  }
}
```

### 12.5 Architecture de Déploiement Cloud-Native

#### **Infrastructure Kubernetes**
```yaml
# Déploiement par pays avec isolation réseau
apiVersion: v1
kind: Namespace
metadata:
  name: iob-mali
  labels:
    country: "mali"
    tenant-isolation: "enabled"
---
apiVersion: apps/v1
kind: Deployment
metadata:
  name: operations-service
  namespace: iob-mali
spec:
  replicas: 3
  selector:
    matchLabels:
      app: operations-service
      country: mali
  template:
    spec:
      containers:
      - name: operations-api
        image: iob/operations-service:v2.0
        env:
        - name: TENANT_COUNTRY
          value: "mali"
        - name: DB_HOST
          value: "mysql-mali.internal"
```

#### **Architecture Multi-Région**
```
┌─────────────────────────────────────────────────────────────┐
│                     Global Load Balancer                   │
│                    (CloudFlare/AWS ALB)                    │
└─────────────────────┬───────────────────────────────────────┘
                      │
    ┌─────────────────┼─────────────────┐
    │                 │                 │
┌───▼────┐       ┌───▼────┐       ┌───▼────┐
│West    │       │Europe  │       │Africa  │
│Africa  │       │Region  │       │Region  │
│Region  │       │        │       │        │
│        │       │        │       │        │
│Mali    │       │France  │       │Senegal │
│Burkina │       │Belgium │       │Ivory   │
│Niger   │       │        │       │Coast   │
└────────┘       └────────┘       └────────┘
```

### 12.6 Patterns d'Architecture Recommandés

#### **1. Event Sourcing pour Audit**
```typescript
// Chaque opération génère des événements immuables
class OperationCreatedEvent {
  constructor(
    public readonly operationId: string,
    public readonly countryId: number,
    public readonly partnerId: number,
    public readonly amount: number,
    public readonly timestamp: Date,
    public readonly metadata: OperationMetadata
  ) {}
}

// Store d'événements partitionné par pays
class EventStore {
  async append(countryId: number, events: DomainEvent[]) {
    const partition = this.getPartition(countryId);
    return partition.append(events);
  }
}
```

#### **2. CQRS pour Performance**
```typescript
// Séparation Command/Query avec vues matérialisées
class OperationCommandHandler {
  async handle(command: CreateOperationCommand) {
    // Validation métier
    await this.validateBusinessRules(command);
    
    // Écriture dans le store d'événements
    const events = this.createEvents(command);
    await this.eventStore.append(command.countryId, events);
    
    // Mise à jour asynchrone des vues
    await this.eventBus.publish(events);
  }
}

class OperationQueryHandler {
  async getOperations(query: GetOperationsQuery) {
    // Lecture depuis les vues matérialisées optimisées
    return this.readModel.getOperations(query.filters);
  }
}
```

#### **3. Saga Pattern pour Transactions Distribuées**
```typescript
// Orchestration des transactions multi-services
class TransferSaga {
  async execute(transfer: CrossBorderTransfer) {
    const saga = new SagaTransaction();
    
    try {
      // Étape 1: Débiter le compte source
      await saga.addStep(
        () => this.debitSourceAccount(transfer),
        () => this.refundSourceAccount(transfer)
      );
      
      // Étape 2: Convertir la devise si nécessaire
      if (transfer.requiresCurrencyConversion) {
        await saga.addStep(
          () => this.convertCurrency(transfer),
          () => this.revertCurrencyConversion(transfer)
        );
      }
      
      // Étape 3: Créditer le compte destination
      await saga.addStep(
        () => this.creditDestinationAccount(transfer),
        () => this.debitDestinationAccount(transfer)
      );
      
      await saga.commit();
    } catch (error) {
      await saga.rollback();
      throw error;
    }
  }
}
```

### 12.7 Stratégie de Migration Progressive

#### **Phase 1 : API Gateway (2-3 mois)**
1. Déployer Kong/Traefik devant l'application existante
2. Implémenter l'authentification JWT multi-tenant
3. Ajouter les headers de contexte tenant
4. Monitoring et observabilité

#### **Phase 2 : Services Critiques (3-4 mois)**
1. Extraire le service d'authentification
2. Créer le service Operations (API)
3. Implémenter le service Cash Registers
4. Migration progressive des endpoints

#### **Phase 3 : Services Métier (4-6 mois)**
1. Service Analytics avec Event Sourcing
2. Service Remittance avec Saga Pattern
3. Service Notifications temps réel
4. Optimisation des performances

#### **Phase 4 : Finalisation (2-3 mois)**
1. Migration complète des données
2. Décommissionnement de l'ancien système
3. Optimisation et monitoring avancé
4. Formation des équipes

### 12.8 Métriques de Performance Cibles

#### **SLA Recommandés**
- **Disponibilité** : 99.9% (8.76h de downtime/an)
- **Latence API** : P95 < 200ms, P99 < 500ms
- **Throughput** : 10,000 req/sec par région
- **RPO** : 15 minutes (Recovery Point Objective)
- **RTO** : 4 heures (Recovery Time Objective)

#### **Monitoring KPIs**
```typescript
// Métriques métier par tenant
interface TenantMetrics {
  country_id: number;
  daily_operations_count: number;
  daily_volume: number;
  average_response_time: number;
  error_rate: number;
  active_users: number;
  cash_registers_online: number;
}
```

### 12.9 Sécurité et Compliance

#### **Standards de Sécurité**
- **Chiffrement** : TLS 1.3, AES-256 au repos
- **Authentification** : JWT + 2FA obligatoire
- **Audit** : Logs immutables avec signature
- **Backup** : 3-2-1 strategy (3 copies, 2 supports, 1 offsite)

#### **Compliance Bancaire**
- **PCI DSS** : Niveau 1 pour les données de paiement
- **GDPR** : Pour les données européennes
- **Audit Trail** : Traçabilité complète des opérations
- **Retention** : 7 ans minimum pour les données financières

### 12.10 Coûts et ROI Estimés

#### **Investissement Initial (12-18 mois)**
- **Développement** : 8-12 développeurs × 15 mois = 1.2M€
- **Infrastructure** : 50K€/an × 2 ans = 100K€
- **Formation & Migration** : 200K€
- **Total** : ~1.5M€

#### **ROI Attendu**
- **Réduction coûts opérationnels** : 40% (automatisation)
- **Augmentation capacité** : 300% (scalabilité)
- **Time-to-market nouveaux pays** : 80% plus rapide
- **ROI** : 250% sur 3 ans

## Conclusion

Cette architecture transformera IOB en une plateforme bancaire moderne, scalable et sécurisée, capable de supporter une croissance internationale massive tout en maintenant l'isolation complète des données par pays et partenaire.

**L'architecture proposée garantit :**
✅ **Scalabilité illimitée** pour nouveaux pays/partenaires  
✅ **Sécurité bancaire** de niveau enterprise  
✅ **Performance** sub-seconde même à grande échelle  
✅ **Compliance** internationale automatique  
✅ **Coûts optimisés** avec infrastructure cloud-native  

Cette architecture API moderne permettra de transformer votre application IOB en un système distribué, scalable et facilement intégrable avec d'autres systèmes bancaires.

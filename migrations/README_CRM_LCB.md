# Guide de Déploiement - Modules CRM et LCB-FT

## Vue d'ensemble

Ce guide explique comment déployer les nouveaux modules :
- **Module CRM** : Gestion des clients, segmentation, fidélisation
- **Module LCB-FT** : Anti-blanchiment, alertes, conformité CENTIF Mali

---

## Pré-requis

- PHP 7.4+
- MySQL 5.7+
- Accès SSH au serveur
- Droits d'exécution sur les scripts PHP

---

## Étapes de déploiement

### 1. Sauvegarde de la base de données

```bash
mysqldump -u root -p iob > backup_iob_$(date +%Y%m%d_%H%M%S).sql
```

### 2. Exécution de la migration SQL

```bash
mysql -u root -p iob < migrations/003_lcb_crm.sql
```

Cette migration crée :
- `TbleClients` - Profils clients
- `TbleClientStats` - Statistiques mensuelles
- `TbleAlertesLCB` - Alertes anti-blanchiment
- `TbleAlertesCRM` - Alertes CRM
- `TbleSeuilsLCB` - Seuils configurables
- Vues SQL optimisées

### 3. Déploiement des fichiers PHP

Copier les fichiers suivants sur le serveur :

**Managers :**
```
Library/Models/ClientManager.class.php
Library/Models/ClientManagerPDO.class.php
Library/Models/LCBManager.class.php
Library/Models/LCBManagerPDO.class.php
```

**Contrôleurs :**
```
Applications/App/Modules/CRM/CRMController.class.php
Applications/App/Modules/LCB/LCBController.class.php
```

**Vues CRM :**
```
Applications/App/Modules/CRM/Views/index.php
Applications/App/Modules/CRM/Views/client.php
Applications/App/Modules/CRM/Views/inactifs.php
Applications/App/Modules/CRM/Views/top.php
Applications/App/Modules/CRM/Views/recherche.php
Applications/App/Modules/CRM/Views/segment.php
```

**Vues LCB :**
```
Applications/App/Modules/LCB/Views/index.php
Applications/App/Modules/LCB/Views/alertes.php
Applications/App/Modules/LCB/Views/detail.php
Applications/App/Modules/LCB/Views/seuils.php
Applications/App/Modules/LCB/Views/surveilles.php
```

**Fichiers modifiés :**
```
Applications/App/Config/routes.xml
Applications/App/Templates/layout.php
Library/Models/BielletageManagerPDO.class.php
```

**Scripts :**
```
scripts/populate_clients.php
```

### 4. Peuplement initial des clients

Ce script analyse l'historique des opérations pour créer les profils clients :

```bash
cd /chemin/vers/iob
php scripts/populate_clients.php
```

Sortie attendue :
```
=================================================
  IOB - Peuplement Initial TbleClients + Stats   
=================================================

[OK] Connexion base de donnees reussie
[OK] Tables CRM/LCB existent
...
[TERMINE] Peuplement initial effectue avec succes!
```

### 5. Vérification

1. Accéder à `/crm/index` - Dashboard CRM
2. Accéder à `/lcb/index` - Dashboard LCB-FT
3. Vérifier que les menus apparaissent dans la sidebar

---

## Droits d'accès

| Module | Rôles autorisés |
|--------|-----------------|
| CRM | ChefCaisse, Head, admin, superadmin |
| LCB-FT | admin, superadmin, Controleur |

Pour ajouter le rôle "Controleur" à un utilisateur, modifiez le champ `statut` dans `TbleUsers`.

---

## Seuils par défaut

Les seuils LCB sont configurables via `/lcb/seuils` (admin uniquement) :

| Code | Description | Valeur par défaut |
|------|-------------|-------------------|
| LCB_SEUIL_DECLARATION | Seuil CENTIF | 15 000 000 FCFA |
| LCB_CUMUL_JOURNALIER | Cumul max par jour | 15 000 000 FCFA |
| LCB_CUMUL_HEBDO | Cumul max par semaine | 25 000 000 FCFA |
| CRM_SEUIL_VIP_VOLUME | Volume pour VIP | 20 000 000 FCFA |
| CRM_JOURS_DORMANT | Jours pour Dormant | 30 jours |
| CRM_JOURS_PERDU | Jours pour Perdu | 90 jours |

---

## Types d'alertes LCB-FT

| Code | Description | Sévérité |
|------|-------------|----------|
| AML-001 | Transaction ≥ seuil CENTIF | CRITIQUE |
| AML-002 | Cumul journalier élevé | HAUTE |
| AML-003 | Cumul hebdomadaire élevé | HAUTE |
| AML-004 | Suspicion de fractionnement | CRITIQUE |
| AML-005 | Dépôt suivi de retrait rapide | HAUTE |
| AML-006 | Utilisation multi-agences | MOYENNE |

---

## Segmentation clients CRM

| Segment | Critères |
|---------|----------|
| VIP | Volume > 20M/mois OU > 50 ops/mois |
| REGULIER | 4+ opérations par mois |
| OCCASIONNEL | 1-4 opérations par mois |
| DORMANT | Inactif 30-90 jours |
| PERDU | Inactif > 90 jours |
| NOUVEAU | ≤ 2 opérations, actif |

---

## Maintenance

### Recalculer les segments (hebdomadaire recommandé)

Via l'interface admin : `/crm/recalculer`

Ou via CLI :
```bash
php -r "
include 'Library/DBFactory.class.php';
\$pdo = \Library\DBFactory::getMysqlConnexion();
\$mgr = new \Library\Models\ClientManagerPDO(\$pdo);
echo \$mgr->recalculerTousSegments() . ' clients recalculés';
"
```

### Surveiller les alertes LCB

Il est recommandé de consulter `/lcb/index` quotidiennement pour traiter les alertes.

---

## Dépannage

### Les modules n'apparaissent pas dans le menu
- Vérifiez que `layout.php` a été mis à jour
- Vérifiez le statut de l'utilisateur connecté

### Erreur "Table doesn't exist"
- La migration `003_lcb_crm.sql` n'a pas été exécutée

### Aucun client dans le CRM
- Le script `populate_clients.php` n'a pas été exécuté

### Les alertes LCB ne se génèrent pas
- Vérifiez que `BielletageManagerPDO.class.php` a été mis à jour
- Les alertes ne sont générées que pour les nouvelles opérations (RefType 1 ou 2)


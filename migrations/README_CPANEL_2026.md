# Guide de Déploiement cPanel - Gestion Fonds de Roulement 2026

## Résumé des Fonctionnalités

Ce déploiement ajoute :

1. **Gestion du Fonds de Roulement** par agence (Espèces + Omni)
2. **Opérations en attente** (non vérifiées = pas encore passées sur Omni)
3. **Blocage des opérations** si la veille n'est pas clôturée
4. **Interface d'initialisation** pour nouvelle année

---

## Étape 1 : Sauvegarde Base de Données

**IMPORTANT : Faites une sauvegarde avant toute modification !**

### Via phpMyAdmin (cPanel) :

1. Connectez-vous à cPanel
2. Ouvrez phpMyAdmin
3. Sélectionnez la base `iob` (ou `cp1146011p43_iob`)
4. Cliquez sur **Exporter**
5. Format : SQL
6. Cliquez sur **Exécuter** et sauvegardez le fichier

---

## Étape 2 : Exécuter la Migration SQL

### Via phpMyAdmin :

1. Sélectionnez la base `iob`
2. Cliquez sur l'onglet **SQL**
3. Copiez le contenu du fichier `DEPLOY_CPANEL_2026.sql`
4. Collez dans la zone de texte
5. Cliquez sur **Exécuter**

### Alternative - Importer le fichier :

1. Cliquez sur l'onglet **Importer**
2. Sélectionnez le fichier `DEPLOY_CPANEL_2026.sql`
3. Cliquez sur **Exécuter**

---

## Étape 3 : Mettre à jour les fichiers PHP

### Fichiers modifiés à uploader via FTP/File Manager :

| Fichier                        | Chemin                                                               |
| ------------------------------ | -------------------------------------------------------------------- |
| routes.xml                     | `Applications/App/Config/routes.xml`                                 |
| JournalController.class.php    | `Applications/App/Modules/Journal/JournalController.class.php`       |
| JournalManagerPDO.class.php    | `Library/Models/JournalManagerPDO.class.php`                         |
| BielletageController.class.php | `Applications/App/Modules/Bielletage/BielletageController.class.php` |
| petitecaisse.php               | `Applications/App/Modules/Journal/Views/petitecaisse.php`            |
| noverified.php                 | `Applications/App/Modules/Journal/Views/noverified.php`              |
| PannelController.class.php     | `Applications/App/Modules/Pannel/PannelController.class.php`         |
| PannelManagerPDO.class.php     | `Library/Models/PannelManagerPDO.class.php`                          |
| fondsRoulement.php             | `Applications/App/Modules/Pannel/Views/fondsRoulement.php`           |
| layout.php                     | `Applications/App/Templates/layout.php`                              |

### Via cPanel File Manager :

1. Ouvrez File Manager dans cPanel
2. Naviguez vers `public_html/` (ou le dossier de l'application)
3. Uploadez/remplacez chaque fichier

### Via FTP (FileZilla, etc.) :

1. Connectez-vous au serveur FTP
2. Naviguez vers le dossier de l'application
3. Uploadez les fichiers en écrasant les anciens

---

## Étape 4 : Vérification

### Tester les nouvelles fonctionnalités :

1. **Menu Fonds de Roulement**

   - Connectez-vous en tant qu'admin
   - Menu latéral → "Fonds de Roulement"
   - Vérifiez que la page s'affiche correctement

2. **Initialiser une agence**

   - Définissez un plafond (ex: 50 000 000)
   - Cliquez sur "Initialiser"
   - Saisissez Espèces et Omni

3. **Petite Caisse**

   - Allez sur `/Journal/petite_caisse`
   - Vérifiez les nouvelles colonnes :
     - Fonds Roulement
     - Plafond
     - Excédent
     - En Attente

4. **Opérations non vérifiées**
   - Allez sur `/Journal/noverified`
   - Seules les opérations 2026+ s'affichent

---

## Structure de la Migration SQL

### Nouvelles colonnes TbleAgency :

```sql
PlafondFondsRoulement DECIMAL(15,2) -- Plafond total autorisé
SoldeOmniReference DECIMAL(15,2)    -- Solde Omni de référence
DateInitialisation DATE             -- Date dernière initialisation
```

### Nouvelle table TbleFondsRoulement :

```sql
RefFonds INT PRIMARY KEY
RefAgency INT                       -- Référence agence
DateFonds DATE                      -- Date du mouvement
SoldeEspeces DECIMAL(15,2)          -- Solde espèces
SoldeOmni DECIMAL(15,2)             -- Solde Omni
FondsTotal DECIMAL(15,2)            -- Calculé automatiquement
TypeMouvement ENUM(...)             -- INITIALISATION, CLOTURE, AJUSTEMENT
RefUsers INT                        -- Utilisateur ayant fait l'action
Commentaire TEXT                    -- Notes
DateCreation DATETIME               -- Horodatage
```

---

## Nouvelles Routes

| URL                       | Description                                |
| ------------------------- | ------------------------------------------ |
| `/Pannel/fonds_roulement` | Interface de gestion fonds de roulement    |
| `/Journal/noverified`     | Liste des opérations non vérifiées (2026+) |

---

## Logique Métier

### Fonds de Roulement

```
Fonds de Roulement = Espèces + Solde Omni
Doit rester constant (ex: 50M)

Versement client : +Espèces, -Omni
Retrait client   : -Espèces, +Omni
```

### Opérations en Attente

```
Opération créée → Validate = 1 (Non vérifiée = En attente Omni)
Control vérifie → Validate = 2 (Vérifiée = Passée sur Omni)
```

### Blocage si veille non clôturée

- Les caissiers ne peuvent pas passer d'opérations si la journée précédente n'est pas clôturée
- Exception : Admins et opérations antidatées

---

## Dépannage

### Erreur "Table doesn't exist"

→ La migration SQL n'a pas été exécutée. Réexécutez `DEPLOY_CPANEL_2026.sql`

### Erreur "Vue non trouvée"

→ Le fichier `fondsRoulement.php` n'est pas au bon endroit. Vérifiez qu'il est dans `Applications/App/Modules/Pannel/Views/`

### Colonnes manquantes

→ Exécutez cette requête pour vérifier :

```sql
DESCRIBE TbleAgency;
DESCRIBE TbleFondsRoulement;
```

### Les opérations anciennes s'affichent

→ Le filtre 2026+ n'est pas appliqué. Vérifiez que `JournalManagerPDO.class.php` est à jour.

---

## Support

En cas de problème, vérifiez :

1. Les logs d'erreur PHP dans cPanel → Error Log
2. Que tous les fichiers ont été uploadés
3. Que la migration SQL s'est exécutée sans erreur

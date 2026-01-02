# Guide de Deploiement - Corrections IOB E-Banking

## Resume des Corrections

Ce deploiement corrige les problemes suivants :

1. **Performance** : Vue Petite Caisse optimisee (240+ requetes → 3 requetes)
2. **Securite** : Injections SQL corrigees, controle d'acces renforce
3. **Logique Metier** : Verification cloture J-1, recalcul cascade, fermeture auto CRON
4. **UX** : Icone eye sur le mot de passe login

---

## Etape 1 : Backup Base de Donnees

```bash
mysqldump -u root -p iob > backup_iob_$(date +%Y%m%d_%H%M%S).sql
```

---

## Etape 2 : Executer la Migration SQL

```bash
mysql -u root -p iob < migrations/001_add_indexes_and_autoclose.sql
```

**Contenu de la migration :**

- Index sur TbleOperations (5 index)
- Index sur TbleRemittance (3 index)
- Index sur TbleSolde (1 index)
- Index sur TbleCompte (1 index)
- Colonne `AutoClose` sur TbleSolde et TbleCompte

**Temps estime** : 2-5 minutes selon la taille de la base

---

## Etape 3 : Deployer les Fichiers PHP Modifies

Fichiers modifies :

| Fichier                                                        | Description                                    |
| -------------------------------------------------------------- | ---------------------------------------------- |
| `Applications/App/Modules/Connexion/Views/index.php`           | Icone eye password                             |
| `Applications/App/Modules/Journal/JournalController.class.php` | Petite Caisse optimisee, restriction admin     |
| `Library/Models/JournalManagerPDO.class.php`                   | Injections SQL, recalcul cascade, optimisation |
| `Library/Models/BielletageManagerPDO.class.php`                | Verification J-1, transactions, antidate       |
| `Library/Models/RemittanceManagerPDO.class.php`                | Injection SQL                                  |
| `Web/config/caissecron.php`                                    | Script CRON fermeture auto                     |

---

## Etape 4 : Configurer le CRON

Ajouter au crontab du serveur :

```bash
# Ouvrir le crontab
crontab -e

# Ajouter cette ligne (execution a minuit chaque jour)
0 0 * * * /usr/bin/php /chemin/complet/vers/Web/config/caissecron.php >> /var/log/iob-cron.log 2>&1
```

**Verification** :

```bash
# Tester le script manuellement
php /chemin/vers/Web/config/caissecron.php

# Verifier les logs
tail -f /var/log/iob-cron.log
```

---

## Etape 5 : Creer le Dossier Logs

```bash
mkdir -p /chemin/vers/iob/logs
chmod 755 /chemin/vers/iob/logs
```

---

## Etape 6 : Ajouter Permission Antidate (Optionnel)

Pour autoriser un utilisateur a antidater :

```sql
INSERT INTO permissions (RefUsers, access) VALUES (ID_UTILISATEUR, 'antidate');
```

---

## Tests Post-Deploiement

### 1. Test Icone Eye Password

- Aller sur la page de login
- Verifier que l'icone oeil apparait
- Cliquer pour afficher/masquer le mot de passe

### 2. Test Performance Petite Caisse

- Aller sur Journal > Petite Caisse
- Mesurer le temps de chargement (doit etre < 2 secondes)

### 3. Test Verification Cloture J-1

- Essayer de faire une operation sans avoir cloture la veille
- Un message d'erreur doit apparaitre

### 4. Test Annulation Arrete (Admin)

- Se connecter en tant qu'admin
- Annuler un arrete sur une journee anterieure
- Verifier que les arretes suivants sont recalcules

### 5. Test CRON

- Lancer manuellement le script CRON
- Verifier les logs dans `/logs/cron_YYYY-MM-DD.log`

---

## Rollback

En cas de probleme :

```bash
# Restaurer la base de donnees
mysql -u root -p iob < backup_iob_YYYYMMDD_HHMMSS.sql

# Restaurer les fichiers PHP depuis le backup git
git checkout HEAD -- Applications/ Library/
```

---

## Contact Support

En cas de probleme lors du deploiement, contacter l'equipe de developpement.

-- ============================================================
-- Migration IOB E-Banking - Performance et Logique Metier
-- Date: 2026-01-01
-- Description: Ajout des index pour performance + colonne AutoClose
-- ============================================================

-- ============================================================
-- PARTIE 1: INDEX PERFORMANCE (TbleOperations - 150k+ lignes)
-- ============================================================

-- Index composite pour les requetes par caisse et date d'approbation
-- Utilise dans: GetOperations, SommeVersement, SommeRetrait, etc.
ALTER TABLE TbleOperations
  ADD INDEX idx_caisse_approve_date (RefCaisse, Approve2_Time),
  ADD INDEX idx_type_approve_date (RefType, Approve2_Time),
  ADD INDEX idx_reset_id (Reset_Id),
  ADD INDEX idx_insert_time (Insert_Time),
  ADD INDEX idx_caisse_type_date (RefCaisse, RefType, Approve2_Time);

-- ============================================================
-- PARTIE 2: INDEX TbleRemittance
-- ============================================================

ALTER TABLE TbleRemittance
  ADD INDEX idx_caisse_insert_time (RefCaisse, Insert_time),
  ADD INDEX idx_reset_id (Reset_Id),
  ADD INDEX idx_type_insert_time (RefType, Insert_time);

-- ============================================================
-- PARTIE 3: INDEX TbleSolde (Fermeture Caisse)
-- ============================================================

ALTER TABLE TbleSolde
  ADD INDEX idx_caisse_date (RefCaisse, DateSolde);

-- ============================================================
-- PARTIE 4: INDEX TbleCompte (Fermeture Agence/Reserve)
-- ============================================================

ALTER TABLE TbleCompte
  ADD INDEX idx_agency_date (RefAgency, DateSolde);

-- ============================================================
-- PARTIE 5: Colonne AutoClose pour fermeture automatique CRON
-- ============================================================

-- 0 = Fermeture manuelle par le caissier
-- 1 = Fermeture automatique par CRON a minuit
ALTER TABLE TbleSolde
  ADD COLUMN AutoClose TINYINT(1) DEFAULT 0 AFTER RefUsers;

-- Meme colonne pour TbleCompte (reserve agence)
ALTER TABLE TbleCompte
  ADD COLUMN AutoClose TINYINT(1) DEFAULT 0 AFTER RefUsers;

-- ============================================================
-- PARTIE 6: Index supplementaires pour les vues
-- ============================================================

-- Index pour TbleBilletage (utilise dans les rapports)
ALTER TABLE TbleBilletage
  ADD INDEX idx_ref_operations (RefOperations);

-- Index pour TbleChmod (permissions utilisateurs)
ALTER TABLE TbleChmod
  ADD INDEX idx_ref_users (RefUsers),
  ADD INDEX idx_ref_caisse (RefCaisse);

-- ============================================================
-- VERIFICATION
-- ============================================================

-- Afficher les index crees
SHOW INDEX FROM TbleOperations;
SHOW INDEX FROM TbleRemittance;
SHOW INDEX FROM TbleSolde;
SHOW INDEX FROM TbleCompte;

-- ============================================================
-- PARTIE 7: Table de log pour reouverture de caisse
-- ============================================================

-- Table pour tracer les reouvertures de caisse (audit)
CREATE TABLE IF NOT EXISTS TbleReouvertureCaisse (
    RefReouverture INT AUTO_INCREMENT PRIMARY KEY,
    RefCaisse INT NOT NULL,
    RefSolde INT NOT NULL COMMENT 'Reference de la fermeture supprimee',
    RefUsers INT NOT NULL COMMENT 'Utilisateur qui a rouvert',
    DateReouverture DATETIME DEFAULT CURRENT_TIMESTAMP,
    Motif VARCHAR(255) DEFAULT NULL,
    SoldeAnnule DECIMAL(15,2) NOT NULL COMMENT 'Solde de la fermeture annulee',
    INDEX idx_caisse_date (RefCaisse, DateReouverture),
    INDEX idx_users (RefUsers)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================================
-- NOTES D'EXECUTION
-- ============================================================
-- 1. Executer ce script pendant une periode de faible activite
-- 2. La creation d'index sur TbleOperations peut prendre quelques minutes
-- 3. Faire un backup avant execution: mysqldump -u root -p iob > backup_avant_migration.sql


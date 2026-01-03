-- ============================================================
-- MIGRATION COMPLETE IOB E-BANKING - DEPLOIEMENT CPANEL
-- Date: Janvier 2026
-- Description: Toutes les migrations pour le systeme de gestion
--              du fonds de roulement et operations en attente
-- ============================================================
-- 
-- INSTRUCTIONS CPANEL:
-- 1. Connectez-vous a phpMyAdmin via cPanel
-- 2. Selectionnez la base de donnees "iob"
-- 3. Allez dans l'onglet "Importer" ou "SQL"
-- 4. Copiez-collez ce script OU importez ce fichier
-- 5. Cliquez sur "Executer"
--
-- ATTENTION: Faites une sauvegarde de votre base avant!
-- ============================================================

-- ============================================================
-- PARTIE 1: COLONNES TBLEAGENCY POUR FONDS DE ROULEMENT
-- ============================================================

-- Verifier et ajouter PlafondFondsRoulement si non existant
SET @exist_plafond := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'TbleAgency' AND COLUMN_NAME = 'PlafondFondsRoulement');

SET @sql_plafond := IF(@exist_plafond = 0, 
    'ALTER TABLE TbleAgency ADD COLUMN PlafondFondsRoulement DECIMAL(15,2) DEFAULT 0 COMMENT ''Plafond total Especes + Omni''',
    'SELECT ''Colonne PlafondFondsRoulement existe deja''');
PREPARE stmt_plafond FROM @sql_plafond;
EXECUTE stmt_plafond;
DEALLOCATE PREPARE stmt_plafond;

-- Verifier et ajouter SoldeOmniReference si non existant
SET @exist_omni := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'TbleAgency' AND COLUMN_NAME = 'SoldeOmniReference');

SET @sql_omni := IF(@exist_omni = 0, 
    'ALTER TABLE TbleAgency ADD COLUMN SoldeOmniReference DECIMAL(15,2) DEFAULT 0 COMMENT ''Solde Omni de reference pour calculs''',
    'SELECT ''Colonne SoldeOmniReference existe deja''');
PREPARE stmt_omni FROM @sql_omni;
EXECUTE stmt_omni;
DEALLOCATE PREPARE stmt_omni;

-- Verifier et ajouter DateInitialisation si non existant
SET @exist_date := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'TbleAgency' AND COLUMN_NAME = 'DateInitialisation');

SET @sql_date := IF(@exist_date = 0, 
    'ALTER TABLE TbleAgency ADD COLUMN DateInitialisation DATE DEFAULT NULL COMMENT ''Date de derniere initialisation''',
    'SELECT ''Colonne DateInitialisation existe deja''');
PREPARE stmt_date FROM @sql_date;
EXECUTE stmt_date;
DEALLOCATE PREPARE stmt_date;

-- ============================================================
-- PARTIE 2: TABLE TBLEFONDSROULEMENT
-- ============================================================

CREATE TABLE IF NOT EXISTS TbleFondsRoulement (
    RefFonds INT AUTO_INCREMENT PRIMARY KEY,
    RefAgency INT NOT NULL,
    DateFonds DATE NOT NULL,
    SoldeEspeces DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Solde especes physique',
    SoldeOmni DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Solde Omni (E-banking ECOBANK)',
    FondsTotal DECIMAL(15,2) GENERATED ALWAYS AS (SoldeEspeces + SoldeOmni) STORED COMMENT 'Total calcule automatiquement',
    TypeMouvement ENUM('INITIALISATION', 'CLOTURE', 'AJUSTEMENT') NOT NULL,
    RefUsers INT NOT NULL,
    Commentaire TEXT DEFAULT NULL,
    DateCreation DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_date (DateFonds),
    INDEX idx_agency (RefAgency),
    UNIQUE KEY idx_agency_date_type (RefAgency, DateFonds, TypeMouvement)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- ============================================================
-- PARTIE 3: INDEX POUR PERFORMANCE
-- ============================================================

-- Index sur PlafondFondsRoulement (ignore si existe)
-- Note: MariaDB/MySQL ignore les erreurs de cle dupliquee avec IF NOT EXISTS
CREATE INDEX idx_plafond_fonds ON TbleAgency(PlafondFondsRoulement);

-- ============================================================
-- VERIFICATION
-- ============================================================

-- Afficher les colonnes ajoutees
SELECT 'Colonnes TbleAgency:' AS Info;
SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'TbleAgency' 
AND COLUMN_NAME IN ('PlafondFondsRoulement', 'SoldeOmniReference', 'DateInitialisation');

-- Afficher la structure de TbleFondsRoulement
SELECT 'Structure TbleFondsRoulement:' AS Info;
DESCRIBE TbleFondsRoulement;

-- ============================================================
-- FIN DE LA MIGRATION
-- ============================================================
SELECT '✓ Migration terminee avec succes!' AS Resultat;


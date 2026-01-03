-- ============================================================
-- Migration: Gestion Fonds de Roulement
-- Date: 2026-01-XX
-- Description: Ajout du plafond de fonds de roulement par agence
--              et table de suivi des initialisations
-- ============================================================

-- Ajouter les colonnes de configuration par agence
ALTER TABLE TbleAgency 
  ADD COLUMN PlafondFondsRoulement DECIMAL(15,2) DEFAULT 0 COMMENT 'Plafond total Especes + Omni',
  ADD COLUMN SoldeOmniReference DECIMAL(15,2) DEFAULT 0 COMMENT 'Solde Omni de reference pour calculs',
  ADD COLUMN DateInitialisation DATE DEFAULT NULL COMMENT 'Date de derniere initialisation';

-- Table pour suivre l'evolution du fonds de roulement
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
    
    FOREIGN KEY (RefAgency) REFERENCES TbleAgency(RefAgency) ON DELETE CASCADE,
    FOREIGN KEY (RefUsers) REFERENCES TbleUsers(RefUsers) ON DELETE RESTRICT,
    
    UNIQUE KEY idx_agency_date_type (RefAgency, DateFonds, TypeMouvement),
    INDEX idx_date (DateFonds),
    INDEX idx_agency (RefAgency)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Index pour performance sur TbleAgency
CREATE INDEX IF NOT EXISTS idx_plafond_fonds ON TbleAgency(PlafondFondsRoulement);

-- ============================================================
-- NOTES
-- ============================================================
-- 1. PlafondFondsRoulement: Montant total autorise (Especes + Omni)
-- 2. SoldeOmniReference: Solde Omni de reference pour les calculs
-- 3. TbleFondsRoulement: Historique des mouvements de fonds
-- 4. TypeMouvement:
--    - INITIALISATION: Initialisation nouvelle annee/periode
--    - CLOTURE: Cloture quotidienne avec soldes reels
--    - AJUSTEMENT: Ajustement manuel si necessaire


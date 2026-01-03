-- ============================================================
-- Migration IOB E-Banking - Stats Temps Réel
-- Date: 2026-01-03
-- Description: Tables de statistiques pré-calculées pour optimisation majeure
-- ============================================================

-- ============================================================
-- PARTIE 1: TABLE STATS OPERATIONS JOURNALIERES
-- ============================================================

CREATE TABLE IF NOT EXISTS TbleStatsOperationsJournalieres (
    RefStat INT AUTO_INCREMENT PRIMARY KEY,
    DateStat DATE NOT NULL,
    RefCaisse INT NOT NULL,
    RefAgency INT NOT NULL,
    RefType INT NOT NULL,
    
    -- Agrégats d'opérations
    NbOperations INT DEFAULT 0,
    TotalMontant DECIMAL(15,2) DEFAULT 0,
    MaxMontant DECIMAL(15,2) DEFAULT 0,
    MinMontant DECIMAL(15,2) DEFAULT 0,
    
    -- Metadata
    DateMAJ DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY idx_unique (DateStat, RefCaisse, RefType),
    INDEX idx_date (DateStat),
    INDEX idx_caisse (RefCaisse),
    INDEX idx_agence (RefAgency),
    INDEX idx_type (RefType)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Statistiques pré-calculées temps réel';

-- ============================================================
-- PARTIE 2: TABLE STATS REMITTANCE JOURNALIERES
-- ============================================================

CREATE TABLE IF NOT EXISTS TbleStatsRemittanceJournalieres (
    RefStat INT AUTO_INCREMENT PRIMARY KEY,
    DateStat DATE NOT NULL,
    RefCaisse INT NOT NULL,
    RefAgency INT NOT NULL,
    RefProduit INT NOT NULL,
    RefType INT NOT NULL,
    
    -- Agrégats de remittance
    NbOperations INT DEFAULT 0,
    TotalMontant DECIMAL(15,2) DEFAULT 0,
    
    -- Metadata
    DateMAJ DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY idx_unique (DateStat, RefCaisse, RefProduit, RefType),
    INDEX idx_date (DateStat),
    INDEX idx_agence (RefAgency),
    INDEX idx_produit (RefProduit)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stats remittance temps réel';

-- ============================================================
-- PARTIE 3: TABLE STATS CAISSE JOURNALIERES (résumé global)
-- ============================================================

CREATE TABLE IF NOT EXISTS TbleStatsCaisseJournalieres (
    RefStat INT AUTO_INCREMENT PRIMARY KEY,
    DateStat DATE NOT NULL,
    RefCaisse INT NOT NULL,
    RefAgency INT NOT NULL,
    
    -- Totaux par type d'opération
    TotalDepot DECIMAL(15,2) DEFAULT 0 COMMENT 'RefType=1',
    TotalRetrait DECIMAL(15,2) DEFAULT 0 COMMENT 'RefType=2',
    TotalAppro DECIMAL(15,2) DEFAULT 0 COMMENT 'RefType=3',
    TotalSortie DECIMAL(15,2) DEFAULT 0 COMMENT 'RefType=4',
    
    -- Compteurs
    NbOperations INT DEFAULT 0,
    NbDepots INT DEFAULT 0,
    NbRetraits INT DEFAULT 0,
    NbAppro INT DEFAULT 0,
    NbSorties INT DEFAULT 0,
    
    -- Remittance
    TotalRemittanceDepot DECIMAL(15,2) DEFAULT 0,
    TotalRemittanceRetrait DECIMAL(15,2) DEFAULT 0,
    
    -- Metadata
    DateMAJ DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY idx_unique (DateStat, RefCaisse),
    INDEX idx_date (DateStat),
    INDEX idx_caisse (RefCaisse),
    INDEX idx_agence (RefAgency)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Résumé journalier par caisse';

-- ============================================================
-- PARTIE 4: INSERTION DONNEES AUJOURD'HUI
-- ============================================================

-- Peupler TbleStatsOperationsJournalieres avec les opérations d'aujourd'hui
INSERT IGNORE INTO TbleStatsOperationsJournalieres 
    (DateStat, RefCaisse, RefAgency, RefType, NbOperations, TotalMontant, MaxMontant, MinMontant)
SELECT 
    DATE(o.Approve2_Time) as DateStat,
    o.RefCaisse,
    c.RefAgency,
    o.RefType,
    COUNT(*) as NbOperations,
    SUM(o.MontantVersement) as TotalMontant,
    MAX(o.MontantVersement) as MaxMontant,
    MIN(o.MontantVersement) as MinMontant
FROM TbleOperations o
INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
WHERE o.Approve2_Id IS NOT NULL 
  AND o.Reset_Id IS NULL
  AND DATE(o.Approve2_Time) = CURDATE()
GROUP BY DATE(o.Approve2_Time), o.RefCaisse, o.RefType;

-- Peupler TbleStatsRemittanceJournalieres avec les remittances d'aujourd'hui
INSERT IGNORE INTO TbleStatsRemittanceJournalieres
    (DateStat, RefCaisse, RefAgency, RefProduit, RefType, NbOperations, TotalMontant)
SELECT 
    DATE(r.Insert_time) as DateStat,
    r.RefCaisse,
    c.RefAgency,
    r.RefProduit,
    r.RefType,
    COUNT(*) as NbOperations,
    SUM(r.MontantTransaction) as TotalMontant
FROM TbleRemittance r
INNER JOIN TbleCaisse c ON c.RefCaisse = r.RefCaisse
WHERE r.Reset_Id IS NULL
  AND DATE(r.Insert_time) = CURDATE()
GROUP BY DATE(r.Insert_time), r.RefCaisse, r.RefProduit, r.RefType;

-- Peupler TbleStatsCaisseJournalieres avec les données d'aujourd'hui
INSERT IGNORE INTO TbleStatsCaisseJournalieres
    (DateStat, RefCaisse, RefAgency, 
     TotalDepot, TotalRetrait, TotalAppro, TotalSortie,
     NbOperations, NbDepots, NbRetraits, NbAppro, NbSorties)
SELECT 
    DATE(o.Approve2_Time) as DateStat,
    o.RefCaisse,
    c.RefAgency,
    SUM(CASE WHEN o.RefType = 1 THEN o.MontantVersement ELSE 0 END) as TotalDepot,
    SUM(CASE WHEN o.RefType = 2 THEN o.MontantVersement ELSE 0 END) as TotalRetrait,
    SUM(CASE WHEN o.RefType = 3 THEN o.MontantVersement ELSE 0 END) as TotalAppro,
    SUM(CASE WHEN o.RefType = 4 THEN o.MontantVersement ELSE 0 END) as TotalSortie,
    COUNT(*) as NbOperations,
    SUM(CASE WHEN o.RefType = 1 THEN 1 ELSE 0 END) as NbDepots,
    SUM(CASE WHEN o.RefType = 2 THEN 1 ELSE 0 END) as NbRetraits,
    SUM(CASE WHEN o.RefType = 3 THEN 1 ELSE 0 END) as NbAppro,
    SUM(CASE WHEN o.RefType = 4 THEN 1 ELSE 0 END) as NbSorties
FROM TbleOperations o
INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
WHERE o.Approve2_Id IS NOT NULL 
  AND o.Reset_Id IS NULL
  AND DATE(o.Approve2_Time) = CURDATE()
GROUP BY DATE(o.Approve2_Time), o.RefCaisse;

-- Ajouter les totaux de remittance à TbleStatsCaisseJournalieres
UPDATE TbleStatsCaisseJournalieres s
INNER JOIN (
    SELECT 
        r.RefCaisse,
        SUM(CASE WHEN r.RefType = 1 THEN r.MontantTransaction ELSE 0 END) as TotalDepot,
        SUM(CASE WHEN r.RefType = 2 THEN r.MontantTransaction ELSE 0 END) as TotalRetrait
    FROM TbleRemittance r
    WHERE r.Reset_Id IS NULL AND DATE(r.Insert_time) = CURDATE()
    GROUP BY r.RefCaisse
) r ON r.RefCaisse = s.RefCaisse
SET 
    s.TotalRemittanceDepot = r.TotalDepot,
    s.TotalRemittanceRetrait = r.TotalRetrait
WHERE s.DateStat = CURDATE();

-- ============================================================
-- PARTIE 5: VERIFICATION
-- ============================================================

-- Vérifier les tables créées
SHOW TABLES LIKE 'TbleStats%';

-- Vérifier les données d'aujourd'hui
SELECT 'StatsOperations' as Table, COUNT(*) as Rows FROM TbleStatsOperationsJournalieres WHERE DateStat = CURDATE()
UNION ALL
SELECT 'StatsRemittance', COUNT(*) FROM TbleStatsRemittanceJournalieres WHERE DateStat = CURDATE()
UNION ALL
SELECT 'StatsCaisse', COUNT(*) FROM TbleStatsCaisseJournalieres WHERE DateStat = CURDATE();

-- ============================================================
-- NOTES D'EXECUTION
-- ============================================================
-- 1. Executer ce script après la migration 007
-- 2. Les données sont peuplées uniquement pour aujourd'hui
-- 3. Les mises à jour futures se feront en temps réel après chaque opération
-- 4. Le StatsManager ajoutera/mettra à jour les stats automatiquement

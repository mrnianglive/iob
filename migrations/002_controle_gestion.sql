-- ============================================================
-- Migration IOB E-Banking - Contrôle de Gestion
-- Date: 2026-01-01
-- Description: Tables pour réconciliation, alertes et audit
-- ============================================================

-- ============================================================
-- PARTIE 1: TABLE DE RÉCONCILIATION QUOTIDIENNE
-- ============================================================

-- Enregistre la réconciliation quotidienne IOB vs ECOBANK
CREATE TABLE IF NOT EXISTS TbleReconciliation (
    RefReconciliation INT AUTO_INCREMENT PRIMARY KEY,
    RefAgency INT NOT NULL,
    DateReconciliation DATE NOT NULL,
    
    -- Soldes calculés par IOB
    SoldeIOB_Depot DECIMAL(15,2) DEFAULT 0 COMMENT 'Total dépôts du jour IOB',
    SoldeIOB_Retrait DECIMAL(15,2) DEFAULT 0 COMMENT 'Total retraits du jour IOB',
    SoldeIOB_Net DECIMAL(15,2) DEFAULT 0 COMMENT 'Net IOB (Dépôts - Retraits)',
    SoldeIOB_Especes DECIMAL(15,2) DEFAULT 0 COMMENT 'Solde espèces en caisse',
    
    -- Soldes saisis depuis ECOBANK
    SoldeEcobank DECIMAL(15,2) DEFAULT NULL COMMENT 'Solde relevé sur plateforme ECOBANK',
    ReferenceEcobank VARCHAR(100) DEFAULT NULL COMMENT 'Référence relevé ECOBANK',
    
    -- Calculs
    Ecart DECIMAL(15,2) DEFAULT 0 COMMENT 'Différence IOB vs ECOBANK',
    FondsRoulement DECIMAL(15,2) DEFAULT 0 COMMENT 'Solde Ecobank + Espèces',
    
    -- Statut et validation
    Statut ENUM('En attente', 'Validé', 'Écart signalé', 'Corrigé') DEFAULT 'En attente',
    RefUsersCreation INT NOT NULL,
    RefUsersValidation INT DEFAULT NULL,
    DateValidation DATETIME DEFAULT NULL,
    Commentaire TEXT DEFAULT NULL,
    
    DateCreation DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY idx_agency_date (RefAgency, DateReconciliation),
    INDEX idx_statut (Statut),
    INDEX idx_date (DateReconciliation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================================
-- PARTIE 2: TABLE DES ALERTES ET ANOMALIES
-- ============================================================

CREATE TABLE IF NOT EXISTS TbleAlertes (
    RefAlerte INT AUTO_INCREMENT PRIMARY KEY,
    TypeAlerte ENUM(
        'ECART_RECONCILIATION',      -- Écart IOB vs ECOBANK
        'SOLDE_NEGATIF',             -- Solde caisse négatif
        'OPERATION_ELEVEE',          -- Opération au-dessus du seuil
        'RETRAIT_SANS_DEPOT',        -- Plus de retraits que de dépôts
        'CAISSE_NON_FERMEE',         -- Caisse non fermée à l'heure
        'OPERATION_ANTIDATEE',       -- Opération antidatée
        'DOUBLON_POSSIBLE',          -- Opération similaire détectée
        'SOLDE_INHABITUEL'           -- Solde anormalement élevé/bas
    ) NOT NULL,
    RefAgency INT DEFAULT NULL,
    RefCaisse INT DEFAULT NULL,
    RefOperations INT DEFAULT NULL,
    
    Severite ENUM('INFO', 'WARNING', 'CRITICAL') DEFAULT 'WARNING',
    Message TEXT NOT NULL,
    Montant DECIMAL(15,2) DEFAULT NULL,
    
    Statut ENUM('Nouvelle', 'En cours', 'Traitée', 'Ignorée') DEFAULT 'Nouvelle',
    RefUsersTraitement INT DEFAULT NULL,
    DateTraitement DATETIME DEFAULT NULL,
    CommentaireTraitement TEXT DEFAULT NULL,
    
    DateCreation DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_type (TypeAlerte),
    INDEX idx_statut (Statut),
    INDEX idx_severite (Severite),
    INDEX idx_date (DateCreation),
    INDEX idx_agency (RefAgency)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================================
-- PARTIE 3: TABLE SEUILS ET PARAMÈTRES DE CONTRÔLE
-- ============================================================

CREATE TABLE IF NOT EXISTS TbleSeuilsControle (
    RefSeuil INT AUTO_INCREMENT PRIMARY KEY,
    CodeSeuil VARCHAR(50) NOT NULL UNIQUE,
    LibelleSeuil VARCHAR(255) NOT NULL,
    Valeur DECIMAL(15,2) NOT NULL,
    TypeValeur ENUM('MONTANT', 'POURCENTAGE', 'NOMBRE') DEFAULT 'MONTANT',
    Actif TINYINT(1) DEFAULT 1,
    DateModification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    RefUsersModification INT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Insérer les seuils par défaut
INSERT INTO TbleSeuilsControle (CodeSeuil, LibelleSeuil, Valeur, TypeValeur) VALUES
('SEUIL_OPERATION_ELEVEE', 'Montant à partir duquel une alerte est générée pour opération élevée', 5000000, 'MONTANT'),
('SEUIL_ECART_RECONCILIATION', 'Écart toléré entre IOB et ECOBANK', 1000, 'MONTANT'),
('SEUIL_ECART_POURCENTAGE', 'Écart en pourcentage toléré', 0.5, 'POURCENTAGE'),
('SEUIL_SOLDE_MAX_CAISSE', 'Solde maximum autorisé en caisse', 50000000, 'MONTANT'),
('SEUIL_NB_OPERATIONS_JOUR', 'Nombre max d''opérations par caissier par jour', 200, 'NOMBRE'),
('HEURE_FERMETURE_ALERTE', 'Heure après laquelle alerter si caisse non fermée (23h = 23)', 23, 'NOMBRE');

-- ============================================================
-- PARTIE 4: VUE POUR DASHBOARD CONTRÔLE
-- ============================================================

-- Vue consolidée des opérations pour le contrôle
CREATE OR REPLACE VIEW v_controle_journalier AS
SELECT 
    DATE(o.Approve2_Time) AS DateOperation,
    a.RefAgency,
    a.NameAgency,
    c.RefCaisse,
    c.NameCaisse,
    
    -- Compteurs
    COUNT(CASE WHEN o.RefType = 1 THEN 1 END) AS NbDepots,
    COUNT(CASE WHEN o.RefType = 2 THEN 1 END) AS NbRetraits,
    COUNT(*) AS NbTotalOperations,
    
    -- Montants
    COALESCE(SUM(CASE WHEN o.RefType = 1 THEN o.MontantVersement ELSE 0 END), 0) AS TotalDepots,
    COALESCE(SUM(CASE WHEN o.RefType = 2 THEN o.MontantVersement ELSE 0 END), 0) AS TotalRetraits,
    COALESCE(SUM(CASE WHEN o.RefType = 3 THEN o.MontantVersement ELSE 0 END), 0) AS TotalAppro,
    COALESCE(SUM(CASE WHEN o.RefType = 4 THEN o.MontantVersement ELSE 0 END), 0) AS TotalSorties,
    
    -- Calculs
    COALESCE(SUM(CASE WHEN o.RefType = 1 THEN o.MontantVersement ELSE 0 END), 0) -
    COALESCE(SUM(CASE WHEN o.RefType = 2 THEN o.MontantVersement ELSE 0 END), 0) AS NetJournalier,
    
    -- Plus grosse opération
    MAX(o.MontantVersement) AS MaxOperation,
    
    -- Opérations antidatées
    SUM(CASE WHEN DATE(o.Insert_Time) != DATE(o.Approve2_Time) THEN 1 ELSE 0 END) AS NbAntidatees

FROM TbleOperations o
INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
INNER JOIN TbleAgency a ON a.RefAgency = c.RefAgency
WHERE o.Approve2_Id IS NOT NULL 
  AND o.Reset_Id IS NULL
  AND o.RefType IN (1, 2, 3, 4)
GROUP BY DATE(o.Approve2_Time), a.RefAgency, a.NameAgency, c.RefCaisse, c.NameCaisse;

-- ============================================================
-- PARTIE 5: VUE POUR DÉTECTION D'ANOMALIES
-- ============================================================

CREATE OR REPLACE VIEW v_anomalies_potentielles AS
SELECT 
    o.RefOperations,
    o.RefCaisse,
    c.NameCaisse,
    a.RefAgency,
    a.NameAgency,
    o.RefType,
    t.NameType,
    o.MontantVersement,
    o.NumCompte,
    o.NameClient,
    o.Insert_Time,
    o.Approve2_Time,
    
    -- Indicateurs d'anomalie
    CASE 
        WHEN o.MontantVersement >= 5000000 THEN 'OPERATION_ELEVEE'
        WHEN DATE(o.Insert_Time) != DATE(o.Approve2_Time) THEN 'ANTIDATEE'
        ELSE NULL
    END AS TypeAnomalie,
    
    CONCAT(u.PrenomUsers, ' ', u.NomUsers) AS Caissier

FROM TbleOperations o
INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
INNER JOIN TbleAgency a ON a.RefAgency = c.RefAgency
INNER JOIN TbleType t ON t.RefType = o.RefType
INNER JOIN TbleUsers u ON u.RefUsers = o.Insert_Id
WHERE o.Approve2_Id IS NOT NULL 
  AND o.Reset_Id IS NULL
  AND (
      o.MontantVersement >= 5000000  -- Opérations élevées
      OR DATE(o.Insert_Time) != DATE(o.Approve2_Time)  -- Antidatées
  );

-- ============================================================
-- PARTIE 6: TABLE AUDIT TRAIL COMPLET
-- ============================================================

CREATE TABLE IF NOT EXISTS TbleAuditTrail (
    RefAudit INT AUTO_INCREMENT PRIMARY KEY,
    TableName VARCHAR(50) NOT NULL,
    RecordId INT NOT NULL,
    Action ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    OldValues JSON DEFAULT NULL,
    NewValues JSON DEFAULT NULL,
    RefUsers INT NOT NULL,
    IpAddress VARCHAR(45) DEFAULT NULL,
    UserAgent TEXT DEFAULT NULL,
    DateAction DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_table_record (TableName, RecordId),
    INDEX idx_user (RefUsers),
    INDEX idx_date (DateAction)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================================
-- PARTIE 7: INDEX SUPPLÉMENTAIRES POUR PERFORMANCE
-- ============================================================

-- Index pour les vues de contrôle
ALTER TABLE TbleOperations
  ADD INDEX IF NOT EXISTS idx_insert_approve_date (Insert_Time, Approve2_Time);

-- ============================================================
-- NOTES
-- ============================================================
-- 1. Exécuter ce script après 001_add_indexes_and_autoclose.sql
-- 2. Les vues peuvent être recréées à tout moment si nécessaire
-- 3. Adapter les seuils selon les besoins métier



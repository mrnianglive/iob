-- ============================================================
-- Migration IOB E-Banking - LCB-FT et CRM Clients
-- Date: 2026-01-01
-- Description: Tables pour anti-blanchiment et CRM clients
-- ============================================================

-- ============================================================
-- PARTIE 1: TABLE PROFIL CLIENT (commune LCB + CRM)
-- ============================================================

CREATE TABLE IF NOT EXISTS TbleClients (
    RefClient INT AUTO_INCREMENT PRIMARY KEY,
    NumCompte VARCHAR(50) NOT NULL,
    NomClient VARCHAR(255) DEFAULT NULL,
    TelClient VARCHAR(20) DEFAULT NULL,
    RefAgencyPrincipale INT DEFAULT NULL COMMENT 'Agence ou le client fait le plus d''operations',
    
    -- Statistiques globales
    DatePremiereOp DATE DEFAULT NULL,
    DateDerniereOp DATE DEFAULT NULL,
    NbTotalOperations INT DEFAULT 0,
    VolumeTotalDepot DECIMAL(15,2) DEFAULT 0,
    VolumeTotalRetrait DECIMAL(15,2) DEFAULT 0,
    MontantMoyenOperation DECIMAL(15,2) DEFAULT 0,
    
    -- Segmentation CRM
    Segment ENUM('VIP', 'REGULIER', 'OCCASIONNEL', 'DORMANT', 'PERDU', 'NOUVEAU') DEFAULT 'NOUVEAU',
    
    -- LCB-FT
    NiveauRisque ENUM('FAIBLE', 'MOYEN', 'ELEVE') DEFAULT 'FAIBLE',
    EstSurveille TINYINT(1) DEFAULT 0 COMMENT '1 = Client sous surveillance LCB',
    MotifSurveillance TEXT DEFAULT NULL,
    
    -- Metadata
    DateCreation DATETIME DEFAULT CURRENT_TIMESTAMP,
    DateMAJ DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY idx_numcompte (NumCompte),
    INDEX idx_agence (RefAgencyPrincipale),
    INDEX idx_segment (Segment),
    INDEX idx_derniere_op (DateDerniereOp),
    INDEX idx_risque (NiveauRisque),
    INDEX idx_surveille (EstSurveille)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Profils clients pour CRM et LCB-FT';

-- ============================================================
-- PARTIE 2: STATISTIQUES MENSUELLES PAR CLIENT
-- ============================================================

CREATE TABLE IF NOT EXISTS TbleClientStats (
    RefStat INT AUTO_INCREMENT PRIMARY KEY,
    NumCompte VARCHAR(50) NOT NULL,
    AnneeMois CHAR(7) NOT NULL COMMENT 'Format: 2026-01',
    RefAgency INT DEFAULT NULL,
    
    -- Compteurs
    NbOperations INT DEFAULT 0,
    NbDepots INT DEFAULT 0,
    NbRetraits INT DEFAULT 0,
    
    -- Volumes
    VolumeDepot DECIMAL(15,2) DEFAULT 0,
    VolumeRetrait DECIMAL(15,2) DEFAULT 0,
    VolumeNet DECIMAL(15,2) DEFAULT 0 COMMENT 'Depot - Retrait',
    
    -- Plus grosse operation du mois
    MaxOperation DECIMAL(15,2) DEFAULT 0,
    
    DateMAJ DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY idx_compte_mois (NumCompte, AnneeMois),
    INDEX idx_mois (AnneeMois),
    INDEX idx_agence (RefAgency)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stats mensuelles par client pour graphiques CRM';

-- ============================================================
-- PARTIE 3: ALERTES LCB-FT (Anti-Blanchiment)
-- ============================================================

CREATE TABLE IF NOT EXISTS TbleAlertesLCB (
    RefAlerteLCB INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Type d'alerte
    CodeAlerte VARCHAR(20) NOT NULL COMMENT 'AML-001, AML-002, etc.',
    Severite ENUM('INFO', 'MOYENNE', 'HAUTE', 'CRITIQUE') DEFAULT 'MOYENNE',
    
    -- References
    NumCompte VARCHAR(50) DEFAULT NULL,
    RefOperations INT DEFAULT NULL,
    RefAgency INT DEFAULT NULL,
    RefCaisse INT DEFAULT NULL,
    
    -- Details
    Montant DECIMAL(15,2) DEFAULT NULL COMMENT 'Montant de l''operation declenchante',
    MontantCumul DECIMAL(15,2) DEFAULT NULL COMMENT 'Cumul si alerte de cumul',
    PeriodeCumul VARCHAR(20) DEFAULT NULL COMMENT 'JOUR, SEMAINE, MOIS',
    Description TEXT NOT NULL,
    
    -- Traitement
    Statut ENUM('NOUVELLE', 'EN_COURS', 'TRAITEE', 'DECLAREE_CENTIF', 'CLASSEE') DEFAULT 'NOUVELLE',
    RefUsersTraitement INT DEFAULT NULL,
    DateTraitement DATETIME DEFAULT NULL,
    ActionPrise TEXT DEFAULT NULL,
    
    -- Metadata
    DateCreation DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_code (CodeAlerte),
    INDEX idx_compte (NumCompte),
    INDEX idx_statut (Statut),
    INDEX idx_severite (Severite),
    INDEX idx_date (DateCreation),
    INDEX idx_agency (RefAgency)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Alertes anti-blanchiment LCB-FT';

-- ============================================================
-- PARTIE 4: SEUILS LCB CONFIGURABLES
-- ============================================================

CREATE TABLE IF NOT EXISTS TbleSeuilsLCB (
    RefSeuil INT AUTO_INCREMENT PRIMARY KEY,
    CodeSeuil VARCHAR(50) NOT NULL UNIQUE,
    LibelleSeuil VARCHAR(255) NOT NULL,
    Valeur DECIMAL(15,2) NOT NULL,
    TypeValeur ENUM('MONTANT', 'POURCENTAGE', 'NOMBRE', 'JOURS') DEFAULT 'MONTANT',
    Actif TINYINT(1) DEFAULT 1,
    DateModification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    RefUsersModification INT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Seuils configurables pour LCB-FT';

-- Inserer les seuils par defaut
INSERT INTO TbleSeuilsLCB (CodeSeuil, LibelleSeuil, Valeur, TypeValeur) VALUES
('LCB_SEUIL_DECLARATION', 'Seuil de declaration CENTIF Mali', 15000000, 'MONTANT'),
('LCB_SEUIL_SURVEILLANCE', 'Seuil de mise sous surveillance', 5000000, 'MONTANT'),
('LCB_CUMUL_JOURNALIER', 'Cumul maximum par client par jour avant alerte', 15000000, 'MONTANT'),
('LCB_CUMUL_HEBDO', 'Cumul maximum par client par semaine avant alerte', 25000000, 'MONTANT'),
('LCB_CUMUL_MENSUEL', 'Cumul maximum par client par mois avant alerte', 50000000, 'MONTANT'),
('LCB_MARGE_FRACTIONNEMENT', 'Marge sous le seuil pour detection fractionnement', 500000, 'MONTANT'),
('LCB_NB_OP_JOUR_SUSPECT', 'Nombre d''operations par jour considere suspect', 5, 'NOMBRE'),
('LCB_DELAI_DEPOT_RETRAIT', 'Delai en heures entre depot et retrait suspect', 24, 'NOMBRE')
ON DUPLICATE KEY UPDATE LibelleSeuil = VALUES(LibelleSeuil);

-- ============================================================
-- PARTIE 5: SEUILS CRM (Segmentation)
-- ============================================================

INSERT INTO TbleSeuilsLCB (CodeSeuil, LibelleSeuil, Valeur, TypeValeur) VALUES
('CRM_SEUIL_VIP_VOLUME', 'Volume mensuel minimum pour statut VIP', 20000000, 'MONTANT'),
('CRM_SEUIL_VIP_NB_OPS', 'Nombre d''ops mensuel minimum pour statut VIP', 50, 'NOMBRE'),
('CRM_JOURS_DORMANT', 'Jours d''inactivite pour statut Dormant', 30, 'JOURS'),
('CRM_JOURS_PERDU', 'Jours d''inactivite pour statut Perdu', 90, 'JOURS'),
('CRM_ALERTE_VIP_INACTIF', 'Jours sans operation pour alerter sur VIP', 7, 'JOURS'),
('CRM_SEUIL_GROS_CLIENT', 'Montant premiere operation pour nouveau gros client', 5000000, 'MONTANT')
ON DUPLICATE KEY UPDATE LibelleSeuil = VALUES(LibelleSeuil);

-- ============================================================
-- PARTIE 6: ALERTES CRM (pour chefs d'agence)
-- ============================================================

CREATE TABLE IF NOT EXISTS TbleAlertesCRM (
    RefAlerteCRM INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Type d'alerte
    CodeAlerte VARCHAR(20) NOT NULL COMMENT 'CRM-001, CRM-002, etc.',
    
    -- References
    NumCompte VARCHAR(50) DEFAULT NULL,
    RefAgency INT NOT NULL,
    
    -- Details
    Message TEXT NOT NULL,
    
    -- Traitement
    Statut ENUM('NOUVELLE', 'VUE', 'TRAITEE') DEFAULT 'NOUVELLE',
    RefUsersTraitement INT DEFAULT NULL,
    DateTraitement DATETIME DEFAULT NULL,
    
    -- Metadata
    DateCreation DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_agence_statut (RefAgency, Statut),
    INDEX idx_compte (NumCompte),
    INDEX idx_date (DateCreation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Alertes CRM pour chefs d''agence';

-- ============================================================
-- PARTIE 7: VUE POUR DETECTION RAPIDE
-- ============================================================

-- Vue des operations du jour pour detection LCB
CREATE OR REPLACE VIEW v_operations_jour AS
SELECT 
    o.RefOperations,
    o.NumCompte,
    o.NameClient,
    o.MontantVersement,
    o.RefType,
    t.NameType,
    c.RefCaisse,
    c.NameCaisse,
    a.RefAgency,
    a.NameAgency,
    o.Insert_Time,
    o.Approve2_Time,
    CONCAT(u.PrenomUsers, ' ', u.NomUsers) AS Caissier
FROM TbleOperations o
INNER JOIN TbleType t ON t.RefType = o.RefType
INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
INNER JOIN TbleAgency a ON a.RefAgency = c.RefAgency
INNER JOIN TbleUsers u ON u.RefUsers = o.Insert_Id
WHERE o.Approve2_Id IS NOT NULL 
  AND o.Reset_Id IS NULL
  AND DATE(o.Approve2_Time) = CURDATE()
  AND o.RefType IN (1, 2);

-- Vue des cumuls par client du jour
CREATE OR REPLACE VIEW v_cumul_client_jour AS
SELECT 
    o.NumCompte,
    o.NameClient,
    COUNT(*) AS NbOperations,
    SUM(CASE WHEN o.RefType = 1 THEN o.MontantVersement ELSE 0 END) AS TotalDepot,
    SUM(CASE WHEN o.RefType = 2 THEN o.MontantVersement ELSE 0 END) AS TotalRetrait,
    SUM(o.MontantVersement) AS TotalVolume,
    MAX(o.MontantVersement) AS MaxOperation,
    GROUP_CONCAT(DISTINCT c.RefAgency) AS Agences
FROM TbleOperations o
INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
WHERE o.Approve2_Id IS NOT NULL 
  AND o.Reset_Id IS NULL
  AND DATE(o.Approve2_Time) = CURDATE()
  AND o.RefType IN (1, 2)
GROUP BY o.NumCompte, o.NameClient;

-- Vue des clients inactifs
CREATE OR REPLACE VIEW v_clients_inactifs AS
SELECT 
    c.*,
    a.NameAgency,
    DATEDIFF(CURDATE(), c.DateDerniereOp) AS JoursInactif
FROM TbleClients c
LEFT JOIN TbleAgency a ON a.RefAgency = c.RefAgencyPrincipale
WHERE c.DateDerniereOp < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
ORDER BY c.VolumeTotalDepot + c.VolumeTotalRetrait DESC;

-- Vue des top clients par agence
CREATE OR REPLACE VIEW v_top_clients_agence AS
SELECT 
    c.*,
    a.NameAgency,
    (c.VolumeTotalDepot + c.VolumeTotalRetrait) AS VolumeTotal,
    DATEDIFF(CURDATE(), c.DateDerniereOp) AS JoursDepuisDerniereOp
FROM TbleClients c
LEFT JOIN TbleAgency a ON a.RefAgency = c.RefAgencyPrincipale
WHERE c.Segment IN ('VIP', 'REGULIER')
ORDER BY VolumeTotal DESC;

-- ============================================================
-- PARTIE 8: INDEX SUPPLEMENTAIRES SUR TbleOperations
-- ============================================================

-- Index pour recherche par compte client (limite a 100 caracteres pour eviter erreur de taille)
-- ALTER TABLE TbleOperations ADD INDEX IF NOT EXISTS idx_numcompte_date (NumCompte(100), Approve2_Time);
-- NOTE: Commenter si l'index existe deja ou cause des erreurs

-- ============================================================
-- NOTES D'EXECUTION
-- ============================================================
-- 1. Executer apres 001_add_indexes_and_autoclose.sql et 002_controle_gestion.sql
-- 2. Les seuils peuvent etre modifies via l'interface admin
-- 3. Apres execution, lancer le script de peuplement initial TbleClients


-- ============================================================
-- Migration IOB E-Banking - Optimisation Vues SQL
-- Date: 2026-01-03
-- Description: Création de vues optimisées pour Journal, Caisse, PetiteCaisse
-- ============================================================

-- ============================================================
-- PARTIE 1: VUE POUR JOURNAL (opérations par caisse + jour)
-- ============================================================

CREATE OR REPLACE VIEW v_operations_caisse_jour AS
SELECT 
    o.RefCaisse,
    c.NameCaisse,
    ca.RefAgency,
    ca.NameAgency,
    DATE(o.Approve2_Time) as DateOp,
    o.RefType,
    COUNT(*) as NbOperations,
    SUM(o.MontantVersement) as TotalMontant,
    MAX(o.MontantVersement) as MaxMontant,
    MIN(o.MontantVersement) as MinMontant
FROM TbleOperations o
INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
INNER JOIN TbleAgency ca ON ca.RefAgency = c.RefAgency
WHERE o.Approve2_Id IS NOT NULL 
  AND o.Reset_Id IS NULL
GROUP BY o.RefCaisse, c.NameCaisse, ca.RefAgency, ca.NameAgency, DATE(o.Approve2_Time), o.RefType;

-- ============================================================
-- PARTIE 2: VUE POUR PETITE CAISSE (remittance par agence + produit + jour)
-- ============================================================

CREATE OR REPLACE VIEW v_remittance_agence_produit_jour AS
SELECT 
    r.RefCaisse,
    c.NameCaisse,
    a.RefAgency,
    a.NameAgency,
    r.RefProduit,
    p.NameProduit,
    DATE(r.Insert_time) as DateOp,
    r.RefType,
    COUNT(*) as NbOperations,
    SUM(r.MontantTransaction) as TotalMontant
FROM TbleRemittance r
INNER JOIN TbleCaisse c ON c.RefCaisse = r.RefCaisse
INNER JOIN TbleAgency a ON a.RefAgency = c.RefAgency
INNER JOIN TbleProduit p ON p.RefProduit = r.RefProduit
WHERE r.Reset_Id IS NULL
GROUP BY r.RefCaisse, c.NameCaisse, a.RefAgency, a.NameAgency, r.RefProduit, p.NameProduit, DATE(r.Insert_time), r.RefType;

-- ============================================================
-- PARTIE 3: VUE POUR RECHERCHE RAPIDE (toutes opérations avec infos)
-- ============================================================

CREATE OR REPLACE VIEW v_operations_completes AS
SELECT 
    o.RefOperations,
    o.NumCompte,
    o.NameClient,
    o.MontantVersement,
    o.RefType,
    t.NameType,
    o.RefCaisse,
    c.NameCaisse,
    ca.RefAgency,
    ca.NameAgency,
    o.Insert_Time,
    o.Approve2_Time,
    o.ValidateDate,
    o.Approve1_Id,
    o.Approve2_Id,
    o.Approve1_Time,
    o.Insert_Id,
    CONCAT(u.PrenomUsers, ' ', u.NomUsers) AS Caissier
FROM TbleOperations o
INNER JOIN TbleType t ON t.RefType = o.RefType
INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
INNER JOIN TbleAgency ca ON ca.RefAgency = c.RefAgency
INNER JOIN TbleUsers u ON u.RefUsers = o.Insert_Id
WHERE o.Approve2_Id IS NOT NULL 
  AND o.Reset_Id IS NULL;

-- ============================================================
-- PARTIE 4: INDEX SUR LES VUES
-- ============================================================

-- Index pour la vue caisse_jour (utilisé dans Journal)
-- CREATE INDEX IF NOT EXISTS idx_v_caisse_date ON v_operations_caisse_jour(RefCaisse, DateOp);
-- CREATE INDEX IF NOT EXISTS idx_v_agence_date ON v_operations_caisse_jour(RefAgency, DateOp);
-- CREATE INDEX IF NOT EXISTS idx_v_caisse_type_date ON v_operations_caisse_jour(RefCaisse, RefType, DateOp);

-- Index pour la vue remittance (utilisé dans PetiteCaisse)
-- CREATE INDEX IF NOT EXISTS idx_v_rem_agence_prod ON v_remittance_agence_produit_jour(RefAgency, RefProduit, DateOp);
-- CREATE INDEX IF NOT EXISTS idx_v_rem_date ON v_remittance_agence_produit_jour(DateOp);

-- Index pour la vue opérations complètes
-- CREATE INDEX IF NOT EXISTS idx_v_ops_date ON v_operations_completes(Approve2_Time);
-- CREATE INDEX IF NOT EXISTS idx_v_ops_caisse_date ON v_operations_completes(RefCaisse, Approve2_Time);
-- CREATE INDEX IF NOT EXISTS idx_v_ops_agence_date ON v_operations_completes(RefAgency, Approve2_Time);

-- ============================================================
-- PARTIE 5: INDEX SUPPLEMENTAIRES POUR TbleOperations
-- ============================================================

-- Index composite pour les requêtes optimisées (évite date() function)
ALTER TABLE TbleOperations ADD INDEX IF NOT EXISTS idx_approve_time_type_reset 
    (Approve2_Time, RefType, Approve2_Id, Reset_Id);

-- Index pour NumCompte prefix
ALTER TABLE TbleOperations ADD INDEX IF NOT EXISTS idx_numcompte_prefix 
    (NumCompte(10));

-- Index composite caisse + type + date
ALTER TABLE TbleOperations ADD INDEX IF NOT EXISTS idx_caisse_type_approve 
    (RefCaisse, RefType, Approve2_Time);

-- ============================================================
-- PARTIE 6: INDEX POUR TbleRemittance
-- ============================================================

-- Index pour les requêtes par date + produit
ALTER TABLE TbleRemittance ADD INDEX IF NOT EXISTS idx_remittance_date_produit 
    (Insert_time, RefProduit, Reset_Id);

-- Index pour les jointures
ALTER TABLE TbleRemittance ADD INDEX IF NOT EXISTS idx_remittance_caisse 
    (RefCaisse, Reset_Id);

-- ============================================================
-- PARTIE 7: VERIFICATION
-- ============================================================

-- Afficher les vues créées
SHOW FULL TABLES WHERE TABLE_TYPE LIKE 'VIEW';

-- Afficher les index créés sur TbleOperations
SHOW INDEX FROM TbleOperations WHERE Key_name LIKE 'idx_%';

-- Afficher les index créés sur TbleRemittance
SHOW INDEX FROM TbleRemittance WHERE Key_name LIKE 'idx_%';

-- ============================================================
-- NOTES D'EXECUTION
-- ============================================================
-- 1. Executer ce script après les migrations 001-006
-- 2. Peut prendre quelques minutes sur une base de données volumineuse
-- 3. Les vues sont utilisables immédiatement après création
-- 4. Tester les requêtes SELECT sur les vues avant de mettre en prod

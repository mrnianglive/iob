-- Migration: 006_optimize_analytics_indexes
-- Date: 2026-01-02
-- Description: Ajoute des index pour optimiser les requêtes Analytics et Performance

-- Index pour les requêtes de totaux par période
CREATE INDEX IF NOT EXISTS idx_operations_approved_date_type 
ON TbleOperations(Approve2_Id, Reset_Id, Approve2_Time, RefType);

-- Index pour les requêtes par mois/année
CREATE INDEX IF NOT EXISTS idx_operations_approve_time 
ON TbleOperations(Approve2_Time);

-- Index pour ValidateDate (utilisé dans les compteurs)
CREATE INDEX IF NOT EXISTS idx_operations_validate_date 
ON TbleOperations(ValidateDate);

-- Index pour les jointures RefCaisse
CREATE INDEX IF NOT EXISTS idx_operations_refcaisse 
ON TbleOperations(RefCaisse);

-- Index composite pour les requêtes de chart par agence
CREATE INDEX IF NOT EXISTS idx_operations_chart_agence 
ON TbleOperations(Approve2_Id, Reset_Id, RefType, RefCaisse);

-- Index pour TbleRemittance
CREATE INDEX IF NOT EXISTS idx_remittance_insert_time 
ON TbleRemittance(Insert_time);

CREATE INDEX IF NOT EXISTS idx_remittance_refcaisse_type 
ON TbleRemittance(RefCaisse, RefType, Reset_Id);

-- Index pour TbleCaisse jointures
CREATE INDEX IF NOT EXISTS idx_caisse_refagency 
ON TbleCaisse(RefAgency);

-- Index pour TbleChmod (très utilisé)
CREATE INDEX IF NOT EXISTS idx_chmod_refusers 
ON TbleChmod(RefUsers);

CREATE INDEX IF NOT EXISTS idx_chmod_refcaisse 
ON TbleChmod(RefCaisse);


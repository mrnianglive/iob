-- Migration: 005_optimize_appro_caisse
-- Date: 2026-01-02
-- Description: Ajoute des index pour optimiser les requêtes ApproCaisse

-- Index composite pour la requête ListeAppro
-- Couvre les colonnes: RefType, Approve2_Id, Reset_Id, Approve2_Time, RefCaisse
CREATE INDEX IF NOT EXISTS idx_operations_appro_type_approved 
ON TbleOperations(RefType, Approve2_Id, Reset_Id, Approve2_Time, RefCaisse);

-- Index sur Approve2_Time pour les filtres par date
CREATE INDEX IF NOT EXISTS idx_operations_approve2_time 
ON TbleOperations(Approve2_Time);

-- Index sur TbleChmod pour les jointures utilisateur
CREATE INDEX IF NOT EXISTS idx_chmod_refusers_refcaisse 
ON TbleChmod(RefUsers, RefCaisse);

-- Index sur TbleCaisse pour les jointures
CREATE INDEX IF NOT EXISTS idx_caisse_refagency 
ON TbleCaisse(RefAgency);


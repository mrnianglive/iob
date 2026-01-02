-- Migration: Convert TEXT to DECIMAL for amount columns
-- Date: 2026-01-02
-- Purpose: Improve data integrity and performance for financial calculations

-- Convertir les colonnes de montants en DECIMAL(15,2)

-- TbleOperations
ALTER TABLE TbleOperations MODIFY MontantVersement DECIMAL(15,2) NOT NULL;
ALTER TABLE TbleOperations MODIFY fraisTimbre DECIMAL(15,2) DEFAULT '0.00';

-- TbleAppro
ALTER TABLE TbleAppro MODIFY MontantAppro DECIMAL(15,2) NOT NULL;

-- TbleCompte
ALTER TABLE TbleCompte MODIFY SoldeCompte DECIMAL(15,2) NOT NULL;

-- TbleRapportOp
ALTER TABLE TbleRapportOp MODIFY SoldeOvEspeces DECIMAL(15,2) NOT NULL;
ALTER TABLE TbleRapportOp MODIFY SoldeOvOmni DECIMAL(15,2) NOT NULL;
ALTER TABLE TbleRapportOp MODIFY Versement DECIMAL(15,2) DEFAULT NULL;
ALTER TABLE TbleRapportOp MODIFY Retrait DECIMAL(15,2) DEFAULT NULL;
ALTER TABLE TbleRapportOp MODIFY ApproOmni DECIMAL(15,2) DEFAULT NULL;
ALTER TABLE TbleRapportOp MODIFY SoldeFrEspeces DECIMAL(15,2) NOT NULL;
ALTER TABLE TbleRapportOp MODIFY SoldeFrOmni DECIMAL(15,2) NOT NULL;

-- TbleRemittance
ALTER TABLE TbleRemittance MODIFY MontantTransaction DECIMAL(15,2) NOT NULL;

-- TbleSolde
ALTER TABLE TbleSolde MODIFY Solde DECIMAL(15,2) NOT NULL;

-- TbleBilletage (quantities - can stay as INT but we'll use DECIMAL for consistency)
ALTER TABLE TbleBilletage MODIFY a2 DECIMAL(10,2) NOT NULL;
ALTER TABLE TbleBilletage MODIFY b2 DECIMAL(10,2) NOT NULL;
ALTER TABLE TbleBilletage MODIFY c2 DECIMAL(10,2) NOT NULL;
ALTER TABLE TbleBilletage MODIFY d2 DECIMAL(10,2) NOT NULL;
ALTER TABLE TbleBilletage MODIFY e2 DECIMAL(10,2) NOT NULL;
ALTER TABLE TbleBilletage MODIFY f2 DECIMAL(10,2) NOT NULL;
ALTER TABLE TbleBilletage MODIFY g2 DECIMAL(10,2) NOT NULL;
ALTER TABLE TbleBilletage MODIFY h2 DECIMAL(10,2) NOT NULL;
ALTER TABLE TbleBilletage MODIFY i2 DECIMAL(10,2) NOT NULL;
ALTER TABLE TbleBilletage MODIFY j2 DECIMAL(10,2) NOT NULL;
ALTER TABLE TbleBilletage MODIFY k2 DECIMAL(10,2) NOT NULL;
ALTER TABLE TbleBilletage MODIFY l2 DECIMAL(10,2) NOT NULL;
ALTER TABLE TbleBilletage MODIFY m2 DECIMAL(10,2) NOT NULL;

-- Note: This migration should be run carefully in production
-- Backup your database before running this migration

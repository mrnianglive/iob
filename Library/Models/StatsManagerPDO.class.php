<?php

namespace Library\Models;

class StatsManagerPDO extends StatsManager
{
    public function incrementOperationStats($refCaisse, $refType, $montant)
    {
        $date = date('Y-m-d');
        $sql = "INSERT INTO TbleStatsOperationsJournalieres 
            (DateStat, RefCaisse, RefAgency, RefType, NbOperations, TotalMontant, MaxMontant, MinMontant)
            SELECT :date, :caisse, c.RefAgency, :type, 1, :montant, :montant, :montant
            FROM TbleCaisse c WHERE c.RefCaisse = :caisse
            ON DUPLICATE KEY UPDATE
                NbOperations = NbOperations + 1,
                TotalMontant = TotalMontant + :montant2,
                MaxMontant = GREATEST(MaxMontant, :montant3),
                MinMontant = LEAST(COALESCE(MinMontant, 999999999999), :montant4),
                DateMAJ = NOW()";
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':date', $date);
        $stmt->bindValue(':caisse', $refCaisse, \PDO::PARAM_INT);
        $stmt->bindValue(':type', $refType, \PDO::PARAM_INT);
        $stmt->bindValue(':montant', $montant);
        $stmt->bindValue(':montant2', $montant);
        $stmt->bindValue(':montant3', $montant);
        $stmt->bindValue(':montant4', $montant);
        $stmt->execute();
    }

    public function incrementRemittanceStats($refCaisse, $refProduit, $refType, $montant)
    {
        $date = date('Y-m-d');
        $sql = "INSERT INTO TbleStatsRemittanceJournalieres
            (DateStat, RefCaisse, RefAgency, RefProduit, RefType, NbOperations, TotalMontant)
            SELECT :date, :caisse, c.RefAgency, :produit, :type, 1, :montant
            FROM TbleCaisse c WHERE c.RefCaisse = :caisse
            ON DUPLICATE KEY UPDATE
                NbOperations = NbOperations + 1,
                TotalMontant = TotalMontant + :montant2,
                DateMAJ = NOW()";
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':date', $date);
        $stmt->bindValue(':caisse', $refCaisse, \PDO::PARAM_INT);
        $stmt->bindValue(':produit', $refProduit, \PDO::PARAM_INT);
        $stmt->bindValue(':type', $refType, \PDO::PARAM_INT);
        $stmt->bindValue(':montant', $montant);
        $stmt->bindValue(':montant2', $montant);
        $stmt->execute();
    }

    public function getCaisseStatsForDate($date, $refCaisse)
    {
        $sql = "SELECT 
            s.RefCaisse,
            c.NameCaisse,
            ca.RefAgency,
            ca.NameAgency,
            COALESCE(s.TotalDepot, 0) as TotalDepot,
            COALESCE(s.TotalRetrait, 0) as TotalRetrait,
            COALESCE(s.TotalAppro, 0) as TotalAppro,
            COALESCE(s.TotalSortie, 0) as TotalSortie,
            COALESCE(s.NbOperations, 0) as NbOperations,
            COALESCE(s.NbDepots, 0) as NbDepots,
            COALESCE(s.NbRetraits, 0) as NbRetraits,
            COALESCE(s.NbAppro, 0) as NbAppro,
            COALESCE(s.NbSorties, 0) as NbSorties,
            COALESCE(s.TotalRemittanceDepot, 0) as RemittanceDepot,
            COALESCE(s.TotalRemittanceRetrait, 0) as RemittanceRetrait,
            COALESCE(sol.Solde, 0) as SoldeInitial
        FROM TbleCaisse c
        INNER JOIN TbleAgency ca ON ca.RefAgency = c.RefAgency
        LEFT JOIN TbleStatsCaisseJournalieres s 
            ON s.DateStat = :date AND s.RefCaisse = c.RefCaisse
        LEFT JOIN TbleSolde sol 
            ON sol.RefCaisse = c.RefCaisse AND DATE(sol.DateSolde) = :date2
        WHERE c.RefCaisse = :refCaisse
        GROUP BY c.RefCaisse, c.NameCaisse, ca.RefAgency, ca.NameAgency, 
                 s.TotalDepot, s.TotalRetrait, s.TotalAppro, s.TotalSortie,
                 s.NbOperations, s.NbDepots, s.NbRetraits, s.NbAppro, s.NbSorties,
                 s.TotalRemittanceDepot, s.TotalRemittanceRetrait, sol.Solde";
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':date', $date);
        $stmt->bindValue(':date2', $date);
        $stmt->bindValue(':refCaisse', $refCaisse, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getJournalStatsForDate($date, $refUsers = null)
    {
        $userCondition = $refUsers ? "AND ch.RefUsers = :refUsers" : "";
        
        $sql = "SELECT 
            s.RefCaisse,
            c.NameCaisse,
            ca.RefAgency,
            ca.NameAgency,
            COALESCE(s.TotalDepot, 0) as TotalDepot,
            COALESCE(s.TotalRetrait, 0) as TotalRetrait,
            COALESCE(s.TotalAppro, 0) as TotalAppro,
            COALESCE(s.TotalSortie, 0) as TotalSortie,
            COALESCE(s.NbOperations, 0) as NbOperations,
            COALESCE(s.TotalRemittanceDepot, 0) as RemittanceDepot,
            COALESCE(s.TotalRemittanceRetrait, 0) as RemittanceRetrait,
            COALESCE(sol.Solde, 0) as SoldeInitial
        FROM TbleCaisse c
        INNER JOIN TbleAgency ca ON ca.RefAgency = c.RefAgency
        INNER JOIN TbleChmod ch ON ch.RefCaisse = c.RefCaisse
        LEFT JOIN TbleStatsCaisseJournalieres s 
            ON s.DateStat = :date AND s.RefCaisse = c.RefCaisse
        LEFT JOIN TbleSolde sol 
            ON sol.RefCaisse = c.RefCaisse AND DATE(sol.DateSolde) = :date2
        WHERE 1=1 $userCondition
        GROUP BY c.RefCaisse, c.NameCaisse, ca.RefAgency, ca.NameAgency,
                 s.TotalDepot, s.TotalRetrait, s.TotalAppro, s.TotalSortie,
                 s.NbOperations, s.TotalRemittanceDepot, s.TotalRemittanceRetrait, sol.Solde
        ORDER BY ca.NameAgency, c.NameCaisse";
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':date', $date);
        $stmt->bindValue(':date2', $date);
        if ($refUsers) {
            $stmt->bindValue(':refUsers', $refUsers, \PDO::PARAM_INT);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getPetiteCaisseStats($date)
    {
        $sql = "SELECT 
            a.RefAgency,
            a.NameAgency,
            p.RefProduit,
            p.NameProduit,
            COALESCE(SUM(CASE WHEN r.RefType = 1 THEN r.TotalMontant ELSE 0 END), 0) as TotalDepot,
            COALESCE(SUM(CASE WHEN r.RefType = 2 THEN r.TotalMontant ELSE 0 END), 0) as TotalRetrait,
            COALESCE(SUM(CASE WHEN r.RefType = 1 THEN r.NbOperations ELSE 0 END), 0) as NbDepot,
            COALESCE(SUM(CASE WHEN r.RefType = 2 THEN r.NbOperations ELSE 0 END), 0) as NbRetrait
        FROM TbleAgency a
        CROSS JOIN TbleProduit p
        LEFT JOIN TbleStatsRemittanceJournalieres r 
            ON r.RefCaisse IN (SELECT RefCaisse FROM TbleCaisse WHERE RefAgency = a.RefAgency)
            AND r.RefProduit = p.RefProduit
            AND r.DateStat = :date
        WHERE p.RefProduit != 1
        GROUP BY a.RefAgency, a.NameAgency, p.RefProduit, p.NameProduit
        ORDER BY a.NameAgency, p.NameProduit";
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':date', $date);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getAllStatsForDate($date)
    {
        $sql = "SELECT 
            c.RefCaisse,
            c.NameCaisse,
            a.RefAgency,
            a.NameAgency,
            o.RefType,
            t.NameType,
            COALESCE(o.NbOperations, 0) as NbOperations,
            COALESCE(o.TotalMontant, 0) as TotalMontant,
            COALESCE(o.MaxMontant, 0) as MaxMontant,
            COALESCE(o.MinMontant, 0) as MinMontant
        FROM TbleCaisse c
        INNER JOIN TbleAgency a ON a.RefAgency = c.RefAgency
        LEFT JOIN TbleStatsOperationsJournalieres o 
            ON o.RefCaisse = c.RefCaisse AND o.DateStat = :date
        LEFT JOIN TbleType t ON t.RefType = o.RefType
        ORDER BY a.NameAgency, c.NameCaisse, o.RefType";
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':date', $date);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    protected function add(\Library\Entity $entity)
    {
        
    }

    protected function count($where = "")
    {
        
    }

    protected function delete($id)
    {
        
    }

    protected function getList($debut = -1, $limite = -1, $where = "")
    {
        
    }

    protected function modify(\Library\Entity $entity)
    {
        
    }

    protected function get($id)
    {
        
    }
}

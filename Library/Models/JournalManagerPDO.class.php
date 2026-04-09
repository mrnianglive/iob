<?php

namespace Library\Models;

use \Library\Entities\Journal;
use DateTime;

class JournalManagerPDO extends JournalManager
{
    public function Operations()
    {
        $query = 'SELECT o.*, c.RefUsers, a.NameAgency AS SentFromAgency
                    FROM operations AS o
                    LEFT JOIN TbleChmod AS c ON c.RefCaisse = o.RefCaisse
                    LEFT JOIN TbleAgency AS a ON a.RefAgency = o.SentFromAgency
                    WHERE o.Approve2_Id IS NOT NULL
                        AND o.Reset_Id IS NULL
                        AND o.Approve2_Time = :jour
                        AND o.RefType IN (1, 2, 3, 4)
                        AND c.RefUsers = :RefUsers
                    ORDER BY o.datePayement ASC';

        $requete = $this->dao->prepare($query);
        $requete->bindValue(':jour', date('Y-m-d'), \PDO::PARAM_STR);
        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->execute();

        return $requete->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function GetOperations($debut, $fin, $Agence, $produit)
    {
        $sql = "SELECT o.*, a.NameAgency AS SentFromAgency FROM operations o
                LEFT JOIN TbleAgency a ON a.RefAgency = o.SentFromAgency
                WHERE o.Approve2_Id IS NOT NULL 
                AND o.Reset_Id IS NULL 
                AND DATE(o.Approve2_Time) BETWEEN :debut AND :fin 
                AND o.RefAgency = :Agence";

        $params = [
            ':debut' => $debut,
            ':fin' => $fin,
            ':Agence' => $Agence
        ];

        if ($produit) {
            $sql .= " AND o.RefProduit = :produit";
            $params[':produit'] = $produit;
        } else {
            $sql .= " AND o.RefProduit IS NULL";
        }

        $sql .= " ORDER BY o.datePayement DESC";

        $requete = $this->dao->prepare($sql);
        $requete->execute($params);

        $data = $requete->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($data as &$row) {
            $row['Debut'] = $debut;
            $row['Fin'] = $fin;
            $row['RefProduit'] = $produit;
        }

        return $data;
    }

    public function UserCaisse($Date, $Pays = NULL, $Agence = NULL, $Caisse = NULL)
    {
        $sql = "SELECT 
            c.*, a.*, 
            COALESCE(si.SoldeInitial, 0) as SoldeInitial,
            COALESCE(sig.SoldeInitialGlobal, 0) as SoldeInitialGlobal,
            COALESCE(ta.TotalAppro, 0) as TotalAppro,
            COALESCE(tv.TotalVersement, 0) as TotalVersement,
            COALESCE(tr.TotalRetrait, 0) as TotalRetrait,
            COALESCE(ts.TotalSortieCaisse, 0) as TotalSortieCaisse,
            COALESCE(srv.SommeVersementRemittance, 0) as SommeVersementRemittance,
            COALESCE(srr.SommeRetraitRemittance, 0) as SommeRetraitRemittance,
            COALESCE(ft.TotalFraisTimbre, 0) as TotalFraisTimbre
        FROM TbleCaisse c
        INNER JOIN TbleAgency a ON a.RefAgency = c.RefAgency
        INNER JOIN TbleChmod ch ON ch.RefCaisse = c.RefCaisse
        LEFT JOIN (
            SELECT RefCaisse, SUM(MontantVersement) as SoldeInitial 
            FROM TbleOperations 
            WHERE Approve2_Id IS NOT NULL AND Reset_Id IS NULL AND Approve2_Time = :Date AND TypeAppro = 1 AND RefType = 3
            GROUP BY RefCaisse
        ) si ON si.RefCaisse = c.RefCaisse
        LEFT JOIN (
            SELECT RefCaisse, SUM(MontantVersement) as SoldeInitialGlobal 
            FROM TbleOperations 
            WHERE Approve2_Id IS NOT NULL AND Reset_Id IS NULL AND Approve2_Time = :Date AND RefType = 3
            GROUP BY RefCaisse
        ) sig ON sig.RefCaisse = c.RefCaisse
        LEFT JOIN (
            SELECT RefCaisse, SUM(MontantVersement) as TotalAppro 
            FROM TbleOperations 
            WHERE Approve2_Id IS NOT NULL AND Reset_Id IS NULL AND Approve2_Time = :Date AND TypeAppro = 2 AND RefType = 3
            GROUP BY RefCaisse
        ) ta ON ta.RefCaisse = c.RefCaisse
        LEFT JOIN (
            SELECT RefCaisse, SUM(MontantVersement) as TotalVersement 
            FROM TbleOperations 
            WHERE Approve2_Id IS NOT NULL AND Reset_Id IS NULL AND Approve2_Time = :Date AND RefType = 1
            GROUP BY RefCaisse
        ) tv ON tv.RefCaisse = c.RefCaisse
        LEFT JOIN (
            SELECT RefCaisse, SUM(MontantVersement) as TotalRetrait 
            FROM TbleOperations 
            WHERE Approve2_Id IS NOT NULL AND Reset_Id IS NULL AND Approve2_Time = :Date AND RefType = 2
            GROUP BY RefCaisse
        ) tr ON tr.RefCaisse = c.RefCaisse
        LEFT JOIN (
            SELECT RefCaisse, SUM(MontantVersement) as TotalSortieCaisse 
            FROM TbleOperations 
            WHERE Approve2_Id IS NOT NULL AND Reset_Id IS NULL AND Approve2_Time = :Date AND RefType = 4
            GROUP BY RefCaisse
        ) ts ON ts.RefCaisse = c.RefCaisse
        LEFT JOIN (
            SELECT RefCaisse, SUM(MontantTransaction) as SommeVersementRemittance 
            FROM TbleRemittance 
            WHERE DATE(Insert_time) = :Date AND RefType = 1 AND Reset_Id IS NULL
            GROUP BY RefCaisse
        ) srv ON srv.RefCaisse = c.RefCaisse
        LEFT JOIN (
            SELECT RefCaisse, SUM(MontantTransaction) as SommeRetraitRemittance 
            FROM TbleRemittance 
            WHERE DATE(Insert_time) = :Date AND RefType = 2 AND Reset_Id IS NULL
            GROUP BY RefCaisse
        ) srr ON srr.RefCaisse = c.RefCaisse
        LEFT JOIN (
            SELECT RefCaisse, SUM(MontantVersement) as TotalFraisTimbre 
            FROM TbleOperations 
            WHERE Approve2_Id IS NOT NULL AND Reset_Id IS NULL AND Approve2_Time = :Date AND RefType = 5
            GROUP BY RefCaisse
        ) ft ON ft.RefCaisse = c.RefCaisse
        WHERE ch.RefUsers = :RefUsers";

        $params = [':Date' => $Date, ':RefUsers' => $_SESSION['RefUsers']];

        if ($Pays !== NULL) {
            $sql .= " AND a.RefPays = :RefPays";
            $params[':RefPays'] = $Pays;
        }
        if ($Agence !== NULL) {
            $sql .= " AND a.RefAgency = :RefAgency";
            $params[':RefAgency'] = $Agence;
        }
        if ($Caisse !== NULL) {
            $sql .= " AND c.RefCaisse = :RefCaisse";
            $params[':RefCaisse'] = $Caisse;
        }

        $requete = $this->dao->prepare($sql);
        foreach ($params as $key => $value) {
            $requete->bindValue($key, $value, \PDO::PARAM_INT);
        }
        $requete->execute();
        $listeCaisse = $requete->fetchAll();

        foreach ($listeCaisse as &$caisse) {
            $caisse['SoldeRemittance'] = $caisse['SommeVersementRemittance'] - $caisse['SommeRetraitRemittance'];
            $caisse['SoldeDisponible'] = $caisse['SoldeInitialGlobal'] + $caisse['TotalVersement'] 
                - $caisse['TotalRetrait'] - $caisse['TotalSortieCaisse'] + $caisse['TotalFraisTimbre'];
            $caisse['SoldeDisponibleGlobal'] = $caisse['SoldeInitialGlobal'] + $caisse['TotalVersement'] 
                - $caisse['TotalRetrait'] - $caisse['TotalSortieCaisse'] + $caisse['SoldeRemittance'] + $caisse['TotalFraisTimbre'];
        }

        return $listeCaisse;
    }

    public function DeleteOperations($id)
    {
        // Optimisation de l'UPDATE en minimisant les colonnes modifiées
        $requete = $this->dao->prepare("
            UPDATE TbleOperations 
            SET Reset_Id = :RefUsers,
                Reset_At = NOW()
            WHERE RefOperations = :RefOperations
        ");
        
        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->bindValue(':RefOperations', $id, \PDO::PARAM_INT);
        
        // Exécution directe sans préparation de la date
        return $requete->execute();
    }

    public function sommeRetraitPeriode($debut = NULL, $fin = NULL, $Agence = NULL, $produit = NULL)
    {
        if (!empty($debut) && !empty($fin) && !empty($Agence) && !empty($produit)) {
            $SommeRetraitPeriode = $this->dao->prepare("SELECT SUM(MontantVersement) AS TotalPeriodeRetrait FROM TbleOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse  INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE  TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND  date(TbleOperations.Approve2_Time) BETWEEN '$debut' AND '$fin'   AND TbleAgency.RefAgency=:Agence AND  (TbleOperations.RefType=2) AND TbleOperations.RefProduit=:RefProduit");
            $SommeRetraitPeriode->bindValue(':Agence', $Agence, \PDO::PARAM_INT);
            $SommeRetraitPeriode->bindValue(':RefProduit', $produit, \PDO::PARAM_INT);
            $SommeRetraitPeriode->execute();
            $DataSomnmeRetrait = $SommeRetraitPeriode->fetch();
            if ($DataSomnmeRetrait['TotalPeriodeRetrait'] == NULL) {
                return 0;
            }
            return $DataSomnmeRetrait['TotalPeriodeRetrait'];
        } else {
            $SommeRetraitPeriode = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalPeriodeRetrait FROM TbleOperations  INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse  WHERE  TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleChmod.RefUsers=:RefUsers AND  (TbleOperations.RefType=2)');
            $SommeRetraitPeriode->bindValue(':jour', date('Y-m-d'), \PDO::PARAM_STR);
            $SommeRetraitPeriode->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $SommeRetraitPeriode->execute();
            $DataSomnmeRetrait = $SommeRetraitPeriode->fetch();
            if ($DataSomnmeRetrait['TotalPeriodeRetrait'] == NULL) {
                return 0;
            }
            return $DataSomnmeRetrait['TotalPeriodeRetrait'];
        }
    }

    public function sommeVersementPeriode($debut = NULL, $fin = NULL, $Agence = NULL, $produit = NULL)
    {
        if (!empty($debut) && !empty($fin) && !empty($Agence) && !empty($produit)) {
            $requete = $this->dao->prepare("SELECT SUM(MontantVersement) AS TotalPeriodeVersement FROM TbleOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse  INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND  date(TbleOperations.Approve2_Time) BETWEEN '$debut' AND '$fin'   AND TbleAgency.RefAgency=:Agence  AND (TbleOperations.RefType=1)  AND TbleOperations.RefProduit=:RefProduit ");
            $requete->bindValue(':Agence', $Agence, \PDO::PARAM_INT);
            $requete->bindValue(':RefProduit', $produit, \PDO::PARAM_INT);
            $requete->execute();
            $data = $requete->fetch();
            if ($data['TotalPeriodeVersement'] == NULL) {
                return 0;
            }
            return $data['TotalPeriodeVersement'];
        } else {
            $requete = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalPeriodeVersement FROM TbleOperations  INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleChmod.RefUsers=:RefUsers  AND (TbleOperations.RefType=1) ');
            $requete->bindValue(':jour', date('Y-m-d'), \PDO::PARAM_STR);
            $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requete->execute();
            $data = $requete->fetch();
            if ($data['TotalPeriodeVersement'] == NULL) {
                return 0;
            }
            return $data['TotalPeriodeVersement'];
        }
    }

    public function sommeVersementPeriodeAvecAppro($debut = NULL, $fin = NULL, $Agence = NULL)
    {
        if (!empty($debut) && !empty($fin) && !empty($Agence)) {
            $requete = $this->dao->prepare("SELECT SUM(MontantVersement) AS TotalPeriodeVersement FROM TbleOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse  INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND  date(TbleOperations.Approve2_Time) BETWEEN '$debut' AND '$fin'   AND TbleAgency.RefAgency=:Agence  AND (TbleOperations.RefType=1)  ");
            $requete->bindValue(':Agence', $Agence, \PDO::PARAM_INT);
            $requete->execute();
            $data = $requete->fetch();
            if ($data['TotalPeriodeVersement'] == NULL) {
                return 0;
            }
            return $data['TotalPeriodeVersement'];
        } else {
            $requete = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalPeriodeVersement FROM TbleOperations  INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleChmod.RefUsers=:RefUsers  AND (TbleOperations.RefType=1) ');
            $requete->bindValue(':jour', date('Y-m-d'), \PDO::PARAM_STR);
            $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requete->execute();
            $data = $requete->fetch();
            if ($data['TotalPeriodeVersement'] == NULL) {
                return 0;
            }
            return $data['TotalPeriodeVersement'];
        }
    }

    public function sommeRetraitPeriodeAvecSortie($debut = NULL, $fin = NULL, $Agence = NULL)
    {
        if (!empty($debut) && !empty($fin) && !empty($Agence)) {
            $SommeRetraitPeriode = $this->dao->prepare("SELECT SUM(MontantVersement) AS TotalPeriodeRetrait FROM TbleOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse  INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE  TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND  date(TbleOperations.Approve2_Time) BETWEEN '$debut' AND '$fin'   AND TbleAgency.RefAgency=:Agence AND  (TbleOperations.RefType=2 OR  TbleOperations.RefType=4) ");
            $SommeRetraitPeriode->bindValue(':Agence', $Agence, \PDO::PARAM_INT);
            $SommeRetraitPeriode->execute();
            $DataSomnmeRetrait = $SommeRetraitPeriode->fetch();
            if ($DataSomnmeRetrait['TotalPeriodeRetrait'] == NULL) {
                return 0;
            }
            return $DataSomnmeRetrait['TotalPeriodeRetrait'];
        } else {
            $SommeRetraitPeriode = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalPeriodeRetrait FROM TbleOperations  INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse  WHERE  TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleChmod.RefUsers=:RefUsers AND  (TbleOperations.RefType=2 OR TbleOperations.RefType=4)');
            $SommeRetraitPeriode->bindValue(':jour', date('Y-m-d'), \PDO::PARAM_STR);
            $SommeRetraitPeriode->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $SommeRetraitPeriode->execute();
            $DataSomnmeRetrait = $SommeRetraitPeriode->fetch();
            if ($DataSomnmeRetrait['TotalPeriodeRetrait'] == NULL) {
                return 0;
            }
            return $DataSomnmeRetrait['TotalPeriodeRetrait'];
        }
    }

    public function YesterdaySoldeAgence($debut = NULL, $fin = NULL, $Agence = NULL)
    {
        $Solde = $this->dao->prepare("SELECT SoldeCompte FROM TbleCompte WHERE DateSolde=(SELECT MAX(DateSolde) FROM TbleCompte WHERE RefAgency=:RefAgency AND DateSolde <:today)");
        $Solde->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $Solde->bindValue(':today', $fin, \PDO::PARAM_STR);
        $Solde->execute();
        $data = $Solde->fetch();
        if ($data['SoldeCompte'] == NULL) {
            return 0;
        }
        return  $data['SoldeCompte'];
    }

    public function  Versement($debut = NULL, $fin = NULL, $Agence = NULL)
    {
        $dixmille = 0;
        $cinqmille = 0;
        $deuxmille = 0;
        $mille = 0;
        $cinqcent = 0;
        $deuxcentcinq = 0;
        $deuxcent = 0;
        $cent = 0;
        $cinquante = 0;
        $vingtcinq = 0;
        $dix = 0;
        $cinq = 0;
        $un = 0;
        if (!empty($debut) && !empty($fin) && !empty($Agence)) {
            $requete = $this->dao->prepare("SELECT * FROM TbleOperations INNER JOIN TbleBilletage ON TbleBilletage.RefOperations=TbleOperations.RefOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse  INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE date(TbleOperations.Approve2_Time) BETWEEN '$debut' AND '$fin'  AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL  AND TbleAgency.RefAgency=:Agence AND (RefType=1 OR RefType=3)  ");
            $requete->bindValue(':Agence', $Agence, \PDO::PARAM_INT);
            $requete->execute();
            $Versement = $requete->fetchAll();
            $VersementList = [];
        } else {
            $requete = $this->dao->prepare("SELECT * FROM TbleOperations INNER JOIN TbleBilletage ON TbleBilletage.RefOperations=TbleOperations.RefOperations INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:day AND TbleChmod.RefUsers=:RefUsers AND (TbleOperations.RefType=1 OR TbleOperations.RefType=3) ");
            $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requete->bindValue(':day', date('Y-m-d'), \PDO::PARAM_STR);
            $requete->execute();
            $Versement = $requete->fetchAll();
            $VersementList = [];
        }

        foreach ($Versement as $key => $value) {
            $dixmille += intval($value['a2']);
            $cinqmille
                += intval($value['b2']);
            $deuxmille += intval($value['c2']);
            $mille += intval($value['d2']);
            $cinqcent
                += intval($value['e2']);
            $deuxcentcinq
                += intval($value['f2']);
            $deuxcent
                += intval($value['g2']);
            $cent += intval($value['h2']);
            $cinquante
                += intval($value['i2']);
            $vingtcinq
                += intval($value['j2']);
            $dix
                += intval($value['k2']);
            $cinq
                += intval($value['l2']);
            $un
                += intval($value['m2']);
        }
        $VersementList['dixmille'] = $dixmille;
        $VersementList['cinqmille'] = $cinqmille;
        $VersementList['deuxmille'] = $deuxmille;
        $VersementList['mille'] = $mille;
        $VersementList['cinqcent'] = $cinqcent;
        $VersementList['deuxcentcinq'] = $deuxcentcinq;
        $VersementList['deuxcent'] = $deuxcent;
        $VersementList['cent'] = $cent;
        $VersementList['cinquante'] = $cinquante;
        $VersementList['vingtcinq'] = $vingtcinq;
        $VersementList['dix'] = $dix;
        $VersementList['cinq'] = $cinq;
        $VersementList['un'] = $un;
        return $VersementList;
    }

    public function  Retrait($debut = NULL, $fin = NULL, $Agence = NULL)
    {
        $dixmille = 0;
        $cinqmille = 0;
        $deuxmille = 0;
        $mille = 0;
        $cinqcent = 0;
        $deuxcentcinq = 0;
        $deuxcent = 0;
        $cent = 0;
        $cinquante = 0;
        $vingtcinq = 0;
        $dix = 0;
        $cinq = 0;
        $un = 0;
        if (!empty($debut) && !empty($fin) && !empty($Agence)) {
            $requete = $this->dao->prepare("SELECT * FROM TbleOperations INNER JOIN TbleBilletage ON TbleBilletage.RefOperations=TbleOperations.RefOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse  INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE date(TbleOperations.Approve2_Time) BETWEEN '$debut' AND '$fin'  AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND TbleAgency.RefAgency=:Agence AND  (TbleOperations.RefType=2 OR TbleOperations.RefType=4) ");
            $requete->bindValue(':Agence', $Agence, \PDO::PARAM_INT);
            $requete->execute();
            $retrait = $requete->fetchAll();
            $RetraitList = [];
        } else {
            $requete = $this->dao->prepare("SELECT * FROM TbleOperations INNER JOIN TbleBilletage ON TbleBilletage.RefOperations=TbleOperations.RefOperations INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:day AND TbleChmod.RefUsers=:RefUsers AND (TbleOperations.RefType=2 OR TbleOperations.RefType=4)");
            $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requete->bindValue(':day', date('Y-m-d'), \PDO::PARAM_STR);
            $requete->execute();
            $retrait = $requete->fetchAll();
            $RetraitList = [];
        }
        foreach ($retrait as $key => $value) {
            $dixmille += intval($value['a2']);
            $cinqmille
                += intval($value['b2']);
            $deuxmille += intval($value['c2']);
            $mille += intval($value['d2']);
            $cinqcent
                += intval($value['e2']);
            $deuxcentcinq
                += intval($value['f2']);
            $deuxcent
                += intval($value['g2']);
            $cent += intval($value['h2']);
            $cinquante
                += intval($value['i2']);
            $vingtcinq
                += intval($value['j2']);
            $dix
                += intval($value['k2']);
            $cinq
                += intval($value['l2']);
            $un
                += intval($value['m2']);
        }
        $RetraitList['dixmille'] = $dixmille;
        $RetraitList['cinqmille'] = $cinqmille;
        $RetraitList['deuxmille'] = $deuxmille;
        $RetraitList['mille'] = $mille;
        $RetraitList['cinqcent'] = $cinqcent;
        $RetraitList['deuxcentcinq'] = $deuxcentcinq;
        $RetraitList['deuxcent'] = $deuxcent;
        $RetraitList['cent'] = $cent;
        $RetraitList['cinquante'] = $cinquante;
        $RetraitList['vingtcinq'] = $vingtcinq;
        $RetraitList['dix'] = $dix;
        $RetraitList['cinq'] = $cinq;
        $RetraitList['un'] = $un;
        return $RetraitList;
    }

    public function GetBielletageJournal($debut, $fin, $Agence)
    {
        $Versement = $this->Versement($debut, $fin, $Agence);
        $Retrait = $this->Retrait($debut, $fin, $Agence);
        $Biellet['dixmille'] = $Versement['dixmille'] - $Retrait['dixmille'];
        $Biellet['cinqmille'] = $Versement['cinqmille'] - $Retrait['cinqmille'];
        $Biellet['deuxmille'] = $Versement['deuxmille'] - $Retrait['deuxmille'];
        $Biellet['mille'] = $Versement['mille'] - $Retrait['mille'];
        $Biellet['cinqcent'] = $Versement['cinqcent'] - $Retrait['cinqcent'];
        $Biellet['deuxcentcinq'] = $Versement['deuxcentcinq'] - $Retrait['deuxcentcinq'];
        $Biellet['deuxcent'] = $Versement['deuxcent'] - $Retrait['deuxcent'];
        $Biellet['cent'] = $Versement['cent'] - $Retrait['cent'];
        $Biellet['cinquante'] = $Versement['cinquante'] - $Retrait['cinquante'];
        $Biellet['vingtcinq'] = $Versement['vingtcinq'] - $Retrait['vingtcinq'];
        $Biellet['dix'] = $Versement['dix'] - $Retrait['dix'];
        $Biellet['cinq'] = $Versement['cinq'] - $Retrait['cinq'];
        $Biellet['un'] = $Versement['un'] - $Retrait['un'];
        return $Biellet;
    }

    public function ValidateOperations()
    {
        $validate = date('Y-m-d H:i:s');
        $requete = $this->dao->prepare("UPDATE TbleOperations SET Validate= 2,DateValidate=:date,RefValidate=:RefUsers,ValidateDate=:validate,SentFromAgency=:SentFromAgency WHERE RefOperations=:RefOperations");
        $requete->bindValue(':RefOperations', $_POST['RefOperations'], \PDO::PARAM_STR);
        $requete->bindValue(':date', $_POST['DateValidate'], \PDO::PARAM_STR);
        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->bindValue(':validate', $validate, \PDO::PARAM_STR);
        $requete->bindValue(':SentFromAgency', $_POST['SentFromAgency'], \PDO::PARAM_INT);
        $requete->execute();
    }

    public function CancelValidate($id)
    {
        $requete = $this->dao->prepare("UPDATE TbleOperations SET Validate= 1,DateValidate=NULL,RefValidate=NULL,ValidateDate=NULL,SentFromAgency=NULL WHERE RefOperations=:RefOperations");
        $requete->bindValue(':RefOperations', $id, \PDO::PARAM_STR);
        $requete->execute();
    }

    public function NbreOperationCaissier($Date, $Caisse)
    {
        $requete = $this->dao->prepare('SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse   WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND DATE(Approve2_Time)=:jour  AND TbleOperations.RefCaisse=:RefCaisse ');
        $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requete->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requete->execute();
        $result = $requete->fetch();
        if ($result['Nbre'] == 0) {
            return 0;
        }
        return $result['Nbre'];
    }

    public function NbreOperationAgence($Agence, $Date)
    {
        $requete = $this->dao->prepare('SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND DATE(Approve2_Time)=:jour AND TbleCaisse.RefAgency=:agence');
        $requete->bindValue(':agence', $Agence, \PDO::PARAM_INT);
        $requete->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requete->execute();
        $result = $requete->fetch();
        if ($result['Nbre'] == 0) {
            return 0;
        }
        return $result['Nbre'];
    }

    public function CaisseAgence($Agence, $Date)
    {
        $requeteAgence = $this->dao->prepare('SELECT * FROM TbleCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleAgency.RefAgency=:RefAgency');
        $requeteAgence->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteAgence->execute();
        $ListeCaisse = $requeteAgence->fetchAll();
        foreach ($ListeCaisse as $key => $value) {

            $ListeCaisse[$key]['SoldeRemittanceVersement'] = $this->SoldeRemittanceVersement($Date, $value['RefCaisse']);
            $ListeCaisse[$key]['SoldeRemittanceRetrait'] = $this->SoldeRemittanceRetrait($Date, $value['RefCaisse']);
            $ListeCaisse[$key]['NbreOperation'] =  $this->NbreOperationCaissier($Date, $value['RefCaisse']);
            $ListeCaisse[$key]['SoldeInitial'] =  $this->SoldeInitialCaisse($Date, $value['RefCaisse']);
            $ListeCaisse[$key]['SoldeInitialGlobal'] =  $this->SoldeInitialCaisseGlobal($Date, $value['RefCaisse']);
            $ListeCaisse[$key]['TotalAppro'] =  $this->TotalApproCaisse($Date, $value['RefCaisse']);
            $ListeCaisse[$key]['TotalVersement'] =  $this->SomnmeVersementCaisse($Date, $value['RefCaisse']);
            $ListeCaisse[$key]['TotalRetrait'] =  $this->SommeRetraitCaisse($Date, $value['RefCaisse']);
            $ListeCaisse[$key]['TotalSortieCaisse'] = $this->TotalSortieCaisse($Date, $value['RefCaisse']);
            $ListeCaisse[$key]['TotalFraisTimbre'] = $this->TotalFraisTimbreCaisse($Date, $value['RefCaisse']);

            $ListeCaisse[$key]['SoldeRemittance'] = $ListeCaisse[$key]['SoldeRemittanceVersement'] - $ListeCaisse[$key]['SoldeRemittanceRetrait'];
            $ListeCaisse[$key]['SoldeDisponible'] =   $ListeCaisse[$key]['SoldeInitialGlobal']  + $ListeCaisse[$key]['TotalVersement'] - $ListeCaisse[$key]['TotalRetrait'] - $ListeCaisse[$key]['TotalSortieCaisse'] + $ListeCaisse[$key]['SoldeRemittance'] + $ListeCaisse[$key]['TotalFraisTimbre'];
        }
        return $ListeCaisse;
    }

    public function CaisseAgenceOptimized($Agence, $Date)
    {
        $sql = "
            SELECT 
                c.RefCaisse,
                c.NameCaisse,
                a.RefAgency,
                a.NameAgency,
                COALESCE(srv.SommeVersementRemittance, 0) as SoldeRemittanceVersement,
                COALESCE(srr.SommeRetraitRemittance, 0) as SoldeRemittanceRetrait,
                COALESCE(noc.NbreOperation, 0) as NbreOperation,
                COALESCE(si.SoldeInitial, 0) as SoldeInitial,
                COALESCE(sig.SoldeInitialGlobal, 0) as SoldeInitialGlobal,
                COALESCE(tac.TotalAppro, 0) as TotalAppro,
                COALESCE(svc.TotalVersement, 0) as TotalVersement,
                COALESCE(src.TotalRetrait, 0) as TotalRetrait,
                COALESCE(tsc.TotalSortieCaisse, 0) as TotalSortieCaisse,
                COALESCE(tft.TotalFraisTimbre, 0) as TotalFraisTimbre
            FROM TbleCaisse c
            INNER JOIN TbleAgency a ON a.RefAgency = c.RefAgency
            LEFT JOIN (
                SELECT 
                    RefCaisse,
                    SUM(MontantTransaction) as SommeVersementRemittance
                FROM TbleRemittance 
                WHERE DATE(Insert_time) = :date
                AND RefType = 1
                AND Reset_Id IS NULL
                GROUP BY RefCaisse
            ) srv ON srv.RefCaisse = c.RefCaisse
            LEFT JOIN (
                SELECT 
                    RefCaisse,
                    SUM(MontantTransaction) as SommeRetraitRemittance
                FROM TbleRemittance 
                WHERE DATE(Insert_time) = :date
                AND RefType = 2
                AND Reset_Id IS NULL
                GROUP BY RefCaisse
            ) srr ON srr.RefCaisse = c.RefCaisse
            LEFT JOIN (
                SELECT 
                    RefCaisse,
                    COUNT(*) as NbreOperation
                FROM TbleOperations 
                WHERE Approve2_Id IS NOT NULL
                AND Reset_Id IS NULL
                AND DATE(Approve2_Time) = :date
                GROUP BY RefCaisse
            ) noc ON noc.RefCaisse = c.RefCaisse
            LEFT JOIN (
                SELECT 
                    RefCaisse,
                    SUM(MontantVersement) as SoldeInitial
                FROM TbleOperations 
                WHERE Approve2_Id IS NOT NULL
                AND Reset_Id IS NULL
                AND DATE(Approve2_Time) = :date
                AND RefType = 1
                AND TypeAppro = 1
                GROUP BY RefCaisse
            ) si ON si.RefCaisse = c.RefCaisse
            LEFT JOIN (
                SELECT 
                    RefCaisse,
                    SUM(MontantVersement) as SoldeInitialGlobal
                FROM TbleOperations 
                WHERE Approve2_Id IS NOT NULL
                AND Reset_Id IS NULL
                AND DATE(Approve2_Time) = :date
                AND RefType = 1
                GROUP BY RefCaisse
            ) sig ON sig.RefCaisse = c.RefCaisse
            LEFT JOIN (
                SELECT 
                    RefCaisse,
                    SUM(MontantVersement) as TotalAppro
                FROM TbleOperations 
                WHERE Approve2_Id IS NOT NULL
                AND Reset_Id IS NULL
                AND DATE(Approve2_Time) = :date
                AND RefType = 3
                GROUP BY RefCaisse
            ) tac ON tac.RefCaisse = c.RefCaisse
            LEFT JOIN (
                SELECT 
                    RefCaisse,
                    SUM(MontantVersement) as TotalVersement
                FROM TbleOperations 
                WHERE Approve2_Id IS NOT NULL
                AND Reset_Id IS NULL
                AND DATE(Approve2_Time) = :date
                AND RefType = 1
                GROUP BY RefCaisse
            ) svc ON svc.RefCaisse = c.RefCaisse
            LEFT JOIN (
                SELECT 
                    RefCaisse,
                    SUM(MontantVersement) as TotalRetrait
                FROM TbleOperations 
                WHERE Approve2_Id IS NOT NULL
                AND Reset_Id IS NULL
                AND DATE(Approve2_Time) = :date
                AND RefType = 2
                GROUP BY RefCaisse
            ) src ON src.RefCaisse = c.RefCaisse
            LEFT JOIN (
                SELECT 
                    RefCaisse,
                    SUM(MontantVersement) as TotalSortieCaisse
                FROM TbleOperations 
                WHERE Approve2_Id IS NOT NULL
                AND Reset_Id IS NULL
                AND DATE(Approve2_Time) = :date
                AND RefType = 4
                GROUP BY RefCaisse
            ) tsc ON tsc.RefCaisse = c.RefCaisse
            LEFT JOIN (
                SELECT 
                    RefCaisse,
                    SUM(MontantVersement) as TotalFraisTimbre
                FROM TbleOperations 
                WHERE Approve2_Id IS NOT NULL
                AND Reset_Id IS NULL
                AND DATE(Approve2_Time) = :date
                AND RefType = 5
                GROUP BY RefCaisse
            ) tft ON tft.RefCaisse = c.RefCaisse
            WHERE a.RefAgency = :refAgency
        ";

        $requete = $this->dao->prepare($sql);
        $requete->bindValue(':date', $Date, \PDO::PARAM_STR);
        $requete->bindValue(':refAgency', $Agence, \PDO::PARAM_INT);
        $requete->execute();
        $ListeCaisse = $requete->fetchAll(\PDO::FETCH_ASSOC);

        // Calculate derived values
        foreach ($ListeCaisse as &$caisse) {
            $caisse['SoldeRemittance'] = $caisse['SoldeRemittanceVersement'] - $caisse['SoldeRemittanceRetrait'];
            $caisse['SoldeDisponible'] = $caisse['SoldeInitialGlobal'] 
                + $caisse['TotalVersement'] 
                - $caisse['TotalRetrait'] 
                - $caisse['TotalSortieCaisse'] 
                + $caisse['SoldeRemittance'] 
                + $caisse['TotalFraisTimbre'];
        }

        return $ListeCaisse;
    }

    public function SoldeRemittanceVersement($Date, $Caisse)
    {
        $requete = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance WHERE DATE(Insert_time)=:jour AND RefCaisse=:RefCaisse AND RefType=1 AND Reset_Id IS NULL');  //AND RefCaisse=:RefCaisse  
        $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requete->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requete->execute();
        $result = $requete->fetch();
        if ($result['SoldeRemittance'] == 0) {
            return 0;
        }
        return $result['SoldeRemittance'];
    }


    public function SoldeRemittanceVersementAgence($Date, $Agence)
    {
        $requete = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE DATE(TbleRemittance.Insert_time)=:jour AND TbleAgency.RefAgency=:RefAgency AND TbleRemittance.RefType=1  AND TbleRemittance.Reset_Id IS NULL');  //AND RefCaisse=:RefCaisse  
        $requete->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requete->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requete->execute();
        $result = $requete->fetch();
        if ($result['SoldeRemittance'] == 0) {
            return 0;
        }
        return $result['SoldeRemittance'];
    }

    public function SoldeRemittanceRetrait($Date, $Caisse)
    {
        $requete = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance WHERE DATE(Insert_time)=:jour AND RefCaisse=:RefCaisse AND RefType=2  AND Reset_Id IS NULL');  //AND RefCaisse=:RefCaisse  
        $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requete->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requete->execute();
        $result = $requete->fetch();
        if ($result['SoldeRemittance'] == 0) {
            return 0;
        }
        return $result['SoldeRemittance'];
    }
    public function SoldeRemittanceRetraitAgence($Date, $Agence)
    {
        $requete = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE DATE(TbleRemittance.Insert_time)=:jour AND TbleAgency.RefAgency=:RefAgency AND TbleRemittance.RefType=2  AND TbleRemittance.Reset_Id IS NULL');  //AND RefCaisse=:RefCaisse  
        $requete->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requete->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requete->execute();
        $result = $requete->fetch();
        if ($result['SoldeRemittance'] == 0) {
            return 0;
        }
        return $result['SoldeRemittance'];
    }



    public function YesterdayReserve($Agence, $date)
    {
        $requeteSoldeInittial = $this->dao->prepare(
            "SELECT SoldeCompte, DateSolde 
         FROM TbleCompte 
         WHERE DateSolde=(SELECT MAX(DateSolde) 
                          FROM TbleCompte 
                          WHERE RefAgency=:RefAgency 
                          AND DateSolde <:today)"
        );
        $requeteSoldeInittial->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':today', $date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();

        // Check if the result is not empty and both SoldeCompte and DateSolde are present
        if (!empty($result) && isset($result['SoldeCompte']) && isset($result['DateSolde'])) {
            // Return both balance and date
            return [
                'SoldeCompte' => $result['SoldeCompte'],
                'DateSolde' => $result['DateSolde']
            ];
        } else {
            // Return a default structure with balance as 0 and no date
            return [
                'SoldeCompte' => 0,
                'DateSolde' => null
            ];
        }
    }


    // public function HasOperationsSinceLastBalance($RefAgency)
    // {
    //     // Fetch the date of the last balance from TbleCompte
    //     $stmtLastBalance = $this->dao->prepare("SELECT MAX(DateSolde) as LastBalanceDate FROM TbleCompte WHERE RefAgency = :RefAgency");
    //     $stmtLastBalance->bindValue(':RefAgency', $RefAgency, \PDO::PARAM_INT);
    //     $stmtLastBalance->execute();
    //     $lastBalanceResult = $stmtLastBalance->fetch();

    //     // If there's no balance at all, we return an error or false to indicate an initial balance is needed
    //     if (!$lastBalanceResult || empty($lastBalanceResult['LastBalanceDate'])) {
    //         return 'Il n’y a aucun solde enregistré pour cette agence. Veuillez enregistrer un solde initial.';
    //     }

    //     $lastBalanceDate = $lastBalanceResult['LastBalanceDate'];

    //     // Now, let's check if there have been operations since that date in TbleOperations
    //     $stmtOperations = $this->dao->prepare("SELECT COUNT(*) as OperationCount FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleAgency.RefAgency = :RefAgency AND Approve2_Time > :LastBalanceDate");
    //     $stmtOperations->bindValue(':RefAgency', $RefAgency, \PDO::PARAM_INT);
    //     $stmtOperations->bindValue(':LastBalanceDate', $lastBalanceDate, \PDO::PARAM_STR);
    //     $stmtOperations->execute();
    //     $operationsResult = $stmtOperations->fetch();

    //     // Now, let's check if there have been operations since that date in TbleRemittance
    //     $stmtRemittance = $this->dao->prepare("SELECT COUNT(*) as OperationCount FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleAgency.RefAgency = :RefAgency AND Insert_time > :LastBalanceDate");
    //     $stmtRemittance->bindValue(':RefAgency', $RefAgency, \PDO::PARAM_INT);
    //     $stmtRemittance->bindValue(':LastBalanceDate', $lastBalanceDate, \PDO::PARAM_STR);
    //     $stmtRemittance->execute();
    //     $remittanceResult = $stmtRemittance->fetch();

    //     // If there have been operations since the last balance, we need to warn the user
    //     if ($operationsResult && $operationsResult['OperationCount'] > 0 || $remittanceResult && $remittanceResult['OperationCount'] > 0) {
    //         return 'Des opérations ont été enregistrées depuis le dernier solde. Veuillez procéder à la clôture de la journée concernée.';
    //     }

    //     // If no operations have occurred since the last balance, we are clear to proceed
    //     return false;
    // }

    public function HasOperationsSinceLastBalance($RefAgency)
    {
        // Utilisation de la fonction YesterdayReserve pour obtenir le dernier solde et la date
        $currentDate = new \DateTime(); // Date d'aujourd'hui
        $yesterdayReserve = $this->YesterdayReserve($RefAgency, $currentDate->format('Y-m-d'));

        if (empty($yesterdayReserve['DateSolde'])) {
            return 'Il n’y a aucun solde enregistré pour cette agence. Veuillez enregistrer un solde initial.';
        }

        $lastBalanceDate = new \DateTime($yesterdayReserve['DateSolde']);
        $lastBalanceAmount = $yesterdayReserve['SoldeCompte']; // Montant du dernier solde
        $interval = $currentDate->diff($lastBalanceDate);

        if ($interval->days > 2) { // Si la différence est de plus de deux jours, retournez un message d'erreur
            return " Le dernier Arrêté de caisse de l'agence date de plus de {$interval->days} jours avec un montant de {$lastBalanceAmount}. Veuillez vérifier et procéder à la clôture.";
        }

        // Définir le début de la journée actuelle
        $startOfCurrentDay = $currentDate->format('Y-m-d 00:00:00');

        // Vérification des opérations depuis la date du dernier solde jusqu'au début de la journée actuelle
        $stmtOperations = $this->dao->prepare("
    SELECT COUNT(*) as OperationCount
    FROM TbleOperations
    INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse = TbleOperations.RefCaisse
    WHERE TbleCaisse.RefAgency = :RefAgency 
    AND TbleOperations.Approve2_Time > :LastBalanceDate
    AND TbleOperations.Approve2_Time < :StartOfCurrentDay
    AND TbleOperations.Reset_Id IS NULL AND TbleOperations.Approve2_Id IS NOT NULL

    ");
        $stmtOperations->bindValue(
            ':RefAgency',
            $RefAgency,
            \PDO::PARAM_INT
        );
        $stmtOperations->bindValue(':LastBalanceDate', $lastBalanceDate->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
        $stmtOperations->bindValue(':StartOfCurrentDay', $startOfCurrentDay, \PDO::PARAM_STR);
        $stmtOperations->execute();
        $operationsResult = $stmtOperations->fetch();

        // Si des opérations ont été enregistrées depuis le dernier solde et avant le début de la journée actuelle, retournez un message d'avertissement
        if ($operationsResult && $operationsResult['OperationCount'] > 0) {
            return 'Des opérations ont été enregistrées depuis le dernier Arrêté de caisse et avant le début de la journée actuelle. Veuillez procéder à la clôture de la journée concernée.';
        }

        // Si aucune opération n'a eu lieu depuis le dernier solde ou que les opérations du jour ont été clôturées, aucun message d'erreur n'est retourné
        return false;
    }



    public function GetLastBalanceDate($RefAgency)
    {
        // Prepare the SQL query to retrieve the latest balance date for the given agency
        $stmt = $this->dao->prepare("SELECT MAX(DateSolde) as LastBalanceDate FROM TbleCompte WHERE RefAgency = :RefAgency");
        $stmt->bindValue(':RefAgency', $RefAgency, \PDO::PARAM_INT);
        $stmt->execute();

        // Fetch the result
        $result = $stmt->fetch();

        // If there's a result, return the date
        if ($result && !empty($result['LastBalanceDate'])) {
            return $result['LastBalanceDate'];
        } else {
            // If there's no record, we can decide to return a default value or false/null
            // Depending on how you want to handle this case in your application logic
            return false; // or return null; or an appropriate default date
        }
    }

    function displayDaysSinceLastDate($lastDateString)
    {
        // Vérifiez d'abord si la chaîne de date est non vide
        if (!empty($lastDateString)) {
            try {
                // Créer un objet DateTime à partir de la chaîne de date
                $lastDate = new DateTime($lastDateString);
                // Obtenir la date actuelle
                $currentDate = new DateTime();
                // Calculer l'intervalle
                $interval = $currentDate->diff($lastDate);

                // Retournez la chaîne de caractères formatée
                return "<small>" . htmlspecialchars(
                    $lastDate->format('Y-m-d'),
                    ENT_QUOTES,
                    'UTF-8'
                ) .
                    "</small> Il y'a " . $interval->days . " jours";
            } catch (\Exception $e) {
                // En cas d'erreur de format de date, retournez une erreur pour être géré plus tard
                return "Date invalide fournie.";
            }
        }
        return "Date non définie."; // Retournez si la date n'est pas définie
    }






    public function SommeDepotAgence($Date, $Agence)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleAgency.RefAgency=:RefAgency AND (TbleOperations.RefType=1)');
        $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        if ($data['TotalVersment'] == 0) {
            return 0;
        }
        return $data['TotalVersment'];
    }
    public function SommeRetraitAgence($Date, $Agence)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleAgency.RefAgency=:RefAgency AND (TbleOperations.RefType=2)');
        $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        if ($data['TotalVersment'] == 0) {
            return 0;
        }
        return $data['TotalVersment'];
    }


    // public function SoldeInitialAgence($Date, $Agence)
    // {
    //     $GetCaisseUsingAgence = $this->dao->prepare('SELECT RefCaisse FROM TbleCaisse WHERE RefAgency=:RefAgency');
    //     $GetCaisseUsingAgence->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
    //     $GetCaisseUsingAgence->execute();
    //     $Caisse = $GetCaisseUsingAgence->fetch();
    //     $Caisse = $Caisse['RefCaisse'];

    //     $requeteSoldeInittial = $this->dao->prepare('SELECT SUM(MontantVersement) AS SoldeInitial FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleOperations.TypeAppro=1  AND TbleOperations.RefType=3 AND TbleOperations.RefCaisse=:RefCaisse ');
    //     $requeteSoldeInittial->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
    //     $requeteSoldeInittial->bindValue(':jour', $Date, \PDO::PARAM_STR);
    //     $requeteSoldeInittial->execute();
    //     $result = $requeteSoldeInittial->fetch();
    //     if ($result['SoldeInitial'] == 0) {
    //         return 0;
    //     }
    //     return $result['SoldeInitial'];
    // }


    public function SoldeInitialAgence($Date, $Agence)
    {
        // Cette requête va chercher directement la somme des montants pour une agence donnée et une date donnée
        // en joignant les tables TbleCaisse et TbleOperations sur RefCaisse
        $requeteSoldeInitial = $this->dao->prepare(
            'SELECT SUM(Op.MontantVersement) AS SoldeInitial 
        FROM TbleOperations AS Op
        INNER JOIN TbleCaisse AS Caisse ON Caisse.RefCaisse = Op.RefCaisse 
        WHERE Op.Approve2_Id IS NOT NULL 
        AND Op.Reset_Id IS NULL 
        AND Op.Approve2_Time = :jour 
        AND Op.TypeAppro = 1 
        AND Op.RefType = 3 
        AND Caisse.RefAgency = :RefAgency'
        );
        $requeteSoldeInitial->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSoldeInitial->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSoldeInitial->execute();

        // On peut utiliser fetchColumn() pour récupérer directement la première colonne du résultat
        $soldeInitial = $requeteSoldeInitial->fetchColumn();

        // Si $soldeInitial est FALSE (aucun résultat trouvé), ou NULL, on retourne 0
        return $soldeInitial ?: 0;
    }






    public function SoldeInitialCaisse($Date, $Caisse)
    {
        $requeteSoldeInittial = $this->dao->prepare('SELECT SUM(MontantVersement) AS SoldeInitial FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleOperations.TypeAppro=1  AND TbleOperations.RefType=3 AND TbleOperations.RefCaisse=:RefCaisse ');
        $requeteSoldeInittial->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
        if ($result['SoldeInitial'] == 0) {
            return 0;
        }
        return $result['SoldeInitial'];
    }
    public function SoldeInitialCaisseGlobal($Date, $Caisse)
    {
        $requeteSoldeInittial = $this->dao->prepare('SELECT SUM(MontantVersement) AS SoldeInitial FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleOperations.RefType=3 AND TbleOperations.RefCaisse=:RefCaisse ');
        $requeteSoldeInittial->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
        if ($result['SoldeInitial'] == 0) {
            return 0;
        }
        return $result['SoldeInitial'];
    }

    public function TotalApproCaisse($Date, $Caisse)
    {
        $requeteSoldeInittial = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalAppro FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleOperations.TypeAppro=2 AND TbleOperations.RefType=3 AND TbleOperations.RefCaisse=:RefCaisse ');
        $requeteSoldeInittial->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
        if ($result['TotalAppro'] == 0) {
            return 0;
        }
        return $result['TotalAppro'];
    }

    public function TotalApproAgenceSansApproInitial($Date, $Agence)
    {
        $requeteSoldeInittial = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalAppro FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE TbleAgency.RefAgency=:RefAgency AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleOperations.RefType=3 AND TbleOperations.TypeAppro=2 ');
        $requeteSoldeInittial->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
        if ($result['TotalAppro'] == 0) {
            return 0;
        }
        return $result['TotalAppro'];
    }
    public function TotalApproAgenceAvecApproInitial($Date, $Agence)
    {
        $requeteSoldeInittial = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalAppro FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE TbleAgency.RefAgency=:RefAgency AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleOperations.RefType=3 AND TbleOperations.TypeAppro=1 ');
        $requeteSoldeInittial->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
        if ($result['TotalAppro'] == 0) {
            return 0;
        }
        return $result['TotalAppro'];
    }

    public function TotalApproAgenceGlobal($Date, $Agence)
    {
        $requeteSoldeInittial = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalAppro FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE TbleAgency.RefAgency=:RefAgency AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleOperations.RefType=3 ');
        $requeteSoldeInittial->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
        if ($result['TotalAppro'] == 0) {
            return 0;
        }
        return $result['TotalAppro'];
    }
    public function TotalSortieCaisse($Date, $Caisse)
    {
        $requeteSoldeInittial = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalAppro FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleOperations.RefType=4 AND TbleOperations.RefCaisse=:RefCaisse ');
        $requeteSoldeInittial->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
        if ($result['TotalAppro'] == 0) {
            return 0;
        }
        return $result['TotalAppro'];
    }

    public function TotalSortieAgence($Date, $Agence)
    {
        $requeteSoldeInittial = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalAppro FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleOperations.RefType=4 AND TbleAgency.RefAgency=:RefAgency ');
        $requeteSoldeInittial->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
        if ($result['TotalAppro'] == 0) {
            return 0;
        }
        return $result['TotalAppro'];
    }

    public function TypeAppro()
    {
        $requete = $this->dao->prepare('SELECT * FROM TbleTypeAppro');
        $requete->execute();
        $result = $requete->fetchAll();
        return $result;
    }

    public function Reserve()
    {
        $time = $_POST['daycloture'] . ' ' . date('H:i:s');
        $requeteAddService = $this->dao->prepare("INSERT INTO TbleCompte(RefAgency,SoldeCompte,DateSolde) VALUES(:RefAgency,:SoldeCompte,:DateSolde)");
        $requeteAddService->bindValue(':RefAgency', $_POST['RefAgency'], \PDO::PARAM_INT);
        $requeteAddService->bindValue(':SoldeCompte', $_POST['ReserveActuelle'], \PDO::PARAM_STR);
        $requeteAddService->bindValue(':DateSolde', $time, \PDO::PARAM_STR);
        $requeteAddService->execute();
    }
    public function SomnmeVersementCaisse($Date, $Caisse)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleOperations.RefCaisse=:RefCaisse AND (TbleOperations.RefType=1)');
        $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        if ($data['TotalVersment'] == 0) {
            return 0;
        }
        return $data['TotalVersment'];
    }
    public function SommeRetraitCaisse($Date, $Caisse)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalRetrait FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleOperations.RefCaisse=:RefCaisse AND (TbleOperations.RefType=2)  ');
        $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        if ($data['TotalRetrait'] == 0) {
            return 0;
        }
        return $data['TotalRetrait'];
    }
    public function CheckDailyClose($Agence, $date)
    {
        $requete = $this->dao->prepare("SELECT * FROM TbleCompte WHERE RefAgency=:RefAgency AND date(DateSolde)=:jour");
        $requete->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requete->bindValue(':jour', $date, \PDO::PARAM_STR);
        $requete->execute();
        $Result = $requete->fetch();
        return $Result;
    }

    public function GetMonthlyClosureStatus($agency, $year, $month)
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = sprintf('%04d-%02d-31', $year, $month);

        $query = $this->dao->prepare("
            SELECT DATE(DateSolde) as closure_date, RefCompte, SoldeCompte
            FROM TbleCompte
            WHERE RefAgency = :RefAgency
            AND DateSolde BETWEEN :startDate AND :endDate
            ORDER BY DateSolde
        ");
        $query->bindValue(':RefAgency', $agency, \PDO::PARAM_INT);
        $query->bindValue(':startDate', $startDate, \PDO::PARAM_STR);
        $query->bindValue(':endDate', $endDate, \PDO::PARAM_STR);
        $query->execute();

        $closures = $query->fetchAll(\PDO::FETCH_ASSOC);

        $status = [];
        foreach ($closures as $closure) {
            $day = date('j', strtotime($closure['closure_date']));
            $status[$day] = [
                'closed' => true,
                'RefCompte' => $closure['RefCompte'],
                'SoldeCompte' => $closure['SoldeCompte']
            ];
        }

        return $status;
    }

    public function GetDaysWithOperations($agency, $year, $month)
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = sprintf('%04d-%02d-31', $year, $month);

        $query = $this->dao->prepare("
            SELECT DISTINCT DATE(Approve2_Time) as op_date
            FROM TbleOperations
            INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse = TbleOperations.RefCaisse
            WHERE TbleCaisse.RefAgency = :RefAgency
            AND Approve2_Time BETWEEN :startDate AND :endDate
            AND Approve2_Id IS NOT NULL
            AND Reset_Id IS NULL
            ORDER BY op_date
        ");
        $query->bindValue(':RefAgency', $agency, \PDO::PARAM_INT);
        $query->bindValue(':startDate', $startDate, \PDO::PARAM_STR);
        $query->bindValue(':endDate', $endDate, \PDO::PARAM_STR);
        $query->execute();

        $days = [];
        $results = $query->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($results as $row) {
            $day = date('j', strtotime($row['op_date']));
            $days[$day] = true;
        }

        return $days;
    }

    public function GetPetiteCaisseDataOptimized($date, $agencies)
    {
        if (empty($agencies)) {
            return [];
        }

        $agencyIds = array_column($agencies, 'RefAgency');
        
        // Build IN clause with named parameters
        $agencyParams = [];
        $agencyPlaceholders = [];
        foreach ($agencyIds as $i => $id) {
            $paramName = ':agency' . $i;
            $agencyParams[$paramName] = $id;
            $agencyPlaceholders[] = $paramName;
        }
        $agencyInClause = implode(',', $agencyPlaceholders);
        
        $sql = "
            SELECT 
                a.RefAgency,
                a.NameAgency,
                COALESCE(srv.SommeVersementRemittance, 0) as SommeDepotRemittance,
                COALESCE(srr.SommeRetraitRemittance, 0) as SommeRetraitRemittance,
                COALESCE(sd.SommeDepot, 0) as SommeDepot,
                COALESCE(sr.SommeRetrait, 0) as SommeRetrait,
                COALESCE(taas.TotalAppro, 0) as TotalAppoAgenceSansApproInitial,
                COALESCE(tsa.TotalSortie, 0) as TotalSortieAgence,
                COALESCE(sft.SommeFraisTimbre, 0) as SommeTimbre,
                COALESCE(taac.TotalAppro, 0) as TotalApproAgenceAvecApproInitial,
                COALESCE(c.RefCompte, 0) as RefCompte,
                c.DateSolde,
                yc.SoldeCompte as YesterdaySoldeCompte,
                yc.DateSolde as YesterdayDateSolde
            FROM TbleAgency a
            LEFT JOIN (
                SELECT 
                    c.RefAgency,
                    SUM(r.MontantTransaction) as SommeVersementRemittance
                FROM TbleRemittance r
                INNER JOIN TbleCaisse c ON c.RefCaisse = r.RefCaisse
                WHERE DATE(r.Insert_time) = :date
                AND r.RefType = 1
                AND r.Reset_Id IS NULL
                GROUP BY c.RefAgency
            ) srv ON srv.RefAgency = a.RefAgency
            LEFT JOIN (
                SELECT 
                    c.RefAgency,
                    SUM(r.MontantTransaction) as SommeRetraitRemittance
                FROM TbleRemittance r
                INNER JOIN TbleCaisse c ON c.RefCaisse = r.RefCaisse
                WHERE DATE(r.Insert_time) = :date
                AND r.RefType = 2
                AND r.Reset_Id IS NULL
                GROUP BY c.RefAgency
            ) srr ON srr.RefAgency = a.RefAgency
            LEFT JOIN (
                SELECT 
                    c.RefAgency,
                    SUM(o.MontantVersement) as SommeDepot
                FROM TbleOperations o
                INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
                WHERE o.Approve2_Id IS NOT NULL
                AND o.Reset_Id IS NULL
                AND DATE(o.Approve2_Time) = :date
                AND o.RefType = 1
                GROUP BY c.RefAgency
            ) sd ON sd.RefAgency = a.RefAgency
            LEFT JOIN (
                SELECT 
                    c.RefAgency,
                    SUM(o.MontantVersement) as SommeRetrait
                FROM TbleOperations o
                INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
                WHERE o.Approve2_Id IS NOT NULL
                AND o.Reset_Id IS NULL
                AND DATE(o.Approve2_Time) = :date
                AND o.RefType = 2
                GROUP BY c.RefAgency
            ) sr ON sr.RefAgency = a.RefAgency
            LEFT JOIN (
                SELECT 
                    c.RefAgency,
                    SUM(o.MontantVersement) as TotalAppro
                FROM TbleOperations o
                INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
                WHERE o.Approve2_Id IS NOT NULL
                AND o.Reset_Id IS NULL
                AND DATE(o.Approve2_Time) = :date
                AND o.RefType = 3
                AND o.TypeAppro = 2
                GROUP BY c.RefAgency
            ) taas ON taas.RefAgency = a.RefAgency
            LEFT JOIN (
                SELECT 
                    c.RefAgency,
                    SUM(o.MontantVersement) as TotalSortie
                FROM TbleOperations o
                INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
                WHERE o.Approve2_Id IS NOT NULL
                AND o.Reset_Id IS NULL
                AND DATE(o.Approve2_Time) = :date
                AND o.RefType = 4
                GROUP BY c.RefAgency
            ) tsa ON tsa.RefAgency = a.RefAgency
            LEFT JOIN (
                SELECT 
                    c.RefAgency,
                    SUM(o.MontantVersement) as SommeFraisTimbre
                FROM TbleOperations o
                INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
                WHERE o.Approve2_Id IS NOT NULL
                AND o.Reset_Id IS NULL
                AND DATE(o.Approve2_Time) = :date
                AND o.RefType = 5
                GROUP BY c.RefAgency
            ) sft ON sft.RefAgency = a.RefAgency
            LEFT JOIN (
                SELECT 
                    c.RefAgency,
                    SUM(o.MontantVersement) as TotalAppro
                FROM TbleOperations o
                INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
                WHERE o.Approve2_Id IS NOT NULL
                AND o.Reset_Id IS NULL
                AND DATE(o.Approve2_Time) = :date
                AND o.RefType = 3
                AND o.TypeAppro = 1
                GROUP BY c.RefAgency
            ) taac ON taac.RefAgency = a.RefAgency
            LEFT JOIN TbleCompte c ON c.RefAgency = a.RefAgency AND DATE(c.DateSolde) = :date
            LEFT JOIN (
                SELECT 
                    RefAgency,
                    SoldeCompte,
                    DateSolde
                FROM TbleCompte
                WHERE (RefAgency, DateSolde) IN (
                    SELECT RefAgency, MAX(DateSolde)
                    FROM TbleCompte
                    WHERE RefAgency IN ($agencyInClause)
                    AND DateSolde < :date
                    GROUP BY RefAgency
                )
            ) yc ON yc.RefAgency = a.RefAgency
            WHERE a.RefAgency IN ($agencyInClause)
        ";

        $requete = $this->dao->prepare($sql);
        $requete->bindValue(':date', $date, \PDO::PARAM_STR);
        
        // Bind agency parameters
        foreach ($agencyParams as $paramName => $value) {
            $requete->bindValue($paramName, $value, \PDO::PARAM_INT);
        }
        
        $requete->execute();
        $results = $requete->fetchAll(\PDO::FETCH_ASSOC);

        // Index results by RefAgency
        $indexed = [];
        foreach ($results as $row) {
            $refAgency = $row['RefAgency'];
            $indexed[$refAgency] = $row;
        }

        // Get caisse data for each agency using optimized method
        $caisseData = [];
        foreach ($agencies as $agency) {
            $refAgency = $agency['RefAgency'];
            $caisseData[$refAgency] = $this->CaisseAgenceOptimized($refAgency, $date);
        }

        // Merge data
        $finalData = [];
        foreach ($agencies as $agency) {
            $refAgency = $agency['RefAgency'];
            $finalData[$refAgency] = array_merge($agency, $indexed[$refAgency] ?? []);
            $finalData[$refAgency]['Afficher'] = $caisseData[$refAgency] ?? [];
            $finalData[$refAgency]['validate'] = !empty($indexed[$refAgency]['RefCompte']) ? $indexed[$refAgency] : null;
            
            // Calculate derived values
            $finalData[$refAgency]['SoldeRemittanceAgence'] = $finalData[$refAgency]['SommeDepotRemittance'] - $finalData[$refAgency]['SommeRetraitRemittance'];
            $finalData[$refAgency]['SommeDepotWithRemittance'] = $finalData[$refAgency]['SommeDepot'] + $finalData[$refAgency]['SommeDepotRemittance'];
            $finalData[$refAgency]['SommeSortieWithRemittance'] = $finalData[$refAgency]['SommeRetrait'] + $finalData[$refAgency]['SommeRetraitRemittance'];
            $finalData[$refAgency]['YesterdayReserve'] = floatval($finalData[$refAgency]['YesterdaySoldeCompte'] ?? 0);
            $finalData[$refAgency]['LastDate'] = $this->displayDaysSinceLastDate($finalData[$refAgency]['YesterdayDateSolde']);
            $finalData[$refAgency]['ReserveActuelle'] = $finalData[$refAgency]['YesterdayReserve'] 
                + $finalData[$refAgency]['SommeDepot'] 
                - $finalData[$refAgency]['SommeRetrait'] 
                + $finalData[$refAgency]['TotalAppoAgenceSansApproInitial'] 
                - $finalData[$refAgency]['TotalSortieAgence'] 
                + $finalData[$refAgency]['SoldeRemittanceAgence'] 
                + $finalData[$refAgency]['SommeTimbre'];
            $finalData[$refAgency]['DayReserve'] = $finalData[$refAgency]['YesterdayReserve'] - $finalData[$refAgency]['TotalApproAgenceAvecApproInitial'];
            
            // Get product data (still separate queries for now)
            $finalData[$refAgency]['SommeDepotProduit'] = $this->SommeDepotProduitAgence($date, $refAgency);
            $finalData[$refAgency]['SommeRetraitProduit'] = $this->SommeRetraitProduitAgence($date, $refAgency);
        }

        return array_values($finalData);
    }
    public function SentFromAgency($Agence)
    {
        $requeteAgence = $this->dao->prepare('SELECT * FROM TbleAgency WHERE RefAgency=:RefAgency');
        $requeteAgence->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteAgence->execute();
        $ListeAgence = $requeteAgence->fetch();
        if (!empty($ListeAgence['NameAgency'])) {
            return $ListeAgence['NameAgency'];
        }
        return '';
    }
    public function SoldeRemittanceVersementAgencePeriode($debut = NULL, $fin = NULL, $Agence = NULL)
    {
        if (!empty($debut) && !empty($fin) && !empty($Agence)) {
            $requete = $this->dao->prepare("SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE date(TbleRemittance.Insert_time) BETWEEN '$debut' AND '$fin' AND TbleAgency.RefAgency=:RefAgency AND TbleRemittance.RefType=1  AND TbleRemittance.Reset_Id IS NULL");  //AND RefCaisse=:RefCaisse  
            $requete->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
            $requete->execute();
            $result = $requete->fetch();
            if ($result['SoldeRemittance'] == 0) {
                return 0;
            }
            return $result['SoldeRemittance'];
        } else {

            $requete = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleRemittance.RefCaisse  WHERE DATE(TbleRemittance.Insert_time)=:jour AND TbleChmod.RefUsers=:RefUsers AND TbleRemittance.RefType=1  AND TbleRemittance.Reset_Id IS NULL');  //AND RefCaisse=:RefCaisse  
            $requete->bindValue(':jour', date('Y-m-d'), \PDO::PARAM_STR);
            $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requete->execute();
            $result = $requete->fetch();
            if ($result['SoldeRemittance'] == 0) {
                return 0;
            }
            return $result['SoldeRemittance'];
        }
    }


    public function SoldeRemittanceRetraitAgencePeriode($debut = NULL, $fin = NULL, $Agence = NULL)
    {
        if (!empty($debut) && !empty($fin) && !empty($Agence)) {
            $requete = $this->dao->prepare("SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE date(TbleRemittance.Insert_time) BETWEEN '$debut' AND '$fin' AND TbleAgency.RefAgency=:RefAgency AND TbleRemittance.RefType=2  AND TbleRemittance.Reset_Id IS NULL");  //AND RefCaisse=:RefCaisse  
            $requete->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
            $requete->execute();
            $result = $requete->fetch();
            if ($result['SoldeRemittance'] == 0) {
                return 0;
            }
            return $result['SoldeRemittance'];
        } else {
            $requete = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleRemittance.RefCaisse  WHERE DATE(TbleRemittance.Insert_time)=:jour AND TbleChmod.RefUsers=:RefUsers AND TbleRemittance.RefType=2  AND TbleRemittance.Reset_Id IS NULL');  //AND RefCaisse=:RefCaisse  
            $requete->bindValue(':jour', date('Y-m-d'), \PDO::PARAM_STR);
            $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requete->execute();
            $result = $requete->fetch();
            if ($result['SoldeRemittance'] == 0) {
                return 0;
            }
            return $result['SoldeRemittance'];
        }
    }
    public function CheckifRetrait($id)
    {
        $query = $this->dao->prepare("SELECT * FROM TbleOperations WHERE RefOperations=:RefOperations AND RefType=2");
        $query->bindValue(':RefOperations', $id, \PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetch();
        return $result;
    }

    public function NewMatch($Ref)
    {
        $check = $this->CheckifRetrait($Ref);
        $id = 0;
        if (!empty($check['RefOperations'])) {
            $string = $check['Remarque'];
            preg_match_all('!\d+!', $string, $matches);
            $id = $matches[0][0];
            if (intval($id) > 0) {
                $query = $this->dao->prepare('SELECT * FROM mytable WHERE Description LIKE \'%' . $id . '%\'');
                $query->execute();
                $data = $query->fetch();
                return $data;
            }
        } else {
            $id = $Ref;
            $query = $this->dao->prepare('SELECT * FROM mytable WHERE Description LIKE \'%' . $id . '%\'');
            $query->execute();
            $data = $query->fetch();
            return $data;
        }
    }
    public function SoldeActuelleCaisse($Date, $Caisse)
    {
        $SoldeInitial =  $this->SoldeInitialCaisse($Date, $Caisse);
        $SoldeInitialGlobal =  $this->SoldeInitialCaisseGlobal($Date, $Caisse);
        $TotalAppro =  $this->TotalApproCaisse($Date, $Caisse);
        $TotalVersement =  $this->SomnmeVersementCaisse($Date, $Caisse);
        $TotalRetrait =  $this->SommeRetraitCaisse($Date, $Caisse);
        $TotalSortieCaisse = $this->TotalSortieCaisse($Date, $Caisse);
        $SommeVersementRemittance = $this->SoldeRemittanceVersement($Date, $Caisse);
        $SommeRetraitRemittance = $this->SoldeRemittanceRetrait($Date, $Caisse);
        $SoldeRemittance = $SommeVersementRemittance - $SommeRetraitRemittance;
        $SoldeDisponible =   $SoldeInitialGlobal  + $TotalVersement - $TotalRetrait - $TotalSortieCaisse;
        $SoldeDisponibleGlobal =   $SoldeInitialGlobal  + $TotalVersement - $TotalRetrait - $TotalSortieCaisse + $SoldeRemittance;
        return $SoldeDisponibleGlobal;
    }


    public function CaisseAgencePerformance($Agence, $debut, $fin)
    {
        $requeteAgence = $this->dao->prepare('SELECT * FROM TbleCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleAgency.RefAgency=:RefAgency');
        $requeteAgence->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteAgence->execute();
        $ListeCaisse = $requeteAgence->fetchAll();
        foreach ($ListeCaisse as $key => $value) {
            $ListeCaisse[$key]['NbreOperation'] =  $this->NbreOperationCaissierPerformance($debut, $fin, $value['RefCaisse']);
            $ListeCaisse[$key]['NbreDepot'] =  $this->NbreDepotCaissierPerformance($debut, $fin, $value['RefCaisse']);
            $ListeCaisse[$key]['NbreRetrait'] =  $this->NbreRetraitCaissierPerformance($debut, $fin, $value['RefCaisse']);
            $ListeCaisse[$key]['SommeDepotProduitCaisse'] =  $this->SommeDepotProduitCaisse($debut, $fin, $value['RefCaisse']);
            $ListeCaisse[$key]['SommeRetraitProduitCaisse'] =  $this->SommeRetraitProduitCaisse($debut, $fin, $value['RefCaisse']);
            $ListeCaisse[$key]['TotalVersement'] =  $this->SomnmeVersementCaissePerfomance($debut, $fin, $value['RefCaisse']);
            $ListeCaisse[$key]['TotalRetrait'] =  $this->SommeRetraitCaissePerformance($debut, $fin, $value['RefCaisse']);

            $ListeCaisse[$key]['NbreAnnulation'] =  $this->NbreOperationCaissierPerformanceCanceled($debut, $fin, $value['RefCaisse']);
        }
        if (empty($ListeCaisse)) {
            return 0;
        }
        return $ListeCaisse;
    }


    public function SomnmeVersementCaissePerfomance($debut, $fin, $Caisse)
    {
        $requeteRemittance = $this->dao->prepare("SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse WHERE RefType=1 AND Reset_Id IS NULL AND TbleRemittance.RefCaisse=:RefCaisse AND date(TbleRemittance.Insert_time) BETWEEN '$debut' AND '$fin'");
        $requeteRemittance->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteRemittance->execute();
        $dataRemittance = $requeteRemittance->fetch();
        $requeteSUm = $this->dao->prepare("SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL  AND date(TbleOperations.Approve2_Time) BETWEEN '$debut' AND '$fin'  AND TbleOperations.RefCaisse=:RefCaisse AND (TbleOperations.RefType=1)");
        $requeteSUm->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        if (empty($data['TotalVersment']) && empty($dataRemittance['SoldeRemittance'])) {
            return 0;
        }
        return $data['TotalVersment'] + $dataRemittance['SoldeRemittance'];
    }
    public function SommeRetraitCaissePerformance($debut, $fin, $Caisse)
    {
        $requeteRemittance = $this->dao->prepare("SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse WHERE RefType=2 AND Reset_Id IS NULL AND TbleRemittance.RefCaisse=:RefCaisse AND date(TbleRemittance.Insert_time) BETWEEN '$debut' AND '$fin'");
        $requeteRemittance->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteRemittance->execute();
        $dataRemittance = $requeteRemittance->fetch();

        $requeteSUm = $this->dao->prepare("SELECT SUM(MontantVersement) AS TotalRetrait FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND date(TbleOperations.Approve2_Time) BETWEEN '$debut' AND '$fin' AND TbleOperations.RefCaisse=:RefCaisse AND (TbleOperations.RefType=2)  ");
        $requeteSUm->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        if (empty($data['TotalRetrait']) && empty($dataRemittance['SoldeRemittance'])) {
            return 0;
        }
        return $data['TotalRetrait'] + $dataRemittance['SoldeRemittance'];
    }

    public function NbreOperationAgencePerformance($Agence, $debut, $fin)
    {

        $requeteRemittance = $this->dao->prepare("SELECT COUNT(RefRemittance) AS Nbre FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse WHERE TbleRemittance.Reset_Id IS NULL AND date(TbleRemittance.Insert_time) BETWEEN '$debut' AND '$fin' AND TbleCaisse.RefAgency=:agence");
        $requeteRemittance->bindValue(':agence', $Agence, \PDO::PARAM_INT);
        $requeteRemittance->execute();
        $dataRemittance = $requeteRemittance->fetch();

        $requete = $this->dao->prepare("SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE (TbleOperations.Reftype=1 OR TbleOperations.Reftype=2 )  AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND date(TbleOperations.Approve2_Time) BETWEEN '$debut' AND '$fin' AND TbleCaisse.RefAgency=:agence");
        $requete->bindValue(':agence', $Agence, \PDO::PARAM_INT);
        $requete->execute();
        $result = $requete->fetch();
        if (empty($result['Nbre']) && empty($dataRemittance['Nbre'])) {
            return 0;
        }
        return $result['Nbre'] + $dataRemittance['Nbre'];
    }

    public function NbreOperationCaissierPerformance($debut, $fin, $Caisse)
    {
        $requeteRemittance = $this->dao->prepare("SELECT COUNT(RefRemittance) AS Nbre FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse WHERE TbleRemittance.Reset_Id IS NULL AND date(TbleRemittance.Insert_time) BETWEEN '$debut' AND '$fin' AND TbleRemittance.RefCaisse=:RefCaisse");
        $requeteRemittance->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteRemittance->execute();
        $dataRemittance = $requeteRemittance->fetch();

        $requete = $this->dao->prepare("SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse   WHERE (TbleOperations.Reftype=1 OR TbleOperations.Reftype=2 )  AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND date(TbleOperations.Approve2_Time) BETWEEN '$debut' AND '$fin'  AND TbleOperations.RefCaisse=:RefCaisse ");
        $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requete->execute();
        $result = $requete->fetch();
        if (empty($result['Nbre']) && empty($dataRemittance['Nbre'])) {
            return 0;
        }
        return $result['Nbre'] + $dataRemittance['Nbre'];
    }



    public function NbreDepotCaissierPerformance($debut, $fin, $Caisse)
    {
        $requeteRemittance = $this->dao->prepare("SELECT COUNT(RefRemittance) AS Nbre FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse WHERE TbleRemittance.Reset_Id IS NULL AND RefType=1 AND date(TbleRemittance.Insert_time) BETWEEN '$debut' AND '$fin' AND TbleRemittance.RefCaisse=:RefCaisse");
        $requeteRemittance->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteRemittance->execute();
        $dataRemittance = $requeteRemittance->fetch();
        $requete = $this->dao->prepare("SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.RefType=1 AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND date(TbleOperations.Approve2_Time) BETWEEN '$debut' AND '$fin'  AND TbleOperations.RefCaisse=:RefCaisse ");
        $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requete->execute();
        $result = $requete->fetch();
        if (empty($result['Nbre']) && empty($dataRemittance['Nbre'])) {
            return 0;
        }
        return $result['Nbre'] + $dataRemittance['Nbre'];
    }


    public function NbreRetraitCaissierPerformance($debut, $fin, $Caisse)
    {
        $requeteRemittance = $this->dao->prepare("SELECT COUNT(RefRemittance) AS Nbre FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse WHERE TbleRemittance.Reset_Id IS NULL AND RefType=2 AND date(TbleRemittance.Insert_time) BETWEEN '$debut' AND '$fin' AND TbleRemittance.RefCaisse=:RefCaisse");
        $requeteRemittance->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteRemittance->execute();
        $dataRemittance = $requeteRemittance->fetch();
        $requete = $this->dao->prepare("SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse  WHERE TbleOperations.RefType=2 AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND date(TbleOperations.Approve2_Time) BETWEEN '$debut' AND '$fin'  AND TbleOperations.RefCaisse=:RefCaisse ");
        $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requete->execute();
        $result = $requete->fetch();
        if (empty($result['Nbre']) && empty($dataRemittance['Nbre'])) {
            return 0;
        }
        return $result['Nbre'] + $dataRemittance['Nbre'];
    }

    public function DeleteSolde($RefSolde)
    {
        $requete = $this->dao->prepare('DELETE FROM TbleSolde WHERE RefSolde=:RefSolde');
        $requete->bindValue(':RefSolde', $RefSolde, \PDO::PARAM_INT);
        $requete->execute();
    }

    public function CancelFermeture($id, $agency, $day)
    {
        $requete = $this->dao->prepare("DELETE FROM TbleCompte WHERE RefCompte=:RefCompte ");
        $requete->bindValue(':RefCompte', $id, \PDO::PARAM_STR);
        $requete->execute();

        $query = $this->dao->prepare("SELECT * FROM TbleCaisse WHERE RefAgency=:RefAgency");
        $query->bindValue(':RefAgency', $agency, \PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetchAll();

        //for each caisse get the  solde fron TbleSolde  for the day

        foreach ($result as $caisse) {
            $query = $this->dao->prepare("SELECT * FROM TbleSolde WHERE RefCaisse=:RefCaisse AND date(DateSolde)=:DateSolde");
            $query->bindValue(':RefCaisse', $caisse['RefCaisse'], \PDO::PARAM_INT);
            $query->bindValue(':DateSolde', $day, \PDO::PARAM_STR);
            $query->execute();
            $result = $query->fetch();
            if (!empty($result)) {
                $this->DeleteSolde($result['RefSolde']);
            }
        }
    }


    public function ArreterSingleCaisse($Caisse, $Date)
    {
        $SoldeRemittanceVersement = $this->SoldeRemittanceVersement($Date, $Caisse);
        $SoldeRemittanceRetrait = $this->SoldeRemittanceRetrait($Date, $Caisse);
        $NbreOperation =  $this->NbreOperationCaissier($Date, $Caisse);
        $SoldeInitial =  $this->SoldeInitialCaisse($Date, $Caisse);
        $SoldeInitialGlobal =  $this->SoldeInitialCaisseGlobal($Date, $Caisse);
        $TotalAppro =  $this->TotalApproCaisse($Date, $Caisse);
        $TotalVersement =  $this->SomnmeVersementCaisse($Date, $Caisse);
        $TotalFraisTimbre =  $this->TotalFraisTimbreCaisse($Date, $Caisse);
        $TotalRetrait =  $this->SommeRetraitCaisse($Date, $Caisse);
        $TotalSortieCaisse = $this->TotalSortieCaisse($Date, $Caisse);
        $SoldeRemittance = $SoldeRemittanceVersement - $SoldeRemittanceRetrait;
        $SoldeDisponible =   $SoldeInitialGlobal + $TotalVersement - $TotalRetrait - $TotalSortieCaisse + $SoldeRemittance + $TotalFraisTimbre;
        return $SoldeDisponible;
    }


    public function SommeDepotProduitAgence($date, $Agence)
    {
        $queryProduit = $this->dao->prepare("SELECT TbleAgency.RefAgency, TbleChmodProduit.RefProduit, TbleProduit.StatutProduit, TbleProduit.NameProduit FROM TbleChmodProduit INNER JOIN TbleProduit ON TbleProduit.RefProduit=TbleChmodProduit.RefProduit INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse = TbleChmodProduit.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency = TbleCaisse.RefAgency WHERE TbleAgency.RefAgency=:RefAgency GROUP BY TbleAgency.RefAgency,TbleChmodProduit.RefProduit");
        $queryProduit->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $queryProduit->execute();
        $resultProduit = $queryProduit->fetchAll();

        $totals = array();

        foreach ($resultProduit as $produit) {
            $total = 0;

            if ($produit['StatutProduit'] == "banque") {
                $query = $this->dao->prepare("SELECT SUM(TbleOperations.MontantVersement) AS Somme FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.RefType=1 AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND TbleOperations.RefProduit=:RefProduit AND date(TbleOperations.Approve2_Time)=:Date AND TbleAgency.RefAgency=:RefAgency");
            } else {
                $query = $this->dao->prepare("SELECT SUM(TbleRemittance.MontantTransaction) AS Somme FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleRemittance.RefType=1 AND TbleRemittance.Reset_Id IS NULL AND TbleRemittance.RefProduit=:RefProduit AND date(TbleRemittance.Insert_time)=:Date AND TbleAgency.RefAgency=:RefAgency");
            }

            $query->bindValue(':RefProduit', $produit['RefProduit'], \PDO::PARAM_INT);
            $query->bindValue(':Date', $date, \PDO::PARAM_STR);
            $query->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
            $query->execute();
            $result = $query->fetch();
            $total = $result['Somme'];

            $totals[$produit['NameProduit']] = $total;
        }
        if ($totals == null) {
            $totals = 0;
        }

        return $totals;
    }

    public function SommeRetraitProduitAgence($date, $Agence)
    {
        $queryProduit = $this->dao->prepare("SELECT TbleAgency.RefAgency, TbleChmodProduit.RefProduit, TbleProduit.StatutProduit, TbleProduit.NameProduit FROM TbleChmodProduit INNER JOIN TbleProduit ON TbleProduit.RefProduit=TbleChmodProduit.RefProduit INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse = TbleChmodProduit.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency = TbleCaisse.RefAgency WHERE TbleAgency.RefAgency=:RefAgency GROUP BY TbleAgency.RefAgency,TbleChmodProduit.RefProduit");
        $queryProduit->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $queryProduit->execute();
        $resultProduit = $queryProduit->fetchAll();

        $totals = array();

        foreach ($resultProduit as $produit) {
            $total = 0;

            if ($produit['StatutProduit'] == "banque") {
                $query = $this->dao->prepare("SELECT SUM(TbleOperations.MontantVersement) AS Somme FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.RefType=2 AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND TbleOperations.RefProduit=:RefProduit AND date(TbleOperations.Approve2_Time)=:Date AND TbleAgency.RefAgency=:RefAgency");
            } else {
                $query = $this->dao->prepare("SELECT SUM(TbleRemittance.MontantTransaction) AS Somme FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleRemittance.RefType=2 AND TbleRemittance.Reset_Id IS NULL AND TbleRemittance.RefProduit=:RefProduit AND date(TbleRemittance.Insert_time)=:Date AND TbleAgency.RefAgency=:RefAgency");
            }

            $query->bindValue(':RefProduit', $produit['RefProduit'], \PDO::PARAM_INT);
            $query->bindValue(':Date', $date, \PDO::PARAM_STR);
            $query->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
            $query->execute();
            $result = $query->fetch();
            $total = $result['Somme'];

            $totals[$produit['NameProduit']] = $total;
        }
        if ($totals == null) {
            $totals = 0;
        }
        return $totals;
    }


    public function SommeDepotProduitCaisse($debut, $fin, $caisse)
    {
        $queryProduit = $this->dao->prepare("SELECT TbleCaisse.RefCaisse , TbleChmodProduit.RefProduit, TbleProduit.StatutProduit, TbleProduit.NameProduit FROM TbleChmodProduit INNER JOIN TbleProduit ON TbleProduit.RefProduit=TbleChmodProduit.RefProduit INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse = TbleChmodProduit.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency = TbleCaisse.RefAgency WHERE TbleCaisse.RefCaisse=:RefCaisse GROUP BY TbleCaisse.RefCaisse,TbleChmodProduit.RefProduit");
        $queryProduit->bindValue(':RefCaisse', $caisse, \PDO::PARAM_INT);
        $queryProduit->execute();
        $resultProduit = $queryProduit->fetchAll();

        $totals = array();

        foreach ($resultProduit as $produit) {
            $total = 0;

            if ($produit['StatutProduit'] == "banque") {
                $query = $this->dao->prepare("SELECT SUM(TbleOperations.MontantVersement) AS Somme FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.RefType=1 AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND TbleOperations.RefProduit=:RefProduit AND date(TbleOperations.Approve2_Time) BETWEEN :Debut AND :Fin AND TbleCaisse.RefCaisse=:RefCaisse");
            } else {
                $query = $this->dao->prepare("SELECT SUM(TbleRemittance.MontantTransaction) AS Somme FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleRemittance.RefType=1 AND TbleRemittance.Reset_Id IS NULL AND TbleRemittance.RefProduit=:RefProduit AND date(TbleRemittance.Insert_time) BETWEEN :Debut AND :Fin AND TbleCaisse.RefCaisse=:RefCaisse");
            }

            $query->bindValue(':RefProduit', $produit['RefProduit'], \PDO::PARAM_INT);
            $query->bindValue(':Debut', $debut, \PDO::PARAM_STR);
            $query->bindValue(':Fin', $fin, \PDO::PARAM_STR);
            $query->bindValue(':RefCaisse', $caisse, \PDO::PARAM_INT);
            $query->execute();
            $result = $query->fetch();
            $total = $result['Somme'];

            $totals[$produit['NameProduit']] = $total;
        }
        if ($totals == null) {
            $totals = 0;
        }
        return $totals;
    }


    public function SommeRetraitProduitCaisse($debut, $fin, $caisse)
    {
        $queryProduit = $this->dao->prepare("SELECT TbleCaisse.RefCaisse , TbleChmodProduit.RefProduit, TbleProduit.StatutProduit, TbleProduit.NameProduit FROM TbleChmodProduit INNER JOIN TbleProduit ON TbleProduit.RefProduit=TbleChmodProduit.RefProduit INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse = TbleChmodProduit.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency = TbleCaisse.RefAgency WHERE TbleCaisse.RefCaisse=:RefCaisse GROUP BY TbleCaisse.RefCaisse,TbleChmodProduit.RefProduit");
        $queryProduit->bindValue(':RefCaisse', $caisse, \PDO::PARAM_INT);
        $queryProduit->execute();
        $resultProduit = $queryProduit->fetchAll();
        $totals = array();

        foreach ($resultProduit as $produit) {
            $total = 0;

            if ($produit['StatutProduit'] == "banque") {
                $query = $this->dao->prepare("SELECT SUM(TbleOperations.MontantVersement) AS Somme FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.RefType=2 AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND TbleOperations.RefProduit=:RefProduit AND date(TbleOperations.Approve2_Time) BETWEEN :Debut AND :Fin AND TbleCaisse.RefCaisse=:RefCaisse");
            } else {
                $query = $this->dao->prepare("SELECT SUM(TbleRemittance.MontantTransaction) AS Somme FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleRemittance.RefType=2 AND TbleRemittance.Reset_Id IS NULL AND TbleRemittance.RefProduit=:RefProduit AND date(TbleRemittance.Insert_time) BETWEEN :Debut AND :Fin AND TbleCaisse.RefCaisse=:RefCaisse");
            }

            $query->bindValue(':RefProduit', $produit['RefProduit'], \PDO::PARAM_INT);
            $query->bindValue(':Debut', $debut, \PDO::PARAM_STR);
            $query->bindValue(':Fin', $fin, \PDO::PARAM_STR);
            $query->bindValue(':RefCaisse', $caisse, \PDO::PARAM_INT);
            $query->execute();
            $result = $query->fetch();
            $total = $result['Somme'];
            $totals[$produit['NameProduit']] = $total;
        }
        if ($totals == null) {
            $totals = 0;
        }

        return $totals;
    }


    public function SommeFraisTimbreAgence($Date, $Agence)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(fraisTimbre) AS TotalVersment FROM TbleOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleAgency.RefAgency=:RefAgency AND (TbleOperations.RefType=1)');
        $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        if ($data['TotalVersment'] == 0) {
            return 0;
        }
        return $data['TotalVersment'];
    }

    public function TotalFraisTimbreCaisse($Date, $Caisse)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(fraisTimbre) AS TotalVersment FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleOperations.RefCaisse=:RefCaisse AND (TbleOperations.RefType=1)');
        $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        if ($data['TotalVersment'] == 0) {
            return 0;
        }
        return $data['TotalVersment'];
    }


    //SELECT SUM(TbleOperations.MontantVersement) AS Somme FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.RefType=1 AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND TbleOperations.RefProduit=1 AND date(TbleOperations.Approve2_Time) BETWEEN '2020-09-01' AND '2020-09-30' AND TbleAgency.RefAgency=1
    public function getLastFiveOperations()
    {
        $query = $this->dao->prepare("SELECT * FROM TbleOperations ORDER BY Approve2_Time DESC LIMIT 5");
        $query->execute();
        $results = $query->fetchAll();
        return $results;
    }

    public function checkMultipleOperations($Nucompte)
    {
        $query = $this->dao->prepare("SELECT COUNT(*) as count FROM TbleOperations WHERE MONTH(Approve2_Time) = MONTH(CURRENT_DATE()) AND YEAR(Approve2_Time) = YEAR(CURRENT_DATE()) AND Nucompte = :Nucompte");
        $query->bindValue(':Nucompte', $Nucompte, \PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetch();
        if ($result['count'] > 1) {
            return "Multiple operations detected for the same account in the current month.";
        }
        return null;
    }

    public function getSingleOperation($RefOperations)
    {
        $query = $this->dao->prepare("SELECT * FROM TbleOperations WHERE RefOperations = :RefOperations");
        $query->bindValue(':RefOperations', $RefOperations, \PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetch();
        return $result;
    }


    public function GetOperationsNonVerifiees()
    {
        $requete = $this->dao->prepare(
            '
    SELECT *
    FROM operations
    INNER JOIN TbleChmod ON TbleChmod.RefCaisse = operations.RefCaisse
    WHERE
        operations.Approve2_Id IS NOT NULL
        AND operations.Reset_Id IS NULL
        AND TbleChmod.RefUsers =:RefUsers
        AND (operations.RefType =1 OR operations.RefType = 2)
        AND operations.Validate = 1
        AND DATEDIFF(NOW(), operations.datePayement) > 3
        AND YEAR(operations.datePayement) = YEAR(NOW())
    ORDER BY operations.datePayement ASC'
        );

        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->execute();

        $data = $requete->fetchAll();

        return $data;
    }


    public function CountOperationsNonVerifiees()
    {
        $requete = $this->dao->prepare(
            '
    SELECT COUNT(*) as nombre_operations
    FROM operations
    INNER JOIN TbleChmod ON TbleChmod.RefCaisse = operations.RefCaisse
    WHERE
        operations.Approve2_Id IS NOT NULL
        AND operations.Reset_Id IS NULL
        AND TbleChmod.RefUsers = :RefUsers
        AND (operations.RefType = 1 OR operations.RefType = 2)
        AND operations.Validate = 1
        AND DATEDIFF(NOW(), operations.datePayement) > 3
        AND YEAR(operations.datePayement) = YEAR(NOW())'
        );

        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->execute();

        $result = $requete->fetchColumn(); // Utilisez fetchColumn pour obtenir la valeur d'une seule colonne

        return $result;
    }



    public function NbreOperationCaissierPerformanceCanceled($debut, $fin, $Caisse)
    {
        $requeteRemittance = $this->dao->prepare("SELECT COUNT(RefRemittance) AS Nbre FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse WHERE TbleRemittance.Reset_Id IS NOT NULL AND date(TbleRemittance.Insert_time) BETWEEN '$debut' AND '$fin' AND TbleRemittance.RefCaisse=:RefCaisse");
        $requeteRemittance->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteRemittance->execute();
        $dataRemittance = $requeteRemittance->fetch();

        $requete = $this->dao->prepare("SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse   WHERE  TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NOT NULL AND date(TbleOperations.Approve2_Time) BETWEEN '$debut' AND '$fin'  AND TbleOperations.RefCaisse=:RefCaisse ");
        $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requete->execute();
        $result = $requete->fetch();
        if (empty($result['Nbre']) && empty($dataRemittance['Nbre'])) {
            return 0;
        }
        return $result['Nbre'] + $dataRemittance['Nbre'];
    }


    public function NbreOperationAgencePerformanceCanceled($Agence, $debut, $fin)
    {

        $requeteRemittance = $this->dao->prepare("SELECT COUNT(RefRemittance) AS Nbre FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse WHERE TbleRemittance.Reset_Id IS NOT NULL AND date(TbleRemittance.Insert_time) BETWEEN '$debut' AND '$fin' AND TbleCaisse.RefAgency=:agence");
        $requeteRemittance->bindValue(':agence', $Agence, \PDO::PARAM_INT);
        $requeteRemittance->execute();
        $dataRemittance = $requeteRemittance->fetch();

        $requete = $this->dao->prepare("SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE  TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS  NOT NULL AND date(TbleOperations.Approve2_Time) BETWEEN '$debut' AND '$fin' AND TbleCaisse.RefAgency=:agence");
        $requete->bindValue(':agence', $Agence, \PDO::PARAM_INT);
        $requete->execute();
        $result = $requete->fetch();
        if (empty($result['Nbre']) && empty($dataRemittance['Nbre'])) {
            return 0;
        }
        return $result['Nbre'] + $dataRemittance['Nbre'];
    }


    public function GetCanceledOperations($debut = null, $fin = null, $Agence = null)
    {
        // Définit $debut à la première journée du mois en cours
        $debut = $debut ?? date('Y-m-01');

        // Définit $fin à la dernière journée du mois en cours
        $fin = $fin ?? date('Y-m-t');

        // Construisez la requête en fonction de la présence de l'agence
        $sql = "SELECT * FROM operations 
            WHERE operations.Approve2_Id IS NOT NULL 
            AND operations.Reset_Id IS NOT NULL 
            AND date(operations.Approve2_Time) BETWEEN :debut AND :fin";

        if ($Agence !== null) {
            $sql .= " AND operations.RefAgency = :Agence";
        }

        $sql .= " AND (operations.RefType = 1 OR operations.RefType = 2 OR operations.RefType = 3 OR operations.RefType = 4) 
              ORDER BY operations.datePayement ASC";

        $requete = $this->dao->prepare($sql);
        $requete->bindValue(':debut', $debut, \PDO::PARAM_STR);
        $requete->bindValue(':fin', $fin, \PDO::PARAM_STR);

        if ($Agence !== null) {
            $requete->bindValue(':Agence', $Agence, \PDO::PARAM_INT);
        }

        $requete->execute();

        $data = $requete->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($data as &$operation) {
            $operation['Debut'] = $debut;
            $operation['Fin'] = $fin;

            $operation['Afficher'] = $this->CaisseAgencePerformance($operation['RefAgency'], $debut, $fin);
            $operation['NbreOP'] = $this->NbreOperationAgencePerformance($operation['RefAgency'], $debut, $fin);
        }

        return $data;
    }

public function queueDeleteOperation($operationId)
{
    $requete = $this->dao->prepare("
        INSERT INTO TbleJobs (
            operation_type,
            operation_id,
            user_id,
            status,
            created_at
        ) VALUES (
            'delete',
            :operation_id,
            :user_id,
            'pending',
            NOW()
        )
    ");

    $requete->bindValue(':operation_id', $operationId, \PDO::PARAM_INT);
    $requete->bindValue(':user_id', $_SESSION['RefUsers'], \PDO::PARAM_INT);
    
    if ($requete->execute()) {
        // Message de succès
        $_SESSION['message'] = [
            'type' => 'success', // Type de message
            'text' => 'L\'opération a été ajoutée à la file d\'attente avec succès.', // Message texte
            'number' => 2 // Code de succès (vous pouvez l'utiliser pour un code d'erreur ou autre)
        ];
    } else {
        // Message d'erreur
        $_SESSION['message'] = [
            'type' => 'error', // Type d'erreur
            'text' => 'Une erreur est survenue lors de l\'ajout de l\'opération à la file d\'attente.', // Message d'erreur
            'number' => 1 // Code d'erreur
        ];
    }
}

private function isOperationClosed($id)
{
    $operation = $this->getSingleOperation($id);
    $day = $operation['Approve2_Time'];
    $agency = $operation['RefAgency'];

    return $this->CheckDailyClose($agency, $day);
}

public function processQueuedOperations()
{
    // Commencer une transaction pour traiter les opérations
    $this->dao->beginTransaction();
    try {
        // Traiter par lots de 50 opérations
        $requete = $this->dao->prepare("
            SELECT id, operation_type, operation_id, user_id FROM TbleJobs 
            WHERE status = 'pending'
            ORDER BY created_at ASC 
            LIMIT 50
        ");
        $requete->execute();
        
        while ($job = $requete->fetch()) {
            try {
                // Marquer comme en cours
                $this->updateJobStatus($job['id'], 'processing');
                
                if ($job['operation_type'] === 'delete') {
                    // Vérifier si l'opération n'est pas verrouillée
                    if (!$this->isOperationClosed($job['operation_id'])) {
                        // Effectuer la suppression
                        $this->performDelete($job['operation_id'], $job['user_id']);
                        $this->updateJobStatus($job['id'], 'completed');
                    } else {
                        $this->updateJobStatus($job['id'], 'failed', 'Opération verrouillée');
                    }
                }
            } catch (\Exception $e) {
                $this->updateJobStatus($job['id'], 'failed', $e->getMessage());
            }
        }

        // Si tout se passe bien, valider la transaction
        $this->dao->commit();
    } catch (\Exception $e) {
        // En cas d'erreur, annuler la transaction
        $this->dao->rollBack();
        throw $e;
    }
}

private function performDelete($operationId, $userId)
{
    // Préparer la requête de suppression
    $requete = $this->dao->prepare("
        UPDATE TbleOperations 
        SET Reset_Id = :user_id,
            Reset_At = NOW()
        WHERE RefOperations = :operation_id
    ");
    
    $requete->bindValue(':user_id', $userId, \PDO::PARAM_INT);
    $requete->bindValue(':operation_id', $operationId, \PDO::PARAM_INT);
    return $requete->execute();
}

private function updateJobStatus($jobId, $status, $error = null)
{
    // Préparer la requête pour mettre à jour le statut du job
    $requete = $this->dao->prepare("
        UPDATE TbleJobs 
        SET status = :status,
            error_message = :error,
            updated_at = NOW()
        WHERE id = :job_id
    ");
    
    $requete->bindValue(':status', $status, \PDO::PARAM_STR);
    $requete->bindValue(':error', $error, \PDO::PARAM_STR);
    $requete->bindValue(':job_id', $jobId, \PDO::PARAM_INT);
    $requete->execute();
}



}
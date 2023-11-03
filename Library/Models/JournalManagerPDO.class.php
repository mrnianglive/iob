<?php

namespace Library\Models;

use \Library\Entities\Journal;

class JournalManagerPDO extends JournalManager
{
    // public function Operations()
    // {
    //     //Old Query before View on SQL $requete = $this->dao->prepare('SELECT * FROM TbleOperations INNER JOIN TbleType ON TbleType.RefType=TbleOperations.RefType INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency LEFT JOIN TbleProduit ON TbleProduit.RefProduit=TbleOperations.RefProduit INNER JOIN TbleUsers ON TbleUsers.Refusers=TbleOperations.Insert_Id  INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleChmod.RefUsers=:RefUsers AND  (TbleOperations.RefType=1 OR TbleOperations.RefType=2  OR TbleOperations.RefType=4) ORDER BY TbleOperations.datePayement ASC ');
    //     $requete = $this->dao->prepare('SELECT * FROM operations  INNER JOIN TbleChmod ON TbleChmod.RefCaisse=operations.RefCaisse WHERE operations.Approve2_Id IS NOT NULL AND operations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleChmod.RefUsers=:RefUsers AND  (operations.RefType=1 OR operations.RefType=2  OR operations.RefType=4) ORDER BY operations.datePayement ASC ');
    //     $requete->bindValue(':jour', date('Y-m-d'), \PDO::PARAM_STR);
    //     $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
    //     $requete->execute();
    //     $data = $requete->fetchAll();
    //     foreach ($data as $key => $value) {
    //         $data[$key]['SentFromAgency'] =  $this->SentFromAgency($value['SentFromAgency']);
    //     }
    //     return $data;
    // }


    public function Operations()
    {
        $query = 'SELECT o.*, c.RefUsers
              FROM operations AS o
              INNER JOIN (
                  SELECT DISTINCT RefCaisse, RefUsers
                  FROM TbleChmod
                  WHERE RefUsers = :RefUsers
              ) AS c ON c.RefCaisse = o.RefCaisse
              WHERE o.Approve2_Id IS NOT NULL
                AND o.Reset_Id IS NULL
                AND o.Approve2_Time = :jour
                AND o.RefType IN (1, 2,3,4)
              ORDER BY o.datePayement ASC';

        $requete = $this->dao->prepare($query);
        $requete->bindValue(':jour', date('Y-m-d'), \PDO::PARAM_STR);
        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->execute();

        $data = $requete->fetchAll();

        foreach ($data as $key => $value) {
            $data[$key]['SentFromAgency'] = $this->SentFromAgency($value['SentFromAgency']);
        }

        return $data;
    }

    public function GetOperations($debut, $fin, $Agence, $produit)
    {
        //Old Query before View on SQL $requete = $this->dao->prepare("SELECT * FROM TbleOperations INNER JOIN TbleType ON TbleType.RefType=TbleOperations.RefType INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency LEFT JOIN TbleProduit ON TbleProduit.RefProduit=TbleOperations.RefProduit INNER JOIN TbleUsers ON TbleUsers.Refusers=TbleOperations.Insert_Id    WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND  date(TbleOperations.Approve2_Time) BETWEEN '$debut' AND '$fin'  AND TbleAgency.RefAgency=:Agence AND  (TbleOperations.RefType=1 OR TbleOperations.RefType=2 OR TbleOperations.RefType=4  ) ORDER BY TbleOperations.datePayement ASC");
        $requete = $this->dao->prepare(" SELECT * FROM operations WHERE operations.Approve2_Id IS NOT NULL AND operations.Reset_Id IS NULL AND  date(operations.Approve2_Time) BETWEEN '$debut' AND '$fin'  AND operations.RefAgency=:Agence AND (operations.RefProduit=:produit) AND (operations.RefType=1 OR operations.RefType=2 OR operations.RefType=3 OR operations.RefType=4  ) ORDER BY operations.datePayement ASC");
        $requete->bindValue(':Agence', $Agence, \PDO::PARAM_INT);
        $requete->bindValue(':produit', $produit, \PDO::PARAM_INT);
        $requete->execute();
        $data = $requete->fetchAll();
        foreach ($data as $key => $value) {
            $data[$key]['Debut'] = $debut;
            $data[$key]['Debut'] = $fin;
            $data[$key]['RefProduit'] = $produit;
        }
        return $data;
    }
    public function  UserCaisse($Date, $Pays = NULL, $Agence = NULL, $Caisse = NULL)
    {
        if ($Pays != NULL && $Agence == NULL && $Caisse == NULL) {
            $requete = $this->dao->prepare("SELECT * FROM TbleCaisse INNER JOIN TbleAgency ON
                TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleCaisse.RefCaisse WHERE
                TbleChmod.RefUsers=:RefUsers AND TbleAgency.RefPays=:RefPays");
            $requete->bindValue(':RefPays', $Pays, \PDO::PARAM_INT);
        } elseif ($Pays != NULL && $Agence != NULL && $Caisse == NULL) {
            $requete = $this->dao->prepare("SELECT * FROM TbleCaisse INNER JOIN TbleAgency ON
                TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleCaisse.RefCaisse WHERE
                TbleChmod.RefUsers=:RefUsers AND TbleAgency.RefPays=:RefPays AND TbleAgency.RefAgency=:RefAgency");
            $requete->bindValue(':RefPays', $Pays, \PDO::PARAM_INT);
            $requete->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        } elseif ($Pays != NULL && $Agence != NULL && $Caisse != NULL) {
            $requete = $this->dao->prepare("SELECT * FROM TbleCaisse INNER JOIN TbleAgency ON
                TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleCaisse.RefCaisse WHERE
                TbleChmod.RefUsers=:RefUsers AND TbleAgency.RefPays=:RefPays AND TbleAgency.RefAgency=:RefAgency AND TbleCaisse.RefCaisse=:RefCaisse");
            $requete->bindValue(':RefPays', $Pays, \PDO::PARAM_INT);
            $requete->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
            $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        } else {
            $requete = $this->dao->prepare("SELECT * FROM TbleCaisse INNER JOIN TbleAgency ON
                TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleCaisse.RefCaisse WHERE
                TbleChmod.RefUsers=:RefUsers");
        }

        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->execute();
        $listeCaisse = $requete->fetchAll();

        foreach ($listeCaisse as $key => $value) {
            $listeCaisse[$key]['SoldeInitial'] = $this->SoldeInitialCaisse($Date, $value['RefCaisse']);
            $listeCaisse[$key]['SoldeInitialGlobal'] = $this->SoldeInitialCaisseGlobal($Date, $value['RefCaisse']);
            $listeCaisse[$key]['TotalAppro'] = $this->TotalApproCaisse($Date, $value['RefCaisse']);
            $listeCaisse[$key]['TotalVersement'] = $this->SomnmeVersementCaisse($Date, $value['RefCaisse']);
            $listeCaisse[$key]['TotalRetrait'] = $this->SommeRetraitCaisse($Date, $value['RefCaisse']);
            $listeCaisse[$key]['TotalSortieCaisse'] = $this->TotalSortieCaisse($Date, $value['RefCaisse']);
            $listeCaisse[$key]['SommeVersementRemittance'] = $this->SoldeRemittanceVersement($Date, $value['RefCaisse']);
            $listeCaisse[$key]['SommeRetraitRemittance'] = $this->SoldeRemittanceRetrait($Date, $value['RefCaisse']);
            $listeCaisse[$key]['SoldeRemittance'] = $listeCaisse[$key]['SommeVersementRemittance'] -
                $listeCaisse[$key]['SommeRetraitRemittance'];

            $ListeCaisse[$key]['TotalFraisTimbre'] = $this->TotalFraisTimbreCaisse($Date, $value['RefCaisse']);



            $listeCaisse[$key]['SoldeDisponible'] = $listeCaisse[$key]['SoldeInitialGlobal'] + $listeCaisse[$key]['TotalVersement']
                - $listeCaisse[$key]['TotalRetrait'] - $listeCaisse[$key]['TotalSortieCaisse'] + $ListeCaisse[$key]['TotalFraisTimbre'];

            $listeCaisse[$key]['SoldeDisponibleGlobal'] = $listeCaisse[$key]['SoldeInitialGlobal'] +
                $listeCaisse[$key]['TotalVersement'] - $listeCaisse[$key]['TotalRetrait'] - $listeCaisse[$key]['TotalSortieCaisse'] +
                $listeCaisse[$key]['SoldeRemittance'] +   $ListeCaisse[$key]['TotalFraisTimbre'];
        }
        return $listeCaisse;
    }


    public function DeleteOperations($id)
    {
        $today = date("Y-m-d H:i:s");
        $requete = $this->dao->prepare("UPDATE TbleOperations SET Reset_Id=:RefUsers,Reset_At=:day WHERE RefOperations=:RefOperations");
        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->bindValue(':day', $today, \PDO::PARAM_STR);
        $requete->bindValue(':RefOperations', $id, \PDO::PARAM_INT);
        $requete->execute();
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


    public function HasOperationsSinceLastBalance($RefAgency)
    {
        // Fetch the date of the last balance from TbleCompte
        $stmtLastBalance = $this->dao->prepare("SELECT MAX(DateSolde) as LastBalanceDate FROM TbleCompte WHERE RefAgency = :RefAgency");
        $stmtLastBalance->bindValue(':RefAgency', $RefAgency, \PDO::PARAM_INT);
        $stmtLastBalance->execute();
        $lastBalanceResult = $stmtLastBalance->fetch();

        // If there's no balance at all, we return an error or false to indicate an initial balance is needed
        if (!$lastBalanceResult || empty($lastBalanceResult['LastBalanceDate'])) {
            return 'Il n’y a aucun solde enregistré pour cette agence. Veuillez enregistrer un solde initial.';
        }

        $lastBalanceDate = $lastBalanceResult['LastBalanceDate'];

        // Now, let's check if there have been operations since that date in TbleOperations
        $stmtOperations = $this->dao->prepare("SELECT COUNT(*) as OperationCount FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleAgency.RefAgency = :RefAgency AND Approve2_Time > :LastBalanceDate");
        $stmtOperations->bindValue(':RefAgency', $RefAgency, \PDO::PARAM_INT);
        $stmtOperations->bindValue(':LastBalanceDate', $lastBalanceDate, \PDO::PARAM_STR);
        $stmtOperations->execute();
        $operationsResult = $stmtOperations->fetch();

        // If there have been operations since the last balance, we need to warn the user
        if ($operationsResult && $operationsResult['OperationCount'] > 0) {
            return 'Des opérations ont été enregistrées depuis le dernier solde. Veuillez procéder à la clôture de la journée concernée.';
        }

        // If no operations have occurred since the last balance, we are clear to proceed
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
}
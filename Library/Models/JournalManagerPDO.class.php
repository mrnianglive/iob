<?php

namespace Library\Models;

use \Library\Entities\Journal;

class JournalManagerPDO extends JournalManager
{
    public function Operations()
    {
        //Old Query before View on SQL $requete = $this->dao->prepare('SELECT * FROM TbleOperations INNER JOIN TbleType ON TbleType.RefType=TbleOperations.RefType INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency LEFT JOIN TbleProduit ON TbleProduit.RefProduit=TbleOperations.RefProduit INNER JOIN TbleUsers ON TbleUsers.Refusers=TbleOperations.Insert_Id  INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleChmod.RefUsers=:RefUsers AND  (TbleOperations.RefType=1 OR TbleOperations.RefType=2  OR TbleOperations.RefType=4) ORDER BY TbleOperations.datePayement ASC ');
         $requete = $this->dao->prepare('SELECT * FROM operations  INNER JOIN TbleChmod ON TbleChmod.RefCaisse=operations.RefCaisse WHERE operations.Approve2_Id IS NOT NULL AND operations.Reset_Id IS NULL AND Approve2_Time >= :debut AND Approve2_Time <= :fin  AND TbleChmod.RefUsers=:RefUsers AND  (operations.RefType=1 OR operations.RefType=2  OR operations.RefType=4) ORDER BY operations.datePayement ASC ');
         $requete->bindValue(':debut', date('Y-m-d') . ' 00:00:00', \PDO::PARAM_STR);
         $requete->bindValue(':fin', date('Y-m-d') . ' 23:59:59', \PDO::PARAM_STR);
         $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
         $requete->execute();
         $data = $requete->fetchAll();
         foreach ($data as $key => $value) {
             $data[$key]['SentFromAgency'] =  $this->SentFromAgency($value['SentFromAgency']);
         }
         return $data;
    }
    public function GetOperations($debut, $fin, $Agence)
    {
        //Old Query before View on SQL $requete = $this->dao->prepare("SELECT * FROM TbleOperations INNER JOIN TbleType ON TbleType.RefType=TbleOperations.RefType INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency LEFT JOIN TbleProduit ON TbleProduit.RefProduit=TbleOperations.RefProduit INNER JOIN TbleUsers ON TbleUsers.Refusers=TbleOperations.Insert_Id    WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND  date(TbleOperations.Approve2_Time) BETWEEN '$debut' AND '$fin'  AND TbleAgency.RefAgency=:Agence AND  (TbleOperations.RefType=1 OR TbleOperations.RefType=2 OR TbleOperations.RefType=4  ) ORDER BY TbleOperations.datePayement ASC");
         $requete = $this->dao->prepare(" SELECT * FROM operations WHERE operations.Approve2_Id IS NOT NULL AND operations.Reset_Id IS NULL AND  operations.Approve2_Time >= :debut AND operations.Approve2_Time <= :fin  AND operations.RefAgency=:Agence AND  (operations.RefType=1 OR operations.RefType=2 OR operations.RefType=4  ) ORDER BY operations.datePayement ASC");
         $requete->bindValue(':debut', $debut . ' 00:00:00', \PDO::PARAM_STR);
         $requete->bindValue(':fin', $fin . ' 23:59:59', \PDO::PARAM_STR);
         $requete->bindValue(':Agence', $Agence, \PDO::PARAM_INT);
         $requete->execute();
         $data = $requete->fetchAll();
         foreach ($data as $key => $value) {
             $data[$key]['Debut'] =  $debut;
             $data[$key]['Fin'] =  $fin;
         }
         return $data;
    }

    public function UserCaisse($Date)
    {
        $requete = $this->dao->prepare("SELECT * FROM TbleCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleCaisse.RefCaisse WHERE TbleChmod.RefUsers=:RefUsers");
        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->execute();
        $listeCaisse = $requete->fetchAll();
        foreach ($listeCaisse as $key => $value) {
            $listeCaisse[$key]['SoldeInitial'] =  $this->SoldeInitialCaisse($Date, $value['RefCaisse']);
            $listeCaisse[$key]['SoldeInitialGlobal'] =  $this->SoldeInitialCaisseGlobal($Date, $value['RefCaisse']);
            $listeCaisse[$key]['TotalAppro'] =  $this->TotalApproCaisse($Date, $value['RefCaisse']);
            $listeCaisse[$key]['TotalVersement'] =  $this->SomnmeVersementCaisse($Date, $value['RefCaisse']);
            $listeCaisse[$key]['TotalRetrait'] =  $this->SommeRetraitCaisse($Date, $value['RefCaisse']);
            $listeCaisse[$key]['TotalSortieCaisse'] = $this->TotalSortieCaisse($Date, $value['RefCaisse']);
            $listeCaisse[$key]['SommeVersementRemittance'] = $this->SoldeRemittanceVersement($Date, $value['RefCaisse']);
            $listeCaisse[$key]['SommeRetraitRemittance'] = $this->SoldeRemittanceRetrait($Date, $value['RefCaisse']);
            $listeCaisse[$key]['SoldeRemittance'] = $listeCaisse[$key]['SommeVersementRemittance'] - $listeCaisse[$key]['SommeRetraitRemittance'];
            $listeCaisse[$key]['SoldeDisponible'] =   $listeCaisse[$key]['SoldeInitialGlobal']  + $listeCaisse[$key]['TotalVersement'] - $listeCaisse[$key]['TotalRetrait'] - $listeCaisse[$key]['TotalSortieCaisse'];
            $listeCaisse[$key]['SoldeDisponibleGlobal'] =   $listeCaisse[$key]['SoldeInitialGlobal']  + $listeCaisse[$key]['TotalVersement'] - $listeCaisse[$key]['TotalRetrait'] - $listeCaisse[$key]['TotalSortieCaisse'] + $listeCaisse[$key]['SoldeRemittance'];
        }
        return $listeCaisse;
    }
    public function DeleteOperations($id)
    {
        $today = date("Y-m-d H:i:s");
        $requete = $this->dao->prepare("UPDATE TbleOperations SET Reset_Id=:RefUsers,Reset_At=:day WHERE RefOperations=:RefOperations");
        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->bindValue(':day', $today, \PDO::PARAM_INT);
        $requete->bindValue(':RefOperations', $id, \PDO::PARAM_INT);
        $requete->execute();
    }
    public function sommeRetraitPeriode($debut = NULL, $fin = NULL, $Agence = NULL)
    {
        if (!empty($debut) && !empty($fin) && !empty($Agence)) {
            $SommeRetraitPeriode = $this->dao->prepare("SELECT SUM(MontantVersement) AS TotalPeriodeRetrait FROM TbleOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse  INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE  TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND  TbleOperations.Approve2_Time >= :debut AND TbleOperations.Approve2_Time <= :fin   AND TbleAgency.RefAgency=:Agence AND  (TbleOperations.RefType=2) ");
            $SommeRetraitPeriode->bindValue(':debut', $debut . ' 00:00:00', \PDO::PARAM_STR);
            $SommeRetraitPeriode->bindValue(':fin', $fin . ' 23:59:59', \PDO::PARAM_STR);
            $SommeRetraitPeriode->bindValue(':Agence', $Agence, \PDO::PARAM_INT);
            $SommeRetraitPeriode->execute();
            $DataSomnmeRetrait = $SommeRetraitPeriode->fetch();
            return $DataSomnmeRetrait['TotalPeriodeRetrait'];
        } else {
            $todayDebut = date('Y-m-d 00:00:00');
            $todayFin = date('Y-m-d 23:59:59');
            $SommeRetraitPeriode = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalPeriodeRetrait FROM TbleOperations  INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time >= :todayDebut AND Approve2_Time <= :todayFin AND TbleChmod.RefUsers=:RefUsers AND  (TbleOperations.RefType=2)');
            $SommeRetraitPeriode->bindValue(':todayDebut', $todayDebut, \PDO::PARAM_STR);
            $SommeRetraitPeriode->bindValue(':todayFin', $todayFin, \PDO::PARAM_STR);
            $SommeRetraitPeriode->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $SommeRetraitPeriode->execute();
            $DataSomnmeRetrait = $SommeRetraitPeriode->fetch();
            return $DataSomnmeRetrait['TotalPeriodeRetrait'];
        }
    }

    public function sommeVersementPeriode($debut = NULL, $fin = NULL, $Agence = NULL)
    {
        if (!empty($debut) && !empty($fin) && !empty($Agence)) {
            $requete = $this->dao->prepare("SELECT SUM(MontantVersement) AS TotalPeriodeVersement FROM TbleOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse  INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND  TbleOperations.Approve2_Time >= :debut AND TbleOperations.Approve2_Time <= :fin   AND TbleAgency.RefAgency=:Agence AND  (TbleOperations.RefType=1)  ");
            $requete->bindValue(':debut', $debut . ' 00:00:00', \PDO::PARAM_STR);
            $requete->bindValue(':fin', $fin . ' 23:59:59', \PDO::PARAM_STR);
            $requete->bindValue(':Agence', $Agence, \PDO::PARAM_INT);
            $requete->execute();
            $data = $requete->fetch();
            return $data['TotalPeriodeVersement'];
        } else {
            $todayDebut = date('Y-m-d 00:00:00');
            $todayFin = date('Y-m-d 23:59:59');
            $requete = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalPeriodeVersement FROM TbleOperations  INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time >= :todayDebut AND Approve2_Time <= :todayFin AND TbleChmod.RefUsers=:RefUsers AND  (TbleOperations.RefType=1) ');
            $requete->bindValue(':todayDebut', $todayDebut, \PDO::PARAM_STR);
            $requete->bindValue(':todayFin', $todayFin, \PDO::PARAM_STR);
            $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requete->execute();
            $data = $requete->fetch();
            return $data['TotalPeriodeVersement'];
        }
    }


    public function sommeVersementPeriodeAvecAppro($debut = NULL, $fin = NULL, $Agence = NULL)
    {
        if (!empty($debut) && !empty($fin) && !empty($Agence)) {
            $requete = $this->dao->prepare("SELECT SUM(MontantVersement) AS TotalPeriodeVersement FROM TbleOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse  INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND  TbleOperations.Approve2_Time >= :debut AND TbleOperations.Approve2_Time <= :fin   AND TbleAgency.RefAgency=:Agence AND  (TbleOperations.RefType=1)  ");
            $requete->bindValue(':debut', $debut . ' 00:00:00', \PDO::PARAM_STR);
            $requete->bindValue(':fin', $fin . ' 23:59:59', \PDO::PARAM_STR);
            $requete->bindValue(':Agence', $Agence, \PDO::PARAM_INT);
            $requete->execute();
            $data = $requete->fetch();
            return $data['TotalPeriodeVersement'];
        } else {
            $todayDebut = date('Y-m-d 00:00:00');
            $todayFin = date('Y-m-d 23:59:59');
            $requete = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalPeriodeVersement FROM TbleOperations  INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time >= :todayDebut AND Approve2_Time <= :todayFin AND TbleChmod.RefUsers=:RefUsers AND  (TbleOperations.RefType=1) ');
            $requete->bindValue(':todayDebut', $todayDebut, \PDO::PARAM_STR);
            $requete->bindValue(':todayFin', $todayFin, \PDO::PARAM_STR);
            $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requete->execute();
            $data = $requete->fetch();
            return $data['TotalPeriodeVersement'];
        }
    }
    public function sommeRetraitPeriodeAvecSortie($debut = NULL, $fin = NULL, $Agence = NULL)
    {
        if (!empty($debut) && !empty($fin) && !empty($Agence)) {
            $SommeRetraitPeriode = $this->dao->prepare("SELECT SUM(MontantVersement) AS TotalPeriodeRetrait FROM TbleOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse  INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE  TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND  TbleOperations.Approve2_Time >= :debut AND TbleOperations.Approve2_Time <= :fin   AND TbleAgency.RefAgency=:Agence AND  (TbleOperations.RefType=2 OR  TbleOperations.RefType=4) ");
            $SommeRetraitPeriode->bindValue(':debut', $debut . ' 00:00:00', \PDO::PARAM_STR);
            $SommeRetraitPeriode->bindValue(':fin', $fin . ' 23:59:59', \PDO::PARAM_STR);
            $SommeRetraitPeriode->bindValue(':Agence', $Agence, \PDO::PARAM_INT);
            $SommeRetraitPeriode->execute();
            $DataSomnmeRetrait = $SommeRetraitPeriode->fetch();
            return $DataSomnmeRetrait['TotalPeriodeRetrait'];
        } else {
            $todayDebut = date('Y-m-d 00:00:00');
            $todayFin = date('Y-m-d 23:59:59');
            $SommeRetraitPeriode = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalPeriodeRetrait FROM TbleOperations  INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time >= :todayDebut AND Approve2_Time <= :todayFin AND TbleChmod.RefUsers=:RefUsers AND  (TbleOperations.RefType=2 OR TbleOperations.RefType=4)');
            $SommeRetraitPeriode->bindValue(':todayDebut', $todayDebut, \PDO::PARAM_STR);
            $SommeRetraitPeriode->bindValue(':todayFin', $todayFin, \PDO::PARAM_STR);
            $SommeRetraitPeriode->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $SommeRetraitPeriode->execute();
            $DataSomnmeRetrait = $SommeRetraitPeriode->fetch();
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
            $requete = $this->dao->prepare("SELECT * FROM TbleOperations INNER JOIN TbleBilletage ON TbleBilletage.RefOperations=TbleOperations.RefOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse  INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE date(TbleOperations.Approve2_Time) BETWEEN :debut AND :fin  AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL  AND TbleAgency.RefAgency=:Agence AND (RefType=1 OR RefType=3)  ");
            $requete->bindValue(':debut', $debut, \PDO::PARAM_STR);
            $requete->bindValue(':fin', $fin, \PDO::PARAM_STR);
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
            $requete = $this->dao->prepare("SELECT * FROM TbleOperations INNER JOIN TbleBilletage ON TbleBilletage.RefOperations=TbleOperations.RefOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse  INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE date(TbleOperations.Approve2_Time) BETWEEN :debut AND :fin  AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND TbleAgency.RefAgency=:Agence AND  (TbleOperations.RefType=2 OR TbleOperations.RefType=4) ");
            $requete->bindValue(':debut', $debut, \PDO::PARAM_STR);
            $requete->bindValue(':fin', $fin, \PDO::PARAM_STR);
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
        return $result['Nbre'];
    }

    public function NbreOperationAgence($Agence, $Date)
    {
        $requete = $this->dao->prepare('SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND DATE(Approve2_Time)=:jour AND TbleCaisse.RefAgency=:agence');
        $requete->bindValue(':agence', $Agence, \PDO::PARAM_INT);
        $requete->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requete->execute();
        $result = $requete->fetch();
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
            $ListeCaisse[$key]['TotalFraisTimbre'] = $this->SommeFraisTimbreCaisse($Date, $value['RefCaisse']);
            $ListeCaisse[$key]['SoldeRemittance'] = $ListeCaisse[$key]['SoldeRemittanceVersement'] - $ListeCaisse[$key]['SoldeRemittanceRetrait'];
            $ListeCaisse[$key]['SoldeDisponible'] =   $ListeCaisse[$key]['SoldeInitialGlobal']  + $ListeCaisse[$key]['TotalVersement'] - $ListeCaisse[$key]['TotalRetrait'] - $ListeCaisse[$key]['TotalSortieCaisse'] + $ListeCaisse[$key]['SoldeRemittance'];
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
        return $result['SoldeRemittance'] ?? 0;
    }


    public function SoldeRemittanceVersementAgence($Date, $Agence)
    {
        $requete = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE DATE(TbleRemittance.Insert_time)=:jour AND TbleAgency.RefAgency=:RefAgency AND TbleRemittance.RefType=1  AND TbleRemittance.Reset_Id IS NULL');  //AND RefCaisse=:RefCaisse  
        $requete->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requete->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requete->execute();
        $result = $requete->fetch();
        return $result['SoldeRemittance'];
    }

    public function SoldeRemittanceRetrait($Date, $Caisse)
    {
        $requete = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance WHERE DATE(Insert_time)=:jour AND RefCaisse=:RefCaisse AND RefType=2  AND Reset_Id IS NULL');  //AND RefCaisse=:RefCaisse  
        $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requete->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requete->execute();
        $result = $requete->fetch();
        return $result['SoldeRemittance'] ?? 0;
    }
    public function SoldeRemittanceRetraitAgence($Date, $Agence)
    {
        $requete = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE DATE(TbleRemittance.Insert_time)=:jour AND TbleAgency.RefAgency=:RefAgency AND TbleRemittance.RefType=2  AND TbleRemittance.Reset_Id IS NULL');  //AND RefCaisse=:RefCaisse  
        $requete->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requete->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requete->execute();
        $result = $requete->fetch();
        return $result['SoldeRemittance'];
    }

    public function YesterdayReserve($Agence, $date)
    {
        $requeteSoldeInittial = $this->dao->prepare("SELECT SoldeCompte, DateSolde FROM TbleCompte WHERE RefAgency=:RefAgency AND DateSolde=(SELECT MAX(DateSolde) FROM TbleCompte WHERE RefAgency=:RefAgency2 AND DateSolde <:today)");
        $requeteSoldeInittial->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':RefAgency2', $Agence, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':today', $date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
        return $result ?: ['SoldeCompte' => 0, 'DateSolde' => null];
    }
    public function SommeDepotAgence($Date, $Agence)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleAgency.RefAgency=:RefAgency AND (TbleOperations.RefType=1)');
        $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['TotalVersment'] ?? 0;
    }
    public function SommeRetraitAgence($Date, $Agence)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleAgency.RefAgency=:RefAgency AND (TbleOperations.RefType=2)');
        $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['TotalVersment'] ?? 0;
    }
    public function SommeFraisTimbreAgence($Date, $Agence)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(fraisTimbre) AS TotalFraisTimbre FROM TbleOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleAgency.RefAgency=:RefAgency');
        $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['TotalFraisTimbre'] ?? 0;
    }
    public function SommeFraisTimbreCaisse($Date, $Caisse)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(fraisTimbre) AS TotalFraisTimbre FROM TbleOperations WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleOperations.RefCaisse=:RefCaisse');
        $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['TotalFraisTimbre'] ?? 0;
    }
    public function SoldeInitialCaisse($Date, $Caisse)
    {
        $requeteSoldeInittial = $this->dao->prepare('SELECT SUM(MontantVersement) AS SoldeInitial FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleOperations.TypeAppro=1  AND TbleOperations.RefType=3 AND TbleOperations.RefCaisse=:RefCaisse ');
        $requeteSoldeInittial->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
        return $result['SoldeInitial'] ?? 0;
    }
    public function SoldeInitialCaisseGlobal($Date, $Caisse)
    {
        $requeteSoldeInittial = $this->dao->prepare('SELECT SUM(MontantVersement) AS SoldeInitial FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleOperations.RefType=3 AND TbleOperations.RefCaisse=:RefCaisse ');
        $requeteSoldeInittial->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
        return $result['SoldeInitial'] ?? 0;
    }

    public function TotalApproCaisse($Date, $Caisse)
    {
        $requeteSoldeInittial = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalAppro FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleOperations.TypeAppro=2 AND TbleOperations.RefType=3 AND TbleOperations.RefCaisse=:RefCaisse ');
        $requeteSoldeInittial->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
        return $result['TotalAppro'] ?? 0;
    }

    public function TotalApproAgenceSansApproInitial($Date, $Agence)
    {
        $requeteSoldeInittial = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalAppro FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE TbleAgency.RefAgency=:RefAgency AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleOperations.RefType=3 AND TbleOperations.TypeAppro=2 ');
        $requeteSoldeInittial->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
        return $result['TotalAppro'] ?? 0;
    }
    public function TotalApproAgenceAvecApproInitial($Date, $Agence)
    {
        $requeteSoldeInittial = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalAppro FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE TbleAgency.RefAgency=:RefAgency AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleOperations.RefType=3 AND TbleOperations.TypeAppro=1 ');
        $requeteSoldeInittial->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
        return $result['TotalAppro'] ?? 0;
    }

    public function TotalApproAgenceGlobal($Date, $Agence)
    {
        $requeteSoldeInittial = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalAppro FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE TbleAgency.RefAgency=:RefAgency AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleOperations.RefType=3 ');
        $requeteSoldeInittial->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
        return $result['TotalAppro'];
    }
    public function TotalSortieCaisse($Date, $Caisse)
    {
        $requeteSoldeInittial = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalAppro FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleOperations.RefType=4 AND TbleOperations.RefCaisse=:RefCaisse ');
        $requeteSoldeInittial->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
        return $result['TotalAppro'] ?? 0;
    }

    public function TotalSortieAgence($Date, $Agence)
    {
        $requeteSoldeInittial = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalAppro FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleOperations.RefType=4 AND TbleAgency.RefAgency=:RefAgency ');
        $requeteSoldeInittial->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
        return $result['TotalAppro'] ?? 0;
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
        return $data['TotalVersment'] ?? 0;
    }
    public function SommeRetraitCaisse($Date, $Caisse)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalRetrait FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleOperations.RefCaisse=:RefCaisse AND (TbleOperations.RefType=2)  ');
        $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['TotalRetrait'] ?? 0;
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

    public function IsYesterdayClosed($refAgency, $date)
    {
        $yesterday = date('Y-m-d', strtotime($date . ' -1 day'));
        $requete = $this->dao->prepare("SELECT RefCompte FROM TbleCompte WHERE RefAgency=:RefAgency AND DATE(DateSolde)=:yesterday");
        $requete->bindValue(':RefAgency', $refAgency, \PDO::PARAM_INT);
        $requete->bindValue(':yesterday', $yesterday, \PDO::PARAM_STR);
        $requete->execute();
        $result = $requete->fetch();
        return !empty($result);
    }
    public function SentFromAgency($Agence)
    {
        $requeteAgence = $this->dao->prepare('SELECT * FROM TbleAgency WHERE RefAgency=:RefAgency');
        $requeteAgence->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteAgence->execute();
        $ListeAgence = $requeteAgence->fetch();
        return $ListeAgence['NameAgency'];
    }
    public function SoldeRemittanceVersementAgencePeriode($debut = NULL, $fin = NULL, $Agence = NULL)
    {
        if (!empty($debut) && !empty($fin) && !empty($Agence)) {
            $requete = $this->dao->prepare("SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE date(TbleRemittance.Insert_time) BETWEEN :debut AND :fin AND TbleAgency.RefAgency=:RefAgency AND TbleRemittance.RefType=1  AND TbleRemittance.Reset_Id IS NULL");  //AND RefCaisse=:RefCaisse  
            $requete->bindValue(':debut', $debut, \PDO::PARAM_STR);
            $requete->bindValue(':fin', $fin, \PDO::PARAM_STR);
            $requete->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
            $requete->execute();
            $result = $requete->fetch();
            return $result['SoldeRemittance'];
        } else {

            $requete = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleRemittance.RefCaisse  WHERE DATE(TbleRemittance.Insert_time)=:jour AND TbleChmod.RefUsers=:RefUsers AND TbleRemittance.RefType=1  AND TbleRemittance.Reset_Id IS NULL');  //AND RefCaisse=:RefCaisse  
            $requete->bindValue(':jour', date('Y-m-d'), \PDO::PARAM_STR);
            $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requete->execute();
            $result = $requete->fetch();
            return $result['SoldeRemittance'];
        }
    }


    public function SoldeRemittanceRetraitAgencePeriode($debut = NULL, $fin = NULL, $Agence = NULL)
    {
        if (!empty($debut) && !empty($fin) && !empty($Agence)) {
            $requete = $this->dao->prepare("SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE date(TbleRemittance.Insert_time) BETWEEN :debut AND :fin AND TbleAgency.RefAgency=:RefAgency AND TbleRemittance.RefType=2  AND TbleRemittance.Reset_Id IS NULL");  //AND RefCaisse=:RefCaisse  
            $requete->bindValue(':debut', $debut, \PDO::PARAM_STR);
            $requete->bindValue(':fin', $fin, \PDO::PARAM_STR);
            $requete->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
            $requete->execute();
            $result = $requete->fetch();
            return $result['SoldeRemittance'];
        } else {
            $requete = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleRemittance.RefCaisse  WHERE DATE(TbleRemittance.Insert_time)=:jour AND TbleChmod.RefUsers=:RefUsers AND TbleRemittance.RefType=2  AND TbleRemittance.Reset_Id IS NULL');  //AND RefCaisse=:RefCaisse  
            $requete->bindValue(':jour', date('Y-m-d'), \PDO::PARAM_STR);
            $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requete->execute();
            $result = $requete->fetch();
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
                $query = $this->dao->prepare('SELECT * FROM mytable WHERE Description LIKE :search');
                $query->bindValue(':search', '%' . $id . '%', \PDO::PARAM_STR);
                $query->execute();
                $data = $query->fetch();
                return $data;
            }
        } else {
            $id = $Ref;
            $query = $this->dao->prepare('SELECT * FROM mytable WHERE Description LIKE :search');
            $query->bindValue(':search', '%' . $id . '%', \PDO::PARAM_STR);
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

            $ListeCaisse[$key]['TotalVersement'] =  $this->SomnmeVersementCaissePerfomance($debut, $fin, $value['RefCaisse']);
            $ListeCaisse[$key]['TotalRetrait'] =  $this->SommeRetraitCaissePerformance($debut, $fin, $value['RefCaisse']);
        }
        return $ListeCaisse;
    }


    public function SomnmeVersementCaissePerfomance($debut, $fin, $Caisse)
    {
        $requeteSUm = $this->dao->prepare("SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL  AND date(TbleOperations.Approve2_Time) BETWEEN :debut AND :fin  AND TbleOperations.RefCaisse=:RefCaisse AND (TbleOperations.RefType=1)");
        $requeteSUm->bindValue(':debut', $debut, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':fin', $fin, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['TotalVersment'];
    }
    public function SommeRetraitCaissePerformance($debut, $fin, $Caisse)
    {
        $requeteSUm = $this->dao->prepare("SELECT SUM(MontantVersement) AS TotalRetrait FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND date(TbleOperations.Approve2_Time) BETWEEN :debut AND :fin AND TbleOperations.RefCaisse=:RefCaisse AND (TbleOperations.RefType=2)  ");
        $requeteSUm->bindValue(':debut', $debut, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':fin', $fin, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['TotalRetrait'];
    }

    public function NbreOperationAgencePerformance($Agence, $debut, $fin)
    {
        $requete = $this->dao->prepare("SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE (TbleOperations.Reftype=1 OR TbleOperations.Reftype=2 )  AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND date(TbleOperations.Approve2_Time) BETWEEN :debut AND :fin AND TbleCaisse.RefAgency=:agence");
        $requete->bindValue(':debut', $debut, \PDO::PARAM_STR);
        $requete->bindValue(':fin', $fin, \PDO::PARAM_STR);
        $requete->bindValue(':agence', $Agence, \PDO::PARAM_INT);
        $requete->execute();
        $result = $requete->fetch();
        return $result['Nbre'];
    }

    public function NbreOperationCaissierPerformance($debut, $fin, $Caisse)
    {
        $requete = $this->dao->prepare("SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse   WHERE (TbleOperations.Reftype=1 OR TbleOperations.Reftype=2 )  AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND date(TbleOperations.Approve2_Time) BETWEEN :debut AND :fin  AND TbleOperations.RefCaisse=:RefCaisse ");
        $requete->bindValue(':debut', $debut, \PDO::PARAM_STR);
        $requete->bindValue(':fin', $fin, \PDO::PARAM_STR);
        $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requete->execute();
        $result = $requete->fetch();
        return $result['Nbre'];
    }



    public function NbreDepotCaissierPerformance($debut, $fin, $Caisse)
    {
        $requete = $this->dao->prepare("SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.RefType=1 AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND date(TbleOperations.Approve2_Time) BETWEEN :debut AND :fin  AND TbleOperations.RefCaisse=:RefCaisse ");
        $requete->bindValue(':debut', $debut, \PDO::PARAM_STR);
        $requete->bindValue(':fin', $fin, \PDO::PARAM_STR);
        $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requete->execute();
        $result = $requete->fetch();
        return $result['Nbre'];
    }


    public function NbreRetraitCaissierPerformance($debut, $fin, $Caisse)
    {
        $requete = $this->dao->prepare("SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse  WHERE TbleOperations.RefType=2 AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND date(TbleOperations.Approve2_Time) BETWEEN :debut AND :fin  AND TbleOperations.RefCaisse=:RefCaisse ");
        $requete->bindValue(':debut', $debut, \PDO::PARAM_STR);
        $requete->bindValue(':fin', $fin, \PDO::PARAM_STR);
        $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requete->execute();
        $result = $requete->fetch();
        return $result['Nbre'];
    }

    public function CancelFermeture($id)
    {
        $requete = $this->dao->prepare("DELETE FROM TbleCompte WHERE RefCompte=:RefCompte ");
        $requete->bindValue(':RefCompte', $id, \PDO::PARAM_STR);
        $requete->execute();
    }

    /**
     * Annule une fermeture de reserve d'agence avec recalcul cascade des jours suivants
     * @param int $id RefCompte a annuler
     */
    public function CancelFermetureWithCascade($id)
    {
        // 1. Recuperer les infos de l'arrete a annuler
        $stmt = $this->dao->prepare("SELECT * FROM TbleCompte WHERE RefCompte = :id");
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $arrete = $stmt->fetch();
        
        if (!$arrete) {
            return; // Arrete non trouve
        }
        
        $dateArrete = date('Y-m-d', strtotime($arrete['DateSolde']));
        $refAgency = $arrete['RefAgency'];
        
        // 2. Demarrer une transaction
        $this->dao->beginTransaction();
        
        try {
            // 3. Supprimer l'arrete
            $delete = $this->dao->prepare("DELETE FROM TbleCompte WHERE RefCompte = :id");
            $delete->bindValue(':id', $id, \PDO::PARAM_INT);
            $delete->execute();
            
            // 4. Recuperer tous les arretes suivants (par date) pour cette agence
            $stmtSuivants = $this->dao->prepare("SELECT * FROM TbleCompte 
                WHERE RefAgency = :agency AND DATE(DateSolde) > :dateArrete
                ORDER BY DateSolde ASC");
            $stmtSuivants->bindValue(':agency', $refAgency, \PDO::PARAM_INT);
            $stmtSuivants->bindValue(':dateArrete', $dateArrete, \PDO::PARAM_STR);
            $stmtSuivants->execute();
            $arretesSuivants = $stmtSuivants->fetchAll();
            
            // 5. Recalculer chaque arrete en cascade
            foreach ($arretesSuivants as $arreteSuivant) {
                $dateSuivant = date('Y-m-d', strtotime($arreteSuivant['DateSolde']));
                
                // Recalculer le solde de la reserve pour ce jour
                $nouveauSolde = $this->calculerSoldeReserveAgence($refAgency, $dateSuivant);
                
                // Mettre a jour le solde
                $update = $this->dao->prepare("UPDATE TbleCompte 
                    SET SoldeCompte = :solde WHERE RefCompte = :refCompte");
                $update->bindValue(':solde', $nouveauSolde, \PDO::PARAM_STR);
                $update->bindValue(':refCompte', $arreteSuivant['RefCompte'], \PDO::PARAM_INT);
                $update->execute();
            }
            
            // 6. Commit la transaction
            $this->dao->commit();
            
        } catch (\Exception $e) {
            $this->dao->rollback();
            throw $e;
        }
    }

    /**
     * Calcule le solde de la reserve d'une agence pour une date donnee
     * @param int $refAgency
     * @param string $date Format Y-m-d
     * @return float Le solde calcule
     */
    private function calculerSoldeReserveAgence($refAgency, $date)
    {
        // Solde veille
        $reserveData = $this->YesterdayReserve($refAgency, $date);
        $soldeVeille = $reserveData['SoldeCompte'] ?? 0;
        
        // Depots du jour
        $depots = $this->SommeDepotAgence($date, $refAgency);
        
        // Retraits du jour  
        $retraits = $this->SommeRetraitAgence($date, $refAgency);
        
        // Appro sans appro initial
        $appro = $this->TotalApproAgenceSansApproInitial($date, $refAgency);
        
        // Sortie agence
        $sortie = $this->TotalSortieAgence($date, $refAgency);
        
        // Remittance
        $remittanceVersement = $this->SoldeRemittanceVersementAgence($date, $refAgency);
        $remittanceRetrait = $this->SoldeRemittanceRetraitAgence($date, $refAgency);
        $soldeRemittance = $remittanceVersement - $remittanceRetrait;
        
        // Calcul final
        $solde = $soldeVeille + $depots - $retraits + $appro - $sortie + $soldeRemittance;
        
        return $solde;
    }

    /**
     * OPTIMISATION PETITE CAISSE
     * Recupere toutes les donnees de petite caisse en quelques requetes au lieu de 240+
     * @param string $date Date au format Y-m-d
     * @param int $refUsers ID de l'utilisateur connecte
     * @return array Donnees structurees par agence avec caisses
     */
    public function GetPetiteCaisseDataOptimized($date, $refUsers)
    {
        // 1. Recuperer les agences de l'utilisateur
        $stmtAgences = $this->dao->prepare("
            SELECT DISTINCT a.RefAgency, a.NameAgency 
            FROM TbleAgency a
            INNER JOIN TbleCaisse c ON c.RefAgency = a.RefAgency
            INNER JOIN TbleChmod ch ON ch.RefCaisse = c.RefCaisse
            WHERE ch.RefUsers = :refUsers
        ");
        $stmtAgences->bindValue(':refUsers', $refUsers, \PDO::PARAM_INT);
        $stmtAgences->execute();
        $agences = $stmtAgences->fetchAll(\PDO::FETCH_ASSOC);
        
        if (empty($agences)) {
            return [];
        }
        
        $agencyIds = array_column($agences, 'RefAgency');
        $agencyIdsStr = implode(',', $agencyIds);
        
        // 2. Recuperer toutes les caisses avec leurs soldes en une requete
        $stmtCaisses = $this->dao->prepare("
            SELECT 
                c.RefCaisse, c.NameCaisse, a.RefAgency, a.NameAgency,
                
                -- Solde initial (veille)
                COALESCE((SELECT Solde FROM TbleSolde 
                    WHERE RefCaisse = c.RefCaisse AND DATE(DateSolde) < :date 
                    ORDER BY DateSolde DESC LIMIT 1), 0) AS SoldeInitial,
                
                -- Appro caisse C2C (RefType=5)
                COALESCE((SELECT SUM(MontantVersement) FROM TbleOperations 
                    WHERE RefCaisse = c.RefCaisse AND RefType = 5 
                    AND DATE(Approve2_Time) = :date 
                    AND Approve2_Id IS NOT NULL AND Reset_Id IS NULL), 0) AS TotalAppro,
                
                -- Versements (RefType=1)
                COALESCE((SELECT SUM(MontantVersement) FROM TbleOperations 
                    WHERE RefCaisse = c.RefCaisse AND RefType = 1 
                    AND DATE(Approve2_Time) = :date 
                    AND Approve2_Id IS NOT NULL AND Reset_Id IS NULL), 0) AS TotalVersement,
                
                -- Retraits (RefType=2)
                COALESCE((SELECT SUM(MontantVersement) FROM TbleOperations 
                    WHERE RefCaisse = c.RefCaisse AND RefType = 2 
                    AND DATE(Approve2_Time) = :date 
                    AND Approve2_Id IS NOT NULL AND Reset_Id IS NULL), 0) AS TotalRetrait,
                
                -- Sortie de fonds (RefType=4)
                COALESCE((SELECT SUM(MontantVersement) FROM TbleOperations 
                    WHERE RefCaisse = c.RefCaisse AND RefType = 4 
                    AND DATE(Approve2_Time) = :date 
                    AND Approve2_Id IS NOT NULL AND Reset_Id IS NULL), 0) AS TotalSortieCaisse,
                
                -- Remittance versement
                COALESCE((SELECT SUM(MontantTransaction) FROM TbleRemittance 
                    WHERE RefCaisse = c.RefCaisse AND RefType = 1 
                    AND DATE(Insert_time) = :date AND Reset_Id IS NULL), 0) AS SoldeRemittanceVersement,
                
                -- Remittance retrait
                COALESCE((SELECT SUM(MontantTransaction) FROM TbleRemittance 
                    WHERE RefCaisse = c.RefCaisse AND RefType = 2 
                    AND DATE(Insert_time) = :date AND Reset_Id IS NULL), 0) AS SoldeRemittanceRetrait
                
            FROM TbleCaisse c
            INNER JOIN TbleAgency a ON a.RefAgency = c.RefAgency
            INNER JOIN TbleChmod ch ON ch.RefCaisse = c.RefCaisse
            WHERE ch.RefUsers = :refUsers AND a.RefAgency IN ($agencyIdsStr)
            ORDER BY a.NameAgency, c.NameCaisse
        ");
        $stmtCaisses->bindValue(':date', $date, \PDO::PARAM_STR);
        $stmtCaisses->bindValue(':refUsers', $refUsers, \PDO::PARAM_INT);
        $stmtCaisses->execute();
        $caisses = $stmtCaisses->fetchAll(\PDO::FETCH_ASSOC);
        
        // 3. Recuperer les donnees aggregees par agence
        $stmtAgenceData = $this->dao->prepare("
            SELECT 
                a.RefAgency, a.NameAgency,
                
                -- Reserve veille
                COALESCE((SELECT SoldeCompte FROM TbleCompte 
                    WHERE RefAgency = a.RefAgency AND DATE(DateSolde) < :date 
                    ORDER BY DateSolde DESC LIMIT 1), 0) AS YesterdayReserve,
                
                -- Validation du jour
                (SELECT RefCompte FROM TbleCompte 
                    WHERE RefAgency = a.RefAgency AND DATE(DateSolde) = :date LIMIT 1) AS ValidateRefCompte,
                
                -- Depots agence
                COALESCE((SELECT SUM(o.MontantVersement) FROM TbleOperations o
                    INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
                    WHERE c.RefAgency = a.RefAgency AND o.RefType = 1 
                    AND DATE(o.Approve2_Time) = :date 
                    AND o.Approve2_Id IS NOT NULL AND o.Reset_Id IS NULL), 0) AS SommeDepot,
                
                -- Retraits agence
                COALESCE((SELECT SUM(o.MontantVersement) FROM TbleOperations o
                    INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
                    WHERE c.RefAgency = a.RefAgency AND o.RefType = 2 
                    AND DATE(o.Approve2_Time) = :date 
                    AND o.Approve2_Id IS NOT NULL AND o.Reset_Id IS NULL), 0) AS SommeSortie,
                
                -- Appro sans appro initial
                COALESCE((SELECT SUM(o.MontantVersement) FROM TbleOperations o
                    INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
                    WHERE c.RefAgency = a.RefAgency AND o.RefType = 3 AND o.TypeAppro != 1
                    AND DATE(o.Approve2_Time) = :date 
                    AND o.Approve2_Id IS NOT NULL AND o.Reset_Id IS NULL), 0) AS TotalAppoAgenceSansApproInitial,
                
                -- Appro avec appro initial
                COALESCE((SELECT SUM(o.MontantVersement) FROM TbleOperations o
                    INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
                    WHERE c.RefAgency = a.RefAgency AND o.RefType = 3 
                    AND DATE(o.Approve2_Time) = :date 
                    AND o.Approve2_Id IS NOT NULL AND o.Reset_Id IS NULL), 0) AS TotalApproAgenceAvecApproInitial,
                
                -- Sortie agence
                COALESCE((SELECT SUM(o.MontantVersement) FROM TbleOperations o
                    INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
                    WHERE c.RefAgency = a.RefAgency AND o.RefType = 4 
                    AND DATE(o.Approve2_Time) = :date 
                    AND o.Approve2_Id IS NOT NULL AND o.Reset_Id IS NULL), 0) AS TotalSortieAgence,
                
                -- Remittance versement agence
                COALESCE((SELECT SUM(r.MontantTransaction) FROM TbleRemittance r
                    INNER JOIN TbleCaisse c ON c.RefCaisse = r.RefCaisse
                    WHERE c.RefAgency = a.RefAgency AND r.RefType = 1 
                    AND DATE(r.Insert_time) = :date AND r.Reset_Id IS NULL), 0) AS SommeDepotRemittance,
                
                -- Remittance retrait agence
                COALESCE((SELECT SUM(r.MontantTransaction) FROM TbleRemittance r
                    INNER JOIN TbleCaisse c ON c.RefCaisse = r.RefCaisse
                    WHERE c.RefAgency = a.RefAgency AND r.RefType = 2 
                    AND DATE(r.Insert_time) = :date AND r.Reset_Id IS NULL), 0) AS SommeRetraitRemittance
                
            FROM TbleAgency a
            WHERE a.RefAgency IN ($agencyIdsStr)
            ORDER BY a.NameAgency
        ");
        $stmtAgenceData->bindValue(':date', $date, \PDO::PARAM_STR);
        $stmtAgenceData->execute();
        $agenceData = $stmtAgenceData->fetchAll(\PDO::FETCH_ASSOC);
        
        // 4. Structurer les donnees
        $result = [];
        foreach ($agenceData as $agence) {
            $refAgency = $agence['RefAgency'];
            
            // Calculer les valeurs derivees
            $agence['SoldeRemittanceAgence'] = $agence['SommeDepotRemittance'] - $agence['SommeRetraitRemittance'];
            $agence['ReserveActuelle'] = $agence['YesterdayReserve'] + $agence['SommeDepot'] - $agence['SommeSortie'] 
                + $agence['TotalAppoAgenceSansApproInitial'] - $agence['TotalSortieAgence'] + $agence['SoldeRemittanceAgence'];
            $agence['DayReserve'] = $agence['YesterdayReserve'] - $agence['TotalApproAgenceAvecApproInitial'];
            
            // Validation
            $agence['validate'] = !empty($agence['ValidateRefCompte']) 
                ? ['RefCompte' => $agence['ValidateRefCompte']] 
                : null;
            
            // Ajouter les caisses de cette agence
            $agence['Afficher'] = [];
            foreach ($caisses as $caisse) {
                if ($caisse['RefAgency'] == $refAgency) {
                    // Calculer solde disponible
                    $caisse['SoldeDisponible'] = $caisse['SoldeInitial'] + $caisse['TotalAppro'] 
                        + $caisse['TotalVersement'] - $caisse['TotalRetrait'] - $caisse['TotalSortieCaisse'];
                    $agence['Afficher'][] = $caisse;
                }
            }
            
            $result[] = $agence;
        }
        
        return $result;
    }

    /**
     * OPTIMISATION ACCUEIL / DASHBOARD
     * Recupere les donnees des caisses de l'utilisateur en une seule requete
     * Remplace UserCaisse() qui faisait 10+ requetes par caisse
     * @param string $date Date au format Y-m-d
     * @return array Liste des caisses avec leurs soldes calcules
     */
    public function UserCaisseOptimized($date)
    {
        $refUsers = $_SESSION['RefUsers'];
        
        $sql = "
            SELECT 
                c.RefCaisse, c.NameCaisse, 
                a.RefAgency, a.NameAgency,
                
                -- Solde initial (solde de la veille)
                COALESCE((SELECT Solde FROM TbleSolde 
                    WHERE RefCaisse = c.RefCaisse AND DATE(DateSolde) < :date 
                    ORDER BY DateSolde DESC LIMIT 1), 0) AS SoldeInitial,
                
                -- Solde initial global (avec appro initial)
                COALESCE((SELECT Solde FROM TbleSolde 
                    WHERE RefCaisse = c.RefCaisse AND DATE(DateSolde) < :date 
                    ORDER BY DateSolde DESC LIMIT 1), 0) + 
                COALESCE((SELECT SUM(MontantVersement) FROM TbleOperations 
                    WHERE RefCaisse = c.RefCaisse AND RefType = 3 AND TypeAppro = 1
                    AND DATE(Approve2_Time) = :date 
                    AND Approve2_Id IS NOT NULL AND Reset_Id IS NULL), 0) AS SoldeInitialGlobal,
                
                -- Appro caisse (RefType=3)
                COALESCE((SELECT SUM(MontantVersement) FROM TbleOperations 
                    WHERE RefCaisse = c.RefCaisse AND RefType = 3 
                    AND DATE(Approve2_Time) = :date 
                    AND Approve2_Id IS NOT NULL AND Reset_Id IS NULL), 0) AS TotalAppro,
                
                -- Versements (RefType=1)
                COALESCE((SELECT SUM(MontantVersement) FROM TbleOperations 
                    WHERE RefCaisse = c.RefCaisse AND RefType = 1 
                    AND DATE(Approve2_Time) = :date 
                    AND Approve2_Id IS NOT NULL AND Reset_Id IS NULL), 0) AS TotalVersement,
                
                -- Retraits (RefType=2)
                COALESCE((SELECT SUM(MontantVersement) FROM TbleOperations 
                    WHERE RefCaisse = c.RefCaisse AND RefType = 2 
                    AND DATE(Approve2_Time) = :date 
                    AND Approve2_Id IS NOT NULL AND Reset_Id IS NULL), 0) AS TotalRetrait,
                
                -- Sortie de fonds (RefType=4)
                COALESCE((SELECT SUM(MontantVersement) FROM TbleOperations 
                    WHERE RefCaisse = c.RefCaisse AND RefType = 4 
                    AND DATE(Approve2_Time) = :date 
                    AND Approve2_Id IS NOT NULL AND Reset_Id IS NULL), 0) AS TotalSortieCaisse,
                
                -- Remittance versement
                COALESCE((SELECT SUM(MontantTransaction) FROM TbleRemittance 
                    WHERE RefCaisse = c.RefCaisse AND RefType = 1 
                    AND DATE(Insert_time) = :date AND Reset_Id IS NULL), 0) AS SommeVersementRemittance,
                
                -- Remittance retrait
                COALESCE((SELECT SUM(MontantTransaction) FROM TbleRemittance 
                    WHERE RefCaisse = c.RefCaisse AND RefType = 2 
                    AND DATE(Insert_time) = :date AND Reset_Id IS NULL), 0) AS SommeRetraitRemittance
                
            FROM TbleCaisse c
            INNER JOIN TbleAgency a ON a.RefAgency = c.RefAgency
            INNER JOIN TbleChmod ch ON ch.RefCaisse = c.RefCaisse
            WHERE ch.RefUsers = :refUsers
            ORDER BY a.NameAgency, c.NameCaisse
        ";
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':date', $date, \PDO::PARAM_STR);
        $stmt->bindValue(':refUsers', $refUsers, \PDO::PARAM_INT);
        $stmt->execute();
        $caisses = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Calculer les valeurs derivees
        foreach ($caisses as $key => $caisse) {
            $caisses[$key]['SoldeRemittance'] = $caisse['SommeVersementRemittance'] - $caisse['SommeRetraitRemittance'];
            $caisses[$key]['SoldeDisponible'] = $caisse['SoldeInitialGlobal'] + $caisse['TotalVersement'] 
                - $caisse['TotalRetrait'] - $caisse['TotalSortieCaisse'];
            $caisses[$key]['SoldeDisponibleGlobal'] = $caisses[$key]['SoldeDisponible'] + $caisses[$key]['SoldeRemittance'];
        }
        
        return $caisses;
    }

    /**
     * OPTIMISATION ACCUEIL
     * Recupere les donnees des agences avec reserve en une seule requete
     * @param string $date Date au format Y-m-d
     * @return array Liste des agences avec leurs reserves
     */
    public function UserAgenceOptimized($date)
    {
        $refUsers = $_SESSION['RefUsers'];
        
        $sql = "
            SELECT DISTINCT
                a.RefAgency, a.NameAgency,
                
                -- Reserve veille
                COALESCE((SELECT SoldeCompte FROM TbleCompte 
                    WHERE RefAgency = a.RefAgency AND DATE(DateSolde) < :date 
                    ORDER BY DateSolde DESC LIMIT 1), 0) AS YesterdayReserve,
                
                -- Solde initial caisse (pour affichage)
                COALESCE((SELECT Solde FROM TbleSolde s
                    INNER JOIN TbleCaisse c2 ON c2.RefCaisse = s.RefCaisse
                    WHERE c2.RefAgency = a.RefAgency AND DATE(s.DateSolde) < :date 
                    ORDER BY s.DateSolde DESC LIMIT 1), 0) AS SommeDepot
                
            FROM TbleAgency a
            INNER JOIN TbleCaisse c ON c.RefAgency = a.RefAgency
            INNER JOIN TbleChmod ch ON ch.RefCaisse = c.RefCaisse
            WHERE ch.RefUsers = :refUsers
            GROUP BY a.RefAgency
            ORDER BY a.NameAgency
        ";
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':date', $date, \PDO::PARAM_STR);
        $stmt->bindValue(':refUsers', $refUsers, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * OPTIMISATION: Recupere toutes les donnees de performance par agence en une seule requete
     * Remplace la boucle avec CaisseAgencePerformance() et NbreOperationAgencePerformance()
     */
    public function AgencePerformanceOptimized($refUsers, $debut, $fin)
    {
        $sql = "
            SELECT 
                a.RefAgency, a.NameAgency,
                -- Nombre total d'operations par agence
                COUNT(CASE WHEN o.RefType IN (1, 2) AND o.Approve2_Id IS NOT NULL AND o.Reset_Id IS NULL 
                    AND DATE(o.Approve2_Time) BETWEEN :debut1 AND :fin1 THEN o.RefOperations END) AS NbreOP,
                -- Donnees des caisses seront traitees separement
                (SELECT COUNT(RefCaisse) FROM TbleCaisse WHERE RefAgency = a.RefAgency) AS NbreCaisses
            FROM TbleAgency a
            INNER JOIN TbleCaisse c ON c.RefAgency = a.RefAgency
            INNER JOIN TbleChmod ch ON ch.RefCaisse = c.RefCaisse
            LEFT JOIN TbleOperations o ON o.RefCaisse = c.RefCaisse
            WHERE ch.RefUsers = :refUsers
            GROUP BY a.RefAgency, a.NameAgency
            ORDER BY a.NameAgency
        ";
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':debut1', $debut, \PDO::PARAM_STR);
        $stmt->bindValue(':fin1', $fin, \PDO::PARAM_STR);
        $stmt->bindValue(':refUsers', $refUsers, \PDO::PARAM_INT);
        $stmt->execute();
        $agences = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Recuperer les performances des caisses par agence en une requete
        $sqlCaisses = "
            SELECT 
                c.RefCaisse, c.NameCaisse, c.RefAgency,
                COUNT(CASE WHEN o.RefType IN (1, 2) THEN o.RefOperations END) AS NbreOperation,
                COUNT(CASE WHEN o.RefType = 1 THEN o.RefOperations END) AS NbreDepot,
                COUNT(CASE WHEN o.RefType = 2 THEN o.RefOperations END) AS NbreRetrait,
                COALESCE(SUM(CASE WHEN o.RefType = 1 THEN o.MontantVersement ELSE 0 END), 0) AS TotalVersement,
                COALESCE(SUM(CASE WHEN o.RefType = 2 THEN o.MontantVersement ELSE 0 END), 0) AS TotalRetrait
            FROM TbleCaisse c
            INNER JOIN TbleChmod ch ON ch.RefCaisse = c.RefCaisse
            LEFT JOIN TbleOperations o ON o.RefCaisse = c.RefCaisse 
                AND o.Approve2_Id IS NOT NULL 
                AND o.Reset_Id IS NULL 
                AND DATE(o.Approve2_Time) BETWEEN :debut AND :fin
            WHERE ch.RefUsers = :refUsers
            GROUP BY c.RefCaisse, c.NameCaisse, c.RefAgency
            ORDER BY c.RefAgency, c.NameCaisse
        ";
        
        $stmtCaisses = $this->dao->prepare($sqlCaisses);
        $stmtCaisses->bindValue(':debut', $debut, \PDO::PARAM_STR);
        $stmtCaisses->bindValue(':fin', $fin, \PDO::PARAM_STR);
        $stmtCaisses->bindValue(':refUsers', $refUsers, \PDO::PARAM_INT);
        $stmtCaisses->execute();
        $allCaisses = $stmtCaisses->fetchAll(\PDO::FETCH_ASSOC);
        
        // Grouper les caisses par agence
        $caissesByAgency = [];
        foreach ($allCaisses as $caisse) {
            $caissesByAgency[$caisse['RefAgency']][] = $caisse;
        }
        
        // Attacher les caisses aux agences
        foreach ($agences as $key => $agence) {
            $agences[$key]['Afficher'] = isset($caissesByAgency[$agence['RefAgency']]) 
                ? $caissesByAgency[$agence['RefAgency']] 
                : [];
        }
        
        return $agences;
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
            COALESCE(sol.SoldeTheorique, 0) as SoldeInitial
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
                 s.NbOperations, s.TotalRemittanceDepot, s.TotalRemittanceRetrait, sol.SoldeTheorique
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

    public function GetOperationsNonVerifiees()
    {
        // OPTIMISATION: Utiliser JOIN au lieu de boucle avec requetes individuelles pour eviter epuisement memoire
        // Filtre 2026+: Le systeme de gestion fonds de roulement commence en 2026
        $requete = $this->dao->prepare("SELECT o.*, COALESCE(a.NameAgency, 'N/A') AS SentFromAgencyName 
            FROM operations o 
            LEFT JOIN TbleAgency a ON a.RefAgency = o.SentFromAgency 
            WHERE o.Validate = 1 AND o.Approve2_Id IS NOT NULL AND o.Reset_Id IS NULL 
            AND YEAR(o.Approve2_Time) >= 2026
            ORDER BY o.Approve2_Time DESC 
            LIMIT 1000");
        $requete->execute();
        $data = $requete->fetchAll(\PDO::FETCH_ASSOC);
        // Garder la compatibilite avec le code existant qui attend SentFromAgency comme nom d'agence
        foreach ($data as $key => $value) {
            $data[$key]['SentFromAgency'] = !empty($value['SentFromAgencyName']) ? $value['SentFromAgencyName'] : 'N/A';
        }
        return $data;
    }

    public function CountOperationsNonVerifiees()
    {
        // Filtre 2026+: Le systeme de gestion fonds de roulement commence en 2026
        $requete = $this->dao->prepare("SELECT COUNT(*) AS Total FROM operations WHERE operations.Validate = 1 AND operations.Approve2_Id IS NOT NULL AND operations.Reset_Id IS NULL AND YEAR(operations.Approve2_Time) >= 2026");
        $requete->execute();
        $result = $requete->fetch();
        return $result['Total'] ?? 0;
    }

    public function GetMontantOperationsNonVerifiees($refAgency = null)
    {
        // Filtre 2026+: Le systeme de gestion fonds de roulement commence en 2026
        if (!empty($refAgency)) {
            $requete = $this->dao->prepare("SELECT SUM(MontantVersement) AS TotalMontant FROM operations WHERE operations.Validate = 1 AND operations.Approve2_Id IS NOT NULL AND operations.Reset_Id IS NULL AND operations.RefAgency = :RefAgency AND YEAR(operations.Approve2_Time) >= 2026");
            $requete->bindValue(':RefAgency', $refAgency, \PDO::PARAM_INT);
        } else {
            $requete = $this->dao->prepare("SELECT SUM(MontantVersement) AS TotalMontant FROM operations WHERE operations.Validate = 1 AND operations.Approve2_Id IS NOT NULL AND operations.Reset_Id IS NULL AND YEAR(operations.Approve2_Time) >= 2026");
        }
        $requete->execute();
        $result = $requete->fetch();
        return $result['TotalMontant'] ?? 0;
    }

    public function CountOperationsNonVerifieesByAgency($refAgency)
    {
        // Filtre 2026+: Le systeme de gestion fonds de roulement commence en 2026
        $requete = $this->dao->prepare("SELECT COUNT(*) AS Total FROM operations WHERE operations.Validate = 1 AND operations.Approve2_Id IS NOT NULL AND operations.Reset_Id IS NULL AND operations.RefAgency = :RefAgency AND YEAR(operations.Approve2_Time) >= 2026");
        $requete->bindValue(':RefAgency', $refAgency, \PDO::PARAM_INT);
        $requete->execute();
        $result = $requete->fetch();
        return $result['Total'] ?? 0;
    }
}
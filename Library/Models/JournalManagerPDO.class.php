<?php

namespace Library\Models;

use \Library\Entities\Journal;

class JournalManagerPDO extends JournalManager
{
    public function Operations()
    {
        $requete = $this->dao->prepare('SELECT * FROM TbleOperations INNER JOIN TbleType ON TbleType.RefType=TbleOperations.RefType INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency LEFT JOIN TbleProduit ON TbleProduit.RefProduit=TbleOperations.RefProduit INNER JOIN TbleUsers ON TbleUsers.Refusers=TbleOperations.Insert_Id  INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleChmod.RefUsers=:RefUsers AND  (TbleOperations.RefType=1 OR TbleOperations.RefType=2) ORDER BY TbleOperations.datePayement ASC ');
        $requete->bindValue(':jour', date('Y-m-d'), \PDO::PARAM_STR);
        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->execute();
        $data = $requete->fetchAll();
        return $data;
    }
    public function GetOperations($debut, $fin, $Agence)
    {
        $requete = $this->dao->prepare("SELECT * FROM TbleOperations INNER JOIN TbleType ON TbleType.RefType=TbleOperations.RefType INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency LEFT JOIN TbleProduit ON TbleProduit.RefProduit=TbleOperations.RefProduit INNER JOIN TbleUsers ON TbleUsers.Refusers=TbleOperations.Insert_Id    WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND  date(TbleOperations.Approve2_Time) BETWEEN '$debut' AND '$fin'  AND TbleAgency.RefAgency=:Agence AND  (TbleOperations.RefType=1 OR TbleOperations.RefType=2) ORDER BY TbleOperations.datePayement ASC");
        $requete->bindValue(':Agence', $Agence, \PDO::PARAM_INT);
        $requete->execute();
        $data = $requete->fetchAll();
        foreach ($data as $key => $value) {
            $data[$key]['Debut'] = $debut;
            $data[$key]['Debut'] = $fin;
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
            $listeCaisse[$key]['SoldeDisponible'] =   $listeCaisse[$key]['SoldeInitialGlobal']  + $listeCaisse[$key]['TotalVersement'] - $listeCaisse[$key]['TotalRetrait'] - $listeCaisse[$key]['TotalSortieCaisse'];
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
            $SommeRetraitPeriode = $this->dao->prepare("SELECT SUM(MontantVersement) AS TotalPeriodeRetrait FROM TbleOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse  INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE  TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND  date(TbleOperations.Approve2_Time) BETWEEN '$debut' AND '$fin'   AND TbleAgency.RefAgency=:Agence AND  (TbleOperations.RefType=2) ");
            $SommeRetraitPeriode->bindValue(':Agence', $Agence, \PDO::PARAM_INT);
            $SommeRetraitPeriode->execute();
            $DataSomnmeRetrait = $SommeRetraitPeriode->fetch();
            return $DataSomnmeRetrait['TotalPeriodeRetrait'];
        } else {
            $SommeRetraitPeriode = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalPeriodeRetrait FROM TbleOperations  INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse  WHERE  TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleChmod.RefUsers=:RefUsers AND  (TbleOperations.RefType=2)');
            $SommeRetraitPeriode->bindValue(':jour', date('Y-m-d'), \PDO::PARAM_STR);
            $SommeRetraitPeriode->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $SommeRetraitPeriode->execute();
            $DataSomnmeRetrait = $SommeRetraitPeriode->fetch();
            return $DataSomnmeRetrait['TotalPeriodeRetrait'];
        }
    }
    public function sommeVersementPeriode($debut = NULL, $fin = NULL, $Agence = NULL)
    {
        if (!empty($debut) && !empty($fin) && !empty($Agence)) {
            $requete = $this->dao->prepare("SELECT SUM(MontantVersement) AS TotalPeriodeVersement FROM TbleOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse  INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND  date(TbleOperations.Approve2_Time) BETWEEN '$debut' AND '$fin'   AND TbleAgency.RefAgency=:Agence  AND (TbleOperations.RefType=1)  ");
            $requete->bindValue(':Agence', $Agence, \PDO::PARAM_INT);
            $requete->execute();
            $data = $requete->fetch();
            return $data['TotalPeriodeVersement'];
        } else {
            $requete = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalPeriodeVersement FROM TbleOperations  INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleChmod.RefUsers=:RefUsers  AND (TbleOperations.RefType=1) ');
            $requete->bindValue(':jour', date('Y-m-d'), \PDO::PARAM_STR);
            $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requete->execute();
            $data = $requete->fetch();
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
            return $data['TotalPeriodeVersement'];
        } else {
            $requete = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalPeriodeVersement FROM TbleOperations  INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleChmod.RefUsers=:RefUsers  AND (TbleOperations.RefType=1) ');
            $requete->bindValue(':jour', date('Y-m-d'), \PDO::PARAM_STR);
            $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requete->execute();
            $data = $requete->fetch();
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
            return $DataSomnmeRetrait['TotalPeriodeRetrait'];
        } else {
            $SommeRetraitPeriode = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalPeriodeRetrait FROM TbleOperations  INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse  WHERE  TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleChmod.RefUsers=:RefUsers AND  (TbleOperations.RefType=2 OR TbleOperations.RefType=4)');
            $SommeRetraitPeriode->bindValue(':jour', date('Y-m-d'), \PDO::PARAM_STR);
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
        $requete = $this->dao->prepare("UPDATE TbleOperations SET Validate= 2,DateValidate=:date,RefValidate=:RefUsers,ValidateDate=:validate WHERE RefOperations=:RefOperations");
        $requete->bindValue(':RefOperations', $_POST['RefOperations'], \PDO::PARAM_STR);
        $requete->bindValue(':date', $_POST['DateValidate'], \PDO::PARAM_STR);
        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->bindValue(':validate', $validate, \PDO::PARAM_STR);
        $requete->execute();
    }
    public function CancelValidate($id)
    {
        $requete = $this->dao->prepare("UPDATE TbleOperations SET Validate= 1,DateValidate=NULL,RefValidate=NULL,ValidateDate=NULL WHERE RefOperations=:RefOperations");
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
            $ListeCaisse[$key]['NbreOperation'] =  $this->NbreOperationCaissier($Date, $value['RefCaisse']);

            $ListeCaisse[$key]['SoldeInitial'] =  $this->SoldeInitialCaisse($Date, $value['RefCaisse']);
            $ListeCaisse[$key]['SoldeInitialGlobal'] =  $this->SoldeInitialCaisseGlobal($Date, $value['RefCaisse']);
            $ListeCaisse[$key]['TotalAppro'] =  $this->TotalApproCaisse($Date, $value['RefCaisse']);
            $ListeCaisse[$key]['TotalVersement'] =  $this->SomnmeVersementCaisse($Date, $value['RefCaisse']);
            $ListeCaisse[$key]['TotalRetrait'] =  $this->SommeRetraitCaisse($Date, $value['RefCaisse']);
            $ListeCaisse[$key]['TotalSortieCaisse'] = $this->TotalSortieCaisse($Date, $value['RefCaisse']);
            $ListeCaisse[$key]['SoldeDisponible'] =   $ListeCaisse[$key]['SoldeInitialGlobal']  + $ListeCaisse[$key]['TotalVersement'] - $ListeCaisse[$key]['TotalRetrait'] - $ListeCaisse[$key]['TotalSortieCaisse'];
        }
        return $ListeCaisse;
    }

    public function YesterdayReserve($Agence, $date)
    {
        $requeteSoldeInittial = $this->dao->prepare("SELECT SoldeCompte FROM TbleCompte WHERE DateSolde=(SELECT MAX(DateSolde) FROM TbleCompte WHERE RefAgency=:RefAgency AND DateSolde <:today)");
        $requeteSoldeInittial->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':today', $date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
        return $result['SoldeCompte'];
    }
    public function SommeDepotAgence($Date, $Agence)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleAgency.RefAgency=:RefAgency AND (TbleOperations.RefType=1)');
        $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['TotalVersment'];
    }
    public function SommeRetraitAgence($Date, $Agence)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleAgency.RefAgency=:RefAgency AND (TbleOperations.RefType=2)');
        $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['TotalVersment'];
    }
    public function SoldeInitialCaisse($Date, $Caisse)
    {
        $requeteSoldeInittial = $this->dao->prepare('SELECT SUM(MontantVersement) AS SoldeInitial FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleOperations.TypeAppro=1  AND TbleOperations.RefType=3 AND TbleOperations.RefCaisse=:RefCaisse ');
        $requeteSoldeInittial->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
        return $result['SoldeInitial'];
    }
    public function SoldeInitialCaisseGlobal($Date, $Caisse)
    {
        $requeteSoldeInittial = $this->dao->prepare('SELECT SUM(MontantVersement) AS SoldeInitial FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleOperations.RefType=3 AND TbleOperations.RefCaisse=:RefCaisse ');
        $requeteSoldeInittial->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
        return $result['SoldeInitial'];
    }

    public function TotalApproCaisse($Date, $Caisse)
    {
        $requeteSoldeInittial = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalAppro FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleOperations.TypeAppro=2 AND TbleOperations.RefType=3 AND TbleOperations.RefCaisse=:RefCaisse ');
        $requeteSoldeInittial->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
        return $result['TotalAppro'];
    }

    public function TotalApproAgenceSansApproInitial($Date, $Agence)
    {
        $requeteSoldeInittial = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalAppro FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE TbleAgency.RefAgency=:RefAgency AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleOperations.RefType=3 AND TbleOperations.TypeAppro=2 ');
        $requeteSoldeInittial->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
        return $result['TotalAppro'];
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
        return $result['TotalAppro'];
    }

    public function TotalSortieAgence($Date, $Agence)
    {
        $requeteSoldeInittial = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalAppro FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleOperations.RefType=4 AND TbleAgency.RefAgency=:RefAgency ');
        $requeteSoldeInittial->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
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
        $requeteAddService = $this->dao->prepare("INSERT INTO TbleCompte(RefAgency,SoldeCompte,DateSolde) VALUES(:RefAgency,:SoldeCompte,:DateSolde)");
        $requeteAddService->bindValue(':RefAgency', $_POST['RefAgency'], \PDO::PARAM_INT);
        $requeteAddService->bindValue(':SoldeCompte', $_POST['ReserveActuelle'], \PDO::PARAM_STR);
        $requeteAddService->bindValue(':DateSolde', $_POST['daycloture'] . date('H:i:s'), \PDO::PARAM_STR);
        $requeteAddService->execute();
    }
    public function SomnmeVersementCaisse($Date, $Caisse)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleOperations.RefCaisse=:RefCaisse AND (TbleOperations.RefType=1)');
        $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['TotalVersment'];
    }
    public function SommeRetraitCaisse($Date, $Caisse)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalRetrait FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleOperations.RefCaisse=:RefCaisse AND (TbleOperations.RefType=2)  ');
        $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
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
}
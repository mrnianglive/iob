<?php

namespace Library\Models;

use \Library\Entities\Analytics;

class AnalyticsManagerPDO extends AnalyticsManager
{

    public function GetOperations($debut, $fin)
    {
        $requete = $this->dao->prepare("SELECT * FROM TbleOperations INNER JOIN TbleType ON TbleType.RefType=TbleOperations.RefType INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN TbleUsers ON TbleUsers.Refusers=TbleOperations.Insert_Id    WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND  date(TbleOperations.Approve2_Time) BETWEEN '$debut' AND '$fin'  AND(TbleOperations.Reftype=1 OR TbleOperations.Reftype=2 ) AND SUBSTRING(TbleOperations.NumCompte,1,8) !=15009792    ORDER BY TbleOperations.datePayement ASC");
        $requete->execute();
        $data = $requete->fetchAll();
        return $data;
    }
    public function ListeAgence($Country = NULL, $Agence = NULL)
    {
        if ($Country == NULL && $Agence == NULL) {
            if ($_SESSION['statut'] == 'superadmin') {
                $requete = $this->dao->prepare("SELECT * FROM TbleAgency INNER JOIN tblpays ON tblpays.RefPays=TbleAgency.RefPays");
            } else {
                $requete = $this->dao->prepare("SELECT * FROM TbleAgency INNER JOIN tblpays ON tblpays.RefPays=TbleAgency.RefPays WHERE TbleAgency.RefPays=:Country");
                $requete->bindValue(':Country', $_SESSION['RefPays'], \PDO::PARAM_STR);
            }
        }
        if ($Country != NULL && $Agence != NULL) {
            $requete = $this->dao->prepare("SELECT * FROM TbleAgency INNER JOIN tblpays ON tblpays.RefPays=TbleAgency.RefPays WHERE TbleAgency.RefPays=:Country AND TbleAgency.RefAgency=:Agence");
            $requete->bindValue(':Country', $Country, \PDO::PARAM_STR);
            $requete->bindValue(':Agence', $Agence, \PDO::PARAM_STR);
        }

        if ($Country != NULL && $Agence == NULL) {
            $requete = $this->dao->prepare("SELECT * FROM TbleAgency INNER JOIN tblpays ON tblpays.RefPays=TbleAgency.RefPays WHERE TbleAgency.RefPays=:Country");
            $requete->bindValue(':Country', $Country, \PDO::PARAM_STR);
        }
        $requete->execute();
        $data = $requete->fetchAll();
        return $data;
    }


    public function ListeCaisse($Country = NULL, $Agence = NULL, $Caisse = NULL)
    {
        if ($Country == NULL && $Agence == NULL && $Caisse == NULL) {
            if ($_SESSION['statut'] == 'superadmin') {
                $requete = $this->dao->prepare("SELECT * FROM TbleCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN tblpays ON tblpays.RefPays=TbleAgency.RefPays");
            } else {
                $requete = $this->dao->prepare("SELECT * FROM TbleCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN tblpays ON tblpays.RefPays=TbleAgency.RefPays WHERE TbleAgency.RefPays=:Country");
                $requete->bindValue(':Country', $_SESSION['RefPays'], \PDO::PARAM_STR);
            }
        }
        if ($Country != NULL && $Agence != NULL && $Caisse != NULL) {
            $requete = $this->dao->prepare("SELECT * FROM TbleCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN tblpays ON tblpays.RefPays=TbleAgency.RefPays WHERE TbleAgency.RefPays=:Country AND TbleAgency.RefAgency=:Agence AND TbleCaisse.RefCaisse=:Caisse");
            $requete->bindValue(':Country', $Country, \PDO::PARAM_STR);
            $requete->bindValue(':Agence', $Agence, \PDO::PARAM_STR);
            $requete->bindValue(':Caisse', $Caisse, \PDO::PARAM_STR);
        }
        if ($Country != NULL && $Agence != NULL && $Caisse == NULL) {
            $requete = $this->dao->prepare("SELECT * FROM TbleCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN tblpays ON tblpays.RefPays=TbleAgency.RefPays WHERE TbleAgency.RefPays=:Country AND TbleAgency.RefAgency=:Agence");
            $requete->bindValue(':Country', $Country, \PDO::PARAM_STR);
            $requete->bindValue(':Agence', $Agence, \PDO::PARAM_STR);
        }
        if ($Country != NULL && $Agence == NULL && $Caisse == NULL) {
            $requete = $this->dao->prepare("SELECT * FROM TbleCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN tblpays ON tblpays.RefPays=TbleAgency.RefPays WHERE TbleAgency.RefPays=:Country");
            $requete->bindValue(':Country', $Country, \PDO::PARAM_STR);
        }
        $requete->execute();
        $data = $requete->fetchAll();
        return $data;
    }


    public function ChartAgenceVersement($agence, $Produit = NULL)
    {
        if ($Produit !== NULL) {
            $requeteSumRemittance = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse 
        INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE MONTH(Insert_time)=:mois AND  YEAR(Insert_time)=:year AND RefType=1 AND Reset_Id IS NULL AND TbleAgency.RefAgency=:agency AND TbleRemittance.RefProduit=:produit');
            $requeteSumRemittance->bindValue(':produit', $Produit, \PDO::PARAM_STR);
        } else {
            $requeteSumRemittance = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse
        INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE MONTH(Insert_time)=:mois AND  YEAR(Insert_time)=:year AND RefType=1 AND Reset_Id IS NULL AND TbleAgency.RefAgency=:agency');
        }
        $requeteSumRemittance->bindValue(':mois', date('m'), \PDO::PARAM_STR);
        $requeteSumRemittance->bindValue(':year', date('Y'), \PDO::PARAM_STR);
        $requeteSumRemittance->bindValue(':agency', $agence, \PDO::PARAM_STR);
        $requeteSumRemittance->execute();
        $dataRemittance = $requeteSumRemittance->fetch();

        if ($Produit !== NULL) {
            $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND MONTH(Approve2_Time)=:mois AND YEAR(Approve2_Time)=:year AND (TbleOperations.RefType=1) AND TbleAgency.RefAgency=:agency AND TbleOperations.RefProduit=:produit');
            $requeteSUm->bindValue(':produit', $Produit, \PDO::PARAM_STR);
        } else {
            $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND MONTH(Approve2_Time)=:mois AND YEAR(Approve2_Time)=:year AND (TbleOperations.RefType=1) AND TbleAgency.RefAgency=:agency');
        }
        $requeteSUm->bindValue(':mois', date('m'), \PDO::PARAM_STR);
        $requeteSUm->bindValue(':year', date('Y'), \PDO::PARAM_STR);
        $requeteSUm->bindValue(':agency', $agence, \PDO::PARAM_STR);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        if ($data == null && $dataRemittance == null) {
            return 0;
        }
        return $data['TotalVersment'] + $dataRemittance['SoldeRemittance'];
    }

    public function ChartAgenceRetrait($agence, $Produit = NULL)
    {
        if ($Produit !== NULL) {
            $requeteSumRemittance = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse 
              INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE MONTH(Insert_time)=:mois AND  YEAR(Insert_time)=:year AND RefType=2 AND Reset_Id IS NULL AND TbleAgency.RefAgency=:agency AND TbleRemittance.RefProduit=:produit');
            $requeteSumRemittance->bindValue(':produit', $Produit, \PDO::PARAM_STR);
        } else {
            $requeteSumRemittance = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse
              INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE MONTH(Insert_time)=:mois AND  YEAR(Insert_time)=:year AND RefType=2 AND Reset_Id IS NULL AND TbleAgency.RefAgency=:agency');
        }
        $requeteSumRemittance->bindValue(':mois', date('m'), \PDO::PARAM_STR);
        $requeteSumRemittance->bindValue(':year', date('Y'), \PDO::PARAM_STR);
        $requeteSumRemittance->bindValue(':agency', $agence, \PDO::PARAM_STR);
        $requeteSumRemittance->execute();
        $dataRemittance = $requeteSumRemittance->fetch();
        if ($Produit !== NULL) {
            $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND MONTH(Approve2_Time)=:mois AND YEAR(Approve2_Time)=:year AND (TbleOperations.RefType=2) AND TbleAgency.RefAgency=:agency AND TbleOperations.RefProduit=:produit');
            $requeteSUm->bindValue(':produit', $Produit, \PDO::PARAM_STR);
        } else {
            $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND MONTH(Approve2_Time)=:mois AND YEAR(Approve2_Time)=:year AND (TbleOperations.RefType=2) AND TbleAgency.RefAgency=:agency');
        }
        $requeteSUm->bindValue(':mois', date('m'), \PDO::PARAM_STR);
        $requeteSUm->bindValue(':year', date('Y'), \PDO::PARAM_STR);
        $requeteSUm->bindValue(':agency', $agence, \PDO::PARAM_STR);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        if ($data == null && $dataRemittance == null) {
            return 0;
        }
        return $data['TotalVersment'] + $dataRemittance['SoldeRemittance'];
    }
    public function ChartCaisseVersement($caisse, $Produit = NULL)
    {
        if ($Produit !== NULL) {
            $requeteSumRemittance = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse 
              INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE MONTH(Insert_time)=:mois AND  YEAR(Insert_time)=:year AND RefType=1 AND Reset_Id IS NULL AND TbleRemittance.RefCaisse=:RefCaisse AND TbleRemittance.RefProduit=:produit');
            $requeteSumRemittance->bindValue(':produit', $Produit, \PDO::PARAM_STR);
        } else {
            $requeteSumRemittance = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse
              INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE MONTH(Insert_time)=:mois AND  YEAR(Insert_time)=:year AND RefType=1 AND Reset_Id IS NULL AND TbleRemittance.RefCaisse=:RefCaisse');
        }
        $requeteSumRemittance->bindValue(':mois', date('m'), \PDO::PARAM_STR);
        $requeteSumRemittance->bindValue(':year', date('Y'), \PDO::PARAM_STR);
        $requeteSumRemittance->bindValue(':RefCaisse', $caisse, \PDO::PARAM_STR);
        $requeteSumRemittance->execute();
        $dataRemittance = $requeteSumRemittance->fetch();
        if ($Produit !== NULL) {
            $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND MONTH(Approve2_Time)=:mois AND YEAR(Approve2_Time)=:year AND (TbleOperations.RefType=1) AND TbleCaisse.RefCaisse=:caisse AND TbleOperations.RefProduit=:produit');
            $requeteSUm->bindValue(':produit', $Produit, \PDO::PARAM_STR);
        } else {
            $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND MONTH(Approve2_Time)=:mois AND YEAR(Approve2_Time)=:year AND (TbleOperations.RefType=1) AND TbleCaisse.RefCaisse=:caisse');
        }
        $requeteSUm->bindValue(':mois', date('m'), \PDO::PARAM_STR);
        $requeteSUm->bindValue(':year', date('Y'), \PDO::PARAM_STR);
        $requeteSUm->bindValue(':caisse', $caisse, \PDO::PARAM_STR);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        if ($data == null && $dataRemittance == null) {
            return 0;
        }
        return $data['TotalVersment'] + $dataRemittance['SoldeRemittance'];
    }

    public function ChartCaisseRetrait($caisse, $Produit = NULL)
    {
        if ($Produit !== NULL) {
            $requeteSumRemittance = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse 
              INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE MONTH(Insert_time)=:mois AND  YEAR(Insert_time)=:year AND RefType=2 AND Reset_Id IS NULL AND TbleRemittance.RefCaisse=:RefCaisse AND TbleRemittance.RefProduit=:produit');
            $requeteSumRemittance->bindValue(':produit', $Produit, \PDO::PARAM_STR);
        } else {
            $requeteSumRemittance = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse
              INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE MONTH(Insert_time)=:mois AND  YEAR(Insert_time)=:year AND RefType=2 AND Reset_Id IS NULL AND TbleRemittance.RefCaisse=:RefCaisse');
        }
        $requeteSumRemittance->bindValue(':mois', date('m'), \PDO::PARAM_STR);
        $requeteSumRemittance->bindValue(':year', date('Y'), \PDO::PARAM_STR);
        $requeteSumRemittance->bindValue(':RefCaisse', $caisse, \PDO::PARAM_STR);
        $requeteSumRemittance->execute();
        $dataRemittance = $requeteSumRemittance->fetch();
        if ($Produit !== NULL) {
            $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND MONTH(Approve2_Time)=:mois AND YEAR(Approve2_Time)=:year AND (TbleOperations.RefType=2) AND TbleCaisse.RefCaisse=:caisse AND TbleOperations.RefProduit=:produit');
            $requeteSUm->bindValue(':produit', $Produit, \PDO::PARAM_STR);
        } else {
            $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND MONTH(Approve2_Time)=:mois AND YEAR(Approve2_Time)=:year AND (TbleOperations.RefType=2) AND TbleCaisse.RefCaisse=:caisse');
        }
        $requeteSUm->bindValue(':mois', date('m'), \PDO::PARAM_STR);
        $requeteSUm->bindValue(':year', date('Y'), \PDO::PARAM_STR);
        $requeteSUm->bindValue(':caisse', $caisse, \PDO::PARAM_STR);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        if ($data == null && $dataRemittance == null) {
            return 0;
        }
        return $data['TotalVersment'] + $dataRemittance['SoldeRemittance'];
    }
    public function ChartVersment($mois, $pays = NULL, $agence = NULL, $caisse = NULL, $Produit = NULL)
    {
        $query = "SELECT SUM(MontantVersement) AS TotalVersement FROM TbleOperations 
              INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse 
              INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  
              WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL 
              AND MONTH(Approve2_Time)=:mois AND YEAR(Approve2_Time)=:year AND TbleOperations.RefType=1";
        $param = array();

        $queryRemittance = "SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse 
              INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE MONTH(Insert_time)=:mois AND  YEAR(Insert_time)=:year AND RefType=1 AND Reset_Id IS NULL";

        if ($pays != NULL) {
            $query .= " AND TbleOperations.RefPays=:pays";
            $queryRemittance .= " AND TbleRemittance.RefPays=:pays";
            $param[':pays'] = $pays;
        }
        if ($agence != NULL) {
            $query .= " AND TbleCaisse.RefAgency=:agence";
            $queryRemittance .= " AND TbleCaisse.RefAgency=:agence";
            $param[':agence'] = $agence;
        }
        if ($caisse != NULL) {
            $query .= " AND TbleOperations.RefCaisse=:caisse";
            $queryRemittance .= " AND TbleRemittance.RefCaisse=:caisse";
            $param[':caisse'] = $caisse;
        }
        if ($Produit != NULL) {
            $query .= " AND TbleOperations.RefProduit=:produit";
            $queryRemittance .= " AND TbleRemittance.RefProduit=:produit";
            $param[':produit'] = $Produit;
        }

        $param[':mois'] = $mois;
        $param[':year'] = date('Y');

        $requeteSum = $this->dao->prepare($query);
        $requeteSum->execute($param);
        $data = $requeteSum->fetch();

        $requeteSumRemittance = $this->dao->prepare($queryRemittance);
        $requeteSumRemittance->execute($param);
        $dataRemittance = $requeteSumRemittance->fetch();

        if ($data == null && $dataRemittance == null) {
            return 0;
        }
        return $data['TotalVersement'] + $dataRemittance['SoldeRemittance'];
    }


    public function ChartRetrait($mois, $pays = NULL, $agence = NULL, $caisse = NULL, $Produit = NULL)
    {
        $query = "SELECT SUM(MontantVersement) AS TotalVersement FROM TbleOperations 
              INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse 
              INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  
              WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL 
              AND MONTH(Approve2_Time)=:mois AND YEAR(Approve2_Time)=:year AND TbleOperations.RefType=2";

        $queryRemittance = "SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse 
              INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE MONTH(Insert_time)=:mois AND YEAR(Insert_time)=:year  AND RefType=2 AND Reset_Id IS NULL";

        $param = array();
        if ($pays != NULL) {
            $query .= " AND TbleOperations.RefPays=:pays";
            $queryRemittance .= " AND TbleRemittance.RefPays=:pays";
            $param[':pays'] = $pays;
        }
        if ($agence != NULL) {
            $query .= " AND TbleCaisse.RefAgency=:agence";
            $queryRemittance .= " AND TbleCaisse.RefAgency=:agence";
            $param[':agence'] = $agence;
        }
        if ($caisse != NULL) {
            $query .= " AND TbleOperations.RefCaisse=:caisse";
            $queryRemittance .= " AND TbleRemittance.RefCaisse=:caisse";
            $param[':caisse'] = $caisse;
        }
        if ($Produit != NULL) {
            $query .= " AND TbleOperations.RefProduit=:produit";
            $queryRemittance .= " AND TbleRemittance.RefProduit=:produit";
            $param[':produit'] = $Produit;
        }

        $param[':mois'] = $mois;
        $param[':year'] = date('Y');

        $requeteSum = $this->dao->prepare($query);
        $requeteSum->execute($param);
        $data = $requeteSum->fetch();

        $requeteSumRemittance = $this->dao->prepare($queryRemittance);
        $requeteSumRemittance->execute($param);
        $dataRemittance = $requeteSumRemittance->fetch();


        if ($data == null && $dataRemittance == null) {
            return 0;
        }
        return $data['TotalVersement'] + $dataRemittance['SoldeRemittance'];
    }
    public function Chart($Country = NULL, $Agence = NULL, $Caisse = NULL, $Produit = NULL)
    {
        $ChartList['Janvier'] = $this->ChartVersment(1, $Country, $Agence, $Caisse, $Produit);
        $ChartList['RJanvier'] = $this->ChartRetrait(1, $Country, $Agence, $Caisse, $Produit);
        $ChartList['Fevrier'] = $this->ChartVersment(2, $Country, $Agence, $Caisse, $Produit);
        $ChartList['RFevrier'] = $this->ChartRetrait(2, $Country, $Agence, $Caisse, $Produit);
        $ChartList['Mars'] = $this->ChartVersment(3, $Country, $Agence, $Caisse, $Produit);
        $ChartList['RMars'] = $this->ChartRetrait(3, $Country, $Agence, $Caisse, $Produit);
        $ChartList['Avril'] = $this->ChartVersment(4, $Country, $Agence, $Caisse, $Produit);
        $ChartList['RAvril'] = $this->ChartRetrait(4, $Country, $Agence, $Caisse, $Produit);
        $ChartList['Mai'] = $this->ChartVersment(5, $Country, $Agence, $Caisse, $Produit);
        $ChartList['RMai'] = $this->ChartRetrait(5, $Country, $Agence, $Caisse, $Produit);
        $ChartList['Juin'] = $this->ChartVersment(6, $Country, $Agence, $Caisse, $Produit);
        $ChartList['RJuin'] = $this->ChartRetrait(6, $Country, $Agence, $Caisse, $Produit);
        $ChartList['Juillet'] = $this->ChartVersment(7, $Country, $Agence, $Caisse, $Produit);
        $ChartList['RJuillet'] = $this->ChartRetrait(7, $Country, $Agence, $Caisse, $Produit);
        $ChartList['Aout'] = $this->ChartVersment(8, $Country, $Agence, $Caisse, $Produit);
        $ChartList['RAout'] = $this->ChartRetrait(8, $Country, $Agence, $Caisse, $Produit);
        $ChartList['Septembre'] = $this->ChartVersment(9, $Country, $Agence, $Caisse, $Produit);
        $ChartList['RSeptembre'] = $this->ChartRetrait(9, $Country, $Agence, $Caisse, $Produit);
        $ChartList['Octobre'] = $this->ChartVersment(10, $Country, $Agence, $Caisse, $Produit);
        $ChartList['ROctobre'] = $this->ChartRetrait(10, $Country, $Agence, $Caisse, $Produit);
        $ChartList['Novembre'] = $this->ChartVersment(11, $Country, $Agence, $Caisse, $Produit);
        $ChartList['RNovembre'] = $this->ChartRetrait(11, $Country, $Agence, $Caisse, $Produit);
        $ChartList['Decembre'] = $this->ChartVersment(12, $Country, $Agence, $Caisse, $Produit);
        $ChartList['RDecembre'] = $this->ChartRetrait(12, $Country, $Agence, $Caisse, $Produit);
        return $ChartList;
    }

    public function CountDayValidate()
    {
        $requete = $this->dao->prepare('SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND DATE(ValidateDate)=:jour');
        $requete->bindValue(':jour', date('Y-m-d'), \PDO::PARAM_STR);
        $requete->execute();
        $result = $requete->fetch();
        if ($result == null) {
            return 0;
        }
        return $result['Nbre'];
    }

    public function CountMonthValidate()
    {
        $requete = $this->dao->prepare('SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND YEAR(ValidateDate)=:year AND MONTH(ValidateDate)=:mois');
        $requete->bindValue(':mois', date('m'), \PDO::PARAM_STR);
        $requete->bindValue(':year', date('Y'), \PDO::PARAM_STR);
        $requete->execute();
        $result = $requete->fetch();
        return $result['Nbre'];
    }

    public function CountMonthOperations()
    {
        $requete = $this->dao->prepare('SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND YEAR(Approve2_time)=:year AND MONTH(Approve2_time)=:mois');
        $requete->bindValue(':mois', date('m'), \PDO::PARAM_STR);
        $requete->bindValue(':year', date('Y'), \PDO::PARAM_STR);
        $requete->execute();
        $result = $requete->fetch();
        if ($result == null) {
            return 0;
        }
        return $result['Nbre'];
    }

    public function CountWeekOperations()
    {
        $requete = $this->dao->prepare('SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_time > NOW() - INTERVAL 7 DAY');
        $requete->execute();
        $result = $requete->fetch();
        if ($result == null) {
            return 0;
        }
        return $result['Nbre'];
    }

    public function CountWeekValidate()
    {
        $requete = $this->dao->prepare('SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND ValidateDate > NOW() - INTERVAL 7 DAY');
        $requete->execute();
        $result = $requete->fetch();
        if ($result == null) {
            return 0;
        }
        return $result['Nbre'];
    }

    public function AddUv()
    {
        $requete = $this->dao->prepare("INSERT INTO TbleUv(RefAgency,RefProduit,MontantDepot,RefType) VALUES(:RefAgency,:RefProduit,:MontantDepot,:RefType)");
        $requete->bindValue(':RefAgency', $_POST['RefAgency'], \PDO::PARAM_INT);
        $requete->bindValue(':RefProduit', $_POST['RefProduit'], \PDO::PARAM_INT);
        $requete->bindValue(':MontantDepot', $_POST['MontantDepot'], \PDO::PARAM_INT);
        $requete->bindValue(':RefType', $_POST['RefType'], \PDO::PARAM_INT);
        $requete->execute();
    }

    public function ListeDepot()
    {
        $requeteDepot = $this->dao->prepare('SELECT * FROM TbleUv INNER JOIN  TbleAgency ON TbleUv.RefAgency=TbleAgency.RefAgency INNER JOIN TbleProduit ON TbleUv.RefProduit=TbleProduit.RefProduit');
        $requeteDepot->execute();
        $ListeDepot = $requeteDepot->fetchAll();
        return $ListeDepot;
    }

    public function UvDepot($Agence, $produit, $date)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantDepot) AS MontantDepot FROM TbleUv WHERE RefAgency=:RefAgency AND RefProduit=:RefProduit AND RefType=1 AND DATE(TbleUv.DateDepot)=:jour');
        $requeteSUm->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSUm->bindValue(':RefProduit', $produit, \PDO::PARAM_INT);
        $requeteSUm->bindValue(':jour', $date, \PDO::PARAM_STR);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        if ($data == null) {
            return 0;
        }
        return $data['MontantDepot'];
    }

    public function UvRetrait($Agence, $produit, $date)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantDepot) AS Montant FROM TbleUv WHERE RefAgency=:RefAgency AND RefProduit=:RefProduit AND RefType=2 AND DATE(TbleUv.DateDepot)=:jour');
        $requeteSUm->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSUm->bindValue(':RefProduit', $produit, \PDO::PARAM_INT);
        $requeteSUm->bindValue(':jour', $date, \PDO::PARAM_STR);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        if ($data == null) {
            return 0;
        } else {
            return $data['Montant'];
        }
    }

    public function YesterdayReserveProduit($Agence, $date, $produit)
    {
        $requeteSoldeInittial = $this->dao->prepare("SELECT SoldeUV FROM TbleSoldeUv WHERE RefAgency=:RefAgency AND RefProduit=:RefProduit AND DateSoldeUV=(SELECT MAX(DateSoldeUV) FROM TbleSoldeUv WHERE RefAgency=:RefAgency AND RefProduit=:RefProduit AND DateSoldeUV <:today)");
        $requeteSoldeInittial->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':RefProduit', $produit, \PDO::PARAM_INT);
        $requeteSoldeInittial->bindValue(':today', $date, \PDO::PARAM_STR);
        $requeteSoldeInittial->execute();
        $result = $requeteSoldeInittial->fetch();
        if (empty($result)) {
            return 0;
        } else {
            return $result['SoldeUV'];
        }
    }
    public function SoldeRemittanceVersementAgenceProduit($Date, $Agence, $produit)
    {
        if ($produit != 1) {
            $requete = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE DATE(TbleRemittance.Insert_time)=:jour AND TbleAgency.RefAgency=:RefAgency AND TbleRemittance.RefType=1  AND TbleRemittance.Reset_Id IS NULL AND TbleRemittance.RefProduit=:RefProduit');  //AND RefCaisse=:RefCaisse  
            $requete->bindValue(':jour', $Date, \PDO::PARAM_STR);
            $requete->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
            $requete->bindValue(':RefProduit', $produit, \PDO::PARAM_INT);
            $requete->execute();
            $result = $requete->fetch();
            return $result['SoldeRemittance'];
        } else {
            $produitEcobank = $this->SommeDepotAgence($Date, $Agence);
            return $produitEcobank;
        }
    }

    public function SoldeRemittanceRetraitAgenceProduit($Date, $Agence, $produit)
    {
        if ($produit != 1) {
            $requete = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE DATE(TbleRemittance.Insert_time)=:jour AND TbleAgency.RefAgency=:RefAgency AND TbleRemittance.RefType=2  AND TbleRemittance.Reset_Id IS NULL AND TbleRemittance.RefProduit=:RefProduit');  //AND RefCaisse=:RefCaisse  
            $requete->bindValue(':jour', $Date, \PDO::PARAM_STR);
            $requete->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
            $requete->bindValue(':RefProduit', $produit, \PDO::PARAM_INT);
            $requete->execute();
            $result = $requete->fetch();
            return $result['SoldeRemittance'];
        } else {
            $produitEcobank = $this->SommeRetraitAgence($Date, $Agence);
            return $produitEcobank;
        }
    }

    public function SommeDepotAgence($Date, $Agence)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleAgency.RefAgency=:RefAgency AND (TbleOperations.RefType=1)');
        $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        if ($data == null) {
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
        if ($data == null) {
            return 0;
        }
        return $data['TotalVersment'];
    }

    public function ListeProduit()
    {
        $requeteProduuit = $this->dao->prepare('SELECT * FROM TbleProduit INNER JOIN TbleBanque ON TbleBanque.RefBanque=TbleProduit.RefBanque');

        $requeteProduuit->execute();
        $ListeProduit = $requeteProduuit->fetchAll();
        return $ListeProduit;
    }
}
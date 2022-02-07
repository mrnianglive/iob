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
    public function ListeAgence()
    {
        $requeteAgence = $this->dao->prepare('SELECT * FROM TbleAgency');
        $requeteAgence->execute();
        $ListeAgence = $requeteAgence->fetchAll();
        return $ListeAgence;
    }

    public function ChartAgenceVersement($agence)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND MONTH(Approve2_Time)=:mois AND YEAR(Approve2_Time)=:year AND (TbleOperations.RefType=1) AND TbleAgency.RefAgency=:agency');
        $requeteSUm->bindValue(':mois', date('m'), \PDO::PARAM_STR);
        $requeteSUm->bindValue(':year', date('Y'), \PDO::PARAM_STR);
        $requeteSUm->bindValue(':agency', $agence, \PDO::PARAM_STR);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['TotalVersment'];
    }

    public function ChartAgenceRetrait($agence)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND MONTH(Approve2_Time)=:mois AND YEAR(Approve2_Time)=:year AND (TbleOperations.RefType=2) AND TbleAgency.RefAgency=:agency');
        $requeteSUm->bindValue(':mois', date('m'), \PDO::PARAM_STR);
        $requeteSUm->bindValue(':year', date('Y'), \PDO::PARAM_STR);
        $requeteSUm->bindValue(':agency', $agence, \PDO::PARAM_STR);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['TotalVersment'];
    }
    public function ChartCaisseVersement($caisse)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND MONTH(Approve2_Time)=:mois AND YEAR(Approve2_Time)=:year AND (TbleOperations.RefType=1) AND TbleCaisse.RefCaisse=:caisse');
        $requeteSUm->bindValue(':mois', date('m'), \PDO::PARAM_STR);
        $requeteSUm->bindValue(':year', date('Y'), \PDO::PARAM_STR);
        $requeteSUm->bindValue(':caisse', $caisse, \PDO::PARAM_STR);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['TotalVersment'];
    }

    public function ChartCaisseRetrait($caisse)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND MONTH(Approve2_Time)=:mois AND YEAR(Approve2_Time)=:year AND (TbleOperations.RefType=2) AND TbleCaisse.RefCaisse=:caisse');
        $requeteSUm->bindValue(':mois', date('m'), \PDO::PARAM_STR);
        $requeteSUm->bindValue(':year', date('Y'), \PDO::PARAM_STR);
        $requeteSUm->bindValue(':caisse', $caisse, \PDO::PARAM_STR);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['TotalVersment'];
    }
    public function ChartVersment($mois)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations   WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND MONTH(Approve2_Time)=:mois AND YEAR(Approve2_Time)=:year AND (TbleOperations.RefType=1)  ');
        $requeteSUm->bindValue(':mois', $mois, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':year', date('Y'), \PDO::PARAM_STR);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['TotalVersment'];
    }

    public function ChartRetrait($mois)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations   WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND MONTH(Approve2_Time)=:mois AND YEAR(Approve2_Time)=:year AND (TbleOperations.RefType=2)  ');
        $requeteSUm->bindValue(':mois', $mois, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':year', date('Y'), \PDO::PARAM_STR);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['TotalVersment'];
    }
    public function Chart()
    {
        $ChartList['Janvier'] = $this->ChartVersment(1);
        $ChartList['RJanvier'] = $this->ChartRetrait(1);
        $ChartList['Fevrier'] = $this->ChartVersment(2);
        $ChartList['RFevrier'] = $this->ChartRetrait(2);
        $ChartList['Mars'] = $this->ChartVersment(3);
        $ChartList['RMars'] = $this->ChartRetrait(3);
        $ChartList['Avril'] = $this->ChartVersment(4);
        $ChartList['RAvril'] = $this->ChartRetrait(4);
        $ChartList['Mai'] = $this->ChartVersment(5);
        $ChartList['RMai'] = $this->ChartRetrait(5);
        $ChartList['Juin'] = $this->ChartVersment(6);
        $ChartList['RJuin'] = $this->ChartRetrait(6);
        $ChartList['Juillet'] = $this->ChartVersment(7);
        $ChartList['RJuillet'] = $this->ChartRetrait(7);
        $ChartList['Aout'] = $this->ChartVersment(8);
        $ChartList['RAout'] = $this->ChartRetrait(8);
        $ChartList['Septembre'] = $this->ChartVersment(9);
        $ChartList['RSeptembre'] = $this->ChartRetrait(9);
        $ChartList['Octobre'] = $this->ChartVersment(10);
        $ChartList['ROctobre'] = $this->ChartRetrait(10);
        $ChartList['Novembre'] = $this->ChartVersment(11);
        $ChartList['RNovembre'] = $this->ChartRetrait(11);
        $ChartList['Decembre'] = $this->ChartVersment(12);
        $ChartList['RDecembre'] = $this->ChartRetrait(12);
        return $ChartList;
    }

    public function CountDayValidate()
    {
        $requete = $this->dao->prepare('SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND DATE(ValidateDate)=:jour');
        $requete->bindValue(':jour', date('Y-m-d'), \PDO::PARAM_STR);
        $requete->execute();
        $result = $requete->fetch();
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
        return $result['Nbre'];
    }

    public function CountWeekOperations()
    {
        $requete = $this->dao->prepare('SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_time > NOW() - INTERVAL 7 DAY');
        $requete->execute();
        $result = $requete->fetch();
        return $result['Nbre'];
    }

    public function CountWeekValidate()
    {
        $requete = $this->dao->prepare('SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND ValidateDate > NOW() - INTERVAL 7 DAY');
        $requete->execute();
        $result = $requete->fetch();
        return $result['Nbre'];
    }
}
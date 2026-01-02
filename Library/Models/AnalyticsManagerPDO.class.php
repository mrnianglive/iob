<?php

namespace Library\Models;

use \Library\Entities\Analytics;

class AnalyticsManagerPDO extends AnalyticsManager
{

    public function GetOperations($debut, $fin)
    {
        $requete = $this->dao->prepare("SELECT * FROM TbleOperations INNER JOIN TbleType ON TbleType.RefType=TbleOperations.RefType INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN TbleUsers ON TbleUsers.Refusers=TbleOperations.Insert_Id    WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND  date(TbleOperations.Approve2_Time) BETWEEN :debut AND :fin  AND(TbleOperations.Reftype=1 OR TbleOperations.Reftype=2 ) AND SUBSTRING(TbleOperations.NumCompte,1,8) !=15009792    ORDER BY TbleOperations.datePayement ASC");
        $requete->bindValue(':debut', $debut, \PDO::PARAM_STR);
        $requete->bindValue(':fin', $fin, \PDO::PARAM_STR);
        $requete->execute();
        $data = $requete->fetchAll();
        return $data;
    }

    /**
     * OPTIMISATION: Recupere les totaux versement/retrait directement en SQL
     * Evite de boucler sur toutes les operations en PHP
     */
    public function GetOperationsTotals($debut, $fin)
    {
        $sql = "SELECT 
            COALESCE(SUM(CASE WHEN RefType = 1 THEN MontantVersement ELSE 0 END), 0) AS TotalVersement,
            COALESCE(SUM(CASE WHEN RefType = 2 THEN MontantVersement ELSE 0 END), 0) AS TotalRetrait
        FROM TbleOperations 
        WHERE Approve2_Id IS NOT NULL 
            AND Reset_Id IS NULL 
            AND DATE(Approve2_Time) BETWEEN :debut AND :fin  
            AND (RefType = 1 OR RefType = 2) 
            AND SUBSTRING(NumCompte, 1, 8) != '15009792'";
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':debut', $debut, \PDO::PARAM_STR);
        $stmt->bindValue(':fin', $fin, \PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
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
        // OPTIMISATION: Une seule requete au lieu de 24
        $sql = "SELECT 
            MONTH(Approve2_Time) AS mois,
            RefType,
            SUM(MontantVersement) AS Total
        FROM TbleOperations 
        WHERE Approve2_Id IS NOT NULL 
            AND Reset_Id IS NULL 
            AND YEAR(Approve2_Time) = :year 
            AND (RefType = 1 OR RefType = 2)
        GROUP BY MONTH(Approve2_Time), RefType
        ORDER BY mois, RefType";
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':year', date('Y'), \PDO::PARAM_STR);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Initialiser tous les mois a 0
        $moisNoms = ['Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin', 
                     'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre'];
        $ChartList = [];
        foreach ($moisNoms as $nom) {
            $ChartList[$nom] = 0;
            $ChartList['R' . $nom] = 0;
        }
        
        // Remplir avec les donnees
        foreach ($results as $row) {
            $moisIndex = intval($row['mois']) - 1;
            $nomMois = $moisNoms[$moisIndex];
            if ($row['RefType'] == 1) {
                $ChartList[$nomMois] = floatval($row['Total']);
            } else {
                $ChartList['R' . $nomMois] = floatval($row['Total']);
            }
        }
        
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

    /**
     * OPTIMISATION: Recupere tous les compteurs de performance en une seule requete
     * Remplace CountDayValidate, CountMonthValidate, CountMonthOperations, CountWeekOperations, CountWeekValidate
     */
    public function GetAllCountersOptimized()
    {
        $today = date('Y-m-d');
        $mois = date('m');
        $year = date('Y');
        
        $sql = "SELECT 
            -- Validations du jour
            SUM(CASE WHEN DATE(ValidateDate) = :today THEN 1 ELSE 0 END) AS DailyValidate,
            -- Validations du mois
            SUM(CASE WHEN YEAR(ValidateDate) = :year AND MONTH(ValidateDate) = :mois THEN 1 ELSE 0 END) AS MonthValidate,
            -- Operations du mois
            SUM(CASE WHEN YEAR(Approve2_time) = :year2 AND MONTH(Approve2_time) = :mois2 THEN 1 ELSE 0 END) AS MonthOperations,
            -- Operations de la semaine
            SUM(CASE WHEN Approve2_time > NOW() - INTERVAL 7 DAY THEN 1 ELSE 0 END) AS CountWeekOperations,
            -- Validations de la semaine
            SUM(CASE WHEN ValidateDate > NOW() - INTERVAL 7 DAY THEN 1 ELSE 0 END) AS CountWeekValidate
        FROM TbleOperations 
        WHERE Approve2_Id IS NOT NULL AND Reset_Id IS NULL";
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':today', $today, \PDO::PARAM_STR);
        $stmt->bindValue(':year', $year, \PDO::PARAM_STR);
        $stmt->bindValue(':mois', $mois, \PDO::PARAM_STR);
        $stmt->bindValue(':year2', $year, \PDO::PARAM_STR);
        $stmt->bindValue(':mois2', $mois, \PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
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
        return $data['Montant'];
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

    /**
     * OPTIMISATION: Recupere toutes les stats agences en une seule requete
     * Remplace les boucles avec ChartAgenceVersement/ChartAgenceRetrait
     * @return array Liste des agences avec leurs totaux
     */
    public function ChartAllAgencesOptimized()
    {
        $sql = "SELECT 
            a.RefAgency, a.NameAgency,
            COALESCE(SUM(CASE WHEN o.RefType = 1 THEN o.MontantVersement ELSE 0 END), 0) AS SommeVersement,
            COALESCE(SUM(CASE WHEN o.RefType = 2 THEN o.MontantVersement ELSE 0 END), 0) AS SommeRetrait
        FROM TbleAgency a
        LEFT JOIN TbleCaisse c ON c.RefAgency = a.RefAgency
        LEFT JOIN TbleOperations o ON o.RefCaisse = c.RefCaisse 
            AND o.Approve2_Id IS NOT NULL 
            AND o.Reset_Id IS NULL 
            AND MONTH(o.Approve2_Time) = :mois 
            AND YEAR(o.Approve2_Time) = :year
            AND (o.RefType = 1 OR o.RefType = 2)
        GROUP BY a.RefAgency, a.NameAgency
        ORDER BY a.NameAgency";
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':mois', date('m'), \PDO::PARAM_STR);
        $stmt->bindValue(':year', date('Y'), \PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * OPTIMISATION: Recupere toutes les stats caisses en une seule requete
     * Remplace les boucles avec ChartCaisseVersement/ChartCaisseRetrait
     * @return array Liste des caisses avec leurs totaux
     */
    public function ChartAllCaissesOptimized()
    {
        $sql = "SELECT 
            c.RefCaisse, c.NameCaisse, a.NameAgency,
            COALESCE(SUM(CASE WHEN o.RefType = 1 THEN o.MontantVersement ELSE 0 END), 0) AS SommeVersement,
            COALESCE(SUM(CASE WHEN o.RefType = 2 THEN o.MontantVersement ELSE 0 END), 0) AS SommeRetrait
        FROM TbleCaisse c
        INNER JOIN TbleAgency a ON a.RefAgency = c.RefAgency
        LEFT JOIN TbleOperations o ON o.RefCaisse = c.RefCaisse 
            AND o.Approve2_Id IS NOT NULL 
            AND o.Reset_Id IS NULL 
            AND MONTH(o.Approve2_Time) = :mois 
            AND YEAR(o.Approve2_Time) = :year
            AND (o.RefType = 1 OR o.RefType = 2)
        GROUP BY c.RefCaisse, c.NameCaisse, a.NameAgency
        ORDER BY a.NameAgency, c.NameCaisse";
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':mois', date('m'), \PDO::PARAM_STR);
        $stmt->bindValue(':year', date('Y'), \PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
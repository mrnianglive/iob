<?php

namespace Library\Models;

use \Library\Entities\Analytics;

class AnalyticsManagerPDO extends AnalyticsManager
{

    public function GetOperations($debut, $fin)
    {
        $requete = $this->dao->prepare("SELECT * FROM TbleOperations INNER JOIN TbleType ON TbleType.RefType=TbleOperations.RefType INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN TbleUsers ON TbleUsers.Refusers=TbleOperations.Insert_Id    WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND  TbleOperations.Approve2_Time >= :debut AND TbleOperations.Approve2_Time <= :fin  AND(TbleOperations.Reftype=1 OR TbleOperations.Reftype=2 ) AND LEFT(TbleOperations.NumCompte,8) !='15009792'    ORDER BY TbleOperations.datePayement ASC");
        $requete->bindValue(':debut', $debut . ' 00:00:00', \PDO::PARAM_STR);
        $requete->bindValue(':fin', $fin . ' 23:59:59', \PDO::PARAM_STR);
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
            AND Approve2_Time >= :debut AND Approve2_Time <= :fin
            AND (RefType = 1 OR RefType = 2) 
            AND LEFT(NumCompte, 8) != '15009792'";
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':debut', $debut . ' 00:00:00', \PDO::PARAM_STR);
        $stmt->bindValue(':fin', $fin . ' 23:59:59', \PDO::PARAM_STR);
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
        $debut = date('Y-m-01 00:00:00');
        $fin = date('Y-m-t 23:59:59');
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time >= :debut AND Approve2_Time <= :fin AND (TbleOperations.RefType=1) AND TbleAgency.RefAgency=:agency');
        $requeteSUm->bindValue(':debut', $debut, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':fin', $fin, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':agency', $agence, \PDO::PARAM_STR);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['TotalVersment'];
    }

    public function ChartAgenceRetrait($agence)
    {
        $debut = date('Y-m-01 00:00:00');
        $fin = date('Y-m-t 23:59:59');
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time >= :debut AND Approve2_Time <= :fin AND (TbleOperations.RefType=2) AND TbleAgency.RefAgency=:agency');
        $requeteSUm->bindValue(':debut', $debut, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':fin', $fin, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':agency', $agence, \PDO::PARAM_STR);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['TotalVersment'];
    }

    public function ChartCaisseVersement($caisse)
    {
        $debut = date('Y-m-01 00:00:00');
        $fin = date('Y-m-t 23:59:59');
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time >= :debut AND Approve2_Time <= :fin AND (TbleOperations.RefType=1) AND TbleCaisse.RefCaisse=:caisse');
        $requeteSUm->bindValue(':debut', $debut, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':fin', $fin, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':caisse', $caisse, \PDO::PARAM_STR);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['TotalVersment'];
    }

    public function ChartCaisseRetrait($caisse)
    {
        $debut = date('Y-m-01 00:00:00');
        $fin = date('Y-m-t 23:59:59');
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time >= :debut AND Approve2_Time <= :fin AND (TbleOperations.RefType=2) AND TbleCaisse.RefCaisse=:caisse');
        $requeteSUm->bindValue(':debut', $debut, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':fin', $fin, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':caisse', $caisse, \PDO::PARAM_STR);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['TotalVersment'];
    }
    public function ChartVersment($mois)
    {
        $debut = date('Y-' . $mois . '-01 00:00:00');
        $fin = date('Y-' . $mois . '-t 23:59:59');
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations   WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time >= :debut AND Approve2_Time <= :fin AND (TbleOperations.RefType=1)  ');
        $requeteSUm->bindValue(':debut', $debut, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':fin', $fin, \PDO::PARAM_STR);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['TotalVersment'];
    }

    public function ChartRetrait($mois)
    {
        $debut = date('Y-' . $mois . '-01 00:00:00');
        $fin = date('Y-' . $mois . '-t 23:59:59');
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations   WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time >= :debut AND Approve2_Time <= :fin AND (TbleOperations.RefType=2)  ');
        $requeteSUm->bindValue(':debut', $debut, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':fin', $fin, \PDO::PARAM_STR);
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
        $requete = $this->dao->prepare('SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND ValidateDate >= :debut AND ValidateDate <= :fin');
        $requete->bindValue(':debut', date('Y-m-d') . ' 00:00:00', \PDO::PARAM_STR);
        $requete->bindValue(':fin', date('Y-m-d') . ' 23:59:59', \PDO::PARAM_STR);
        $requete->execute();
        $result = $requete->fetch();
        return $result['Nbre'];
    }

    public function CountMonthValidate()
    {
        $debut = date('Y-m-01 00:00:00');
        $fin = date('Y-m-t 23:59:59');
        $requete = $this->dao->prepare('SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND ValidateDate >= :debut AND ValidateDate <= :fin');
        $requete->bindValue(':debut', $debut, \PDO::PARAM_STR);
        $requete->bindValue(':fin', $fin, \PDO::PARAM_STR);
        $requete->execute();
        $result = $requete->fetch();
        return $result['Nbre'];
    }

    public function CountMonthOperations()
    {
        $debut = date('Y-m-01 00:00:00');
        $fin = date('Y-m-t 23:59:59');
        $requete = $this->dao->prepare('SELECT COUNT(RefOperations) AS Nbre FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_time >= :debut AND Approve2_time <= :fin');
        $requete->bindValue(':debut', $debut, \PDO::PARAM_STR);
        $requete->bindValue(':fin', $fin, \PDO::PARAM_STR);
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
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');
        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd = date('Y-m-t 23:59:59');
        
        $sql = "SELECT 
            -- Validations du jour
            SUM(CASE WHEN ValidateDate >= :todayStart AND ValidateDate <= :todayEnd THEN 1 ELSE 0 END) AS DailyValidate,
            -- Validations du mois
            SUM(CASE WHEN ValidateDate >= :monthStart AND ValidateDate <= :monthEnd THEN 1 ELSE 0 END) AS MonthValidate,
            -- Operations du mois
            SUM(CASE WHEN Approve2_time >= :monthStart AND Approve2_time <= :monthEnd THEN 1 ELSE 0 END) AS MonthOperations,
            -- Operations de la semaine
            SUM(CASE WHEN Approve2_time > NOW() - INTERVAL 7 DAY THEN 1 ELSE 0 END) AS CountWeekOperations,
            -- Validations de la semaine
            SUM(CASE WHEN ValidateDate > NOW() - INTERVAL 7 DAY THEN 1 ELSE 0 END) AS CountWeekValidate
        FROM TbleOperations 
        WHERE Approve2_Id IS NOT NULL AND Reset_Id IS NULL";
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':todayStart', $todayStart, \PDO::PARAM_STR);
        $stmt->bindValue(':todayEnd', $todayEnd, \PDO::PARAM_STR);
        $stmt->bindValue(':monthStart', $monthStart, \PDO::PARAM_STR);
        $stmt->bindValue(':monthEnd', $monthEnd, \PDO::PARAM_STR);
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
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantDepot) AS MontantDepot FROM TbleUv WHERE RefAgency=:RefAgency AND RefProduit=:RefProduit AND RefType=1 AND DateDepot >= :debut AND DateDepot <= :fin');
        $requeteSUm->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSUm->bindValue(':RefProduit', $produit, \PDO::PARAM_INT);
        $requeteSUm->bindValue(':debut', $date . ' 00:00:00', \PDO::PARAM_STR);
        $requeteSUm->bindValue(':fin', $date . ' 23:59:59', \PDO::PARAM_STR);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['MontantDepot'];
    }

    public function UvRetrait($Agence, $produit, $date)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantDepot) AS Montant FROM TbleUv WHERE RefAgency=:RefAgency AND RefProduit=:RefProduit AND RefType=2 AND DateDepot >= :debut AND DateDepot <= :fin');
        $requeteSUm->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSUm->bindValue(':RefProduit', $produit, \PDO::PARAM_INT);
        $requeteSUm->bindValue(':debut', $date . ' 00:00:00', \PDO::PARAM_STR);
        $requeteSUm->bindValue(':fin', $date . ' 23:59:59', \PDO::PARAM_STR);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['Montant'];
    }

    public function SoldeRemittanceVersementAgenceProduit($Date, $Agence, $produit)
    {
        if ($produit != 1) {
            $requete = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE Insert_time >= :debut AND Insert_time <= :fin AND TbleAgency.RefAgency=:RefAgency AND TbleRemittance.RefType=1  AND TbleRemittance.Reset_Id IS NULL AND TbleRemittance.RefProduit=:RefProduit');
            $requete->bindValue(':debut', $Date . ' 00:00:00', \PDO::PARAM_STR);
            $requete->bindValue(':fin', $Date . ' 23:59:59', \PDO::PARAM_STR);
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
            $requete = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE Insert_time >= :debut AND Insert_time <= :fin AND TbleAgency.RefAgency=:RefAgency AND TbleRemittance.RefType=2  AND TbleRemittance.Reset_Id IS NULL AND TbleRemittance.RefProduit=:RefProduit');
            $requete->bindValue(':debut', $Date . ' 00:00:00', \PDO::PARAM_STR);
            $requete->bindValue(':fin', $Date . ' 23:59:59', \PDO::PARAM_STR);
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
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time >= :debut AND Approve2_Time <= :fin  AND TbleAgency.RefAgency=:RefAgency AND (TbleOperations.RefType=1)');
        $requeteSUm->bindValue(':debut', $Date . ' 00:00:00', \PDO::PARAM_STR);
        $requeteSUm->bindValue(':fin', $Date . ' 23:59:59', \PDO::PARAM_STR);
        $requeteSUm->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['TotalVersment'];
    }
    public function SommeRetraitAgence($Date, $Agence)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations  INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time >= :debut AND Approve2_Time <= :fin  AND TbleAgency.RefAgency=:RefAgency AND (TbleOperations.RefType=2)');
        $requeteSUm->bindValue(':debut', $Date . ' 00:00:00', \PDO::PARAM_STR);
        $requeteSUm->bindValue(':fin', $Date . ' 23:59:59', \PDO::PARAM_STR);
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
        $debut = date('Y-m-01 00:00:00');
        $fin = date('Y-m-t 23:59:59');
        $sql = "SELECT 
            a.RefAgency, a.NameAgency,
            COALESCE(SUM(CASE WHEN o.RefType = 1 THEN o.MontantVersement ELSE 0 END), 0) AS SommeVersement,
            COALESCE(SUM(CASE WHEN o.RefType = 2 THEN o.MontantVersement ELSE 0 END), 0) AS SommeRetrait
        FROM TbleAgency a
        LEFT JOIN TbleCaisse c ON c.RefAgency = a.RefAgency
        LEFT JOIN TbleOperations o ON o.RefCaisse = c.RefCaisse 
            AND o.Approve2_Id IS NOT NULL 
            AND o.Reset_Id IS NULL 
            AND o.Approve2_Time >= :debut 
            AND o.Approve2_Time <= :fin
            AND (o.RefType = 1 OR o.RefType = 2)
        GROUP BY a.RefAgency, a.NameAgency
        ORDER BY a.NameAgency";
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':debut', $debut, \PDO::PARAM_STR);
        $stmt->bindValue(':fin', $fin, \PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function ChartAllCaissesOptimized()
    {
        $debut = date('Y-m-01 00:00:00');
        $fin = date('Y-m-t 23:59:59');
        $sql = "SELECT 
            c.RefCaisse, c.NameCaisse, a.NameAgency,
            COALESCE(SUM(CASE WHEN o.RefType = 1 THEN o.MontantVersement ELSE 0 END), 0) AS SommeVersement,
            COALESCE(SUM(CASE WHEN o.RefType = 2 THEN o.MontantVersement ELSE 0 END), 0) AS SommeRetrait
        FROM TbleCaisse c
        INNER JOIN TbleAgency a ON a.RefAgency = c.RefAgency
        LEFT JOIN TbleOperations o ON o.RefCaisse = c.RefCaisse 
            AND o.Approve2_Id IS NOT NULL 
            AND o.Reset_Id IS NULL 
            AND o.Approve2_Time >= :debut 
            AND o.Approve2_Time <= :fin
            AND (o.RefType = 1 OR o.RefType = 2)
        GROUP BY c.RefCaisse, c.NameCaisse, a.NameAgency
        ORDER BY a.NameAgency, c.NameCaisse";
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':debut', $debut, \PDO::PARAM_STR);
        $stmt->bindValue(':fin', $fin, \PDO::PARAM_STR);
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
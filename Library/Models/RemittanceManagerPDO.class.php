<?php

namespace Library\Models;

use \Library\Entities\Pannel;

class RemittanceManagerPDO extends RemittanceManager
{
    public function ListeType()
    {
        $requete = $this->dao->prepare('SELECT * FROM TbleType');
        $requete->execute();
        $ListeType = $requete->fetchAll();
        return $ListeType;
    }
    public function Update($time, $id)
    {
        $requete = $this->dao->prepare('UPDATE TbleRemittance SET Insert_time=:time WHERE RefRemittance=:id');
        $requete->bindValue(':time', $time);
        $requete->bindValue(':id', $id);
        $requete->execute();
    }

    public function add()
    {
        $refCaisse = $_POST['RefCaisse'];
        $refProduit = $_POST['RefProduit'];
        $refType = $_POST['RefType'];
        $numPhone = $_POST['NumPhone'];
        $montantTransaction = $_POST['MontantTransaction'];

        // Check for duplicate operations within the last 5 minutes
        if ($this->hasDuplicateTransaction($refCaisse, $refProduit, $refType, $numPhone, $montantTransaction)) {
            return false; // Duplicate found, handle accordingly
        }

        $refPays = isset($_SESSION['RefPays']) ? $_SESSION['RefPays'] : $_POST['RefPays'];
        $nomComplet = $_POST['NomComplet'];
        $insertId = $_SESSION['RefUsers'];
        $antidate = $_POST['Antidate'] ?? '';

        $sql = "INSERT INTO TbleRemittance (RefCaisse, RefProduit, RefType, NumPhone, NomComplet, MontantTransaction, Insert_id, RefPays)
            VALUES (:RefCaisse, :RefProduit, :RefType, :NumPhone, :NomComplet, :MontantTransaction, :Insert_id, :RefPays)";

        try {
            $stmt = $this->dao->prepare($sql);
            $stmt->bindValue(':RefCaisse', $refCaisse, \PDO::PARAM_INT);
            $stmt->bindValue(':RefProduit', $refProduit, \PDO::PARAM_INT);
            $stmt->bindValue(':RefType', $refType, \PDO::PARAM_INT);
            $stmt->bindValue(':NumPhone', $numPhone, \PDO::PARAM_STR);
            $stmt->bindValue(':NomComplet', $nomComplet, \PDO::PARAM_STR);
            $stmt->bindValue(':MontantTransaction', $montantTransaction, \PDO::PARAM_STR);
            $stmt->bindValue(':Insert_id', $insertId, \PDO::PARAM_INT);
            $stmt->bindValue(':RefPays', $refPays, \PDO::PARAM_INT);

            $stmt->execute();
            $id = $this->dao->lastInsertId();
 
            // Mettre à jour les stats temps réel après l'insertion
            try {
                $statsManager = new StatsManagerPDO($this->dao);
                $statsManager->incrementRemittanceStats(
                    $refCaisse,
                    $refProduit,
                    $refType,
                    $montantTransaction
                );
            } catch (\Exception $e) {
                error_log("Erreur Stats Remittance: " . $e->getMessage());
            }
 
            if (!empty($antidate)) {
                $timestamp = $antidate . ' ' . date('H:i:s');
                $this->updateTransactionTimestamp($id, $timestamp);
            }
        } catch (\PDOException $e) {
            // Handle database errors (e.g., log, display a message, or roll back the transaction)
            // Don't forget to replace this with proper error handling
            error_log('Database Error: ' . $e->getMessage());
            return false;
        }

        return true; // Transaction added successfully
    }

    private function hasDuplicateTransaction($refCaisse, $refProduit, $refType, $numPhone, $montantTransaction)
    {
        $fiveMinutesAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));
        $sql = "SELECT * FROM TbleRemittance 
            WHERE RefCaisse = :RefCaisse 
            AND RefProduit = :RefProduit 
            AND RefType = :RefType 
            AND NumPhone = :NumPhone 
            AND MontantTransaction = :MontantTransaction 
            AND Insert_time > :fiveMinutesAgo";

        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':RefCaisse', $refCaisse, \PDO::PARAM_INT);
        $stmt->bindValue(':RefProduit', $refProduit, \PDO::PARAM_INT);
        $stmt->bindValue(':RefType', $refType, \PDO::PARAM_INT);
        $stmt->bindValue(':NumPhone', $numPhone, \PDO::PARAM_STR);
        $stmt->bindValue(':MontantTransaction', $montantTransaction, \PDO::PARAM_STR);
        $stmt->bindValue(':fiveMinutesAgo', $fiveMinutesAgo, \PDO::PARAM_STR);
        $stmt->execute();

        return !empty($stmt->fetchAll());
    }

    private function updateTransactionTimestamp($transactionId, $timestamp)
    {
        $sql = "UPDATE TbleRemittance SET Insert_time = :timestamp WHERE RefRemittance = :RefRemittance";
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':timestamp', $timestamp, \PDO::PARAM_STR);
        $stmt->bindValue(':RefRemittance', $transactionId, \PDO::PARAM_INT);
        $stmt->execute();
    }








    public function ListeOperations($date)
    {
        $requete = $this->dao->prepare('SELECT * FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN TbleProduit ON TbleProduit.RefProduit=TbleRemittance.RefProduit INNER JOIN TbleType ON TbleType.RefType=TbleRemittance.RefType INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleCaisse.RefCaisse WHERE date(TbleRemittance.Insert_time)=:today AND TbleChmod.RefUsers=:RefUsers AND TbleRemittance.Reset_Id IS NULL ORDER BY TbleRemittance.RefRemittance DESC');
        $requete->bindValue(':today', $date, \PDO::PARAM_STR);
        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->execute();
        $ListeOperations = $requete->fetchAll();
        // foreach ($ListeOperations as $key => $value) {
        //     $ListeOperations[$key]['SoldeRemittanceVersement'] = $this->SoldeRemittanceVersementAgence($date, $date, $value['RefAgency']);
        //     $ListeOperations[$key]['SoldeRemittanceRetrait'] = $this->SoldeRemittanceRetraitAgence($date, $date, $value['RefAgency']);
        // }
        return $ListeOperations;
    }

    public function DeleteOperations($id)
    {
        $today = date("Y-m-d H:i:s");
        $requete = $this->dao->prepare("UPDATE TbleRemittance SET Reset_Id=:RefUsers,Reset_At=:day WHERE RefRemittance=:RefRemittance");
        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->bindValue(':day', $today, \PDO::PARAM_INT);
        $requete->bindValue(':RefRemittance', $id, \PDO::PARAM_INT);
        $requete->execute();
    }

    public function GetOperations($debut, $fin, $Agence)
    {
        $requete = $this->dao->prepare("SELECT * FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN TbleProduit ON TbleProduit.RefProduit=TbleRemittance.RefProduit INNER JOIN TbleType ON TbleType.RefType=TbleRemittance.RefType  WHERE  date(TbleRemittance.Insert_time) BETWEEN :debut AND :fin  AND TbleAgency.RefAgency=:Agence AND TbleRemittance.Reset_Id IS NULL ORDER BY TbleRemittance.RefRemittance DESC");
        $requete->bindValue(':debut', $debut, \PDO::PARAM_STR);
        $requete->bindValue(':fin', $fin, \PDO::PARAM_STR);
        $requete->bindValue(':Agence', $Agence, \PDO::PARAM_INT);
        $requete->execute();
        $data = $requete->fetchAll();
        foreach ($data as $key => $value) {
            $data[$key]['Debut'] = $debut;
            $data[$key]['Debut'] = $fin;
            // $data[$key]['SoldeRemittanceVersement'] = $this->SoldeRemittanceVersementAgence($debut, $fin, $Agence);
            // $data[$key]['SoldeRemittanceRetrait'] = $this->SoldeRemittanceRetraitAgence($debut, $fin, $Agence);
        }
        return $data;
    }

    public function ValidateOperations()
    {
        $validate = date('Y-m-d H:i:s');
        $requete = $this->dao->prepare("UPDATE TbleRemittance SET Validate= 2,DateValidate=:date,RefValidate=:RefUsers,DateValidate=:validate,SentFromAgency=:SentFromAgency WHERE RefRemittance=:RefRemittance");
        $requete->bindValue(':RefRemittance', $_POST['RefRemittance'], \PDO::PARAM_INT);
        $requete->bindValue(':date', $_POST['DateValidate'], \PDO::PARAM_STR);
        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->bindValue(':validate', $validate, \PDO::PARAM_STR);
        $requete->bindValue(':SentFromAgency', $_POST['SentFromAgency'], \PDO::PARAM_INT);
        $requete->execute();
    }
    public function CancelValidate($id)
    {
        $requete = $this->dao->prepare("UPDATE TbleRemittance SET Validate= 1,DateValidate=NULL,RefValidate=NULL,DateValidate=NULL,SentFromAgency=NULL WHERE RefRemittance=:RefRemittance");
        $requete->bindValue(':RefRemittance', $id, \PDO::PARAM_STR);
        $requete->execute();
    }

    public function SoldeRemittanceVersementAgence($Debut, $Fin, $Agence)
    {
        $requete = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE DATE(TbleRemittance.Insert_time) BETWEEN :Debut AND :Fin AND TbleAgency.RefAgency=:RefAgency AND TbleRemittance.RefType=1  AND TbleRemittance.Reset_Id IS NULL');  //AND RefCaisse=:RefCaisse  


        $requete->bindValue(':Debut', $Debut, \PDO::PARAM_STR);
        $requete->bindValue(':Fin', $Fin, \PDO::PARAM_STR);
        $requete->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requete->execute();
        $result = $requete->fetch();
        if ($result['SoldeRemittance'] == 0) {
            return 0;
        }
        return $result['SoldeRemittance'];
    }


    public function SoldeRemittanceRetraitAgence($Debut, $Fin, $Agence)
    {
        $requete = $this->dao->prepare('SELECT SUM(MontantTransaction) AS SoldeRemittance FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE DATE(TbleRemittance.Insert_time) BETWEEN :Debut AND :Fin AND TbleAgency.RefAgency=:RefAgency AND TbleRemittance.RefType=2  AND TbleRemittance.Reset_Id IS NULL');  //AND RefCaisse=:RefCaisse  
        $requete->bindValue(':Debut', $Debut, \PDO::PARAM_STR);
        $requete->bindValue(':Fin', $Fin, \PDO::PARAM_STR);
        $requete->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requete->execute();
        $result = $requete->fetch();
        if ($result['SoldeRemittance'] == 0) {
            return 0;
        }
        return $result['SoldeRemittance'];
    }

    public function getSingleOperation($id)
    {
        $requete = $this->dao->prepare('SELECT *  FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE RefRemittance=:RefRemittance ');  //AND RefCaisse=:RefCaisse  
        $requete->bindValue(':RefRemittance', $id, \PDO::PARAM_INT);
        $requete->execute();
        $result = $requete->fetch();
        return $result;
    }
}

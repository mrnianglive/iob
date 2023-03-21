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

    public function Add()
    {
        if (isset($_SESSION['RefPays'])) {
            $pays = $_SESSION['RefPays'];
        } else {
            $pays = $_POST['RefPays'];
        }

        $requete = $this->dao->prepare("INSERT INTO TbleRemittance(RefCaisse,RefProduit,RefType,NumPhone,NomComplet,MontantTransaction,Insert_id,RefPays) VALUES(:RefCaisse,:RefProduit,:RefType,:NumPhone,:NomComplet,:MontantTransaction,:Insert_id,:RefPays)");
        $requete->bindValue(':RefCaisse', $_POST['RefCaisse'], \PDO::PARAM_INT);
        $requete->bindValue(':RefProduit', $_POST['RefProduit'], \PDO::PARAM_INT);
        $requete->bindValue(':RefType', $_POST['RefType'], \PDO::PARAM_INT);
        $requete->bindValue(':NumPhone', $_POST['NumPhone'], \PDO::PARAM_STR);
        $requete->bindValue(':NomComplet', $_POST['NomComplet'], \PDO::PARAM_STR);
        $requete->bindValue(':MontantTransaction', $_POST['MontantTransaction'], \PDO::PARAM_STR);
        $requete->bindValue(':Insert_id', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->bindValue(':RefPays', $pays, \PDO::PARAM_INT);
        $requete->execute();
        $id = $this->dao->lastInsertId();
        if (!empty($_POST['Antidate'])) {
            $time = $_POST['Antidate'] . ' ' . date('H:i:s');
            $this->Update($time, $id);
        }
    }

    public function ListeOperations($date)
    {
        $requete = $this->dao->prepare('SELECT * FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN TbleProduit ON TbleProduit.RefProduit=TbleRemittance.RefProduit INNER JOIN TbleType ON TbleType.RefType=TbleRemittance.RefType INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleCaisse.RefCaisse WHERE date(TbleRemittance.Insert_time)=:today AND TbleChmod.RefUsers=:RefUsers AND TbleRemittance.Reset_Id IS NULL ORDER BY TbleRemittance.RefRemittance DESC');
        $requete->bindValue(':today', $date, \PDO::PARAM_STR);
        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->execute();
        $ListeOperations = $requete->fetchAll();
        foreach ($ListeOperations as $key => $value) {
            $ListeOperations[$key]['SoldeRemittanceVersement'] = $this->SoldeRemittanceVersementAgence($date, $date, $value['RefAgency']);
            $ListeOperations[$key]['SoldeRemittanceRetrait'] = $this->SoldeRemittanceRetraitAgence($date, $date, $value['RefAgency']);
        }
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
        $requete = $this->dao->prepare("SELECT * FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN TbleProduit ON TbleProduit.RefProduit=TbleRemittance.RefProduit INNER JOIN TbleType ON TbleType.RefType=TbleRemittance.RefType  WHERE  date(TbleRemittance.Insert_time) BETWEEN '$debut' AND '$fin'  AND TbleAgency.RefAgency=:Agence AND TbleRemittance.Reset_Id IS NULL ORDER BY TbleRemittance.RefRemittance DESC");
        $requete->bindValue(':Agence', $Agence, \PDO::PARAM_INT);
        $requete->execute();
        $data = $requete->fetchAll();
        foreach ($data as $key => $value) {
            $data[$key]['Debut'] = $debut;
            $data[$key]['Debut'] = $fin;
            $data[$key]['SoldeRemittanceVersement'] = $this->SoldeRemittanceVersementAgence($debut, $fin, $Agence);
            $data[$key]['SoldeRemittanceRetrait'] = $this->SoldeRemittanceRetraitAgence($debut, $fin, $Agence);
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
}

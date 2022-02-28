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

    public function Add()
    {
        $requete = $this->dao->prepare("INSERT INTO TbleRemittance(RefCaisse,RefProduit,RefType,NumPhone,NomComplet,MontantTransaction,Insert_id) VALUES(:RefCaisse,:RefProduit,:RefType,:NumPhone,:NomComplet,:MontantTransaction,:Insert_id)");
        $requete->bindValue(':RefCaisse', $_POST['RefCaisse'], \PDO::PARAM_INT);
        $requete->bindValue(':RefProduit', $_POST['RefProduit'], \PDO::PARAM_INT);
        $requete->bindValue(':RefType', $_POST['RefType'], \PDO::PARAM_INT);
        $requete->bindValue(':NumPhone', $_POST['NumPhone'], \PDO::PARAM_STR);
        $requete->bindValue(':NomComplet', $_POST['NomComplet'], \PDO::PARAM_STR);
        $requete->bindValue(':MontantTransaction', $_POST['MontantTransaction'], \PDO::PARAM_STR);
        $requete->bindValue(':Insert_id', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->execute();
    }

    public function ListeOperations($date)
    {
        $requete = $this->dao->prepare('SELECT * FROM TbleRemittance INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleRemittance.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN TbleProduit ON TbleProduit.RefProduit=TbleRemittance.RefProduit INNER JOIN TbleType ON TbleType.RefType=TbleRemittance.RefType INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleCaisse.RefCaisse WHERE date(TbleRemittance.Insert_time)=:today AND TbleChmod.RefUsers=:RefUsers AND TbleRemittance.Reset_Id IS NULL ORDER BY TbleRemittance.RefRemittance DESC');
        $requete->bindValue(':today', $date, \PDO::PARAM_STR);
        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->execute();
        $ListeOperations = $requete->fetchAll();
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
}
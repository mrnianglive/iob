<?php

namespace Library\Models;

use \Library\Entities\Bielletage;

class BielletageManagerPDO extends BielletageManager
{
    public function GetInvoice($id)
    {
        $requeteGetInvoice = $this->dao->prepare("SELECT TbleBilletage.*, TbleOperations.*, TbleUsers.NomUsers,TbleUsers.PrenomUsers, TbleCaisse.*, TbleAgency.*, TbleProduit.*, TbleBanque.NameBanque FROM TbleBilletage INNER JOIN TbleOperations ON TbleOperations.RefOperations=TbleBilletage.RefOperations INNER JOIN TbleUsers ON TbleUsers.RefUsers=TbleOperations.Insert_Id INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency LEFT JOIN TbleProduit ON TbleProduit.RefProduit=TbleOperations.RefProduit LEFT JOIN TbleBanque ON TbleBanque.RefBanque=TbleProduit.RefBanque  WHERE  TbleBilletage.RefOperations=:RefOperations TbleProduit.RefBanque=:RefBanque");
        $requeteGetInvoice->bindValue(':RefOperations', $id, \PDO::PARAM_INT);
        $requeteGetInvoice->bindValue(':RefBanque', $_SESSION['RefBanque'], \PDO::PARAM_INT);
        $requeteGetInvoice->execute();
        $dataInvoice = $requeteGetInvoice->fetch();
        if ($dataInvoice) {
            return  $dataInvoice;
        } else {
            return false;
        }
    }
}

<?php

namespace Library\Models;

use \Library\Entities\Journal;

class JournalManagerPDO extends JournalManager
{
    public function Operations()
    {
        $requete = $this->dao->prepare('SELECT * FROM operations  INNER JOIN TbleProduit ON TbleProduit.RefProduit=operations.RefProduit WHERE operations.Approve2_Id IS NOT NULL AND operations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleProduit.RefBanque=:RefBanque AND (operations.RefType=1 OR operations.RefType=2  OR operations.RefType=4) ORDER BY operations.datePayement ASC ');
        $requete->bindValue(':jour', date('Y-m-d'), \PDO::PARAM_STR);
        $requete->bindValue(':RefBanque', $_SESSION['RefBanque'], \PDO::PARAM_INT);
        $requete->execute();
        $data = $requete->fetchAll();
        return $data;
    }
    public function GetOperations($debut, $fin, $Agence)
    {
        $requete = $this->dao->prepare(" SELECT * FROM operations  INNER JOIN TbleProduit ON TbleProduit.RefProduit=operations.RefProduit WHERE operations.Approve2_Id IS NOT NULL AND operations.Reset_Id IS NULL AND  date(operations.Approve2_Time) BETWEEN '$debut' AND '$fin'  AND operations.RefAgency=:Agence AND (operations.RefType=1 OR operations.RefType=2 ) AND TbleProduit.RefBanque=:RefBanque ORDER BY operations.datePayement ASC");
        $requete->bindValue(':Agence', $Agence, \PDO::PARAM_INT);
        $requete->bindValue(':RefBanque', $_SESSION['RefBanque'], \PDO::PARAM_INT);
        $requete->execute();
        $data = $requete->fetchAll();
        foreach ($data as $key => $value) {
            $data[$key]['Debut'] = $debut;
            $data[$key]['Debut'] = $fin;
        }
        return $data;
    }
}

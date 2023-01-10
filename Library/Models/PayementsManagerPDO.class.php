<?php

namespace Library\Models;

use \Library\Entities\Pannel;

class PayementsManagerPDO extends PayementsManager
{



    public function ListeZone()
    {
        $requeteAgence = $this->dao->prepare('SELECT * FROM tblezone');
        $requeteAgence->execute();
        $ListeZone = $requeteAgence->fetchAll();
        return $ListeZone;
    }

    public function ListeBeneficiare()
    {
        $requeteAgence = $this->dao->prepare('SELECT * FROM tbleBeneficiare');
        $requeteAgence->execute();
        $ListeZone = $requeteAgence->fetchAll();
        return $ListeZone;
    }


    public function ListeDistributeur()
    {
        $requeteAgence = $this->dao->prepare('SELECT * FROM tbleDistributeur');
        $requeteAgence->execute();
        $ListeZone = $requeteAgence->fetchAll();
        return $ListeZone;
    }


    public function ListeCampagne()
    {
        $requeteAgence = $this->dao->prepare('SELECT * FROM tbleCampagne');
        $requeteAgence->execute();
        $ListeZone = $requeteAgence->fetchAll();
        return $ListeZone;
    }

    public function AddZone()
    {
        $requeteAdd = $this->dao->prepare("INSERT INTO tblezone(NameZone) VALUES(:NameZone)");
        $requeteAdd->bindValue(':NameZone', $_POST['NameZone'], \PDO::PARAM_STR);
        $requeteAdd->execute();
    }

    public function DeleteZone($id)
    {
        $requete = $this->dao->prepare('DELETE FROM tblezone WHERE RefZone=:RefZone');
        $requete->bindValue(':RefZone', $id, \PDO::PARAM_INT);
        $requete->execute();
    }
}
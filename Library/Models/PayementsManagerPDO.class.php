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
}
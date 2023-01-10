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
        $requeteAgence = $this->dao->prepare('SELECT * FROM tbleBeneficiare INNER JOIN tblezone ON tbleBeneficiare.RefZone = tblezone.RefZone');
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

    public function AddBeneficiare()
    {
        $requete = $this->dao->prepare("INSERT INTO tbleBeneficiare(NomBeneficiare, PrenomBeneficiare, TelBeneficiare,RefZone,MontantBeneficaire) VALUES(:NomBeneficiare,:PrenomBeneficiare,:TelBeneficiare,:RefZone,:MontantBeneficaire)");
        $requete->bindValue(':NomBeneficiare', $_POST['NomBeneficiare'], \PDO::PARAM_STR);
        $requete->bindValue(':PrenomBeneficiare', $_POST['PrenomBeneficiare'], \PDO::PARAM_STR);
        $requete->bindValue(':TelBeneficiare', $_POST['TelBeneficiare'], \PDO::PARAM_STR);
        $requete->bindValue(':RefZone', $_POST['RefZone'], \PDO::PARAM_INT);
        $requete->bindValue(':MontantBeneficaire', $_POST['MontantBeneficaire'], \PDO::PARAM_STR);
        $requete->execute();
    }
    public function DeleteBeneficiare($id)
    {
        $requete = $this->dao->prepare('DELETE FROM tbleBeneficiare WHERE RefBeneficiare=:RefBeneficiare');
        $requete->bindValue(':RefBeneficiare', $id, \PDO::PARAM_INT);
        $requete->execute();
    }
}
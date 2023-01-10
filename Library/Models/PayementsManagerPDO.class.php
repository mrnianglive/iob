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
        $requete = $this->dao->prepare('DELETE FROM tbleBeneficiare WHERE RefBenefi=:RefBenefi');
        $requete->bindValue(':RefBenefi', $id, \PDO::PARAM_INT);
        $requete->execute();
    }

    public function addDistributeur()
    {
        $requete = $this->dao->prepare("INSERT INTO tbleDistributeur(NomDistributeur, PrenomDistributeur, TelDistributeur,AdresseDistributeur,TypeDistributeur) VALUES(:NomDistributeur,:PrenomDistributeur,:TelDistributeur,:AdresseDistributeur,:TypeDistributeur)");
        $requete->bindValue(':NomDistributeur', $_POST['NomDistributeur'], \PDO::PARAM_STR);
        $requete->bindValue(':PrenomDistributeur', $_POST['PrenomDistributeur'], \PDO::PARAM_STR);
        $requete->bindValue(':TelDistributeur', $_POST['TelDistributeur'], \PDO::PARAM_STR);
        $requete->bindValue(':AdresseDistributeur', $_POST['AdresseDistributeur'], \PDO::PARAM_STR);
        $requete->bindValue(':TypeDistributeur', $_POST['TypeDistributeur'], \PDO::PARAM_STR);
        $requete->execute();
    }

    public function deleteDistributeur($id)
    {
        $requete = $this->dao->prepare('DELETE FROM tbleDistributeur WHERE RefDistributeur=:RefDistributeur');
        $requete->bindValue(':RefDistributeur', $id, \PDO::PARAM_INT);
        $requete->execute();
    }
}
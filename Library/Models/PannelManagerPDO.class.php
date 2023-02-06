<?php

namespace Library\Models;

use \Library\Entities\Pannel;

class PannelManagerPDO extends PannelManager
{


    public function ListeAgence()
    {
        $requeteAgence = $this->dao->prepare('SELECT * FROM TbleAgency');
        $requeteAgence->execute();
        $ListeAgence = $requeteAgence->fetchAll();
        return $ListeAgence;
    }

    public function UserAgence()
    {
        $requeteAgence = $this->dao->prepare('SELECT * FROM TbleAgency INNER JOIN TbleCaisse ON TbleCaisse.RefAgency=TbleAgency.RefAgency INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleCaisse.RefCaisse WHERE TbleChmod.RefUsers=:RefUsers GROUP BY(TbleAgency.RefAgency)');
        $requeteAgence->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requeteAgence->execute();
        $ListeAgence = $requeteAgence->fetchAll();
        return $ListeAgence;
    }


    public function GetAgency($id)
    {
        $requeteAgence = $this->dao->prepare('SELECT * FROM TbleAgency WHERE RefAgency=:RefAgency');
        $requeteAgence->bindValue(':RefAgency', $id, \PDO::PARAM_INT);
        $requeteAgence->execute();
        $ListeAgence = $requeteAgence->fetch();
        return $ListeAgence;
    }


    public function ListeProduit()
    {
        $requeteProduuit = $this->dao->prepare('SELECT * FROM TbleProduit INNER JOIN TbleBanque ON TbleBanque.RefBanque=TbleProduit.RefBanque');
        $requeteProduuit->execute();
        $ListeProduit = $requeteProduuit->fetchAll();
        return $ListeProduit;
    }


    public function AddProduit()
    {
        $requeteAdd = $this->dao->prepare("INSERT INTO TbleProduit(NameProduit,RefBanque) VALUES(:NameProduit,:RefBanque)");
        $requeteAdd->bindValue(':NameProduit', $_POST['NameProduit'], \PDO::PARAM_STR);
        $requeteAdd->bindValue(':RefBanque', $_POST['RefBanque'], \PDO::PARAM_INT);
        $requeteAdd->execute();
    }

    public function DeleteProduit($Produit)
    {
        $requete = $this->dao->prepare('DELETE FROM TbleProduit WHERE RefProduit=:RefProduit');
        $requete->bindValue(':RefProduit', $Produit, \PDO::PARAM_INT);
        $requete->execute();
    }

    public function AddCaisse()
    {
        $requeteAddService = $this->dao->prepare("INSERT INTO TbleCaisse(NameCaisse,RefAgency) VALUES(:NameCaisse,:RefAgency)");
        $requeteAddService->bindValue(':NameCaisse', $_POST['NameCaisse'], \PDO::PARAM_STR);
        $requeteAddService->bindValue(':RefAgency', $_POST['RefAgency'], \PDO::PARAM_INT);
        $requeteAddService->execute();
    }
    public function ListeCaisse()
    {
        $requeteAgence = $this->dao->prepare('SELECT * FROM TbleCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency');
        $requeteAgence->execute();
        $ListeCaisse = $requeteAgence->fetchAll();
        return $ListeCaisse;
    }
    public function ListeDays()
    {
        $requeteDays = $this->dao->prepare('SELECT * FROM TbleDays');
        $requeteDays->execute();
        $ListeDays = $requeteDays->fetchAll();
        return $ListeDays;
    }
    public function AddOuverture()
    {
        $requeteDelete = $this->dao->prepare('DELETE FROM TbleOuverture WHERE RefCaisse=:RefCaisse');
        $requeteDelete->bindValue(':RefCaisse', $_POST['RefCaisse'], \PDO::PARAM_INT);
        $requeteDelete->execute();
        if (!empty($_POST['RefDays'])) {
            foreach ($_POST['RefDays'] as $key => $value) {
                $requeteInsert = $this->dao->prepare('INSERT INTO TbleOuverture(RefCaisse,RefDays,HeureDebut,HeureFin) VALUES(:caisse,:days,:debut,:fin)');
                $requeteInsert->bindValue(':caisse', $_POST['RefCaisse'], \PDO::PARAM_INT);
                $requeteInsert->bindValue(':days', $_POST['RefDays'][$key], \PDO::PARAM_INT);
                $requeteInsert->bindValue(':debut', $_POST['HeureDebut'][$key], \PDO::PARAM_STR);
                $requeteInsert->bindValue(':fin', $_POST['HeureFin'][$key], \PDO::PARAM_STR);
                $requeteInsert->execute();
                header("location: /Pannel/Caisse");
                $_SESSION['flash']['success'] = "Opération Effectuée";
            }
        }
    }
    public function DeleteAgence($Agence)
    {
        $requete = $this->dao->prepare('DELETE FROM TbleAgency WHERE RefAgency=:RefAgency');
        $requete->bindValue(':RefAgency', $Agence, \PDO::PARAM_INT);
        $requete->execute();
    }
    public function ListeBanque()
    {
        $requeteBanque = $this->dao->prepare('SELECT * FROM TbleBanque');
        $requeteBanque->execute();
        $ListeBanque = $requeteBanque->fetchAll();
        return $ListeBanque;
    }
    public function AddAgency()
    {
        $requeteAddService = $this->dao->prepare("INSERT INTO TbleAgency(NameAgency,TelAgence,RefPays) VALUES(:NameAgency,:TelAgence,:RefPays)");
        $requeteAddService->bindValue(':NameAgency', $_POST['NameAgency'], \PDO::PARAM_STR);
        $requeteAddService->bindValue(':TelAgence', $_POST['TelAgence'], \PDO::PARAM_STR);
        $requeteAddService->bindValue(':RefPays', $_POST['RefPays'], \PDO::PARAM_INT);
        $requeteAddService->execute();
    }

    public function AddBanque()
    {
        $requete = $this->dao->prepare("INSERT INTO TbleBanque(NameBanque,RefPays) VALUES(:NameBanque,:RefPays)");
        $requete->bindValue(':NameBanque', $_POST['NameBanque'], \PDO::PARAM_STR);
        $requete->bindValue(':RefPays', $_POST['RefPays'], \PDO::PARAM_INT);
        $requete->execute();
    }
    public function DeleteBanque($Banque)
    {
        $requete = $this->dao->prepare('DELETE FROM TbleBanque WHERE RefBanque=:RefBanque');
        $requete->bindValue(':RefBanque', $Banque, \PDO::PARAM_INT);
        $requete->execute();
    }
    public function VerifOpening($Caisse, $Days)
    {
        $requete = $this->dao->prepare('SELECT * FROM TbleOuverture WHERE RefCaisse=:RefCaisse AND RefDays=:RefDays');
        $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requete->bindValue(':RefDays', $Days, \PDO::PARAM_INT);
        $requete->execute();
        $data = $requete->fetch();
        return $data;
    }
    public function VerifProduit($caisse, $Produit)
    {
        $requete = $this->dao->prepare('SELECT * FROM TbleChmodProduit WHERE RefCaisse=:RefCaisse AND RefProduit=:RefProduit');
        $requete->bindValue(':RefCaisse', $caisse, \PDO::PARAM_INT);
        $requete->bindValue(':RefProduit', $Produit, \PDO::PARAM_INT);
        $requete->execute();
        $data = $requete->fetch();
        if (!empty($data) && isset($data)) {
            return $data['RefCaisse'];
        }
        return null;
    }

    public function AddChmodProduit()
    {
        $requeteDelete = $this->dao->prepare('DELETE FROM TbleChmodProduit WHERE RefProduit=:RefProduit');
        $requeteDelete->bindValue(':RefProduit', $_POST['RefProduit'], \PDO::PARAM_INT);
        $requeteDelete->execute();
        if (!empty($_POST['RefCaisse'])) {
            foreach ($_POST['RefCaisse'] as $key => $value) {
                $requeteInsert = $this->dao->prepare('INSERT INTO TbleChmodProduit(RefCaisse,RefProduit) VALUES(:RefCaisse,:RefProduit)');
                $requeteInsert->bindValue(':RefCaisse', $value, \PDO::PARAM_INT);
                $requeteInsert->bindValue(':RefProduit', $_POST['RefProduit'], \PDO::PARAM_INT);
                $requeteInsert->execute();
            }
        }
    }



    public function GetAgencyUsingCaisseID($id)
    {
        $requete = $this->dao->prepare('SELECT * FROM TbleAgency INNER JOIN TbleCaisse ON TbleCaisse.RefAgency=TbleAgency.RefAgency WHERE TbleCaisse.RefCaisse=:RefCaisse');
        $requete->bindValue(':RefCaisse', $id, \PDO::PARAM_INT);
        $requete->execute();
        $data = $requete->fetch();
        return $data;
    }


    public function UserPermission()
    {
        $requete = $this->dao->prepare("SELECT * FROM permissions WHERE RefUsers=:RefUsers");
        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->execute();
        $result = $requete->fetchAll();
        return $result;
    }


    public function GetLinks()
    {
        $requete = $this->dao->prepare('SELECT * FROM tbllinks');
        $requete->execute();
        $data = $requete->fetchAll();
        return $data;
    }

    public function addLinks()
    {
        $requeteAddService = $this->dao->prepare("INSERT INTO tbllinks(url,url_name,btn,target) VALUES(:url,:url_name,:btn,:target)");
        $requeteAddService->bindValue(':url', $_POST['url'], \PDO::PARAM_STR);
        $requeteAddService->bindValue(':url_name', $_POST['url_name'], \PDO::PARAM_STR);
        $requeteAddService->bindValue(':btn', $_POST['btn'], \PDO::PARAM_STR);
        $requeteAddService->bindValue(':target', $_POST['target'], \PDO::PARAM_STR);
        $requeteAddService->execute();
    }

    public function ListePays()
    {
        $requete = $this->dao->prepare('SELECT * FROM tblpays');
        $requete->execute();
        $data = $requete->fetchAll();
        return $data;
    }

    public function AddPays()
    {
        $requeteAddService = $this->dao->prepare("INSERT INTO tblpays(nomPays) VALUES(:nomPays)");
        $requeteAddService->bindValue(':nomPays', $_POST['nomPays'], \PDO::PARAM_STR);
        $requeteAddService->execute();
    }
}
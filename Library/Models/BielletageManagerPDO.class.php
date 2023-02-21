<?php

namespace Library\Models;

use \Library\Entities\Bielletage;

class BielletageManagerPDO extends BielletageManager
{
    public function ChomdUser($type = NULL)
    {
        if ($type == 1) {
            $requete = $this->dao->prepare('SELECT * FROM TbleChmodAppro WHERE RefUsers=:RefUsers');
        } else {
            $requete = $this->dao->prepare('SELECT * FROM TbleChmod WHERE RefUsers=:RefUsers');
        }
        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->execute();
        $display = $requete->fetchAll();
        if (!empty($display) && isset($display)) {
            return $display;
        }
        return null;
    }
    public function CheckOuverture($data = NULL)
    {
        $ChomdUser = $this->ChomdUser($data);
        if (!empty($ChomdUser)) {
            $Caisse = [];
            foreach ($ChomdUser as $key => $value) {
                $Caisse[] = $value['RefCaisse'];
            }
            $implode = implode(',', $Caisse);
            $requete =
                $this->dao->prepare("SELECT * FROM TbleOuverture INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOuverture.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  WHERE TbleOuverture.RefCaisse IN (" . $implode . ") AND TbleOuverture.RefDays=:jour AND NOW() BETWEEN TbleOuverture.HeureDebut AND TbleOuverture.HeureFin ");
            $requete->bindValue(':jour', (date('w') == 0) ? 7 : date('w'), \PDO::PARAM_STR);
            $requete->execute();
            $display = $requete->fetchAll();
            foreach ($display as $key => $value) {
                $display[$key]['caisse'] = $this->CheckDailyClose($value['RefCaisse']);
            }
            if (!empty($display) && isset($display)) {
                return $display;
            }
            return null;
        }
    }
    public function CheckDailyClose($Caisse)
    {
        $requete = $this->dao->prepare("SELECT * FROM TbleSolde WHERE RefCaisse=:RefCaisse AND date(DateSolde)=:jour");
        $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requete->bindValue(':jour', date('Y-m-d'), \PDO::PARAM_STR);
        $requete->execute();
        $Result = $requete->fetch();
        if (!empty($Result) && isset($Result['RefCaisse'])) {
            return $Result['RefCaisse'];
        }
        return null;
    }
    public function CheckAfterRapport($Caisse)
    {
        $requete = $this->dao->prepare("SELECT * FROM TbleRapportOp WHERE RefCaisse=:RefCaisse AND Date=:jour");
        $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requete->bindValue(':jour', date('Y-m-d'), \PDO::PARAM_STR);
        $requete->execute();
        $data = $requete->fetch();
        if (!empty($data) && isset($data)) {
            return $data;
        }
        return null;
    }

    public  function GetCaisse($Date, $Country = NULL, $Agence = NULL, $Caisse = NULL)
    {
        // Old Query befpre VIEW ON SQL $requeteCaisse = $this->dao->prepare('SELECT * FROM TbleOperations LEFT JOIN TbleType ON TbleType.RefType=TbleOperations.RefType INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  LEFT JOIN TbleProduit ON TbleProduit.RefProduit=TbleOperations.RefProduit  INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleCaisse.RefCaisse  WHERE TbleOperations.Reset_Id IS NULL AND TbleOperations.Insert_Time=:today AND TbleChmod.RefUsers=:RefUsers ORDER BY TbleOperations.RefOperations DESC ');
        $query = "SELECT * FROM operations INNER JOIN TbleChmod ON TbleChmod.RefCaisse=operations.RefCaisse  WHERE operations.Reset_Id IS NULL ";
        $params = array();

        if ($Date != NULL) {
            $query .= ' AND operations.Insert_Time=:today';
            $params[':today'] = $Date;
        }


        if ($Country != NULL) {
            $query .= " WHERE TbleAgency.RefPays=:RefPays";
            $params[':RefPays'] = $Country;
        }
        if ($Agence != NULL) {
            $query .= ' AND operations.RefAgency=:RefAgency';
            $params[':RefAgency'] = $Agence;
        }
        if ($Caisse != NULL) {
            $query .= ' AND operations.RefCaisse=:RefCaisse';
            $params[':RefCaisse'] = $Caisse;
        } else {
            $query .= ' AND TbleChmod.RefUsers=:RefUsers';
            $params[':RefUsers'] = $_SESSION['RefUsers'];
        }
        $query .= ' ORDER BY operations.RefOperations DESC ';
        $requeteCaisse = $this->dao->prepare($query);
        $requeteCaisse->execute($params);
        $GetCaisse = $requeteCaisse->fetchAll();
        if (!empty($GetCaisse) && isset($GetCaisse)) {
            return $GetCaisse;
        }
        return [];
    }
    public function GetInvoice($id)
    {
        $requeteGetInvoice = $this->dao->prepare("SELECT * FROM TbleBilletage INNER JOIN TbleOperations ON TbleOperations.RefOperations=TbleBilletage.RefOperations INNER JOIN TbleUsers ON TbleUsers.RefUsers=TbleOperations.Insert_Id INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE  TbleBilletage.RefOperations=:RefOperations");
        $requeteGetInvoice->bindValue(':RefOperations', $id, \PDO::PARAM_INT);
        $requeteGetInvoice->execute();
        $dataInvoice = $requeteGetInvoice->fetch();
        return $dataInvoice;
    }

    public function GetAgency($Caisse)
    {
        $requete = $this->dao->prepare('SELECT * FROM TbleCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleCaisse.RefCaisse=:RefCaisse');
        $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requete->execute();
        $data = $requete->fetch();
        return $data['NameAgency'];
    }
    public function Add()
    {
        if (!empty($_POST['Antidate'])) {
            $date = $_POST['Antidate'];
        } else {
            $date = date('Y-m-d');
        }

        if (!empty($_POST['TypeRetrait'])) {
            $TypeRetrait = $_POST['TypeRetrait'];
        } else {
            $TypeRetrait = null;
        }
        if (!empty($_POST['RefProduit'])) {
            $RefProduit = $_POST['RefProduit'];
        } else {
            $RefProduit = null;
        }


        $result = uniqid();
        if (intval($_POST['MontantVersement']) > 0  && !empty($_POST['MontantVersement'])  && !empty($_POST['RefCaisse']) && !empty($_POST['TelDeposant'])) {

            if (!empty($_POST['TypeAppro'])) {
                $TypeAppro = $_POST['TypeAppro'];
            } else {
                $TypeAppro = null;
            }
            if ($_POST['RefType'] == 5) {
                $this->SortieCaisse2Caisse();
                $this->ApproCaisse2Caisse();
            } else {

                $requeteAddversement = $this->dao->prepare('INSERT INTO TbleOperations(RefCaisse,NumCompte,NameClient,MontantVersement,Remarque,Insert_Id,Insert_Time,Approve1_Id,Approve1_Time,Approve2_Id,Approve2_Time,Bordereau,NameDeposant,TelDeposant,RefType,TypeAppro,RefProduit,TypeRetrait,uniqid,RefPays) VALUES(:RefCaisse,:NumCompte,:NameClient,:MontantVersement,:Remarque,:Insert_Id,:Insert_Time,:Approve1_Id,:Approve1_Time,:Approve2_Id,:Approve2_Time,:Bordereau,:NameDeposant,:TelDeposant,:RefType,:TypeAppro,:RefProduit,:TypeRetrait,:uniqid,:RefPays)');
                $requeteAddversement->bindValue(':RefCaisse', $_POST['RefCaisse'], \PDO::PARAM_INT);
                $requeteAddversement->bindValue(':NumCompte', $_POST['NumCompte'], \PDO::PARAM_STR);
                $requeteAddversement->bindValue(':NameClient', $_POST['NameClient'], \PDO::PARAM_STR);
                $requeteAddversement->bindValue(':MontantVersement', $_POST['MontantVersement'], \PDO::PARAM_STR);
                $requeteAddversement->bindValue(':Remarque', $_POST['Remarque'], \PDO::PARAM_STR);
                $requeteAddversement->bindValue(':Insert_Id', $_SESSION['RefUsers'], \PDO::PARAM_INT);
                $requeteAddversement->bindValue(':Insert_Time', $date, \PDO::PARAM_STR);
                $requeteAddversement->bindValue(':Approve1_Id', $_SESSION['RefUsers'], \PDO::PARAM_INT);
                $requeteAddversement->bindValue(':Approve1_Time', $date, \PDO::PARAM_STR);
                $requeteAddversement->bindValue(':Approve2_Id', $_SESSION['RefUsers'], \PDO::PARAM_INT);
                $requeteAddversement->bindValue(':Approve2_Time', $date, \PDO::PARAM_STR);
                $requeteAddversement->bindValue(':Bordereau', 'NULL', \PDO::PARAM_STR);
                $requeteAddversement->bindValue(':NameDeposant', $_POST['NameDeposant'], \PDO::PARAM_STR);
                $requeteAddversement->bindValue(':TelDeposant', $_POST['TelDeposant'], \PDO::PARAM_STR);
                $requeteAddversement->bindValue(':RefType', $_POST['RefType'], \PDO::PARAM_INT);
                $requeteAddversement->bindValue(':TypeAppro', $TypeAppro, \PDO::PARAM_INT);
                $requeteAddversement->bindValue(':RefProduit', $RefProduit, \PDO::PARAM_INT);
                $requeteAddversement->bindValue(':TypeRetrait', $TypeRetrait, \PDO::PARAM_INT);
                $requeteAddversement->bindValue(':uniqid', $result, \PDO::PARAM_STR);
                $requeteAddversement->bindValue(':RefPays', $_SESSION['RefPays'], \PDO::PARAM_INT);
                $requeteAddversement->execute();
                $Refoperations = $this->dao->lastInsertId();
                $requetteBilletage = $this->dao->prepare('INSERT INTO TbleBilletage(RefOperations,a1,a2,b1,b2,c1,c2,d1,d2,e1,e2,f1,f2,g1,g2,h1,h2,i1,i2,j1,j2,k1,k2,l1,l2,m1,m2) VALUES(:RefOperations,:a1,:a2,:b1,:b2,:c1,:c2,:d1,:d2,:e1,:e2,:f1,:f2,:g1,:g2,:h1,:h2,:i1,:i2,:j1,:j2,:k1,:k2,:l1,:l2,:m1,:m2)');
                $requetteBilletage->bindValue(':RefOperations', $Refoperations, \PDO::PARAM_INT);
                $requetteBilletage->bindValue(':a1', $_POST['a1'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':a2', $_POST['a2'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':b1', $_POST['b1'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':b2', $_POST['b2'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':c1', $_POST['c1'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':c2', $_POST['c2'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':d1', $_POST['d1'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':d2', $_POST['d2'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':e1', $_POST['e1'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':e2', $_POST['e2'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':f1', $_POST['f1'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':f2', $_POST['f2'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':g1', $_POST['g1'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':g2', $_POST['g2'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':h1', $_POST['h1'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':h2', $_POST['h2'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':i1', $_POST['i1'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':i2', $_POST['i2'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':j1', $_POST['j1'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':j2', $_POST['j2'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':k1', $_POST['k1'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':k2', $_POST['k2'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':l1', $_POST['l1'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':l2', $_POST['l2'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':m1', $_POST['m1'], \PDO::PARAM_STR);
                $requetteBilletage->bindValue(':m2', $_POST['m2'], \PDO::PARAM_STR);
                $requetteBilletage->execute();
            }
            //Alerte sortie de fond de caisse
            if ($_POST['RefType']  == 4) {
                $this->AlerteSortie($_POST['RefCaisse'], $_POST['MontantVersement']);
            }

            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Opération réussie !';
            $_SESSION['message']['number'] = 2;
            header("location: /");
        } else {

            $_SESSION['message']['type'] = 'error';
            $_SESSION['message']['text'] = "Veuillez  reprendre l'operation. le Formulaire n'est pas remplit correctement,!";
            $_SESSION['message']['number'] = 2;
            header("location: /");
        }
    }
    public function YesterdaySolde($Agence = NULL)
    {
    }

    public function SommeVersementCaisse($Date)
    {

        if ($_SESSION['statut'] != 'admin' && $_SESSION['statut'] != 'superadmin') {

            $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleOperations.Insert_Id=:RefUsers AND (TbleOperations.RefType=1 OR TbleOperations.RefType=3) ');
            $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
            $requeteSUm->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requeteSUm->execute();
            $data = $requeteSUm->fetch();
            if ($data['TotalVersment'] == NULL) {
                return 0;
            }
            return $data['TotalVersment'];
        } else {
            $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND (TbleOperations.RefType=1 OR TbleOperations.RefType=3)   ');
            $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
            $requeteSUm->execute();
            $data = $requeteSUm->fetch();
            if ($data['TotalVersment'] == NULL) {
                return 0;
            }
            return $data['TotalVersment'];
        }
    }
    public function SommeRetraitCaisse($Date)
    {
        if ($_SESSION['statut'] != 'admin' && $_SESSION['statut'] != 'superadmin') {
            $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleOperations.Insert_Id=:RefUsers AND (TbleOperations.RefType=2 OR TbleOperations.RefType=4)  ');
            $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
            $requeteSUm->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requeteSUm->execute();
            $data = $requeteSUm->fetch();
            if ($data['TotalVersment'] == NULL) {
                return 0;
            }
            return $data['TotalVersment'];
        } else {
            $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND (TbleOperations.RefType=2 OR TbleOperations.RefType=4)  ');
            $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
            $requeteSUm->execute();
            $data = $requeteSUm->fetch();
            if ($data['TotalVersment'] == NULL) {
                return 0;
            }
            return $data['TotalVersment'];
        }
    }

    public  function getCurrentWeek()
    {
        $monday = strtotime("last monday");
        $monday = date('w', $monday) == date('w') ? $monday + 7 * 86400 : $monday;
        $sunday = strtotime(date("Y-m-d", $monday) . " +6 days");
        $date['Debut']  = date("Y-m-d", $monday);
        $date['Fin'] = date("Y-m-d", $sunday);
        return $date;
    }
    public  function daysofweek()
    {
        $getCurrentWeek = $this->getCurrentWeek();
        $tableau = [];
        for ($i = 0; $i < 7; $i++) {
            $tableau[] = date('Y-m-d', strtotime($getCurrentWeek['Debut'] . " +" . $i . " days"));
        }
        return $tableau;
    }

    public function SommeVersementStatistique($Date)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleCaisse.RefCaisse WHERE TbleChmod.RefUsers=:RefUsers AND  TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND (TbleOperations.RefType=1)   ');
        $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        if ($data['TotalVersment'] == NULL) {
            return 0;
        }
        return $data['TotalVersment'];
    }
    public function SommeRetraitStatistique($Date)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleCaisse.RefCaisse WHERE TbleChmod.RefUsers=:RefUsers AND   TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND (TbleOperations.RefType=2) ');
        $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        if ($data['TotalVersment'] == NULL) {
            return 0;
        }
        return $data['TotalVersment'];
    }
    public function DailyVersement()
    {
        $jour = $this->daysofweek();
        $Result['LundiVersement'] = $this->SommeVersementStatistique($jour['0']);
        $Result['MardiVersement'] = $this->SommeVersementStatistique($jour['1']);
        $Result['MercrediVersement'] = $this->SommeVersementStatistique($jour['2']);
        $Result['JeudiVersement'] = $this->SommeVersementStatistique($jour['3']);
        $Result['VendrediVersement'] = $this->SommeVersementStatistique($jour['4']);
        $Result['SamediVersement'] = $this->SommeVersementStatistique($jour['5']);
        $Result['LundiRetrait'] = $this->SommeRetraitStatistique($jour['0']);
        $Result['MardiRetrait'] = $this->SommeRetraitStatistique($jour['1']);
        $Result['MercrediRetrait'] = $this->SommeRetraitStatistique($jour['2']);
        $Result['JeudiRetrait'] = $this->SommeRetraitStatistique($jour['3']);
        $Result['VendrediRetrait'] = $this->SommeRetraitStatistique($jour['4']);
        $Result['SamediRetrait'] = $this->SommeRetraitStatistique($jour['5']);
        return $Result;
    }
    public function SommeVersementAgence($Caisse, $Date)
    {
        if (!empty($Caisse)) {
            $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations   WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleOperations.RefCaisse=:RefCaisse   AND (TbleOperations.RefType=1 OR TbleOperations.RefType=3) ');
            $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
            $requeteSUm->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
            $requeteSUm->execute();
            $data = $requeteSUm->fetch();
            if ($data['TotalVersment'] == NULL) {
                return 0;
            }
            return $data['TotalVersment'];
        } else {
            $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleChmod.RefUsers=:RefUsers   AND (TbleOperations.RefType=1 OR TbleOperations.RefType=3)  ');
            $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
            $requeteSUm->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requeteSUm->execute();
            $data = $requeteSUm->fetch();
            if ($data['TotalVersment'] == NULL) {
                return 0;
            }
            return $data['TotalVersment'];
        }
    }
    public function SommeRetraitAgence($Caisse, $Date)
    {
        if (!empty($Caisse)) {
            $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleOperations.RefCaisse=:RefCaisse AND (TbleOperations.RefType=2 OR TbleOperations.RefType=4)   ');
            $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
            $requeteSUm->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
            $requeteSUm->execute();
            $data = $requeteSUm->fetch();
            if ($data['TotalVersment'] == NULL) {
                return 0;
            }
            return $data['TotalVersment'];
        } else {
            $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleChmod.RefUsers=:RefUsers AND (TbleOperations.RefType=2 OR TbleOperations.RefType=4)  ');
            $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
            $requeteSUm->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requeteSUm->execute();
            $data = $requeteSUm->fetch();
            if ($data['TotalVersment'] == NULL) {
                return 0;
            }
            return $data['TotalVersment'];
        }
    }

    public function TypeRetrait()
    {
        $requete = $this->dao->prepare('SELECT * FROM TbleTypeRetrait');
        $requete->execute();
        $result = $requete->fetchAll();
        return $result;
    }

    public function getResetStatus($Refoperations)
    {
        $requete = $this->dao->prepare('SELECT * FROM TbleOperations WHERE RefOperations=:RefOperations ');
        $requete->bindValue(':RefOperations', $Refoperations, \PDO::PARAM_INT);
        $requete->execute();
        $result = $requete->fetch();
        if ($result['Reset_Id'] == null && $result['Reset_At'] == null) {
            return false;
        } else {
            return true;
        }
    }

    public function AlerteSortie($caisse, $montant)
    {
        $query = $this->dao->prepare('SELECT * FROM TbleCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency WHERE TbleCaisse.RefCaisse=:RefCaisse');
        $query->bindValue(':RefCaisse', $caisse, \PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetch();
        $pays = $this->dao->prepare('SELECT * FROM TblePays WHERE RefPays=:RefPays');
        $pays->bindValue(':RefPays', $result['RefPays'], \PDO::PARAM_INT);
        $pays->execute();
        $pays = $pays->fetch();

        $from = "no-reply@malicreances-sa.com";
        $subject = "SORTIE DE FONDS ";

        $atitle = $subject;
        $alert = "Sortie de fonds de la " . $result['NameCaisse'] . " " . $result['NameAgency'] . " d'un  montant de : " . number_format($montant, 0, '.', '.') . " FCFA";

        require_once __DIR__ . '/../../Applications/App/Templates/templatemail.php';
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        //Get Agency country

        $to = $pays['EmailAlert'];
        // Create email headers
        $headers .= 'From: ' . $from . "\r\n" .
            'Reply-To: ' . $from . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        mail($to, $subject, $content, $headers);
    }

    public function SortieCaisse2Caisse()
    {
        if (!empty($_POST['Antidate'])) {
            $date = $_POST['Antidate'];
        } else {
            $date = date('Y-m-d');
        }
        $result = uniqid();

        $requeteAddversement = $this->dao->prepare('INSERT INTO TbleOperations(RefCaisse,NumCompte,NameClient,MontantVersement,Remarque,Insert_Id,Insert_Time,Approve1_Id,Approve1_Time,Approve2_Id,Approve2_Time,Bordereau,NameDeposant,TelDeposant,RefType,TypeAppro,RefProduit,TypeRetrait,uniqid,RefPays) VALUES(:RefCaisse,:NumCompte,:NameClient,:MontantVersement,:Remarque,:Insert_Id,:Insert_Time,:Approve1_Id,:Approve1_Time,:Approve2_Id,:Approve2_Time,:Bordereau,:NameDeposant,:TelDeposant,:RefType,:TypeAppro,:RefProduit,:TypeRetrait,:uniqid,:RefPays)');
        $requeteAddversement->bindValue(':RefCaisse', $_POST['RefCaisse'], \PDO::PARAM_INT);
        $requeteAddversement->bindValue(':NumCompte', 'Intern', \PDO::PARAM_STR);
        $requeteAddversement->bindValue(':NameClient', 'Intern', \PDO::PARAM_STR);
        $requeteAddversement->bindValue(':MontantVersement', $_POST['MontantVersement'], \PDO::PARAM_STR);
        $requeteAddversement->bindValue(':Remarque', $_POST['Remarque'], \PDO::PARAM_STR);
        $requeteAddversement->bindValue(':Insert_Id', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requeteAddversement->bindValue(':Insert_Time', $date, \PDO::PARAM_STR);
        $requeteAddversement->bindValue(':Approve1_Id', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requeteAddversement->bindValue(':Approve1_Time', $date, \PDO::PARAM_STR);
        $requeteAddversement->bindValue(':Approve2_Id', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requeteAddversement->bindValue(':Approve2_Time', $date, \PDO::PARAM_STR);
        $requeteAddversement->bindValue(':Bordereau', 'NULL', \PDO::PARAM_STR);
        $requeteAddversement->bindValue(':NameDeposant', $_POST['NameDeposant'], \PDO::PARAM_STR);
        $requeteAddversement->bindValue(':TelDeposant', $_POST['TelDeposant'], \PDO::PARAM_STR);
        $requeteAddversement->bindValue(':RefType', 4, \PDO::PARAM_INT);
        $requeteAddversement->bindValue(':TypeAppro', $_POST['TypeAppro'], \PDO::PARAM_INT);
        $requeteAddversement->bindValue(':RefProduit', $_POST['RefProduit'], \PDO::PARAM_INT);
        $requeteAddversement->bindValue(':TypeRetrait', $_POST['TypeRetrait'], \PDO::PARAM_INT);
        $requeteAddversement->bindValue(':uniqid', $result, \PDO::PARAM_STR);
        $requeteAddversement->bindValue(':RefPays', $_SESSION['RefPays'], \PDO::PARAM_INT);
        $requeteAddversement->execute();
        $Refoperations = $this->dao->lastInsertId();
        $requetteBilletage = $this->dao->prepare('INSERT INTO TbleBilletage(RefOperations,a1,a2,b1,b2,c1,c2,d1,d2,e1,e2,f1,f2,g1,g2,h1,h2,i1,i2,j1,j2,k1,k2,l1,l2,m1,m2) VALUES(:RefOperations,:a1,:a2,:b1,:b2,:c1,:c2,:d1,:d2,:e1,:e2,:f1,:f2,:g1,:g2,:h1,:h2,:i1,:i2,:j1,:j2,:k1,:k2,:l1,:l2,:m1,:m2)');
        $requetteBilletage->bindValue(':RefOperations', $Refoperations, \PDO::PARAM_INT);
        $requetteBilletage->bindValue(':a1', $_POST['a1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':a2', $_POST['a2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':b1', $_POST['b1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':b2', $_POST['b2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':c1', $_POST['c1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':c2', $_POST['c2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':d1', $_POST['d1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':d2', $_POST['d2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':e1', $_POST['e1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':e2', $_POST['e2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':f1', $_POST['f1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':f2', $_POST['f2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':g1', $_POST['g1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':g2', $_POST['g2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':h1', $_POST['h1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':h2', $_POST['h2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':i1', $_POST['i1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':i2', $_POST['i2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':j1', $_POST['j1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':j2', $_POST['j2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':k1', $_POST['k1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':k2', $_POST['k2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':l1', $_POST['l1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':l2', $_POST['l2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':m1', $_POST['m1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':m2', $_POST['m2'], \PDO::PARAM_STR);
        $requetteBilletage->execute();
    }


    public function ApproCaisse2Caisse()
    {
        if (!empty($_POST['Antidate'])) {
            $date = $_POST['Antidate'];
        } else {
            $date = date('Y-m-d');
        }
        $result = uniqid();
        $requeteAddversement = $this->dao->prepare('INSERT INTO TbleOperations(RefCaisse,NumCompte,NameClient,MontantVersement,Remarque,Insert_Id,Insert_Time,Approve1_Id,Approve1_Time,Approve2_Id,Approve2_Time,Bordereau,NameDeposant,TelDeposant,RefType,TypeAppro,RefProduit,TypeRetrait,uniqid,RefPays) VALUES(:RefCaisse,:NumCompte,:NameClient,:MontantVersement,:Remarque,:Insert_Id,:Insert_Time,:Approve1_Id,:Approve1_Time,:Approve2_Id,:Approve2_Time,:Bordereau,:NameDeposant,:TelDeposant,:RefType,:TypeAppro,:RefProduit,:TypeRetrait,:uniqid,:RefPays)');
        $requeteAddversement->bindValue(':RefCaisse', $_POST['Destination'], \PDO::PARAM_INT);
        $requeteAddversement->bindValue(':NumCompte', 'Intern', \PDO::PARAM_STR);
        $requeteAddversement->bindValue(':NameClient', 'Intern', \PDO::PARAM_STR);
        $requeteAddversement->bindValue(':MontantVersement', $_POST['MontantVersement'], \PDO::PARAM_STR);
        $requeteAddversement->bindValue(':Remarque', $_POST['Remarque'], \PDO::PARAM_STR);
        $requeteAddversement->bindValue(':Insert_Id', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requeteAddversement->bindValue(':Insert_Time', $date, \PDO::PARAM_STR);
        $requeteAddversement->bindValue(':Approve1_Id', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requeteAddversement->bindValue(':Approve1_Time', $date, \PDO::PARAM_STR);
        $requeteAddversement->bindValue(':Approve2_Id', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requeteAddversement->bindValue(':Approve2_Time', $date, \PDO::PARAM_STR);
        $requeteAddversement->bindValue(':Bordereau', 'NULL', \PDO::PARAM_STR);
        $requeteAddversement->bindValue(':NameDeposant', $_POST['NameDeposant'], \PDO::PARAM_STR);
        $requeteAddversement->bindValue(':TelDeposant', $_POST['TelDeposant'], \PDO::PARAM_STR);
        $requeteAddversement->bindValue(':RefType', 3, \PDO::PARAM_INT);
        $requeteAddversement->bindValue(':TypeAppro', 2, \PDO::PARAM_INT);
        $requeteAddversement->bindValue(':RefProduit', $_POST['RefProduit'], \PDO::PARAM_INT);
        $requeteAddversement->bindValue(':TypeRetrait', $_POST['TypeRetrait'], \PDO::PARAM_INT);
        $requeteAddversement->bindValue(':uniqid', $result, \PDO::PARAM_STR);
        $requeteAddversement->bindValue(':RefPays', $_SESSION['RefPays'], \PDO::PARAM_INT);
        $requeteAddversement->execute();
        $Refoperations = $this->dao->lastInsertId();
        $requetteBilletage = $this->dao->prepare('INSERT INTO TbleBilletage(RefOperations,a1,a2,b1,b2,c1,c2,d1,d2,e1,e2,f1,f2,g1,g2,h1,h2,i1,i2,j1,j2,k1,k2,l1,l2,m1,m2) VALUES(:RefOperations,:a1,:a2,:b1,:b2,:c1,:c2,:d1,:d2,:e1,:e2,:f1,:f2,:g1,:g2,:h1,:h2,:i1,:i2,:j1,:j2,:k1,:k2,:l1,:l2,:m1,:m2)');
        $requetteBilletage->bindValue(':RefOperations', $Refoperations, \PDO::PARAM_INT);
        $requetteBilletage->bindValue(':a1', $_POST['a1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':a2', $_POST['a2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':b1', $_POST['b1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':b2', $_POST['b2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':c1', $_POST['c1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':c2', $_POST['c2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':d1', $_POST['d1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':d2', $_POST['d2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':e1', $_POST['e1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':e2', $_POST['e2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':f1', $_POST['f1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':f2', $_POST['f2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':g1', $_POST['g1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':g2', $_POST['g2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':h1', $_POST['h1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':h2', $_POST['h2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':i1', $_POST['i1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':i2', $_POST['i2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':j1', $_POST['j1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':j2', $_POST['j2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':k1', $_POST['k1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':k2', $_POST['k2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':l1', $_POST['l1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':l2', $_POST['l2'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':m1', $_POST['m1'], \PDO::PARAM_STR);
        $requetteBilletage->bindValue(':m2', $_POST['m2'], \PDO::PARAM_STR);
        $requetteBilletage->execute();
    }
}

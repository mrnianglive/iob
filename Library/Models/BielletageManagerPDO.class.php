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
        return $display;
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
            return $display;
        }
    }
    public function CheckDailyClose($Caisse)
    {
        $requete = $this->dao->prepare("SELECT * FROM TbleSolde WHERE RefCaisse=:RefCaisse AND date(DateSolde)=:jour");
        $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requete->bindValue(':jour', date('Y-m-d'), \PDO::PARAM_STR);
        $requete->execute();
        $Result = $requete->fetch();
        return $Result['RefCaisse'];
    }
    public function CheckAfterRapport($Caisse)
    {
        $requete = $this->dao->prepare("SELECT * FROM TbleRapportOp WHERE RefCaisse=:RefCaisse AND Date=:jour");
        $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
        $requete->bindValue(':jour', date('Y-m-d'), \PDO::PARAM_STR);
        $requete->execute();
        $data = $requete->fetch();
        return $data;
    }

    /**
     * Verifie si la journee precedente a ete cloturee pour une caisse
     * @param int $Caisse RefCaisse
     * @return bool True si la veille est cloturee ou si c'etait un jour non ouvrable
     */
    public function CheckPreviousDayClosed($Caisse)
    {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        // Verifier si hier etait un jour ouvrable pour cette caisse
        $dayOfWeek = date('w', strtotime($yesterday));
        $dayOfWeek = ($dayOfWeek == 0) ? 7 : $dayOfWeek; // Dimanche = 7
        
        $stmtOuverture = $this->dao->prepare("SELECT * FROM TbleOuverture 
            WHERE RefCaisse = :caisse AND RefDays = :day");
        $stmtOuverture->bindValue(':caisse', $Caisse, \PDO::PARAM_INT);
        $stmtOuverture->bindValue(':day', $dayOfWeek, \PDO::PARAM_INT);
        $stmtOuverture->execute();
        
        if ($stmtOuverture->rowCount() == 0) {
            // Hier n'etait pas un jour ouvrable pour cette caisse, pas besoin de verifier
            return true;
        }
        
        // Verifier si hier a ete cloture
        $stmtSolde = $this->dao->prepare("SELECT RefSolde FROM TbleSolde 
            WHERE RefCaisse = :caisse AND DATE(DateSolde) = :yesterday");
        $stmtSolde->bindValue(':caisse', $Caisse, \PDO::PARAM_INT);
        $stmtSolde->bindValue(':yesterday', $yesterday, \PDO::PARAM_STR);
        $stmtSolde->execute();
        
        return $stmtSolde->rowCount() > 0;
    }

    /**
     * Verifie si l'utilisateur connecte a la permission d'antidater
     * access = 3 correspond a la permission antidate operations
     * access = 4 correspond a la permission antidate remittance
     * @return bool
     */
    private function hasAntidatePermission()
    {
        // Les admins ont toujours la permission
        if ($_SESSION['statut'] === 'admin') {
            return true;
        }
        
        // Verifier dans la table permissions (access = 3 pour antidate operations)
        $stmt = $this->dao->prepare("SELECT * FROM permissions 
            WHERE RefUsers = :refUsers AND access = 3");
        $stmt->bindValue(':refUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Log une operation antidatee pour audit
     * @param array $postData Les donnees POST de l'operation
     */
    private function logAntidateOperation($postData)
    {
        $logFile = __DIR__ . '/../../logs/antidate_' . date('Y-m') . '.log';
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logEntry = sprintf(
            "[%s] User: %s (ID: %d) | IP: %s | Caisse: %s | Montant: %s | Date antidatee: %s | Type: %s\n",
            date('Y-m-d H:i:s'),
            $_SESSION['login'] ?? 'unknown',
            $_SESSION['RefUsers'] ?? 0,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $postData['RefCaisse'] ?? 'unknown',
            $postData['MontantVersement'] ?? 0,
            $postData['Antidate'] ?? 'unknown',
            $postData['RefType'] ?? 'unknown'
        );
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    public  function GetCaisse()
    {

        // Old Query befpre VIEW ON SQL $requeteCaisse = $this->dao->prepare('SELECT * FROM TbleOperations LEFT JOIN TbleType ON TbleType.RefType=TbleOperations.RefType INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency  LEFT JOIN TbleProduit ON TbleProduit.RefProduit=TbleOperations.RefProduit  INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleCaisse.RefCaisse  WHERE TbleOperations.Reset_Id IS NULL AND TbleOperations.Insert_Time=:today AND TbleChmod.RefUsers=:RefUsers ORDER BY TbleOperations.RefOperations DESC ');
        $requeteCaisse = $this->dao->prepare('SELECT * FROM operations INNER JOIN TbleChmod ON TbleChmod.RefCaisse=operations.RefCaisse  WHERE operations.Reset_Id IS NULL AND operations.Insert_Time=:today AND TbleChmod.RefUsers=:RefUsers ORDER BY operations.RefOperations DESC ');

        $requeteCaisse->bindValue(':today', date('Y-m-d'), \PDO::PARAM_STR);
        $requeteCaisse->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requeteCaisse->execute();
        $GetCaisse = $requeteCaisse->fetchAll();
        return $GetCaisse;
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
    
    /**
     * Recupere le RefAgency a partir d'une caisse
     */
    public function GetAgencyFromCaisse($refCaisse)
    {
        $stmt = $this->dao->prepare('SELECT RefAgency FROM TbleCaisse WHERE RefCaisse = :refCaisse');
        $stmt->bindValue(':refCaisse', $refCaisse, \PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? $data['RefAgency'] : null;
    }
    public function Add()
    {
        // Verification: La journee precedente doit etre cloturee
        // (sauf si c'est une operation antidatee)
        if (empty($_POST['Antidate'])) {
            if (!$this->CheckPreviousDayClosed($_POST['RefCaisse'])) {
                $_SESSION['message'] = array(
                    'type' => 'error',
                    'text' => 'Impossible d\'effectuer une operation. La journee precedente n\'a pas ete cloturee pour cette caisse.',
                    'number' => 2
                );
                header("location: /");
                exit;
            }
        }

        // Verification: Permission antidate requise pour antidater
        if (!empty($_POST['Antidate'])) {
            if (!$this->hasAntidatePermission()) {
                $_SESSION['message'] = array(
                    'type' => 'error',
                    'text' => 'Vous n\'avez pas la permission d\'antidater des operations.',
                    'number' => 2
                );
                header("location: /");
                exit;
            }
            $date = $_POST['Antidate'];
            
            // Log de l'operation antidatee pour audit
            $this->logAntidateOperation($_POST);
        } else {
            $date = date('Y-m-d');
        }
        $result = uniqid();
        if (intval($_POST['MontantVersement']) > 0  && !empty($_POST['MontantVersement'])  && !empty($_POST['RefCaisse']) && !empty($_POST['TelDeposant'])) {

            if ($_POST['RefType'] == 5) {
                // Transfert inter-caisses: utiliser une transaction pour garantir l'integrite
                $this->dao->beginTransaction();
                try {
                    $this->SortieCaisse2Caisse();
                    $this->ApproCaisse2Caisse();
                    $this->dao->commit();
                } catch (\Exception $e) {
                    $this->dao->rollback();
                    $_SESSION['message'] = array(
                        'type' => 'error',
                        'text' => 'Erreur lors du transfert inter-caisses: ' . $e->getMessage(),
                        'number' => 2
                    );
                    header("location: /");
                    exit;
                }
            } else {

                $requeteAddversement = $this->dao->prepare('INSERT INTO TbleOperations(RefCaisse,NumCompte,NameClient,MontantVersement,Remarque,Insert_Id,Insert_Time,Approve1_Id,Approve1_Time,Approve2_Id,Approve2_Time,Bordereau,NameDeposant,TelDeposant,RefType,TypeAppro,RefProduit,TypeRetrait,uniqid) VALUES(:RefCaisse,:NumCompte,:NameClient,:MontantVersement,:Remarque,:Insert_Id,:Insert_Time,:Approve1_Id,:Approve1_Time,:Approve2_Id,:Approve2_Time,:Bordereau,:NameDeposant,:TelDeposant,:RefType,:TypeAppro,:RefProduit,:TypeRetrait,:uniqid)');
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
                $requeteAddversement->bindValue(':TypeAppro', $_POST['TypeAppro'], \PDO::PARAM_INT);
                $requeteAddversement->bindValue(':RefProduit', $_POST['RefProduit'], \PDO::PARAM_INT);
                $requeteAddversement->bindValue(':TypeRetrait', $_POST['TypeRetrait'], \PDO::PARAM_INT);
                $requeteAddversement->bindValue(':uniqid', $result, \PDO::PARAM_STR);
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

            // =========================================
            // INTEGRATION CRM + LCB-FT
            // =========================================
            
            // Uniquement pour les depots et retraits (RefType 1 et 2)
            if (in_array($_POST['RefType'], [1, 2]) && !empty($_POST['NumCompte'])) {
                try {
                    // Recuperer l'agence de la caisse
                    $refAgency = $this->GetAgencyFromCaisse($_POST['RefCaisse']);
                    
                    // 1. Mettre a jour le profil client (CRM)
                    $clientManager = new ClientManagerPDO($this->dao);
                    $clientManager->createOrUpdateClient([
                        'NumCompte' => $_POST['NumCompte'],
                        'NameClient' => $_POST['NameClient'],
                        'TelDeposant' => $_POST['TelDeposant'],
                        'RefAgency' => $refAgency,
                        'MontantVersement' => $_POST['MontantVersement'],
                        'RefType' => $_POST['RefType']
                    ]);
                    
                    // 2. Analyser la transaction pour LCB-FT (Anti-blanchiment)
                    if (isset($Refoperations)) {
                        $lcbManager = new LCBManagerPDO($this->dao);
                        $lcbManager->analyzeTransaction($Refoperations);
                    }
                } catch (\Exception $e) {
                    // Log l'erreur mais ne bloque pas l'operation
                    error_log("Erreur CRM/LCB: " . $e->getMessage());
                }
            }
            // =========================================
            
            header("location: /");
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Opération réussie !';
            $_SESSION['message']['number'] = 2;
        } else {
            header("location: /");
            $_SESSION['message']['type'] = 'error';
            $_SESSION['message']['text'] = "Veuillez  reprendre l'operation. le Formulaire n'est pas remplit correctement,!";
            $_SESSION['message']['number'] = 2;
        }
    }
    public function YesterdaySolde($Agence = NULL)
    {
    }

    public function SommeVersementCaisse($Date)
    {

        if ($_SESSION['statut'] != 'admin') {

            $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleOperations.Insert_Id=:RefUsers AND (TbleOperations.RefType=1 OR TbleOperations.RefType=3)  ');
            $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
            $requeteSUm->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requeteSUm->execute();
            $data = $requeteSUm->fetch();
            return $data['TotalVersment'];
        } else {
            $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND (TbleOperations.RefType=1 OR TbleOperations.RefType=3)  ');
            $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
            $requeteSUm->execute();
            $data = $requeteSUm->fetch();
            return $data['TotalVersment'];
        }
    }
    public function SommeRetraitCaisse($Date)
    {
        if ($_SESSION['statut'] != 'admin') {
            $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleOperations.Insert_Id=:RefUsers AND (TbleOperations.RefType=2 OR TbleOperations.RefType=4)  ');
            $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
            $requeteSUm->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requeteSUm->execute();
            $data = $requeteSUm->fetch();
            return $data['TotalVersment'];
        } else {
            $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND (TbleOperations.RefType=2 OR TbleOperations.RefType=4)  ');
            $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
            $requeteSUm->execute();
            $data = $requeteSUm->fetch();
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
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleCaisse.RefCaisse WHERE TbleChmod.RefUsers=:RefUsers AND  TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND (TbleOperations.RefType=1)  ');
        $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
        return $data['TotalVersment'];
    }
    public function SommeRetraitStatistique($Date)
    {
        $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleCaisse.RefCaisse WHERE TbleChmod.RefUsers=:RefUsers AND   TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND (TbleOperations.RefType=2)');
        $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
        $requeteSUm->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requeteSUm->execute();
        $data = $requeteSUm->fetch();
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
            return $data['TotalVersment'];
        } else {
            $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour AND TbleChmod.RefUsers=:RefUsers   AND (TbleOperations.RefType=1 OR TbleOperations.RefType=3)  ');
            $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
            $requeteSUm->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requeteSUm->execute();
            $data = $requeteSUm->fetch();
            return $data['TotalVersment'];
        }
    }
    public function SommeRetraitAgence($Caisse, $Date)
    {
        if (!empty($Caisse)) {
            $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleOperations.RefCaisse=:RefCaisse AND (TbleOperations.RefType=2 OR TbleOperations.RefType=4)  ');
            $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
            $requeteSUm->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
            $requeteSUm->execute();
            $data = $requeteSUm->fetch();
            return $data['TotalVersment'];
        } else {
            $requeteSUm = $this->dao->prepare('SELECT SUM(MontantVersement) AS TotalVersment FROM TbleOperations INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse  WHERE TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND Approve2_Time=:jour  AND TbleChmod.RefUsers=:RefUsers AND (TbleOperations.RefType=2 OR TbleOperations.RefType=4)  ');
            $requeteSUm->bindValue(':jour', $Date, \PDO::PARAM_STR);
            $requeteSUm->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requeteSUm->execute();
            $data = $requeteSUm->fetch();
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
        $requete = $this->dao->prepare('SELECT * FROM TbleOperations WHERE RefOperations=:RefOperations');
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

        $from = "no-reply@malicreances-sa.com";
        $subject = "SORTIE DE FONDS ";

        $atitle = $subject;
        $alert = "Sortie de fonds de la " . $result['NameCaisse'] . " " . $result['NameAgency'] . " d'un  montant de : " . number_format($montant, 0, '.', '.') . " FCFA";

        require_once __DIR__ . '/../../Applications/App/Templates/templatemail.php';
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $to = "control_iob@malicreances-sa.com";
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

        $requeteAddversement = $this->dao->prepare('INSERT INTO TbleOperations(RefCaisse,NumCompte,NameClient,MontantVersement,Remarque,Insert_Id,Insert_Time,Approve1_Id,Approve1_Time,Approve2_Id,Approve2_Time,Bordereau,NameDeposant,TelDeposant,RefType,TypeAppro,RefProduit,TypeRetrait,uniqid) VALUES(:RefCaisse,:NumCompte,:NameClient,:MontantVersement,:Remarque,:Insert_Id,:Insert_Time,:Approve1_Id,:Approve1_Time,:Approve2_Id,:Approve2_Time,:Bordereau,:NameDeposant,:TelDeposant,:RefType,:TypeAppro,:RefProduit,:TypeRetrait,:uniqid)');
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
        $requeteAddversement = $this->dao->prepare('INSERT INTO TbleOperations(RefCaisse,NumCompte,NameClient,MontantVersement,Remarque,Insert_Id,Insert_Time,Approve1_Id,Approve1_Time,Approve2_Id,Approve2_Time,Bordereau,NameDeposant,TelDeposant,RefType,TypeAppro,RefProduit,TypeRetrait,uniqid) VALUES(:RefCaisse,:NumCompte,:NameClient,:MontantVersement,:Remarque,:Insert_Id,:Insert_Time,:Approve1_Id,:Approve1_Time,:Approve2_Id,:Approve2_Time,:Bordereau,:NameDeposant,:TelDeposant,:RefType,:TypeAppro,:RefProduit,:TypeRetrait,:uniqid)');
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

    /**
     * Verifie si une caisse peut etre rouverte
     * Conditions: 
     * 1. La caisse est fermee aujourd'hui
     * 2. On est dans les heures d'ouverture
     * 3. L'utilisateur a les droits (ChefCaisse, admin, superadmin)
     * 
     * @param int $refCaisse
     * @return array ['canReopen' => bool, 'reason' => string, 'fermeture' => array|null]
     */
    public function CanReopenCaisse($refCaisse)
    {
        $result = ['canReopen' => false, 'reason' => '', 'fermeture' => null];
        
        // 1. Verifier si l'utilisateur a les droits
        $allowedRoles = ['ChefCaisse', 'admin', 'superadmin', 'Head'];
        if (!in_array($_SESSION['statut'], $allowedRoles)) {
            $result['reason'] = 'Vous n\'avez pas les droits pour rouvrir une caisse. Roles autorises: Chef de Caisse, Admin.';
            return $result;
        }
        
        // 2. Verifier si la caisse est fermee aujourd'hui
        $stmtFermeture = $this->dao->prepare("
            SELECT ts.*, tc.NameCaisse, ta.NameAgency 
            FROM TbleSolde ts
            INNER JOIN TbleCaisse tc ON tc.RefCaisse = ts.RefCaisse
            INNER JOIN TbleAgency ta ON ta.RefAgency = tc.RefAgency
            WHERE ts.RefCaisse = :refCaisse AND DATE(ts.DateSolde) = :today
        ");
        $stmtFermeture->bindValue(':refCaisse', $refCaisse, \PDO::PARAM_INT);
        $stmtFermeture->bindValue(':today', date('Y-m-d'), \PDO::PARAM_STR);
        $stmtFermeture->execute();
        $fermeture = $stmtFermeture->fetch(\PDO::FETCH_ASSOC);
        
        if (!$fermeture) {
            $result['reason'] = 'Cette caisse n\'est pas fermee aujourd\'hui.';
            return $result;
        }
        
        $result['fermeture'] = $fermeture;
        
        // 3. Verifier si on est dans les heures d'ouverture
        $dayOfWeek = (date('w') == 0) ? 7 : date('w'); // Dimanche = 7
        $stmtOuverture = $this->dao->prepare("
            SELECT * FROM TbleOuverture 
            WHERE RefCaisse = :refCaisse 
            AND RefDays = :dayOfWeek
            AND NOW() BETWEEN HeureDebut AND HeureFin
        ");
        $stmtOuverture->bindValue(':refCaisse', $refCaisse, \PDO::PARAM_INT);
        $stmtOuverture->bindValue(':dayOfWeek', $dayOfWeek, \PDO::PARAM_INT);
        $stmtOuverture->execute();
        
        if ($stmtOuverture->rowCount() == 0) {
            $result['reason'] = 'La reouverture n\'est possible que pendant les heures d\'ouverture de la caisse.';
            return $result;
        }
        
        // Toutes les conditions sont remplies
        $result['canReopen'] = true;
        $result['reason'] = 'La caisse peut etre rouverte.';
        return $result;
    }

    /**
     * Rouvre une caisse fermee par erreur
     * 
     * @param int $refCaisse
     * @param string $motif Raison de la reouverture
     * @return array ['success' => bool, 'message' => string]
     */
    public function ReopenCaisse($refCaisse, $motif = '')
    {
        // Verifier si on peut rouvrir
        $check = $this->CanReopenCaisse($refCaisse);
        
        if (!$check['canReopen']) {
            return ['success' => false, 'message' => $check['reason']];
        }
        
        $fermeture = $check['fermeture'];
        
        try {
            $this->dao->beginTransaction();
            
            // 1. Logger la reouverture AVANT de supprimer
            $stmtLog = $this->dao->prepare("
                INSERT INTO TbleReouvertureCaisse 
                (RefCaisse, RefSolde, RefUsers, Motif, SoldeAnnule) 
                VALUES (:refCaisse, :refSolde, :refUsers, :motif, :solde)
            ");
            $stmtLog->bindValue(':refCaisse', $refCaisse, \PDO::PARAM_INT);
            $stmtLog->bindValue(':refSolde', $fermeture['RefSolde'], \PDO::PARAM_INT);
            $stmtLog->bindValue(':refUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $stmtLog->bindValue(':motif', $motif ?: 'Fermeture par erreur', \PDO::PARAM_STR);
            $stmtLog->bindValue(':solde', $fermeture['Solde'], \PDO::PARAM_STR);
            $stmtLog->execute();
            
            // 2. Supprimer la fermeture
            $stmtDelete = $this->dao->prepare("DELETE FROM TbleSolde WHERE RefSolde = :refSolde");
            $stmtDelete->bindValue(':refSolde', $fermeture['RefSolde'], \PDO::PARAM_INT);
            $stmtDelete->execute();
            
            $this->dao->commit();
            
            return [
                'success' => true, 
                'message' => 'Caisse ' . $fermeture['NameCaisse'] . ' rouverte avec succes. L\'operation a ete tracee.'
            ];
            
        } catch (\Exception $e) {
            $this->dao->rollBack();
            return ['success' => false, 'message' => 'Erreur lors de la reouverture: ' . $e->getMessage()];
        }
    }

    /**
     * Liste les caisses fermees aujourd'hui pour l'utilisateur
     * (Pour afficher dans l'interface de reouverture)
     * 
     * @return array Liste des caisses fermees
     */
    public function GetClosedCaissesToday()
    {
        $ChomdUser = $this->ChomdUser();
        if (empty($ChomdUser)) {
            return [];
        }
        
        $caisseIds = array_column($ChomdUser, 'RefCaisse');
        $placeholders = implode(',', array_fill(0, count($caisseIds), '?'));
        
        $sql = "
            SELECT ts.*, tc.NameCaisse, ta.NameAgency,
                   CASE WHEN ts.AutoClose = 1 THEN 'Automatique' ELSE 'Manuelle' END AS TypeFermeture
            FROM TbleSolde ts
            INNER JOIN TbleCaisse tc ON tc.RefCaisse = ts.RefCaisse
            INNER JOIN TbleAgency ta ON ta.RefAgency = tc.RefAgency
            WHERE ts.RefCaisse IN ($placeholders)
            AND DATE(ts.DateSolde) = ?
            ORDER BY tc.NameCaisse
        ";
        
        $stmt = $this->dao->prepare($sql);
        foreach ($caisseIds as $index => $id) {
            $stmt->bindValue($index + 1, $id, \PDO::PARAM_INT);
        }
        $stmt->bindValue(count($caisseIds) + 1, date('Y-m-d'), \PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Historique des reouvertures pour audit
     * 
     * @param int $limit
     * @return array
     */
    public function GetReouvertureHistory($limit = 50)
    {
        $stmt = $this->dao->prepare("
            SELECT r.*, tc.NameCaisse, ta.NameAgency, 
                   CONCAT(u.PrenomUsers, ' ', u.NomUsers) AS NomComplet
            FROM TbleReouvertureCaisse r
            INNER JOIN TbleCaisse tc ON tc.RefCaisse = r.RefCaisse
            INNER JOIN TbleAgency ta ON ta.RefAgency = tc.RefAgency
            INNER JOIN TbleUsers u ON u.RefUsers = r.RefUsers
            ORDER BY r.DateReouverture DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
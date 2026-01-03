<?php

namespace Applications\App\Modules\Bielletage;

class BielletageController extends \Library\BackController
{

    public function executeIndex(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Accueil"); // Titre de la page

        // Récupération des données pour l'affichage de l'accueil
        $data = $this->getHomeData();

        // Ajout des données à la vue
        $this->page->addVar("CheckOuverture", $data['checkOuverture']);
        $this->page->addVar('Operation', $data['operations']);
        $this->page->addVar('Agence', $data['agence']);
        $this->page->addVar('Solde', $data['solde']);
        $this->page->addVar('SoldeGlobal', $data['soldeGlobal']);
        $this->page->addVar('SommeVersement', $data['sommeVersement']);
        $this->page->addVar('SommeRetrait', $data['sommeRetrait']);
        $this->page->addVar('SommeVersementGlobal', $data['sommeVersementGlobal']);
        $this->page->addVar('SommeRetraitGlobal', $data['sommeRetraitGlobal']);
        $this->page->addVar('SommeRemittanceDepot', $data['sommeRemittanceDepot']);
        $this->page->addVar('SommeRemittanceRetrait', $data['sommeRemittanceRetrait']);
        $this->page->addVar('SoldeRemittance', $data['soldeRemittance']);
        $this->page->addVar('links', $data['links']);
        
        // Variables manquantes pour la vue
        $FirstLogin = $this->managers->getManagerOf('User')->FirstLogin();
        $this->page->addVar('FirstLogin', $FirstLogin);
        
        $AllPermissions = $this->managers->getManagerOf('Pannel')->UserPermission();
        $permissions = is_array($AllPermissions) ? array_column($AllPermissions, 'access') : [];
        $this->page->addVar('permission', $permissions);
    }

    private function getHomeData()
    {
        $today = date('Y-m-d');
        
        // Récupération des données pour l'affichage de l'accueil
        $checkOuverture = $this->managers->getManagerOf("Bielletage")->CheckOuverture();
        $operations = $this->managers->getManagerOf('Bielletage')->GetCaisse();
        
        // OPTIMISATION: Utiliser la methode optimisee (1 requete au lieu de 10+ par caisse)
        $usersCaisse = $this->managers->getManagerOf("Journal")->UserCaisseOptimized($today);

        // Calcul des totaux
        $solde = 0;
        $soldeGlobal = 0;
        $sommeVersement = 0;
        $sommeRetrait = 0;
        $sommeRemittanceDepot = 0;
        $sommeRemittanceRetrait = 0;
        $soldeRemittance = 0;
        foreach ($usersCaisse as $user) {
            $solde += $user['SoldeDisponible'];
            $soldeGlobal += $user['SoldeDisponibleGlobal'];
            $sommeVersement += $user['TotalVersement'];
            $sommeRetrait += $user['TotalRetrait'];
            $sommeRemittanceDepot += $user['SommeVersementRemittance'];
            $sommeRemittanceRetrait += $user['SommeRetraitRemittance'];
            $soldeRemittance += $user['SoldeRemittance'];
        }
        $sommeVersementGlobal = $sommeVersement + $sommeRemittanceDepot;
        $sommeRetraitGlobal = $sommeRetrait + $sommeRemittanceRetrait;
        
        // OPTIMISATION: Utiliser la methode optimisee pour les agences
        $agence = $this->managers->getManagerOf("Journal")->UserAgenceOptimized($today);

        // Récupération des liens pour le menu
        $links = $this->managers->getManagerOf('Pannel')->GetLinks();

        return array(
            'checkOuverture' => $checkOuverture,
            'operations' => $operations,
            'agence' => $agence,
            'solde' => $solde,
            'soldeGlobal' => $soldeGlobal,
            'sommeVersement' => $sommeVersement,
            'sommeRetrait' => $sommeRetrait,
            'sommeVersementGlobal' => $sommeVersementGlobal,
            'sommeRetraitGlobal' => $sommeRetraitGlobal,
            'sommeRemittanceDepot' => $sommeRemittanceDepot,
            'sommeRemittanceRetrait' => $sommeRemittanceRetrait,
            'soldeRemittance' => $soldeRemittance,
            'links' => $links
        );
    }



    public function executeStopcaisse(\Library\HTTPRequest $request)
    {
        // Get manager objects
        $managerBielletage = $this->managers->getManagerOf('Bielletage');
        $managerArreter = $this->managers->getManagerOf('Arreter');

        // Get ID from request
        $id = $request->getData('id');

        // Get SommeVersement and SommeRetrait
        $SommeVersement = $managerBielletage->SommeVersementAgence($id, date('Y-m-d'));
        $SommeRetrait = $managerBielletage->SommeRetraitAgence($id, date('Y-m-d'));

        // Add variables to page
        $this->page->addVar('SommeVersement', $SommeVersement);
        $this->page->addVar('SommeRetrait', $SommeRetrait);

        // Get YesterdaySolde
        $Yesterday = $managerBielletage->YesterdaySolde($id);

        // Calculate Solde
        $Solde = $SommeVersement - $SommeRetrait;

        // StopCaisse
        $managerArreter->StopCaisse($id, $Solde, ('Y-m-d H:i:s'));

        // Redirect
        $this->app()->httpResponse()->redirect('/Arreter/index');
    }

    public function executeBielletage(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Nouvelle Opération");

        $manager = $this->managers->getManagerOf("Bielletage");
        $Chmod = ($_GET['id'] == 3) ? $manager->CheckOuverture(1) : $manager->CheckOuverture();
        $this->page->addVar("CheckOuverture", $Chmod);

        $TypeAppro = $this->managers->getManagerOf("Journal")->TypeAppro();
        $this->page->addVar("TypeAppro", $TypeAppro);

        $TypeRetrait = $manager->TypeRetrait();
        $this->page->addVar("TypeRetrait", $TypeRetrait);

        $AllPermissions = $this->managers->getManagerOf('Pannel')->UserPermission();
        $permissions = array_column($AllPermissions, 'access');
        $this->page->addVar('permission', $permissions);
    }


    public function executeInvoice(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Bordereau"); // Titre de la page
        $this->page->setTemplate('bordereau');
        if ($request->method() == 'POST') {
            $reference  = $request->postData('id');
        } else {
            $reference = $request->getData('id');
        }
        $Invoice  = $this->managers->getManagerOf("Bielletage")->GetInvoice($reference); //Recuperation de la liste
        $this->page->addVar("GetInvoice", $Invoice); // Creation de la variable, ajout d'une variable a la vue
        $getResetStatus = $this->managers->getManagerOf("Bielletage")->getResetStatus($reference);
        $this->page->addVar("getResetStatus", $getResetStatus); // Creation de la variable, ajout d'une variable a la vue

        // Conversion du montant en lettres
        require_once $_SERVER['DOCUMENT_ROOT'] . '/config/nombre_en_lettre.php';
        $montant = $Invoice['MontantVersement'] ?? 0;
        $numberToLetter = NumberToLetter($montant);
        $this->page->addVar("numberToLetter", $numberToLetter);
    }
    public function executeAdd(\Library\HTTPRequest $request)
    {
        $GetAgencyUsingCaisseID = $this->managers->getManagerOf("Pannel")->GetAgencyUsingCaisseID($request->postData('RefCaisse'));
        $refAgency = $GetAgencyUsingCaisseID['RefAgency'];
        $journalManager = $this->managers->getManagerOf("Journal");
        
        // Verifier que la veille est cloturee (sauf pour admins et operations antidatees)
        $allowedRoles = ['admin', 'superadmin'];
        if (empty($request->postData('Antidate')) && !in_array($_SESSION['statut'], $allowedRoles)) {
            if (!$journalManager->IsYesterdayClosed($refAgency, date('Y-m-d'))) {
                $_SESSION['message'] = array(
                    'type' => 'error',
                    'text' => 'Vous devez clôturer la journée précédente avant de pouvoir effectuer une nouvelle opération. Veuillez contacter votre administrateur.',
                    'number' => 2
                );
                $this->app()->httpResponse()->redirect('/bielletage/' . $request->postData('RefType'));
                return;
            }
        }
        
        $YesterdayReserveData = $journalManager->YesterdayReserve($refAgency, date('Y-m-d'));
        $YesterdayReserve = $YesterdayReserveData['SoldeCompte'] ?? 0;
        $VerifAppro  = $journalManager->TotalApproAgenceGlobal(date('Y-m-d'), $refAgency);

        if (!empty($request->postData('Antidate'))) {
            //Antidate Operation
            $this->managers->getManagerOf("Bielletage")->Add(); //Recuperation de la liste
        } else {

            if ($VerifAppro == 0 && ($request->postData('RefType') == 1 || $request->postData('RefType') == 2)) {
                $_SESSION['message']['type'] = 'warning';
                $_SESSION['message']['text'] = 'Vous devez approvisionner la caisse avant de pouvoir effectuer une opération';
                $_SESSION['message']['number'] = 2;
                $this->app()->httpResponse()->redirect('/bielletage/' . $request->postData('RefType'));
            } else {

                if ($request->postData('RefType') == 3 && $request->postData('TypeAppro') == 1) {
                    if ($request->postData('MontantVersement') <= $YesterdayReserve) {
                        $this->managers->getManagerOf("Bielletage")->Add(); //Recuperation de la liste
                    } else {
                        $_SESSION['message']['type'] = 'warning';
                        $_SESSION['message']['text'] = 'Le Montant de la transaction est supérieur au solde de la reserve.';
                        $_SESSION['message']['number'] = 2;
                        $this->app()->httpResponse()->redirect('/bielletage/' . $request->postData('RefType'));
                    }
                } elseif ($request->postData('RefType') == 4 or $request->postData('RefType') == 2 or $request->postData('RefType') == 5) {
                    $SoldeActuelleCaisse = $this->managers->getManagerOf("Journal")->SoldeActuelleCaisse(date('Y-m-d'), $request->postData('RefCaisse'));
                    if ($request->postData('MontantVersement') <= $SoldeActuelleCaisse) {
                        $this->managers->getManagerOf("Bielletage")->Add(); //Recuperation de la liste
                    } else {
                        $_SESSION['message']['type'] = 'warning';
                        $_SESSION['message']['text'] = 'Le Montant de la transaction supérieur au solde de la caisse. Veuillez faire un appro de la caisse ou Contactez votre administrateur .';
                        $_SESSION['message']['number'] = 2;
                        $this->app()->httpResponse()->redirect('/bielletage/' . $request->postData('RefType'));
                    }
                } else {
                    $this->managers->getManagerOf("Bielletage")->Add(); //Recuperation de la liste
                }
            }
        }
    }

    public function executeDashboard(\Library\HTTPRequest $request)
    {
        $today = date('Y-m-d');
        $this->page->addVar("titles", "Dashboard"); // Titre de la page
        
        $Biellet = $this->managers->getManagerOf('Arreter')->GetDailyBielletage($today);
        $this->page->addVar('Biellet', $Biellet);
        $DailyVersement = $this->managers->getManagerOf('Bielletage')->DailyVersement();
        $this->page->addVar('DailyVersement', $DailyVersement);
        
        // OPTIMISATION: Utiliser la methode optimisee (1 requete au lieu de 10+ par caisse)
        $UsersCaisse = $this->managers->getManagerOf("Journal")->UserCaisseOptimized($today);
        
        $Solde = 0;
        $SommeVersement = 0;
        $SommeRetrait = 0;
        $SommeRemittanceDepot = 0;
        $SommeRemittanceRetrait = 0;
        $SoldeRemittance = 0;
        $SoldeGlobal = 0;
        foreach ($UsersCaisse as $key => $value) {
            $Solde += $value['SoldeDisponible'];
            $SoldeGlobal += $value['SoldeDisponibleGlobal'];
            $SommeVersement += $value['TotalVersement'];
            $SommeRetrait += $value['TotalRetrait'];
            $SommeRemittanceDepot += $value['SommeVersementRemittance'];
            $SommeRemittanceRetrait += $value['SommeRetraitRemittance'];
            $SoldeRemittance += $value['SoldeRemittance'];
        }

        // OPTIMISATION: Utiliser la methode optimisee pour les agences
        $Agence = $this->managers->getManagerOf("Journal")->UserAgenceOptimized($today);
        
        $this->page->addVar('Agence', $Agence);
        $this->page->addVar('Solde', $Solde);
        $this->page->addVar('SoldeGlobal', $SoldeGlobal);
        $this->page->addVar('SommeVersement', $SommeVersement);
        $this->page->addVar('SommeRetrait', $SommeRetrait);
        $this->page->addVar('SommeVersementGlobal', $SommeVersement + $SommeRemittanceDepot);
        $this->page->addVar('SommeRetraitGlobal', $SommeRetrait + $SommeRemittanceRetrait);
        $this->page->addVar('SommeRemittanceDepot', $SommeRemittanceDepot);
        $this->page->addVar('SommeRemittanceRetrait', $SommeRemittanceRetrait);
        $this->page->addVar('SoldeRemittance', $SoldeRemittance);
    }

    /**
     * Affiche la page de reouverture des caisses
     * Accessible: ChefCaisse, admin, superadmin, Head
     */
    public function executeReouvrircaisse(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Réouverture de Caisse");
        
        // Verifier les droits
        $allowedRoles = ['ChefCaisse', 'admin', 'superadmin', 'Head'];
        if (!in_array($_SESSION['statut'], $allowedRoles)) {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Vous n\'avez pas accès à cette fonctionnalité.',
                'number' => 2
            ];
            $this->app()->httpResponse()->redirect('/');
            return;
        }
        
        // Recuperer les caisses fermees aujourd'hui
        $closedCaisses = $this->managers->getManagerOf('Bielletage')->GetClosedCaissesToday();
        $this->page->addVar('ClosedCaisses', $closedCaisses);
        
        // Historique des reouvertures (pour admin seulement)
        if ($_SESSION['statut'] == 'admin' || $_SESSION['statut'] == 'superadmin') {
            $history = $this->managers->getManagerOf('Bielletage')->GetReouvertureHistory(20);
            $this->page->addVar('ReouvertureHistory', $history);
        }
    }

    /**
     * Action de reouverture d'une caisse
     */
    public function executeDoReopen(\Library\HTTPRequest $request)
    {
        $refCaisse = $request->postData('RefCaisse');
        $motif = $request->postData('Motif');
        
        if (empty($refCaisse)) {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Caisse non spécifiée.',
                'number' => 2
            ];
            $this->app()->httpResponse()->redirect('/bielletage/reouvrircaisse');
            return;
        }
        
        $result = $this->managers->getManagerOf('Bielletage')->ReopenCaisse($refCaisse, $motif);
        
        $_SESSION['message'] = [
            'type' => $result['success'] ? 'success' : 'error',
            'text' => $result['message'],
            'number' => $result['success'] ? 1 : 2
        ];
        
        $this->app()->httpResponse()->redirect('/bielletage/reouvrircaisse');
    }

    /**
     * API pour verifier si une caisse peut etre rouverte (AJAX)
     */
    public function executeCheckReopen(\Library\HTTPRequest $request)
    {
        header('Content-Type: application/json');
        
        $refCaisse = $request->getData('id');
        $result = $this->managers->getManagerOf('Bielletage')->CanReopenCaisse($refCaisse);
        
        echo json_encode($result);
        exit;
    }
}
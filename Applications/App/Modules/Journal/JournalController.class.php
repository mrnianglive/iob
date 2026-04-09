<?php

namespace Applications\App\Modules\Journal;

class JournalController extends \Library\BackController
{
    public function executeIndex(\Library\HTTPRequest $request)
    {
        $pageTitle = "Journal de Caisse";
        $this->page->addVar("titles", $pageTitle);

        $bielletageManager = $this->managers->getManagerOf("Bielletage");
        $Chmod = $bielletageManager->CheckOuverture();
        $this->page->addVar("CheckOuverture", $Chmod);

        $pannelManager = $this->managers->getManagerOf("Pannel");
        $Agence = $pannelManager->UserAgence();
        $this->page->addVar('UserAgence', $Agence);

        $Debut = $request->postData('Debut');
        $Fin = $request->postData('Fin');
        $Value = $request->postData('RefAgency');
        $RefProduit = $request->postData('RefProduit');

        // $this->page->addVars(compact('Debut', 'Fin', 'Value', 'RefProduit'));

        $this->page->addVar('Debut', $Debut);
        $this->page->addVar('Fin', $Fin);
        $this->page->addVar('Value', $Value);

        $this->page->addVar('RefProduit', $RefProduit);

        $ListeAgence = $pannelManager->ListeAgence();
        $this->page->addVar("ListeAgence", $ListeAgence);

        $journalManager = $this->managers->getManagerOf('Journal');
        $Operations = [];

        if (!empty($Value) || isset($_GET['value'])) {
            $Debut = $_GET['debut'] ?? $Debut;
            $Fin = $_GET['fin'] ?? $Fin;
            $Value = $_GET['value'] ?? $Value;
            $RefProduit = $_GET['produit'] ?? $RefProduit;

            $Operations = $journalManager->GetOperations($Debut, $Fin, $Value, $RefProduit);
            // $this->page->addVars(compact('Debut', 'Fin', 'Value', 'RefProduit'));
            $this->page->addVar('Debut', $Debut);
            $this->page->addVar('Fin', $Fin);
            $this->page->addVar('Value', $Value);
            $this->page->addVar('RefProduit', $RefProduit);
        } else {
            $Operations = $journalManager->Operations();
        }
        $this->page->addVar('Operations', $Operations);

        $sommeVersementPeriode = $journalManager->sommeVersementPeriode($Debut, $Fin, $Value, $RefProduit);
        $this->page->addVar('sommeVersementPeriode', $sommeVersementPeriode);

        $sommeRetraitPeriode = $journalManager->sommeRetraitPeriode($Debut, $Fin, $Value, $RefProduit);
        $this->page->addVar('sommeRetraitPeriode', $sommeRetraitPeriode);

        $UsersCaisse = $journalManager->UserCaisse(date('Y-m-d'));
        $SoldeGlobal = 0;
        foreach ($UsersCaisse as $key => $value) {
            $SoldeGlobal += $value['SoldeDisponibleGlobal'];
        }
        $this->page->addVar('Solde', $SoldeGlobal);

        $this->page->addVar('match', $journalManager);

        $permissions = [];
        $AllPermissions = $pannelManager->UserPermission();
        foreach ($AllPermissions as $key => $value) {
            $permissions[] = $value['access'];
        }
        $this->page->addVar('permission', $permissions);
    }

    public function executeValidate(\Library\HTTPRequest $request)
    {
        $this->managers->getManagerOf("Journal")->ValidateOperations($request);
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Opération validée avec succès';
        $_SESSION['message']['number'] = 2;
        if (!empty($request->postData('Debut')) && !empty($request->postData('Fin'))) {
            $this->app()->httpResponse()->redirect("/Journal/index/" . $request->postData('Debut') . "/" . $request->postData('Fin') . "/" . $request->postData('RefAgency') . "/" . $request->postData('RefProduit')); //Retour en arriere
        } else {
            $this->app()->httpResponse()->redirect("/Journal/index"); //Retour en arriere
        }
    }
    public function executeCancelvalidate(\Library\HTTPRequest $request)
    {
        $this->managers->getManagerOf("Journal")->CancelValidate($request->getData('id'));
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Validation annulée avec succès';
        $_SESSION['message']['number'] = 2;
        $this->app()->httpResponse()->redirect("/Journal/index"); //Retour en arriere
    }

    public function executeDelete(\Library\HTTPRequest $request)
    {
        $id = $request->getData('id');
        
        try {
            // Démarrer la suppression en arrière-plan
            $this->managers->getManagerOf("Journal")->DeleteOperations($id);
            
            $this->setMessageAndRedirect('info', 'La suppression est en cours de traitement. Cela peut prendre quelques instants...');
        } catch (\Exception $e) {
            // En cas d'erreur, afficher un message d'erreur
            $this->setMessageAndRedirect('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

   

    private function setMessageAndRedirect($type, $text)
    {
        $_SESSION['message'] = ['type' => $type, 'text' => $text, 'number' => 2];
        $this->app()->httpResponse()->redirect('/Journal/index');
    }

    public function executePetitecaisse(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Petite Caisse");

        if (!empty($request->postData('jour'))) {
            $date = $request->postData('jour');
            $this->page->addVar('day', $request->postData('jour'));
        } elseif (!empty($request->getData('jour'))) {
            $date = $request->getData('jour');
            $this->page->addVar('day', $request->getData('jour'));
        } else {
            $date = date('Y-m-d');
            $this->page->addVar('day', $date);
        }

        // Get agencies based on user role
        if ($_SESSION['statut'] == 'admin' || $_SESSION['statut'] == 'superadmin' || $_SESSION['statut'] == 'Control') {
            $Agence = $this->managers->getManagerOf("Pannel")->ListeAgence();
        } else {
            $Agence = $this->managers->getManagerOf("Pannel")->UserAgence();
        }

        // Use optimized method that combines all queries into 1-2 SQL queries
        $Agence = $this->managers->getManagerOf("Journal")->GetPetiteCaisseDataOptimized($date, $Agence);

        $this->page->addVar('Agence', $Agence);
    }

    public function executeCancelFermeture(\Library\HTTPRequest $request)
    {
        $this->managers->getManagerOf("Journal")->CancelFermeture($request->getData('id'), $request->getData('RefAgency'), $request->getData('day'));
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Fermeture annulée avec succès';
        $_SESSION['message']['number'] = 3;
        $this->app()->httpResponse()->redirect("/Journal/petite_caisse"); //Retour en arriere
    }



    public function executeNoverified(\Library\HTTPRequest $request)
    {
        $pageTitle = "Journal de Caisse des opérations non vérifiées";
        $this->page->addVar("titles", $pageTitle);

        $pannelManager = $this->managers->getManagerOf("Pannel");
        $ListeAgence = $pannelManager->ListeAgence();
        $this->page->addVar("ListeAgence", $ListeAgence);

        $JournalManager = $this->managers->getManagerOf("Journal");

        $Operations = $JournalManager->GetOperationsNonVerifiees();

        $CountOperationsNonVerifiees
            = $JournalManager->CountOperationsNonVerifiees();
        $this->page->addVar('CountOperationsNonVerifiees', $CountOperationsNonVerifiees);

        $pannelManager = $this->managers->getManagerOf("Pannel");
        $permissions = [];
        $AllPermissions = $pannelManager->UserPermission();
        foreach ($AllPermissions as $key => $value) {
            $permissions[] = $value['access'];
        }
        $this->page->addVar('permission', $permissions);
        $this->page->addVar('Operations', $Operations);
    }


    public function executeCanceled(\Library\HTTPRequest $request)
    {
        $pageTitle = "Journal de Caissse des opérations annulées";
        $this->page->addVar("titles", $pageTitle);

        $bielletageManager = $this->managers->getManagerOf("Bielletage");
        $Chmod = $bielletageManager->CheckOuverture();
        $this->page->addVar("CheckOuverture", $Chmod);

        $pannelManager = $this->managers->getManagerOf("Pannel");
        $Agence = $pannelManager->UserAgence();
        $this->page->addVar('UserAgence', $Agence);

        $Debut = $request->postData('Debut');
        $Fin = $request->postData('Fin');
        $Value = $request->postData('RefAgency');

        $this->page->addVar('Debut', $Debut);
        $this->page->addVar('Fin', $Fin);
        $this->page->addVar('Value', $Value);

        $ListeAgence = $pannelManager->ListeAgence();
        $this->page->addVar("ListeAgence", $ListeAgence);

        $journalManager = $this->managers->getManagerOf('Journal');
        $Operations = [];

        if (!empty($Value) || isset($_GET['value'])) {
            $Debut = $_GET['debut'] ?? $Debut;
            $Fin = $_GET['fin'] ?? $Fin;
            $Value = $_GET['value'] ?? $Value;

            $Operations = $journalManager->GetCanceledOperations($Debut, $Fin, $Value);
            $this->page->addVar('Debut', $Debut);
            $this->page->addVar('Fin', $Fin);
            $this->page->addVar('Value', $Value);
        } else {
            $Operations = $journalManager->GetCanceledOperations();
        }
        $this->page->addVar('Operations', $Operations);

        $permissions = [];
        $AllPermissions = $pannelManager->UserPermission();
        foreach ($AllPermissions as $key => $value) {
            $permissions[] = $value['access'];
        }
        $this->page->addVar('permission', $permissions);
    }

    public function executeGestionfermeture(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Gestion des Fermetures");

        // Pour admin/control/superadmin : toutes les agences, sinon seulement les agences de l'utilisateur
        if ($_SESSION['statut'] == 'admin' || $_SESSION['statut'] == 'superadmin' || $_SESSION['statut'] == 'Control') {
            $Agence = $this->managers->getManagerOf("Pannel")->ListeAgence();
        } else {
            $Agence = $this->managers->getManagerOf("Pannel")->UserAgence();
        }
        $this->page->addVar('Agence', $Agence);

        $currentYear = date('Y');
        $currentMonth = date('n');

        if ($request->postData('year')) {
            $year = $request->postData('year');
            $this->page->addVar('year', $year);
        } else {
            $year = $currentYear;
            $this->page->addVar('year', $year);
        }

        if ($request->postData('month')) {
            $month = $request->postData('month');
            $this->page->addVar('month', $month);
        } else {
            $month = $currentMonth;
            $this->page->addVar('month', $month);
        }

        if ($request->postData('RefAgency')) {
            $selectedAgency = $request->postData('RefAgency');
            $this->page->addVar('selectedAgency', $selectedAgency);
        } elseif (!empty($Agence)) {
            $selectedAgency = $Agence[0]['RefAgency'];
            $this->page->addVar('selectedAgency', $selectedAgency);
        }
    }

    public function executeGetPetiteCaisseData(\Library\HTTPRequest $request)
    {
        $date = $request->postData('jour');
        
        if (!$date) {
            $this->jsonResponse(['error' => 'Date parameter missing'], 400);
            return;
        }

        // Pour admin/control/superadmin : toutes les agences, sinon seulement les agences de l'utilisateur
        if ($_SESSION['statut'] == 'admin' || $_SESSION['statut'] == 'superadmin' || $_SESSION['statut'] == 'Control') {
            $Agence = $this->managers->getManagerOf("Pannel")->ListeAgence();
        } else {
            $Agence = $this->managers->getManagerOf("Pannel")->UserAgence();
        }

        $journalManager = $this->managers->getManagerOf("Journal");
        $petiteCaisseData = $journalManager->GetPetiteCaisseDataOptimized($date, $Agence);

        $this->jsonResponse([
            'success' => true,
            'data' => $petiteCaisseData,
            'date' => $date
        ]);
    }

    public function executeGetClosureStatus(\Library\HTTPRequest $request)
    {
        $year = $request->postData('year');
        $month = $request->postData('month');
        $agency = $request->postData('RefAgency');

        if (!$year || !$month || !$agency) {
            $this->jsonResponse(['error' => 'Missing parameters'], 400);
            return;
        }

        $journalManager = $this->managers->getManagerOf("Journal");
        $closures = $journalManager->GetMonthlyClosureStatus($agency, $year, $month);
        $daysWithOps = $journalManager->GetDaysWithOperations($agency, $year, $month);

        $this->jsonResponse([
            'closures' => $closures,
            'daysWithOps' => $daysWithOps
        ]);
    }

    public function executeFermerAgence(\Library\HTTPRequest $request)
    {
        $agency = $request->postData('RefAgency');
        $date = $request->postData('date');
        $solde = $request->postData('SoldeActuelle');

        if (!$agency || !$date || $solde === null) {
            $_SESSION['message']['type'] = 'error';
            $_SESSION['message']['text'] = 'Paramètres manquants';
            $_SESSION['message']['number'] = 3;
            $this->app()->httpResponse()->redirect("/Journal/gestion_fermeture");
            return;
        }

        $time = $date . ' ' . date('H:i:s');
        $this->managers->getManagerOf("Journal")->Reserve();

        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Fermeture effectuée avec succès';
        $_SESSION['message']['number'] = 3;
        $this->app()->httpResponse()->redirect("/Journal/gestion_fermeture");
    }
}
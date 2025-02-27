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
        
        // Récupération de la date
        $date = $request->postData('jour') ?: date('Y-m-d');
        $this->page->addVar('day', $date);
        
        // Chargement asynchrone des données
        $this->loadPetiteCaisseData($date);
        $this->loadSoldeReserveData($date);
    }

    private function loadPetiteCaisseData($date)
    {
        $journalManager = $this->managers->getManagerOf("Journal");
        $pannelManager = $this->managers->getManagerOf("Pannel");
        $agences = $pannelManager->UserAgence();
        
        $petiteCaisseData = [];
        foreach ($agences as $agence) {
            $caisseData = [
                'RefAgency' => $agence['RefAgency'],
                'NameAgency' => $agence['NameAgency'],
                'Afficher' => $journalManager->CaisseAgence($agence['RefAgency'], $date)
            ];
            
            // Calcul des totaux pour chaque caisse
            foreach ($caisseData['Afficher'] as &$caisse) {
                $caisse['SoldeRemittanceVersement'] = floatval($journalManager->SoldeRemittanceVersementAgence($date, $agence['RefAgency']));
                $caisse['SoldeRemittanceRetrait'] = floatval($journalManager->SoldeRemittanceRetraitAgence($date, $agence['RefAgency']));
            }
            
            $petiteCaisseData[] = $caisseData;
        }
        
        $this->page->addVar('PetiteCaisseData', $petiteCaisseData);
    }

    private function loadSoldeReserveData($date)
    {
        $journalManager = $this->managers->getManagerOf("Journal");
        $pannelManager = $this->managers->getManagerOf("Pannel");
        $agences = $pannelManager->UserAgence();
        
        $soldeReserveData = [];
        foreach ($agences as $agence) {
            $reserveData = [
                'RefAgency' => $agence['RefAgency'],
                'NameAgency' => $agence['NameAgency']
            ];
            
            // Récupération des données de réserve
            $yesterdayData = $journalManager->YesterdayReserve($agence['RefAgency'], $date);
            $reserveData['YesterdayReserve'] = floatval($yesterdayData['SoldeCompte']);
            $reserveData['LastDate'] = $journalManager->displayDaysSinceLastDate($yesterdayData['DateSolde']);
            
            // Calcul des mouvements
            $reserveData['SommeDepot'] = floatval($journalManager->SommeDepotAgence($date, $agence['RefAgency']));
            $reserveData['SommeSortie'] = floatval($journalManager->SommeRetraitAgence($date, $agence['RefAgency']));
            $reserveData['SommeDepotRemittance'] = floatval($journalManager->SoldeRemittanceVersementAgence($date, $agence['RefAgency']));
            $reserveData['SommeRetraitRemittance'] = floatval($journalManager->SoldeRemittanceRetraitAgence($date, $agence['RefAgency']));
            
            // Calculs des totaux
            $reserveData['SommeDepotWithRemittance'] = $reserveData['SommeDepot'] + $reserveData['SommeDepotRemittance'];
            $reserveData['SommeSortieWithRemittance'] = $reserveData['SommeSortie'] + $reserveData['SommeRetraitRemittance'];
            $reserveData['TotalAppoAgenceSansApproInitial'] = floatval($journalManager->TotalApproAgenceSansApproInitial($date, $agence['RefAgency']));
            $reserveData['TotalSortieAgence'] = floatval($journalManager->TotalSortieAgence($date, $agence['RefAgency']));
            $reserveData['SommeTimbre'] = floatval($journalManager->SommeFraisTimbreAgence($date, $agence['RefAgency']));
            
            // Calcul de la réserve actuelle
            $reserveData['ReserveActuelle'] = $reserveData['YesterdayReserve'] + 
                $reserveData['SommeDepotWithRemittance'] - 
                $reserveData['SommeSortieWithRemittance'] +
                $reserveData['TotalAppoAgenceSansApproInitial'] - 
                $reserveData['TotalSortieAgence'] + 
                $reserveData['SommeTimbre'];
            
            $reserveData['DayReserve'] = $reserveData['YesterdayReserve'] - 
                floatval($journalManager->TotalApproAgenceAvecApproInitial($date, $agence['RefAgency']));
            
            // Données pour le modal
            $reserveData['SommeDepotProduit'] = $journalManager->SommeDepotProduitAgence($date, $agence['RefAgency']);
            $reserveData['SommeSortieProduit'] = $journalManager->SommeRetraitProduitAgence($date, $agence['RefAgency']);
            
            // Vérification de la validation
            $reserveData['validate'] = $journalManager->CheckDailyClose($agence['RefAgency'], $date);
            
            $soldeReserveData[] = $reserveData;
        }
        
        $this->page->addVar('SoldeReserveData', $soldeReserveData);
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
}
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

        // 1. Initialisation des managers
        $journalManager = $this->managers->getManagerOf("Journal");
        $pannelManager = $this->managers->getManagerOf("Pannel");
        
        // 2. Récupération de la date
        $date = !empty($request->postData('jour')) ? $request->postData('jour') : date('Y-m-d');
        $this->page->addVar('day', $date);

        // 3. Récupération des agences et préparation des données
        $Agence = $pannelManager->UserAgence();
        $totalGlobal = [
            'SommeDepot' => 0,
            'SommeSortie' => 0,
            'SommeTimbre' => 0,
            'SoldeGlobal' => 0
        ];

        foreach ($Agence as $key => $value) {
            // 3.1 Récupération des données de caisse
            $Agence[$key]['Afficher'] = $journalManager->CaisseAgence($value['RefAgency'], $date);
            $Agence[$key]['validate'] = $journalManager->CheckDailyClose($value['RefAgency'], $date);

            // 3.2 Calcul des remittances
            $Agence[$key]['SommeDepotRemittance'] = floatval($journalManager->SoldeRemittanceVersementAgence($date, $value['RefAgency']));
            $Agence[$key]['SommeRetraitRemittance'] = floatval($journalManager->SoldeRemittanceRetraitAgence($date, $value['RefAgency']));
            $Agence[$key]['SoldeRemittanceAgence'] = $Agence[$key]['SommeDepotRemittance'] - $Agence[$key]['SommeRetraitRemittance'];

            // 3.3 Récupération des données de réserve
            $reserveData = $journalManager->YesterdayReserve($value['RefAgency'], $date);
            $Agence[$key]['YesterdayReserve'] = floatval($reserveData['SoldeCompte'] ?? 0);
            $Agence[$key]['LastDate'] = $journalManager->displayDaysSinceLastDate($reserveData['DateSolde'] ?? null);

            // 3.4 Calcul des mouvements
            $Agence[$key]['SommeDepot'] = floatval($journalManager->SommeDepotAgence($date, $value['RefAgency']));
            $Agence[$key]['SommeSortie'] = floatval($journalManager->SommeRetraitAgence($date, $value['RefAgency']));
            $Agence[$key]['SommeTimbre'] = floatval($journalManager->SommeFraisTimbreAgence($date, $value['RefAgency']));

            // 3.5 Calcul des totaux avec remittance
            $Agence[$key]['SommeDepotWithRemittance'] = $Agence[$key]['SommeDepot'] + $Agence[$key]['SommeDepotRemittance'];
            $Agence[$key]['SommeSortieWithRemittance'] = $Agence[$key]['SommeSortie'] + $Agence[$key]['SommeRetraitRemittance'];

            // 3.6 Calcul des mouvements spéciaux
            $Agence[$key]['TotalAppoAgenceSansApproInitial'] = floatval($journalManager->TotalApproAgenceSansApproInitial($date, $value['RefAgency']));
            $Agence[$key]['TotalSortieAgence'] = floatval($journalManager->TotalSortieAgence($date, $value['RefAgency']));

            // 3.7 Calcul de la réserve
            $Agence[$key]['ReserveActuelle'] = $Agence[$key]['YesterdayReserve'] + 
                $Agence[$key]['SommeDepot'] - $Agence[$key]['SommeSortie'] +
                $Agence[$key]['TotalAppoAgenceSansApproInitial'] - $Agence[$key]['TotalSortieAgence'] + 
                $Agence[$key]['SoldeRemittanceAgence'] + $Agence[$key]['SommeTimbre'];

            $Agence[$key]['DayReserve'] = $Agence[$key]['YesterdayReserve'] - 
                floatval($journalManager->TotalApproAgenceAvecApproInitial($date, $value['RefAgency']));

            // 3.8 Récupération des données par produit pour la modale
            $Agence[$key]['SommeDepotProduit'] = $journalManager->SommeDepotProduitAgence($date, $value['RefAgency']);
            $Agence[$key]['SommeSortieProduit'] = $journalManager->SommeRetraitProduitAgence($date, $value['RefAgency']);

            // 3.9 Mise à jour des totaux globaux
            $totalGlobal['SommeDepot'] += $Agence[$key]['SommeDepotWithRemittance'];
            $totalGlobal['SommeSortie'] += $Agence[$key]['SommeSortieWithRemittance'];
            $totalGlobal['SommeTimbre'] += $Agence[$key]['SommeTimbre'];
            $totalGlobal['SoldeGlobal'] += $Agence[$key]['ReserveActuelle'];
        }

        // 4. Ajout des variables à la vue
        $this->page->addVar('Agence', $Agence);
        $this->page->addVar('totalGlobal', $totalGlobal);

        // 5. Ajout des permissions pour les actions conditionnelles
        $permissions = [];
        $AllPermissions = $pannelManager->UserPermission();
        foreach ($AllPermissions as $value) {
            $permissions[] = $value['access'];
        }
        $this->page->addVar('permission', $permissions);
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
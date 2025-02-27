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
        $this->page->addVar("titles", "Petite Caisse"); // Titre de la page
        $Agence  = $this->managers->getManagerOf("Pannel")->UserAgence(); //Recuperation de la liste
        foreach ($Agence as $key => $value) {
            if (!empty($request->postData('jour'))) {
                $date = $request->postData('jour');
                $this->page->addVar('day', $request->postData('jour'));
            } else {
                $date = date('Y-m-d');
                $this->page->addVar('day', $date);
            }
            $Agence[$key]['SommeDepotRemittance'] = floatval($this->managers->getManagerOf("Journal")->SoldeRemittanceVersementAgence($date, $value['RefAgency']));
            $Agence[$key]['SommeRetraitRemittance'] = floatval($this->managers->getManagerOf("Journal")->SoldeRemittanceRetraitAgence($date, $value['RefAgency']));

            $Agence[$key]['SoldeRemittanceAgence'] = $Agence[$key]['SommeDepotRemittance'] - $Agence[$key]['SommeRetraitRemittance'];
            $Agence[$key]['Afficher'] = $this->managers->getManagerOf("Journal")->CaisseAgence($value['RefAgency'], $date);
            $Agence[$key]['validate'] = $this->managers->getManagerOf("Journal")->CheckDailyClose($value['RefAgency'], $date);

            $reserveData = $this->managers->getManagerOf("Journal")->YesterdayReserve($value['RefAgency'], $date);

            // Ensure numeric value for YesterdayReserve
            $Agence[$key]['YesterdayReserve'] = floatval($reserveData['SoldeCompte']);

            // Additionally, if you want to store the date of the last recorded balance
            $Agence[$key]['LastDate'] = $this->managers->getManagerOf("Journal")->displayDaysSinceLastDate($reserveData['DateSolde']);



            $Agence[$key]['SommeDepot'] = floatval($this->managers->getManagerOf("Journal")->SommeDepotAgence($date, $value['RefAgency']));
            $Agence[$key]['SommeSortie'] = floatval($this->managers->getManagerOf("Journal")->SommeRetraitAgence($date, $value['RefAgency']));

            $Agence[$key]['SommeDepotWithRemittance'] = $Agence[$key]['SommeDepot'] + $Agence[$key]['SommeDepotRemittance'];
            $Agence[$key]['SommeSortieWithRemittance'] = $Agence[$key]['SommeSortie'] + $Agence[$key]['SommeRetraitRemittance'];

            $Agence[$key]['TotalAppoAgenceSansApproInitial'] = floatval($this->managers->getManagerOf("Journal")->TotalApproAgenceSansApproInitial($date, $value['RefAgency']));
            $Agence[$key]['TotalSortieAgence'] = floatval($this->managers->getManagerOf("Journal")->TotalSortieAgence($date, $value['RefAgency']));

            $Agence[$key]['SommeTimbre'] = floatval($this->managers->getManagerOf("Journal")->SommeFraisTimbreAgence($date, $value['RefAgency']));

            // Calculate ReserveActuelle using numeric values
            $Agence[$key]['ReserveActuelle'] = $Agence[$key]['YesterdayReserve'] + $Agence[$key]['SommeDepot'] - $Agence[$key]['SommeSortie'] +
                $Agence[$key]['TotalAppoAgenceSansApproInitial'] - $Agence[$key]['TotalSortieAgence'] + $Agence[$key]['SoldeRemittanceAgence'] + $Agence[$key]['SommeTimbre'];


            $Agence[$key]['DayReserve'] = $Agence[$key]['YesterdayReserve'] - floatval($this->managers->getManagerOf("Journal")->TotalApproAgenceAvecApproInitial($date, $value['RefAgency']));

            $Agence[$key]['SommeDepotProduit'] = $this->managers->getManagerOf("Journal")->SommeDepotProduitAgence($date, $value['RefAgency']);
            $Agence[$key]['SommeSortieProduit'] = $this->managers->getManagerOf("Journal")->SommeRetraitProduitAgence($date, $value['RefAgency']);
        }
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
}
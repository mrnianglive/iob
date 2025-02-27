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
        
        // Récupérer la date une seule fois
        $date = !empty($request->postData('jour')) ? $request->postData('jour') : date('Y-m-d');
        $this->page->addVar('day', $date);

        // Récupérer les agences en une seule requête
        $journalManager = $this->managers->getManagerOf("Journal");
        $pannelManager = $this->managers->getManagerOf("Pannel");
        
        $Agence = $pannelManager->UserAgence();
        
        // Préparer les données pour le chargement asynchrone
        $this->page->addVar('loadDataAsynchronously', true);
        $this->page->addVar('Agence', $Agence);
    }

    // Nouvelle méthode pour le chargement asynchrone des données
    public function executeGetAgencyData(\Library\HTTPRequest $request)
    {
        try {
            if (!$request->getData('date') || !$request->getData('RefAgency')) {
                throw new \Exception('Paramètres manquants');
            }

            $date = $request->getData('date');
            $RefAgency = $request->getData('RefAgency');
            
            $journalManager = $this->managers->getManagerOf("Journal");
            
            // Récupérer toutes les données en une seule requête si possible
            $data = [
                'SommeDepotRemittance' => floatval($journalManager->SoldeRemittanceVersementAgence($date, $RefAgency)),
                'SommeRetraitRemittance' => floatval($journalManager->SoldeRemittanceRetraitAgence($date, $RefAgency)),
                'Afficher' => $journalManager->CaisseAgence($RefAgency, $date),
                'validate' => $journalManager->CheckDailyClose($RefAgency, $date)
            ];

            // Récupérer les données de réserve
            $reserveData = $journalManager->YesterdayReserve($RefAgency, $date);
            $data['YesterdayReserve'] = floatval($reserveData['SoldeCompte'] ?? 0);
            $data['LastDate'] = $journalManager->displayDaysSinceLastDate($reserveData['DateSolde'] ?? null);

            // Calculer les sommes
            $data['SommeDepot'] = floatval($journalManager->SommeDepotAgence($date, $RefAgency));
            $data['SommeSortie'] = floatval($journalManager->SommeRetraitAgence($date, $RefAgency));
            
            // Calculer les totaux
            $data['SoldeRemittanceAgence'] = $data['SommeDepotRemittance'] - $data['SommeRetraitRemittance'];
            $data['SommeDepotWithRemittance'] = $data['SommeDepot'] + $data['SommeDepotRemittance'];
            $data['SommeSortieWithRemittance'] = $data['SommeSortie'] + $data['SommeRetraitRemittance'];
            
            // Récupérer les autres données
            $data['TotalAppoAgenceSansApproInitial'] = floatval($journalManager->TotalApproAgenceSansApproInitial($date, $RefAgency));
            $data['TotalSortieAgence'] = floatval($journalManager->TotalSortieAgence($date, $RefAgency));
            $data['SommeTimbre'] = floatval($journalManager->SommeFraisTimbreAgence($date, $RefAgency));
            
            // Calculer la réserve actuelle
            $data['ReserveActuelle'] = $data['YesterdayReserve'] + $data['SommeDepot'] - $data['SommeSortie'] +
                $data['TotalAppoAgenceSansApproInitial'] - $data['TotalSortieAgence'] + 
                $data['SoldeRemittanceAgence'] + $data['SommeTimbre'];
            
            $data['DayReserve'] = $data['YesterdayReserve'] - 
                floatval($journalManager->TotalApproAgenceAvecApproInitial($date, $RefAgency));
            
            // Récupérer les données par produit
            $data['SommeDepotProduit'] = $journalManager->SommeDepotProduitAgence($date, $RefAgency);
            $data['SommeSortieProduit'] = $journalManager->SommeRetraitProduitAgence($date, $RefAgency);

            // Désactiver tout output précédent
            ob_clean();
            
            // Envoyer les headers
            header('Content-Type: application/json');
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            
            // Retourner les données JSON
            echo json_encode($data, JSON_NUMERIC_CHECK);
            exit;

        } catch (\Exception $e) {
            // En cas d'erreur, retourner une réponse JSON avec l'erreur
            ob_clean();
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
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
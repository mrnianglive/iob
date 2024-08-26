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
            $this->page->addVar('Debut', $Debut);
            $this->page->addVar('Fin', $Fin);
            $this->page->addVar('Value', $Value);
            $this->page->addVar('RefProduit', $RefProduit);
        } else {
            $Operations = $journalManager->Operations();
        }
        $this->page->addVar('Operations', $Operations);

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
        $this->page->addVar("titles", "Suppression ");

        if ($this->isOperationClosed($request->getData('id'))) {
            $this->setMessageAndRedirect('error', 'Impossible de supprimer une opération d\'un jour fermé');
        } else {
            $this->managers->getManagerOf("Journal")->DeleteOperations($request->getData('id'));
            $this->setMessageAndRedirect('success', 'Opération supprimée avec succès');
        }
    }

    private function isOperationClosed($id)
    {
        $operation = $this->managers->getManagerOf("Journal")->getSingleOperation($id);
        $day = $operation['Approve2_Time'];
        $agency = $operation['RefAgency'];

        return $this->managers->getManagerOf("Journal")->CheckDailyClose($agency, $day);
    }

    private function setMessageAndRedirect($type, $text)
    {
        $_SESSION['message'] = ['type' => $type, 'text' => $text, 'number' => 2];
        $this->app()->httpResponse()->redirect('/Journal/index');
    }

    public function executePetitecaisse(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Petite Caisse");
        $Agence = $this->managers->getManagerOf("Pannel")->UserAgence();
        
        $date = $request->postData('jour') ?: date('Y-m-d');
        $this->page->addVar('day', $date);
        
        foreach ($Agence as $key => $value) {
            $Agence[$key]['Afficher'] = $this->managers->getManagerOf("Journal")->CaisseAgence($value['RefAgency'], $date);
            $Agence[$key]['validate'] = $this->managers->getManagerOf("Journal")->CheckDailyClose($value['RefAgency'], $date);
        }
        
        $this->page->addVar('Agence', $Agence);
    }

  public function executeGetPetiteCaisseData(\Library\HTTPRequest $request)
{
    $date = $request->postData('date') ?? date('Y-m-d');
    $refAgency = $request->postData('refAgency') ?? 1;

    if (!$refAgency) {
        $this->jsonResponse(['error' => 'RefAgency is required'], 400);
        return;
    }

    $journalManager = $this->managers->getManagerOf("Journal");

    try {
        $sommeDepotRemittance = $journalManager->SoldeRemittanceVersementAgence($date, $refAgency);
        $sommeRetraitRemittance = $journalManager->SoldeRemittanceRetraitAgence($date, $refAgency);
        $soldeRemittanceAgence = $sommeDepotRemittance - $sommeRetraitRemittance;

        $reserveData = $journalManager->YesterdayReserve($refAgency, $date);
        $yesterdayReserve = $reserveData['SoldeCompte'];
        $lastDate = $journalManager->displayDaysSinceLastDate($reserveData['DateSolde']);

        $sommeDepot = $journalManager->SommeDepotAgence($date, $refAgency);
        $sommeSortie = $journalManager->SommeRetraitAgence($date, $refAgency);

        $totalAppoAgenceSansApproInitial = $journalManager->TotalApproAgenceSansApproInitial($date, $refAgency);
        $totalSortieAgence = $journalManager->TotalSortieAgence($date, $refAgency);
        $sommeTimbre = $journalManager->SommeFraisTimbreAgence($date, $refAgency);

        $totalApproAgenceAvecApproInitial = $journalManager->TotalApproAgenceAvecApproInitial($date, $refAgency);

        $sommeDepotProduit = $journalManager->SommeDepotProduitAgence($date, $refAgency);
        $sommeSortieProduit = $journalManager->SommeRetraitProduitAgence($date, $refAgency);

        $data = [
            'SommeDepotRemittance' => $sommeDepotRemittance,
            'SommeRetraitRemittance' => $sommeRetraitRemittance,
            'SoldeRemittanceAgence' => $soldeRemittanceAgence,
            'YesterdayReserve' => $yesterdayReserve,
            'LastDate' => $lastDate,
            'SommeDepot' => $sommeDepot,
            'SommeSortie' => $sommeSortie,
            'SommeDepotWithRemittance' => $sommeDepot + $sommeDepotRemittance,
            'SommeSortieWithRemittance' => $sommeSortie + $sommeRetraitRemittance,
            'TotalAppoAgenceSansApproInitial' => $totalAppoAgenceSansApproInitial,
            'TotalSortieAgence' => $totalSortieAgence,
            'SommeTimbre' => $sommeTimbre,
            'ReserveActuelle' => $yesterdayReserve + $sommeDepot - $sommeSortie + $totalAppoAgenceSansApproInitial - $totalSortieAgence + $soldeRemittanceAgence + $sommeTimbre,
            'DayReserve' => $yesterdayReserve - $totalApproAgenceAvecApproInitial,
            'SommeDepotProduit' => $sommeDepotProduit,
            'SommeSortieProduit' => $sommeSortieProduit,
        ];

        $this->jsonResponse($data);
    } catch (\Exception $e) {
        $this->jsonResponse(['error' => $e->getMessage()], 500);
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

    public function executeGetTotals(\Library\HTTPRequest $request)
    {
        $journalManager = $this->managers->getManagerOf('Journal');
        $Debut = $request->getData('Debut');
        $Fin = $request->getData('Fin');
        $Value = $request->getData('RefAgency');
        $RefProduit = $request->getData('RefProduit');

        $totalDepot = $journalManager->sommeVersementPeriode($Debut, $Fin, $Value, $RefProduit);
        $totalRetrait = $journalManager->sommeRetraitPeriode($Debut, $Fin, $Value, $RefProduit);

        $this->jsonResponse([
            'totalDepot' => $totalDepot,
            'totalRetrait' => $totalRetrait
        ]);
    }

    protected function jsonResponse($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}
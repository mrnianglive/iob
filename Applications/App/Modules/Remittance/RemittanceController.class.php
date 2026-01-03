<?php

namespace Applications\App\Modules\Remittance;

use DateTime;

class RemittanceController extends \Library\BackController
{


    public function executeIndex(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Accueil"); // Titre de la page
        $ListeProduits  = $this->managers->getManagerOf("Pannel")->ListeProduit();
        $this->page->addVar("ListeProduit", $ListeProduits);
        $Chmod  = $this->managers->getManagerOf("Bielletage")->CheckOuverture(); //Recuperation de la liste
        $this->page->addVar("CheckOuverture", $Chmod); // Creation de la variable, ajout d'une variable a la vue
        $ListeType  = $this->managers->getManagerOf("Remittance")->ListeType();
        $this->page->addVar("ListeType", $ListeType);
        $ListeAgence  = $this->managers->getManagerOf("Pannel")->ListeAgence();
        $this->page->addVar("ListeAgence", $ListeAgence);
        $Agence  = $this->managers->getManagerOf("Pannel")->UserAgence();
        $this->page->addVar('UserAgence', $Agence);
        $this->page->addVar('Debut', $request->postData('Debut'));
        $this->page->addVar('Fin', $request->postData('Fin'));
        $this->page->addVar('Value', $request->postData('RefAgency'));
        $SoldeRemittanceVersement = 0;
        $SoldeRemittanceRetrait = 0;
        $Antidate = $request->postData('Antidate') ?? '';

        if ($request->method() == 'POST' && $request->postData('RefCaisse')) {
            $RefCaisse = $request->postData('RefCaisse');
            $RefAgency = $this->managers->getManagerOf("Pannel")->GetAgencyUsingCaisseID($RefCaisse)['RefAgency'];
            $Today = date('Y-m-d');



            if (!empty($Antidate)) {
                $this->managers->getManagerOf("Remittance")->Add($request);
                return;
            }

            // Validation du solde de la veille
            $balanceError = $this->ValidYesterdaySold($RefAgency, $Today);
            if ($balanceError) {
                $this->addFlash('error', $balanceError);
                $this->app()->httpResponse()->redirect('/remittances/index');
                return;
            }

            // Vérification du solde actuel de la caisse
            $SoldeActuelleCaisse = $this->managers->getManagerOf("Journal")->SoldeActuelleCaisse($Today, $RefCaisse);
            $MontantTransaction = $request->postData('MontantTransaction');
            $RefType = $request->postData('RefType');



            if ($RefType == 2 && $MontantTransaction > $SoldeActuelleCaisse) {
                $this->addFlash('warning', 'Le montant de la transaction est supérieur au solde de la caisse. Veuillez faire un appro de la caisse ou contactez votre administrateur.');
                $this->app()->httpResponse()->redirect('/remittances/index');
                return;
            }

            // D'autres vérifications nécessaires...

            // Si toutes les vérifications sont bonnes, on peut ajouter l'opération
            $this->managers->getManagerOf("Remittance")->Add($request);
            $this->addFlash('success', 'Ajout réussi !');
            $this->app()->httpResponse()->redirect('/remittances/index');
        }

        if (!empty($request->postData('RefAgency')) or isset($_GET['value'])) {
            if (isset($_GET['debut']) && isset($_GET['fin']) && isset($_GET['value'])) {
                $Operation  = $this->managers->getManagerOf("Remittance")->GetOperations($_GET['debut'], $_GET['fin'], $_GET['value']);
                $this->page->addVar('Debut', $_GET['debut']);
                $this->page->addVar('Fin', $_GET['fin']);
                $this->page->addVar('Value', $_GET['value']);
            } else {
                $Operation  = $this->managers->getManagerOf("Remittance")->GetOperations($request->postData('Debut'), $request->postData('Fin'), $request->postData('RefAgency'));
                $this->page->addVar('Debut', $request->postData('Debut'));
                $this->page->addVar('Fin', $request->postData('Fin'));
                $this->page->addVar('Value', $request->postData('RefAgency'));
            }

            foreach ($Operation as $key => $value) {
                if ($value['RefType'] == 1) {
                    $SoldeRemittanceVersement += $value['MontantTransaction'];
                } else {
                    $SoldeRemittanceRetrait += $value['MontantTransaction'];
                }
            }
            $this->page->addVar('SoldeRemittanceVersement', $SoldeRemittanceVersement);
            $this->page->addVar('SoldeRemittanceRetrait', $SoldeRemittanceRetrait);
            $this->page->addVar('Operation', $Operation);
        } else {
            $Operation = $this->managers->getManagerOf('Remittance')->ListeOperations(date('Y-m-d'), date('Y-m-d'));
            foreach ($Operation as $key => $value) {

                if ($value['RefType'] == 1) {
                    $SoldeRemittanceVersement += $value['MontantTransaction'];
                } else {
                    $SoldeRemittanceRetrait += $value['MontantTransaction'];
                }
            }
            $this->page->addVar('SoldeRemittanceVersement', $SoldeRemittanceVersement);
            $this->page->addVar('SoldeRemittanceRetrait', $SoldeRemittanceRetrait);

            $this->page->addVar('Operation', $Operation);
        }
        $ListePays  = $this->managers->getManagerOf("Pannel")->ListePays();
        $this->page->addVar("ListePays", $ListePays);
        $permissions = array();
        $AllPermissions = $this->managers->getManagerOf('Pannel')->UserPermission();
        foreach ($AllPermissions as $key => $value) {
            $permissions[] = $value['access'];
        }
        $this->page->addVar('permission', $permissions);
    }

    // Méthode helper pour ajouter des messages flash
    private function addFlash($type, $message)
    {
        $_SESSION['message']['type'] = $type;
        $_SESSION['message']['text'] = $message;
        $_SESSION['message']['number'] = 2; // Vous pouvez ajuster ce numéro selon votre système de numérotation des messages
    }

    private function ValidYesterdaySold($RefAgency, $date)
    {
        // Get the date of the last known balance
        $YesterdayReserveDate = $this->managers->getManagerOf("Journal")->GetLastBalanceDate($RefAgency);

        // Convert to DateTime objects for comparison
        $lastBalanceDateTime = new DateTime($YesterdayReserveDate);
        $currentDateDateTime = new DateTime($date);

        // Check if last known balance is not from yesterday
        if ($lastBalanceDateTime->format('Y-m-d') != $currentDateDateTime->modify('-3 day')->format('Y-m-d')) {
            // Check if there were operations since the last known balance
            if ($this->managers->getManagerOf("Journal")->HasOperationsSinceLastBalance($RefAgency, $YesterdayReserveDate)) {
                // Prompt user to close the books for the last operational day
                return "La dernière clôture de solde ne correspond pas à la date attendue (hier). Des opérations ont été effectuées depuis. Veuillez procéder à la clôture de la journée concernée.";
            }
        }
        // If the balance is up-to-date or no operations since last balance, return null indicating no error
        return null;
    }


    private function isOperationClosed($id)
    {
        $operation = $this->managers->getManagerOf("Remittance")->getSingleOperation($id);
        $day = $operation['Insert_time'];
        $agency = $operation['RefAgency'];

        return $this->managers->getManagerOf("Journal")->CheckDailyClose($agency, $day);
    }

    public function executeDelete(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Suppresion "); // Titre de la page
        if ($this->isOperationClosed($request->getData('id'))) {
            $_SESSION['message']['type'] = 'warning';
            $_SESSION['message']['text'] = 'Impossible de supprimer une opération d\'un jour fermé';
            $_SESSION['message']['number'] = 2;
            $this->app()->httpResponse()->redirect('/remittances/index');
        } else {
            $this->managers->getManagerOf("Remittance")->DeleteOperations($request->getData('id'));
        }
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Suppression réussie !';
        $_SESSION['message']['number'] = 2;
        $this->app()->httpResponse()->redirect('/remittances/index'); //Retour en arriere
    }

    public function executeValidate(\Library\HTTPRequest $request)
    {
        $this->managers->getManagerOf("Remittance")->ValidateOperations($request);
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Validation réussie !';
        $_SESSION['message']['number'] = 2;
        if (!empty($request->postData('Debut')) && !empty($request->postData('Fin'))) {
            $this->app()->httpResponse()->redirect("/remittances/index/" . $request->postData('Debut') . "/" . $request->postData('Fin') . "/" . $request->postData('RefAgency')); //Retour en arriere
        } else {
            $this->app()->httpResponse()->redirect("/remittances/index"); //Retour en arriere
        }
    }
    public function executeCancelvalidate(\Library\HTTPRequest $request)
    {
        $this->managers->getManagerOf("Remittance")->CancelValidate($request->getData('id'));
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Annulation réussie !';
        $_SESSION['message']['number'] = 2;
        $this->app()->httpResponse()->redirect("/remittances/index"); //Retour en arriere
    }
}

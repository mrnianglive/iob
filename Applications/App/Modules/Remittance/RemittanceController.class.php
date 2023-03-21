<?php

namespace Applications\App\Modules\Remittance;

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
        if ($request->method() == 'POST' && $request->postData('RefCaisse')) {
            $this->managers->getManagerOf("Remittance")->Add($request);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Ajout réussie !';
            $_SESSION['message']['number'] = 2;
            $this->app()->httpResponse()->redirect('/remittances/index'); //Retour en arriere

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
                $SoldeRemittanceVersement += $value['SoldeRemittanceVersement'];
                $SoldeRemittanceRetrait += $value['SoldeRemittanceRetrait'];
            }
            $this->page->addVar('SoldeRemittanceVersement', $SoldeRemittanceVersement);
            $this->page->addVar('SoldeRemittanceRetrait', $SoldeRemittanceRetrait);
            $this->page->addVar('Operation', $Operation);
        } else {
            $Operation = $this->managers->getManagerOf('Remittance')->ListeOperations(date('Y-m-d'), date('Y-m-d'));
            foreach ($Operation as $key => $value) {
                $SoldeRemittanceVersement += $value['SoldeRemittanceVersement'];
                $SoldeRemittanceRetrait += $value['SoldeRemittanceRetrait'];
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


    public function executeDelete(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Suppresion "); // Titre de la page
        $this->managers->getManagerOf("Remittance")->DeleteOperations($request->getData('id'));
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

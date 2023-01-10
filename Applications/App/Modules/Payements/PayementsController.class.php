<?php

namespace Applications\App\Modules\Payements;

class PayementsController extends \Library\BackController
{
    public function executeIndex(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Accueil"); // Titre de la page
    }


    public function executeZone(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Liste des Zones"); // Titre de la page
        $ListeZone  = $this->managers->getManagerOf("Payements")->ListeZone();
        $this->page->addVar("ListeZone", $ListeZone);

        if ($request->method() == 'POST' && !empty($request->postData('NameZone'))) {
            $this->managers->getManagerOf("Payements")->AddZone($request); //Recuperation de la liste
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Ajout réussie !';
            $_SESSION['message']['number'] = 2;
            $this->app()->httpResponse()->redirect('/payements/zone'); //Retour en arriere
        }
    }

    public function executeDeleteZone(\Library\HTTPRequest $request)
    {
        $this->managers->getManagerOf("Payements")->DeleteZone($request->getData('id'));
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Suppression réussie !';
        $_SESSION['message']['number'] = 2;
        $this->app()->httpResponse()->redirect('/payements/zone'); //Retour en arriere

    }


    public function executeBeneficiaire(\Library\HTTPRequest $request)
    {

        $this->page->addVar("titles", "Liste des Beneficiaires"); // Titre de la page
        $ListeBenefi  = $this->managers->getManagerOf("Payements")->ListeBeneficiare();
        $this->page->addVar("ListeBenefi", $ListeBenefi);

        $ListeZone  = $this->managers->getManagerOf("Payements")->ListeZone();
        $this->page->addVar("ListeZone", $ListeZone);

        if ($request->method() == 'POST') {
            $this->managers->getManagerOf("Payements")->AddBeneficiare($request); //Recuperation de la liste
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Ajout réussie !';
            $_SESSION['message']['number'] = 2;
            $this->app()->httpResponse()->redirect('/payements/benficiaire'); //Retour en arriere
        }
    }

    public function executeDeleteBeneficiare(\Library\HTTPRequest $request)
    {
        $this->managers->getManagerOf("Payements")->DeleteBeneficiare($request->getData('id'));
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Suppression réussie !';
        $_SESSION['message']['number'] = 2;
        $this->app()->httpResponse()->redirect('/payements/benficiaire'); //Retour en arriere

    }
}
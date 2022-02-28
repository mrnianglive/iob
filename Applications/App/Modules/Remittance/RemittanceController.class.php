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


        if ($request->method() == 'POST' && $request->postData('RefCaisse')) {
            $this->managers->getManagerOf("Remittance")->Add($request);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Ajout réussie !';
            $_SESSION['message']['number'] = 2;
            $this->app()->httpResponse()->redirect('/remittances/index'); //Retour en arriere

        }
        if (!empty($request->postData('jour'))) {
            $date = $request->postData('jour');
            $this->page->addVar('day', $request->postData('jour'));
            $Operation  = $this->managers->getManagerOf("Remittance")->ListeOperations($request->postData('jour'));
        } else {
            $date = date('Y-m-d');
            $this->page->addVar('day', $date);
            $Operation  = $this->managers->getManagerOf("Remittance")->ListeOperations($date);
        }
        $this->page->addVar("Operation", $Operation);
    }

    public function executeDelete(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Suppresion "); // Titre de la page
        $this->managers->getManagerOf("Remittance")->DeleteOperations($request->getData('id'));
        $this->app()->httpResponse()->redirect('/remittances/index'); //Retour en arriere
    }
}
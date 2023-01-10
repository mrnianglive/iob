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
    }


    public function executeBeneficiaire(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Liste des Beneficiaires"); // Titre de la page

        $ListeBenefi  = $this->managers->getManagerOf("Payements")->ListeBeneficiare();
        $this->page->addVar("ListeBenefi", $ListeBenefi);
    }
}
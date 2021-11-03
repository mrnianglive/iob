<?php

namespace Applications\App\Modules\Bielletage;

class BielletageController extends \Library\BackController
{
    public function executeIndex(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Accueil"); // Titre de la page
        $Chmod  = $this->managers->getManagerOf("Bielletage")->CheckOuverture(); //Recuperation de la liste
        $this->page->addVar("CheckOuverture", $Chmod); // Creation de la variable, ajout d'une variable a la vue
        $Operations = $this->managers->getManagerOf('Bielletage')->GetCaisse();
        $this->page->addVar('Operation', $Operations);
        $Biellet = $this->managers->getManagerOf('Arreter')->GetDailyBielletage(date('Y-m-d'));
        $this->page->addVar('Biellet', $Biellet);
        $DailyVersement = $this->managers->getManagerOf('Bielletage')->DailyVersement();
        $this->page->addVar('DailyVersement', $DailyVersement);
        $UsersCaisse = $this->managers->getManagerOf("Journal")->UserCaisse(date('Y-m-d'));
        $Solde = 0;
        $SommeVersement = 0;
        $SommeRetrait = 0;
        foreach ($UsersCaisse as $key => $value) {
            $Solde += $value['SoldeDisponible'];
            $SommeVersement += $value['TotalVersement'];
            $SommeRetrait += $value['TotalRetrait'];
        }

        $Agence  = $this->managers->getManagerOf("Pannel")->UserAgence(); //Recuperation de la liste
        foreach ($Agence as $key => $value) {
            $Agence[$key]['SommeDepot'] = $this->managers->getManagerOf("Journal")->SoldeInitialCaisse(date('Y-m-d'), $value['RefAgency']);
            $Agence[$key]['YesterdayReserve'] = $this->managers->getManagerOf("Journal")->YesterdayReserve($value['RefAgency'], date('Y-m-d'));
        }
        $this->page->addVar('Agence', $Agence);
        $this->page->addVar('Solde', $Solde);
        $this->page->addVar('SommeVersement', $SommeVersement);
        $this->page->addVar('SommeRetrait', $SommeRetrait);
    }
    public function executeStopcaisse(\Library\HTTPRequest $request)
    {
        $SommeVersement = $this->managers->getManagerOf('Bielletage')->SommeVersementAgence($request->getData('id'), date('Y-m-d'));
        $this->page->addVar('SommeVersement', $SommeVersement);
        $SommeRetrait = $this->managers->getManagerOf('Bielletage')->SommeRetraitAgence($request->getData('id'), date('Y-m-d'));
        $this->page->addVar('SommeRetrait', $SommeRetrait);
        $Yesterday = $this->managers->getManagerOf('Bielletage')->YesterdaySolde($request->getData('id'));
        //On ne tient pas compte de Yesterday pour chaque caisse
        $Solde = $SommeVersement - $SommeRetrait;
        $this->managers->getManagerOf('Arreter')->StopCaisse($request->getData('id'), $Solde);
        $this->app()->httpResponse()->redirect('/Arreter/index'); //Retour en arriere
    }
    public function executeBielletage(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Nouvelle Opération"); // Titre de la page
        if ($_GET['id'] == 3) {
            $Chmod  = $this->managers->getManagerOf("Bielletage")->CheckOuverture(1); //Recuperation de la liste
        } else {
            $Chmod  = $this->managers->getManagerOf("Bielletage")->CheckOuverture(); //Recuperation de la liste
        }
        $this->page->addVar("CheckOuverture", $Chmod); // Creation de la variable, ajout d'une variable a la vue
        $TypeAppro  = $this->managers->getManagerOf("Journal")->TypeAppro(); //Recuperation de la liste
        $this->page->addVar("TypeAppro", $TypeAppro); // Creation de la variable, ajout d'une variable a la vue

    }
    public function executeInvoice(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Bordereau"); // Titre de la page
        $this->page->setTemplate('bordereau');
        if ($request->method() == 'POST') {
            $reference  = $request->postData('id');
        } else {
            $reference = $request->getData('id');
        }
        $Invoice  = $this->managers->getManagerOf("Bielletage")->GetInvoice($reference); //Recuperation de la liste
        $this->page->addVar("GetInvoice", $Invoice); // Creation de la variable, ajout d'une variable a la vue
    }
    public function executeAdd(\Library\HTTPRequest $request)
    {
        $this->managers->getManagerOf("Bielletage")->Add(); //Recuperation de la liste
    }
}
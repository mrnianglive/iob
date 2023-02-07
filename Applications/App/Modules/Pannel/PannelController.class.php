<?php

namespace Applications\App\Modules\Pannel;

class PannelController extends \Library\BackController
{
    public function executeListeCaisse(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Liste des Caisses"); // Titre de la page
        $ListeAgence  = $this->managers->getManagerOf("Pannel")->ListeAgence();
        $this->page->addVar("ListeAgence", $ListeAgence);
        $ListeCaisse  = $this->managers->getManagerOf("Pannel")->ListeCaisse();

        $ListeDays  = $this->managers->getManagerOf("Pannel")->ListeDays();
        foreach ($ListeCaisse as $key => $value) {
            foreach ($ListeDays as $key1 => $value1) {
                $Opening[$value['RefCaisse']][$value1['RefDays']] = $this->managers->getManagerOf('Pannel')->VerifOpening($value['RefCaisse'], $value1['RefDays']);
            }
        }
        $this->page->addVar("ListeDays", $ListeDays);
        $this->page->addVar("ListeCaisse", $ListeCaisse);
        $this->page->addVar("Opening", $Opening);

        if ($request->method() == 'POST' && !empty($request->postData('RefAgency'))) {
            $AddCaisse  = $this->managers->getManagerOf("Pannel")->AddCaisse($request); //Recuperation de la liste
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Ajout réussie !';
            $_SESSION['message']['number'] = 2;
            $this->app()->httpResponse()->redirect('/Pannel/Caisse'); //Retour en arriere
        }
        if ($request->method() == 'POST' && !empty($request->postData('RefCaisse'))) {
            $AddOuverture  = $this->managers->getManagerOf("Pannel")->AddOuverture($request); //Recuperation de la liste
        }
    }
    public function executeListeagence(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Liste des Agences"); // Titre de la page
        $ListeAgence  = $this->managers->getManagerOf("Pannel")->ListeAgence();
        $this->page->addVar("ListeAgence", $ListeAgence);
        $ListeBanque  = $this->managers->getManagerOf("Pannel")->ListeBanque();
        $this->page->addVar("ListeBanque", $ListeBanque);
        $ListePays  = $this->managers->getManagerOf("Pannel")->ListePays();
        $this->page->addVar("ListePays", $ListePays);
        if ($request->method() == 'POST') {
            $this->managers->getManagerOf("Pannel")->AddAgency($request);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Ajout réussie !';
            $_SESSION['message']['number'] = 2;
            $this->app()->httpResponse()->redirect('/Pannel/Agence'); //Retour en arriere

        }
    }
    public function executeDeleteagence(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Delete Agence"); // Titre de la page
        $this->managers->getManagerOf("Pannel")->DeleteAgence($request->getData('id'));
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Suppression réussie !';
        $_SESSION['message']['number'] = 2;
        $this->app()->httpResponse()->redirect('/Pannel/Agence'); //Retour en arriere

    }
    public function executeListebanque(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Liste des Banque"); // Titre de la page
        $ListeBanque  = $this->managers->getManagerOf("Pannel")->ListeBanque();
        $this->page->addVar("ListeBanque", $ListeBanque);

        $ListePays  = $this->managers->getManagerOf("Pannel")->ListePays();
        $this->page->addVar("ListePays", $ListePays);

        if ($request->method() == 'POST') {
            $this->managers->getManagerOf("Pannel")->AddBanque($request);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Ajout réussie !';
            $_SESSION['message']['number'] = 2;
            $this->app()->httpResponse()->redirect('/Pannel/Banque'); //Retour en arriere

        }
    }
    public function executeDeletebanque(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Delete Agence"); // Titre de la page
        $this->managers->getManagerOf("Pannel")->DeleteBanque($request->getData('id'));
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Suppression réussie !';
        $_SESSION['message']['number'] = 2;
        $this->app()->httpResponse()->redirect('/Pannel/Banque'); //Retour en arriere
    }

    public function executeListeProduit(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Liste des Produits"); // Titre de la page
        $ListeBanque  = $this->managers->getManagerOf("Pannel")->ListeBanque();
        $this->page->addVar("ListeBanque", $ListeBanque);
        $ListeCaisse  = $this->managers->getManagerOf("Pannel")->ListeCaisse();
        $ListeProduits  = $this->managers->getManagerOf("Pannel")->ListeProduit();
        $produits = array();
        foreach ($ListeCaisse as $key => $caisse) {
            foreach ($ListeProduits as $clef => $produit) {
                $produits[$caisse['RefCaisse']][$produit['RefProduit']] =
                    $this->managers->getManagerOf('Pannel')->VerifProduit($caisse['RefCaisse'], $produit['RefProduit']);
            }
        }
        $this->page->addVar("ListeProduit", $ListeProduits);
        $this->page->addVar("ListeCaisse", $ListeCaisse);
        $this->page->addVar("produits", $produits);
        if ($request->method() == 'POST' && empty($request->postData('RefProduit'))) {
            $this->managers->getManagerOf("Pannel")->AddProduit($request);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Ajout réussie !';
            $_SESSION['message']['number'] = 2;
            $this->app()->httpResponse()->redirect('/Pannel/Produit'); //Retour en arriere

        } elseif ($request->method() == 'POST' && !empty($request->postData('RefProduit'))) {
            $this->managers->getManagerOf("Pannel")->AddChmodProduit($request);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Ajout réussie !';
            $_SESSION['message']['number'] = 2;
            $this->app()->httpResponse()->redirect('/Pannel/Produit'); //Retour en arriere
        }
    }
    public function executeDeleteProduit(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Delete Produit"); // Titre de la page
        $this->managers->getManagerOf("Pannel")->DeleteProduit($request->getData('id'));
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Suppression réussie !';
        $_SESSION['message']['number'] = 2;
        $this->app()->httpResponse()->redirect('/Pannel/Produit'); //Retour en arriere
    }

    public function executeLinks(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Liste des liens"); // Titre de la page
        $links = $this->managers->getManagerOf('Pannel')->GetLinks();
        $this->page->addVar('links', $links);

        $ListePays  = $this->managers->getManagerOf("Pannel")->ListePays();
        $this->page->addVar("ListePays", $ListePays);

        if ($request->method() == 'POST') {
            $this->managers->getManagerOf("Pannel")->addLinks($request);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Ajout réussie !';
            $_SESSION['message']['number'] = 2;
            $this->app()->httpResponse()->redirect('/Pannel/links'); //Retour en arriere

        }
    }

    public function executeListePays(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Liste des Pays"); // Titre de la page
        $ListePays  = $this->managers->getManagerOf("Pannel")->ListePays();
        $this->page->addVar("ListePays", $ListePays);

        if ($request->method() == 'POST') {
            $this->managers->getManagerOf("Pannel")->AddPays($request);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Ajout réussie !';
            $_SESSION['message']['number'] = 2;
            $this->app()->httpResponse()->redirect('/Pannel/Pays'); //Retour en arriere

        }
    }
}
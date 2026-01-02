<?php

namespace Applications\App\Modules\Journal;

class JournalController extends \Library\BackController
{
    public function executeIndex(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Journal de Caisse"); // Titre de la page
        $Chmod  = $this->managers->getManagerOf("Bielletage")->CheckOuverture(); //Recuperation de la liste
        $this->page->addVar("CheckOuverture", $Chmod); // Creation de la variable, ajout d'une variable a la vue
        $Agence  = $this->managers->getManagerOf("Pannel")->UserAgence();
        $this->page->addVar('UserAgence', $Agence);
        $this->page->addVar('Debut', $request->postData('Debut'));
        $this->page->addVar('Fin', $request->postData('Fin'));
        $this->page->addVar('Value', $request->postData('RefAgency'));
        // $Biellet = $this->managers->getManagerOf('Journal')->GetBielletageJournal(NULL, NULL, NULL);
        // $this->page->addVar('Biellet', $Biellet);
        $ListeAgence  = $this->managers->getManagerOf("Pannel")->ListeAgence();
        $this->page->addVar("ListeAgence", $ListeAgence);
        if (!empty($request->postData('RefAgency')) or isset($_GET['value'])) {
            // Determiner la source des donnees (GET ou POST)
            if (isset($_GET['debut']) && isset($_GET['fin']) && isset($_GET['value'])) {
                $debut = $_GET['debut'];
                $fin = $_GET['fin'];
                $refAgency = $_GET['value'];
            } else {
                $debut = $request->postData('Debut');
                $fin = $request->postData('Fin');
                $refAgency = $request->postData('RefAgency');
            }
            
            // Mettre a jour les variables de la vue
            $this->page->addVar('Debut', $debut);
            $this->page->addVar('Fin', $fin);
            $this->page->addVar('Value', $refAgency);
            
            // Recuperer les operations
            $Operations = $this->managers->getManagerOf('Journal')->GetOperations($debut, $fin, $refAgency);
            $this->page->addVar('Operations', $Operations);

            // Calcul des soldes avec les bonnes valeurs
            $SoldeRemittanceVersementAgencePeriode = $this->managers->getManagerOf('Journal')->SoldeRemittanceVersementAgencePeriode($debut, $fin, $refAgency);
            $SoldeRemittanceRetraitAgencePeriode = $this->managers->getManagerOf('Journal')->SoldeRemittanceRetraitAgencePeriode($debut, $fin, $refAgency);
            $SoldeRemittanceAgence = $SoldeRemittanceVersementAgencePeriode - $SoldeRemittanceRetraitAgencePeriode;

            $sommeVersementPeriode = $this->managers->getManagerOf('Journal')->sommeVersementPeriode($debut, $fin, $refAgency);
            $this->page->addVar('sommeVersementPeriode', $sommeVersementPeriode);
            $sommeRetraitPeriode = $this->managers->getManagerOf('Journal')->sommeRetraitPeriode($debut, $fin, $refAgency);
            $this->page->addVar('sommeRetraitPeriode', $sommeRetraitPeriode);
            $sommeVersementPeriodeAvecAppro = $this->managers->getManagerOf('Journal')->sommeVersementPeriodeAvecAppro($debut, $fin, $refAgency);
            $this->page->addVar('sommeVersementPeriodeAvecAppro', $sommeVersementPeriodeAvecAppro);
            $sommeRetraitPeriodeAvecSortie = $this->managers->getManagerOf('Journal')->sommeRetraitPeriodeAvecSortie($debut, $fin, $refAgency);
            $this->page->addVar('sommeRetraitPeriodeAvecSortie', $sommeRetraitPeriodeAvecSortie);
            $Yesterday = $this->managers->getManagerOf('Journal')->YesterdaySoldeAgence($debut, $fin, $refAgency);
            $Solde = ($sommeVersementPeriodeAvecAppro - $sommeRetraitPeriodeAvecSortie) + $Yesterday + $SoldeRemittanceAgence;
            $this->page->addVar('Solde', $Solde);
        } else {
            $Operations = $this->managers->getManagerOf('Journal')->Operations();
            $this->page->addVar('Operations', $Operations);
            $sommeVersementPeriode = $this->managers->getManagerOf('Journal')->sommeVersementPeriode();
            $this->page->addVar('sommeVersementPeriode', $sommeVersementPeriode);
            $sommeRetraitPeriode = $this->managers->getManagerOf('Journal')->sommeRetraitPeriode();
            $this->page->addVar('sommeRetraitPeriode', $sommeRetraitPeriode);
            $sommeVersementPeriodeAvecAppro = $this->managers->getManagerOf('Journal')->sommeVersementPeriodeAvecAppro();
            $this->page->addVar('sommeVersementPeriodeAvecAppro', $sommeVersementPeriodeAvecAppro);
            $sommeRetraitPeriodeAvecSortie = $this->managers->getManagerOf('Journal')->sommeRetraitPeriodeAvecSortie();
            $this->page->addVar('sommeRetraitPeriodeAvecSortie', $sommeRetraitPeriodeAvecSortie);

            $UsersCaisse = $this->managers->getManagerOf("Journal")->UserCaisse(date('Y-m-d'));
            $SoldeGlobal = 0;
            foreach ($UsersCaisse as $key => $value) {
                $SoldeGlobal += $value['SoldeDisponibleGlobal'];
            }
            $this->page->addVar('Solde', $SoldeGlobal);
        }
        $this->page->addVar('match', $this->managers->getManagerOf('Journal'));

        $permissions = array();
        $AllPermissions = $this->managers->getManagerOf('Pannel')->UserPermission();
        foreach ($AllPermissions as $key => $value) {
            $permissions[] = $value['access'];
        }
        $this->page->addVar('permission', $permissions);
    }
    public function executeValidate(\Library\HTTPRequest $request)
    {
        $this->managers->getManagerOf("Journal")->ValidateOperations($request);
        if (!empty($request->postData('Debut')) && !empty($request->postData('Fin'))) {
            $this->app()->httpResponse()->redirect("/Journal/index/" . $request->postData('Debut') . "/" . $request->postData('Fin') . "/" . $request->postData('RefAgency')); //Retour en arriere
        } else {
            $this->app()->httpResponse()->redirect("/Journal/index"); //Retour en arriere
        }
    }
    public function executeCancelvalidate(\Library\HTTPRequest $request)
    {
        $this->managers->getManagerOf("Journal")->CancelValidate($request->getData('id'));
        $this->app()->httpResponse()->redirect("/Journal/index"); //Retour en arriere
    }

    public function executeDelete(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Suppresion "); // Titre de la page
        $this->managers->getManagerOf("Journal")->DeleteOperations($request->getData('id'));
        $this->app()->httpResponse()->redirect('/Journal/index'); //Retour en arriere
    }

    public function executePetitecaisse(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Petite Caisse"); // Titre de la page
        
        // Determiner la date
        if (!empty($request->postData('jour'))) {
            $date = $request->postData('jour');
        } else {
            $date = date('Y-m-d');
        }
        $this->page->addVar('day', $date);
        
        // OPTIMISATION: Utiliser la methode optimisee qui reduit 240+ requetes a 3 requetes
        $Agence = $this->managers->getManagerOf("Journal")->GetPetiteCaisseDataOptimized($date, $_SESSION['RefUsers']);
        
        // Recuperer les donnees de produits pour le tableau remittance (si necessaire)
        $ListeProduit = $this->managers->getManagerOf("Pannel")->ListeProduit();
        $Agenc = $this->managers->getManagerOf("Pannel")->ListeAgence();
        $tab = [];
        
        // Note: Cette partie peut aussi etre optimisee si necessaire
        foreach ($Agenc as $keyagence => $agency) {
            foreach ($ListeProduit as $key => $produit) {
                $tab[$keyagence][$key]['SommeDepotRemittanceProduit'] = $this->managers->getManagerOf("Analytics")->SoldeRemittanceVersementAgenceProduit($date, $agency['RefAgency'], $produit['RefProduit']);
                $tab[$keyagence][$key]['SommeRetraitRemittanceProduit'] = $this->managers->getManagerOf("Analytics")->SoldeRemittanceRetraitAgenceProduit($date, $agency['RefAgency'], $produit['RefProduit']);
            }
        }
        
        $this->page->addVar('ListeProduit', $ListeProduit);
        $this->page->addVar('Agenc', $Agenc);
        $this->page->addVar('tab', $tab);
        $this->page->addVar('Agence', $Agence);
    }

    public function executeCancelFermeture(\Library\HTTPRequest $request)
    {
        // Restriction: Seul l'admin peut annuler une fermeture
        if ($_SESSION['statut'] !== 'admin') {
            $_SESSION['message'] = array(
                'type' => 'error',
                'text' => 'Seul l\'administrateur peut annuler une fermeture de caisse.',
                'number' => 2
            );
            $this->app()->httpResponse()->redirect("/Journal/petite_caisse");
            return;
        }
        
        // Appeler la nouvelle methode avec recalcul cascade
        $this->managers->getManagerOf("Journal")->CancelFermetureWithCascade($request->getData('id'));
        $this->app()->httpResponse()->redirect("/Journal/petite_caisse"); //Retour en arriere
    }
}
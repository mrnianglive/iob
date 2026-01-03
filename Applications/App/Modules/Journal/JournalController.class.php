<?php

namespace Applications\App\Modules\Journal;

class JournalController extends \Library\BackController
{
    public function executeIndex(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Journal de Caisse"); // Titre de la page
        $Chmod = $this->managers->getManagerOf("Bielletage")->CheckOuverture(); //Recuperation de la liste
        $this->page->addVar("CheckOuverture", $Chmod); // Creation de la variable, ajout d'une variable a la vue
        $Agence = $this->managers->getManagerOf("Pannel")->UserAgence();
        $this->page->addVar('UserAgence', $Agence);
        
        // Valeurs par défaut pour les dates
        $debut = $request->postData('Debut') ?? date('Y-m-d');
        $fin = $request->postData('Fin') ?? date('Y-m-d');
        $refAgency = $request->postData('RefAgency') ?? '';
        
        $this->page->addVar('Debut', $debut);
        $this->page->addVar('Fin', $fin);
        $this->page->addVar('Value', $refAgency);
        
        // Liste des produits pour le dropdown
        $ListeProduit = $this->managers->getManagerOf("Pannel")->ListeProduit();
        $this->page->addVar('ListeProduit', $ListeProduit);
        $this->page->addVar('RefProduit', $request->postData('RefProduit') ?? '');
        
        $ListeAgence = $this->managers->getManagerOf("Pannel")->ListeAgence();
        $this->page->addVar("ListeAgence", $ListeAgence);
        $this->page->addVar('match', $this->managers->getManagerOf('Journal'));
        
        // OPTIMISATION: Utiliser getJournalStatsForDate au lieu de UserCaisse + 8 requêtes par caisse
        if (!empty($refAgency) or isset($_GET['value'])) {
            // Déterminer la source des données (GET ou POST)
            if (isset($_GET['debut']) && isset($_GET['fin']) && isset($_GET['value'])) {
                $debut = $_GET['debut'];
                $fin = $_GET['fin'];
                $refAgency = $_GET['value'];
            } else {
                $debut = $request->postData('Debut');
                $fin = $request->postData('Fin');
                $refAgency = $request->postData('RefAgency');
            }
            
            // Mettre à jour les variables de la vue
            $this->page->addVar('Debut', $debut);
            $this->page->addVar('Fin', $fin);
            $this->page->addVar('Value', $refAgency);
            
            // OPTIMISATION: Récupérer toutes les opérations en une seule requête
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
            // OPTIMISATION: Pour l'affichage du jour, utiliser getJournalStatsForDate
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

            // OPTIMISATION: Utiliser getJournalStatsForDate au lieu de UserCaisse + 8 requêtes par caisse
            $journalStats = $this->managers->getManagerOf('Stats')->getJournalStatsForDate(date('Y-m-d'), $_SESSION['RefUsers']);
            $SoldeGlobal = 0;
            foreach ($journalStats as $value) {
                $SoldeGlobal += ($value['TotalDepot'] - $value['TotalRetrait'] - $value['TotalSortie']);
            }
            $this->page->addVar('Solde', $SoldeGlobal);
        }
        
        // Permissions
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

        // Déterminer la date
        $date = $request->postData('jour') ?? date('Y-m-d');
        $this->page->addVar('day', $date);

        $journalManager = $this->managers->getManagerOf('Journal');
        $pannelManager = $this->managers->getManagerOf('Pannel');
        $statsManager = $this->managers->getManagerOf('Stats');

        // Récupérer les agences
        $Agences = $pannelManager->UserAgence();

        // Stats par produit (pour le modal)
        $matriceRaw = $statsManager->getPetiteCaisseStats($date);
        $StatsProduits = [];
        $ListeProduit = [];
        foreach ($matriceRaw as $stat) {
            $refA = $stat['RefAgency'];
            $nameP = $stat['NameProduit'];
            if (!isset($StatsProduits[$refA])) {
                $StatsProduits[$refA] = [
                    'SommeDepotProduit' => [],
                    'SommeSortieProduit' => [],
                    'DepotRemittance' => 0,
                    'RetraitRemittance' => 0
                ];
            }
            $StatsProduits[$refA]['SommeDepotProduit'][$nameP] = $stat['TotalDepot'];
            $StatsProduits[$refA]['SommeSortieProduit'][$nameP] = $stat['TotalRetrait'];
            $StatsProduits[$refA]['DepotRemittance'] += $stat['TotalDepot'];
            $StatsProduits[$refA]['RetraitRemittance'] += $stat['TotalRetrait'];

            if (!isset($ListeProduit[$stat['RefProduit']])) {
                $ListeProduit[$stat['RefProduit']] = ['RefProduit' => $stat['RefProduit'], 'NameProduit' => $nameP];
            }
        }
        $this->page->addVar('ListeProduit', array_values($ListeProduit));

        foreach ($Agences as $key => $value) {
            $refAgency = $value['RefAgency'];

            // Detail des caisses
            $Agences[$key]['Afficher'] = $journalManager->CaisseAgence($refAgency, $date);
            $Agences[$key]['validate'] = $journalManager->CheckDailyClose($refAgency, $date);

            // Reserve et Solde Agence
            $depotRemittance = $StatsProduits[$refAgency]['DepotRemittance'] ?? 0;
            $retraitRemittance = $StatsProduits[$refAgency]['RetraitRemittance'] ?? 0;

            $reserveData = $journalManager->YesterdayReserve($refAgency, $date);
            $yesterdayReserve = $reserveData['SoldeCompte'] ?? 0;
            $Agences[$key]['YesterdayReserve'] = $yesterdayReserve;

            // Calculer LastDate (jours écoulés)
            if (!empty($reserveData['DateSolde'])) {
                $lastDate = new \DateTime($reserveData['DateSolde']);
                $today = new \DateTime($date);
                $interval = $lastDate->diff($today);
                $days = $interval->format('%a');
                $Agences[$key]['LastDate'] = ($days == 0) ? "Aujourd'hui" : "Il y a $days jour(s)";
            } else {
                $Agences[$key]['LastDate'] = "Aucune donnée";
            }

            $sommeDepot = $journalManager->SommeDepotAgence($date, $refAgency);
            $sommeSortie = $journalManager->SommeRetraitAgence($date, $refAgency);
            $Agences[$key]['SommeDepotWithRemittance'] = $sommeDepot + $depotRemittance;
            $Agences[$key]['SommeSortieWithRemittance'] = $sommeSortie + $retraitRemittance;

            $appro = $journalManager->TotalApproAgenceSansApproInitial($date, $refAgency);
            $sortie = $journalManager->TotalSortieAgence($date, $refAgency);
            $timbre = $journalManager->SommeFraisTimbreAgence($date, $refAgency);
            $Agences[$key]['SommeTimbre'] = $timbre;

            $approTotal = $journalManager->TotalApproAgenceAvecApproInitial($date, $refAgency);

            $Agences[$key]['ReserveActuelle'] = $yesterdayReserve + $sommeDepot - $sommeSortie + $appro - $sortie + ($depotRemittance - $retraitRemittance) + $timbre;
            $Agences[$key]['DayReserve'] = $yesterdayReserve - $approTotal;

            // Modal produits
            $Agences[$key]['SommeDepotProduit'] = $StatsProduits[$refAgency]['SommeDepotProduit'] ?? [];
            $Agences[$key]['SommeSortieProduit'] = $StatsProduits[$refAgency]['SommeSortieProduit'] ?? [];

            // Fonds de roulement et operations en attente
            $agenceInfo = $pannelManager->GetAgency($refAgency);
            $plafondFondsRoulement = $agenceInfo['PlafondFondsRoulement'] ?? 0;
            $soldeOmniReference = $agenceInfo['SoldeOmniReference'] ?? 0;
            
            // Calculer le total Especes + Omni
            // Especes = ReserveActuelle (solde especes physique)
            // Omni = SoldeOmniReference (pour le moment, sera remplace par calcul reel plus tard)
            $totalFondsRoulement = $Agences[$key]['ReserveActuelle'] + $soldeOmniReference;
            $excedent = max(0, $totalFondsRoulement - $plafondFondsRoulement);
            
            // Operations en attente (non verifiees = pas encore passees sur Omni)
            $montantOperationsEnAttente = $journalManager->GetMontantOperationsNonVerifiees($refAgency);
            $countOperationsEnAttente = $journalManager->CountOperationsNonVerifieesByAgency($refAgency);
            
            $Agences[$key]['PlafondFondsRoulement'] = $plafondFondsRoulement;
            $Agences[$key]['SoldeOmniReference'] = $soldeOmniReference;
            $Agences[$key]['TotalFondsRoulement'] = $totalFondsRoulement;
            $Agences[$key]['Excedent'] = $excedent;
            $Agences[$key]['MontantOperationsEnAttente'] = $montantOperationsEnAttente;
            $Agences[$key]['CountOperationsEnAttente'] = $countOperationsEnAttente;
        }

        $this->page->addVar('Agence', $Agences);
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

    public function executeNoverified(\Library\HTTPRequest $request)
    {
        $pageTitle = "Journal de Caisse des opérations non vérifiées";
        $this->page->addVar("titles", $pageTitle);

        $pannelManager = $this->managers->getManagerOf("Pannel");
        $ListeAgence = $pannelManager->ListeAgence();
        $this->page->addVar("ListeAgence", $ListeAgence);

        $JournalManager = $this->managers->getManagerOf("Journal");

        $Operations = $JournalManager->GetOperationsNonVerifiees();

        $CountOperationsNonVerifiees = $JournalManager->CountOperationsNonVerifiees();
        $this->page->addVar('CountOperationsNonVerifiees', $CountOperationsNonVerifiees);

        $permissions = [];
        $AllPermissions = $pannelManager->UserPermission();
        foreach ($AllPermissions as $key => $value) {
            $permissions[] = $value['access'];
        }
        $this->page->addVar('permission', $permissions);
        $this->page->addVar('Operations', $Operations);
    }
}
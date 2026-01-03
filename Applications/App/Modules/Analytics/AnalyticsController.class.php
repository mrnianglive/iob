<?php

namespace Applications\App\Modules\Analytics;

class AnalyticsController extends \Library\BackController
{
    public function executeIndex(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Analytics"); // Titre de la page
        
        // Valeurs par défaut pour les dates
        $debut = $request->postData('Debut') ?? date('Y-m-01');
        $fin = $request->postData('Fin') ?? date('Y-m-d');
        $this->page->addVar('Debut', $debut);
        $this->page->addVar('Fin', $fin);
        
        $TotalVersement = 0;
        $TotalRetrait = 0;
        $Commission = 0;
        $CommissionRetrait = 0;
        
        if (!empty($request->postData('Debut')) && !empty($request->postData('Fin'))) {
            $debut = $request->postData('Debut');
            $fin = $request->postData('Fin');
            
            // Recuperer les operations pour affichage
            $Operations = $this->managers->getManagerOf('Analytics')->GetOperations($debut, $fin);
            $this->page->addVar('Operations', $Operations);
            $this->page->addVar('Debut', $debut);
            $this->page->addVar('Fin', $fin);
            
            // OPTIMISATION: Recuperer les totaux directement en SQL au lieu de boucler
            $totals = $this->managers->getManagerOf('Analytics')->GetOperationsTotals($debut, $fin);
            $TotalVersement = floatval($totals['TotalVersement']);
            $TotalRetrait = floatval($totals['TotalRetrait']);
            
            // Calcul des commissions
            if ($TotalVersement <= 500000000) {
                $Commission = $TotalVersement * 0.002;
            } elseif ($TotalVersement <= 1000000000) {
                $Commission = $TotalVersement * 0.0018;
            } else {
                $Commission = $TotalVersement * 0.0010;
            }
            
            if ($TotalRetrait <= 500000000) {
                $CommissionRetrait = $TotalRetrait * 0.0015;
            } elseif ($TotalRetrait <= 1000000000) {
                $CommissionRetrait = $TotalRetrait * 0.00075;
            } elseif ($TotalRetrait <= 2000000000) {
                $CommissionRetrait = $TotalRetrait * 0.0005;
            }
        }
        
        $this->page->addVar('totalVersement', $TotalVersement);
        $this->page->addVar('totalRetrait', $TotalRetrait);
        $this->page->addVar('CommissionDepot', $Commission);
        $this->page->addVar('CommissionRetrait', $CommissionRetrait);
    }

    public function executeChart(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Chart "); // Titre de la page
        
        // Charger les listes pour les filtres
        $Pays = $this->managers->getManagerOf("Pannel")->ListePays();
        $this->page->addVar("Pays", $Pays);
        
        $ListeProduit = $this->managers->getManagerOf("Pannel")->ListeProduit();
        $this->page->addVar("ListeProduit", $ListeProduit);
        
        // OPTIMISATION: Chart() fait maintenant 1 requete au lieu de 24
        $Charts = $this->managers->getManagerOf('Analytics')->Chart();
        $this->page->addVar('Chart', $Charts);
        
        // OPTIMISATION: Une seule requete pour toutes les agences au lieu de 2 par agence
        $ListeAgence = $this->managers->getManagerOf("Analytics")->ChartAllAgencesOptimized();
        $this->page->addVar("ListeAgence", $ListeAgence);
        
        // OPTIMISATION: Une seule requete pour toutes les caisses au lieu de 2 par caisse
        $ListeCaisse = $this->managers->getManagerOf("Analytics")->ChartAllCaissesOptimized();
        $this->page->addVar("ListeCaisse", $ListeCaisse);
    }

    public function executePerformance(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Analyse des performances"); // Titre de la page
        $ListeBanque  = $this->managers->getManagerOf("Pannel")->ListeBanque();
        $this->page->addVar("ListeBanque", $ListeBanque);
        
        $Chmod  = $this->managers->getManagerOf("Bielletage")->CheckOuverture();
        $this->page->addVar("CheckOuverture", $Chmod);

        // Determiner les dates
        if (!empty($request->postData('Debut')) && !empty($request->postData('Fin'))) {
            $debut = $request->postData('Debut');
            $fin = $request->postData('Fin');
        } else {
            $debut = date('Y-m-d');
            $fin = date('Y-m-d');
        }
        $this->page->addVar('debut', $debut);
        $this->page->addVar('fin', $fin);
        
        // OPTIMISATION: Une seule methode qui recupere tout au lieu de boucles
        $Agence = $this->managers->getManagerOf("Journal")->AgencePerformanceOptimized(
            $_SESSION['RefUsers'], 
            $debut, 
            $fin
        );
        $this->page->addVar('Agence', $Agence);

        // OPTIMISATION: Une seule requete pour tous les compteurs au lieu de 5
        $counters = $this->managers->getManagerOf('Analytics')->GetAllCountersOptimized();
        $this->page->addVar('DailyValidate', $counters['DailyValidate'] ?? 0);
        $this->page->addVar('MonthValidate', $counters['MonthValidate'] ?? 0);
        $this->page->addVar('MonthOperations', $counters['MonthOperations'] ?? 0);
        $this->page->addVar('CountWeekOperations', $counters['CountWeekOperations'] ?? 0);
        $this->page->addVar('CountWeekValidate', $counters['CountWeekValidate'] ?? 0);
    }
}
<?php

namespace Applications\App\Modules\Analytics;

class AnalyticsController extends \Library\BackController
{
    public function executeIndex(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Analytics"); // Titre de la page
        $this->page->addVar('Debut', $request->postData('Debut'));
        $this->page->addVar('Fin', $request->postData('Fin'));
        $TotalVersement = 0;
        $TotalRetrait = 0;
        $Commission = 0;
        $CommissionRetrait = 0;
        if (!empty($request->postData('Debut')) && !empty($request->postData('Fin'))) {
            $Operations = $this->managers->getManagerOf('Analytics')->GetOperations($request->postData('Debut'), $request->postData('Fin'));
            $this->page->addVar('Operations', $Operations);
            $this->page->addVar('Debut', $request->postData('Debut'));
            $this->page->addVar('Fin', $request->postData('Fin'));
            foreach ($Operations as $Operation) {
                if ($Operation['RefType'] == 1) {
                    $TotalVersement += $Operation['MontantVersement'];
                    if ($TotalVersement <= (500000000)) {
                        $Commission = $TotalVersement * (0.002);
                    } elseif ($TotalVersement >= 500000001 && $TotalVersement <= 1000000000) {
                        $Commission = $TotalVersement * (0.0018);
                    } elseif ($TotalVersement >= 1000000001) {
                        $Commission = $TotalVersement * (0.0010);
                    }
                } elseif ($Operation['RefType'] == 2) {
                    $TotalRetrait += $Operation['MontantVersement'];
                    if ($TotalRetrait <= (500000000)) {
                        $CommissionRetrait = $TotalRetrait * (0.0015);
                    } elseif ($TotalRetrait >= 500000001 && $TotalRetrait <= 1000000000) {
                        $CommissionRetrait = $TotalRetrait * (0.00075);
                    } elseif ($TotalRetrait >= 1000000001 && $TotalRetrait <= 2000000000) {
                        $CommissionRetrait = $TotalRetrait * (0.0005);
                    }
                }
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

        $Country = isset($_POST['RefPays']) ? $_POST['RefPays'] : '';
        $Agency = isset($_POST['RefAgency']) ? $_POST['RefAgency'] : '';
        $Caisse = isset($_POST['RefCaisse']) ? $_POST['RefCaisse'] : '';

        $Charts = $this->managers->getManagerOf('Analytics')->Chart($Country, $Agency, $Caisse);
        $this->page->addVar('Chart', $Charts);

        $Pays = $this->managers->getManagerOf("Pannel")->ListePays();
        $this->page->addVar("Pays", $Pays);

        //The Agence List should be in function of the country Posted in the form, if the country is not posted, the list should take all the agence
        $analytics =  $this->managers->getManagerOf("Analytics");
        $ListeAgence = $analytics->ListeAgence($Country, $Agency);

        if (count($Agency) == 1) {
            $ListeAgence[0]['SommeVersement'] = $this->managers->getManagerOf("Analytics")->ChartAgenceVersement($ListeAgence[0]['RefAgency']);
            $ListeAgence[0]['SommeRetrait'] = $this->managers->getManagerOf("Analytics")->ChartAgenceRetrait($ListeAgence[0]['RefAgency']);
        } elseif (!empty($ListeAgence) && count($ListeAgence['RefAgency']) > 1) {
            foreach ($ListeAgence as $key => $agence) {
                $ListeAgence[$key]['SommeVersement'] = $this->managers->getManagerOf("Analytics")->ChartAgenceVersement($agence['RefAgency']);
                $ListeAgence[$key]['SommeRetrait'] = $this->managers->getManagerOf("Analytics")->ChartAgenceRetrait($agence['RefAgency']);
            }
        } else {
            $ListeAgence = array(); // initialize $ListeAgence to an empty array

        }




        $this->page->addVar("ListeAgence", $ListeAgence);

        $ListeCaisse  =  $this->managers->getManagerOf("Pannel")->ListeCaisse();
        foreach ($ListeCaisse as $key => $caisse) {
            $ListeCaisse[$key]['SommeVersement'] = $this->managers->getManagerOf("Analytics")->ChartCaisseVersement($caisse['RefCaisse']);
            $ListeCaisse[$key]['SommeRetrait'] = $this->managers->getManagerOf("Analytics")->ChartCaisseRetrait($caisse['RefCaisse']);
        }
        $this->page->addVar("ListeCaisse", $ListeCaisse);
    }

    public function executePerformance(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Analyse des performances"); // Titre de la page
        $ListeBanque  = $this->managers->getManagerOf("Pannel")->ListeBanque();
        $this->page->addVar("ListeBanque", $ListeBanque);
        $ListePays = $this->managers->getManagerOf("Pannel")->ListePays();
        $this->page->addVar("ListePays", $ListePays);
        $Agence  = $this->managers->getManagerOf("Pannel")->UserAgence(); //Recuperation de la liste
        $Chmod  = $this->managers->getManagerOf("Bielletage")->CheckOuverture(); //Recuperation de la liste
        $this->page->addVar("CheckOuverture", $Chmod); // Creation de la variable, ajout d'une variable a la vue
        foreach ($Agence as $key => $value) {

            if (!empty($request->postData('Debut')) && !empty($request->postData('Fin'))) {
                $debut = $request->postData('Debut');
                $fin = $request->postData('Fin');

                $this->page->addVar('debut', $request->postData('Debut'));
                $this->page->addVar('fin', $request->postData('Fin'));
            } else {
                $debut = date('Y-m-d');
                $fin = date('Y-m-d');
                $this->page->addVar('debut', $debut);
                $this->page->addVar('fin', $fin);
            }
            $Agence[$key]['Afficher'] = $this->managers->getManagerOf("Journal")->CaisseAgencePerformance($value['RefAgency'], $debut, $fin);
            $Agence[$key]['NbreOP'] = $this->managers->getManagerOf("Journal")->NbreOperationAgencePerformance($value['RefAgency'], $debut, $fin);
        }
        $this->page->addVar('Agence', $Agence);

        $DailyValidate = $this->managers->getManagerOf('Analytics')->CountDayValidate();
        $this->page->addVar('DailyValidate', $DailyValidate);
        $MonthValidate = $this->managers->getManagerOf('Analytics')->CountMonthValidate();
        $this->page->addVar('MonthValidate', $MonthValidate);

        $MonthOperations = $this->managers->getManagerOf('Analytics')->CountMonthOperations();
        $this->page->addVar('MonthOperations', $MonthOperations);

        $CountWeekOperations = $this->managers->getManagerOf('Analytics')->CountWeekOperations();
        $this->page->addVar('CountWeekOperations', $CountWeekOperations);

        $CountWeekValidate = $this->managers->getManagerOf('Analytics')->CountWeekValidate();
        $this->page->addVar('CountWeekValidate', $CountWeekValidate);
    }


    public function executeUv(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Gestion des UV "); // Titre de la page
        $ListeAgence  = $this->managers->getManagerOf("Pannel")->ListeAgence();
        $this->page->addVar('ListeAgence', $ListeAgence);
        $ListeProduit  = $this->managers->getManagerOf("Pannel")->ListeProduit();
        $this->page->addVar('ListeProduit', $ListeProduit);

        $ListeDepot  = $this->managers->getManagerOf("Analytics")->ListeDepot();
        $this->page->addVar('ListeDepot', $ListeDepot);
        $ListeType  = $this->managers->getManagerOf("Remittance")->ListeType();
        $this->page->addVar("ListeType", $ListeType);
        if ($request->method() == 'POST') {
            $this->managers->getManagerOf("Analytics")->AddUv($request);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Ajout réussie !';
            $_SESSION['message']['number'] = 2;
            $this->app()->httpResponse()->redirect('/Analytics/uv'); //Retour en arriere

        }
    }
}

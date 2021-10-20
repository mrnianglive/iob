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
        $this->page->addVar('Commission', $Commission + $CommissionRetrait);
    }

    public function executeChart(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Chart "); // Titre de la page
        $Charts = $this->managers->getManagerOf('Analytics')->Chart();
        $this->page->addVar('Chart', $Charts);
        $ListeAgence  = $this->managers->getManagerOf("Pannel")->ListeAgence();
        foreach ($ListeAgence as $key => $agence) {
            $ListeAgence[$key]['SommeVersement'] = $this->managers->getManagerOf("Analytics")->ChartAgenceVersement($agence['RefAgency']);
            $ListeAgence[$key]['SommeRetrait'] = $this->managers->getManagerOf("Analytics")->ChartAgenceRetrait($agence['RefAgency']);
        }
        $this->page->addVar("ListeAgence", $ListeAgence);
        $ListeCaisse  = $this->managers->getManagerOf("Pannel")->ListeCaisse();
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
        $Agence  = $this->managers->getManagerOf("Pannel")->UserAgence(); //Recuperation de la liste
        foreach ($Agence as $key => $value) {
            $Agence[$key]['Afficher'] = $this->managers->getManagerOf("Journal")->CaisseAgence($value['RefAgency'], '2021-10-18');
            $Agence[$key]['NbreOP'] = $this->managers->getManagerOf("Journal")->NbreOperationAgence($value['RefAgency'], '2021-10-18');
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
}
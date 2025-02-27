<?php

namespace Applications\App\Modules\Bielletage;

use DateTime;

class BielletageController extends \Library\BackController
{
    public function executeIndex(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Accueil");

        $permissions = array_column($this->managers->getManagerOf('Pannel')->UserPermission(), 'access');
        $this->page->addVar('permission', $permissions);

        $Country = $request->postData('RefPays', '');
        $Agency = $request->postData('RefAgency', '');
        $Caisse = $request->postData('RefCaisse', '');
        
        $essentialData = $this->getEssentialHomeData($Country, $Agency, $Caisse);

        $this->page->addVar('FirstLogin', $this->managers->getManagerOf('User')->FirstLogin());
        $this->page->addVar('CountOperationsNonVerifiees', $this->managers->getManagerOf('Journal')->CountOperationsNonVerifiees());

        foreach ($essentialData as $key => $value) {
            $this->page->addVar($key, $value);
        }
        $this->page->addVar('loadSumsAsynchronously', true);
    }

    private function getEssentialHomeData($Country, $Agency, $Caisse)
    {
        return [
            'CheckOuverture' => $this->managers->getManagerOf("Bielletage")->CheckOuverture(),
            'Operation' => $this->managers->getManagerOf('Bielletage')->GetCaisse(date('Y-m-d'), $Country, $Agency, $Caisse),
            'Pays' => $this->managers->getManagerOf("Pannel")->ListePays(),
            'ListeAgence' => $this->managers->getManagerOf("Pannel")->ListeAgence(),
            'ListeCaisse' => $this->managers->getManagerOf("Pannel")->ListeCaisse(),
            'links' => $this->managers->getManagerOf('Pannel')->GetLinks(),
            'Country' => $Country,
            'Agency' => $Agency,
            'Caisse' => $Caisse,
        ];
    }

    public function executeGetSums(\Library\HTTPRequest $request)
    {
        try {
            $date = date('Y-m-d');
            $Country = $request->getData('Country', '');
            $Agency = $request->getData('Agency', '');
            $Caisse = $request->getData('Caisse', '');

            $usersCaisse = $this->managers->getManagerOf("Journal")->UserCaisse($date, $Country, $Agency, $Caisse);
            
            $response = array_merge(
                $this->calculateSums($usersCaisse),
                ['agence' => $this->getAgenceData()]
            );

            $this->JsonResponse($response);
        } catch (\Exception $e) {
            $this->JsonResponse(['message' => $e->getMessage()], false, 500);
        }
    }

    private function calculateSums($usersCaisse)
    {
        $sums = [
            'SommeVersementGlobal' => 0, 'SommeRetraitGlobal' => 0, 'SoldeGlobal' => 0,
            'Solde' => 0, 'SommeVersement' => 0, 'SommeRetrait' => 0,
            'SommeRemittanceDepot' => 0, 'SommeRemittanceRetrait' => 0, 'SoldeRemittance' => 0,
        ];

        foreach ($usersCaisse as $user) {
            $sums['Solde'] += $user['SoldeDisponible'];
            $sums['SoldeGlobal'] += $user['SoldeDisponibleGlobal'];
            $sums['SommeVersement'] += $user['TotalVersement'];
            $sums['SommeRetrait'] += $user['TotalRetrait'];
            $sums['SommeRemittanceDepot'] += $user['SommeVersementRemittance'];
            $sums['SommeRemittanceRetrait'] += $user['SommeRetraitRemittance'];
            $sums['SoldeRemittance'] += $user['SoldeRemittance'];
        }

        $sums['SommeVersementGlobal'] = $sums['SommeVersement'] + $sums['SommeRemittanceDepot'];
        $sums['SommeRetraitGlobal'] = $sums['SommeRetrait'] + $sums['SommeRemittanceRetrait'];
        $sums['Date'] = date('Y-m-d');

        return $sums;
    }

    private function getAgenceData()
    {
        $agence = $this->managers->getManagerOf("Pannel")->UserAgence();
        $currentDate = date('Y-m-d');
        
        foreach ($agence as &$value) {
            $value['SommeDepot'] = $this->managers->getManagerOf("Journal")->SoldeInitialAgence($currentDate, $value['RefAgency']);
            $reserveData = $this->managers->getManagerOf("Journal")->YesterdayReserve($value['RefAgency'], $currentDate);
            $value['YesterdayReserve'] = $reserveData['SoldeCompte'] ?? null;
            $value['LastDate'] = $reserveData['DateSolde'] ?? null;
            $value['CheckAgencyBalance'] = $this->checkAgencyBalanceStatus($value['RefAgency']);
        }
        return $agence;
    }

    private function checkAgencyBalanceStatus($RefAgency)
    {
        $currentDate = date('Y-m-d');
        $result = $this->managers->getManagerOf("Bielletage")->HasOperationsSinceLastBalance($RefAgency, $currentDate);
        $agencyData = $this->managers->getManagerOf("Pannel")->GetAgency($RefAgency);

        $agencyName = is_array($agencyData) ? $agencyData['NameAgency'] ?? 'Inconnue' : $agencyData;

        $data = [
            'error_message' => '',
            'success_message' => ''
        ];

        if ($result !== false) {
            $data['error_message'] = "{$agencyName}: {$result}";
        } else {
            $data['success_message'] = "Tout est en ordre avec le solde de l'agence '{$agencyName}'.";
        }

        return $data;
    }

    public function executeStopcaisse(\Library\HTTPRequest $request)
    {
        $managerArreter = $this->managers->getManagerOf('Arreter');

        $Solde = $this->managers->getManagerOf('Journal')->ArreterSingleCaisse($id = $request->getData('id'), date('Y-m-d'));

        $Date = date('Y-m-d H:i:s');
        $managerArreter->StopCaisse($id, $Solde, $Date);

        $this->app()->httpResponse()->redirect('/Arreter/index');
    }

    public function executeBielletagebefore(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Nouvelle Opération");

        $manager = $this->managers->getManagerOf("Bielletage");
        $Chmod = ($_GET['id'] == 3) ? $manager->CheckOuverture(1) : $manager->CheckOuverture();
        $this->page->addVar("CheckOuverture", $Chmod);

        $TypeAppro = $this->managers->getManagerOf("Journal")->TypeAppro();
        $this->page->addVar("TypeAppro", $TypeAppro);

        $TypeRetrait = $manager->TypeRetrait();
        $this->page->addVar("TypeRetrait", $TypeRetrait);
        $ListePays  = $this->managers->getManagerOf("Pannel")->ListePays();
        $this->page->addVar("ListePays", $ListePays);

        $AllPermissions = $this->managers->getManagerOf('Pannel')->UserPermission();
        $permissions = array_column($AllPermissions, 'access');
        $this->page->addVar('permission', $permissions);
    }

    public function executeInvoice(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Bordereau");
        $this->page->setTemplate('bordereau');
        if ($request->method() == 'POST') {
            $reference  = $request->postData('id');
        } else {
            $reference = $request->getData('id');
        }

        $Invoice  = $this->managers->getManagerOf("Bielletage")->GetInvoice($reference);
        $this->page->addVar("GetInvoice", $Invoice);

        $getResetStatus = $this->managers->getManagerOf("Bielletage")->getResetStatus($reference);
        $this->page->addVar("getResetStatus", $getResetStatus);
        $numberToLetter = $this->managers->getManagerOf('Arreter')->NumberToLetter(intval($Invoice['MontantVersement']));
        $this->page->addVar("numberToLetter", $numberToLetter);
    }

    public function executeAdd(\Library\HTTPRequest $request)
    {
        $data = $this->extractRequestData($request);
        $GetAgencyUsingCaisseID = $this->managers->getManagerOf("Pannel")->GetAgencyUsingCaisseID($data['RefCaisse']);
        $data['RefAgency'] = $GetAgencyUsingCaisseID['RefAgency'];
        $data['Today'] = date('Y-m-d');

        if (!empty($data['Antidate'])) {
            $this->managers->getManagerOf("Bielletage")->Add();
            return;
        }

        $validationResult = $this->validateTransaction($data);
        if ($validationResult !== true) {
            $this->redirectWithMessage($validationResult['type'], $validationResult['message'], $validationResult['number'], $data['RefType']);
            return;
        }

        $this->managers->getManagerOf("Bielletage")->Add();
    }

    private function extractRequestData($request)
    {
        return [
            'RefCaisse' => $request->postData('RefCaisse'),
            'RefType' => $request->postData('RefType'),
            'TypeAppro' => $request->postData('TypeAppro'),
            'MontantVersement' => $request->postData('MontantVersement'),
            'Antidate' => $request->postData('Antidate'),
        ];
    }

    private function validateTransaction($data)
    {
        $balanceError = $this->ValidYesterdaySold($data['RefAgency'], $data['Today']);
        if ($balanceError) {
            return ['type' => 'error', 'message' => $balanceError, 'number' => 5];
        }

        if ($this->isRequiredApprovisionnement($data['RefType'], $data['RefAgency'], $data['Today'])) {
            return ['type' => 'warning', 'message' => 'Vous devez approvisionner la caisse avant de pouvoir effectuer une opération', 'number' => 2];
        }

        if ($data['RefType'] == 3 && $data['TypeAppro'] == 1 && !$this->isValidMontantVersement($data['MontantVersement'], $data['RefAgency'], $data['Today'])) {
            return ['type' => 'warning', 'message' => 'Le montant de la transaction est supérieur au solde de la réserve.', 'number' => 2];
        }

        if (in_array($data['RefType'], [2, 4, 5]) && !$this->isValidSoldeCaisse($data['MontantVersement'], $data['RefCaisse'], $data['Today'])) {
            return ['type' => 'warning', 'message' => 'Le montant de la transaction est supérieur au solde de la caisse. Veuillez faire un appro de la caisse ou contactez votre administrateur.', 'number' => 2];
        }

        return true;
    }

    private function redirectWithMessage($type, $text, $number, $RefType)
    {
        $_SESSION['message'] = compact('type', 'text', 'number');
        $this->app()->httpResponse()->redirect('/bielletage/' . $RefType);
    }

    private function isRequiredApprovisionnement($RefType, $RefAgency, $Today)
    {
        $VerifAppro = $this->managers->getManagerOf("Journal")->TotalApproAgenceGlobal($Today, $RefAgency);
        return ($VerifAppro == 0 && in_array($RefType, [1, 2]));
    }

    private function isValidMontantVersement($MontantVersement, $RefAgency, $Today)
    {
        $YesterdayReserve = $this->managers->getManagerOf("Journal")->YesterdayReserve($RefAgency, $Today);
        return $MontantVersement <= $YesterdayReserve['SoldeCompte'];
    }

    private function isValidSoldeCaisse($MontantVersement, $RefCaisse, $Today)
    {
        $SoldeActuelleCaisse = $this->managers->getManagerOf("Journal")->SoldeActuelleCaisse($Today, $RefCaisse);
        return $MontantVersement <= $SoldeActuelleCaisse;
    }

    private function ValidYesterdaySold($RefAgency, $date)
    {
        $YesterdayReserveDate = $this->managers->getManagerOf("Journal")->GetLastBalanceDate($RefAgency);

        $lastBalanceDateTime = new \DateTime($YesterdayReserveDate);
        $currentDateDateTime = new \DateTime($date);

        if ($lastBalanceDateTime->format('Y-m-d') != $currentDateDateTime->modify('-3 day')->format('Y-m-d')) {
            $operationsSinceLastBalance = $this->managers->getManagerOf("Journal")->HasOperationsSinceLastBalance($RefAgency);

            if (is_string($operationsSinceLastBalance)) {
                return $operationsSinceLastBalance;
            }
        }

        return null;
    }

    public function executeDashboard(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Dashboard");
        $Biellet = $this->managers->getManagerOf('Arreter')->GetDailyBielletage(date('Y-m-d'));
        $this->page->addVar('Biellet', $Biellet);
        $DailyVersement = $this->managers->getManagerOf('Bielletage')->DailyVersement();
        $this->page->addVar('DailyVersement', $DailyVersement);
        $UsersCaisse = $this->managers->getManagerOf("Journal")->UserCaisse(date('Y-m-d'));
        $Solde = 0;
        $SommeVersement = 0;
        $SommeRetrait = 0;
        $SommeRemittanceDepot = 0;
        $SommeRemittanceRetrait = 0;
        $SoldeRemittance = 0;
        $SoldeGlobal = 0;
        foreach ($UsersCaisse as $key => $value) {
            $Solde += $value['SoldeDisponible'];
            $SoldeGlobal += $value['SoldeDisponibleGlobal'];
            $SommeVersement += $value['TotalVersement'];
            $SommeRetrait += $value['TotalRetrait'];
            $SommeRemittanceDepot += $value['SommeVersementRemittance'];
            $SommeRemittanceRetrait += $value['SommeRetraitRemittance'];
            $SoldeRemittance += $value['SoldeRemittance'];
        }

        $Agence  = $this->managers->getManagerOf("Pannel")->UserAgence();
        foreach ($Agence as $key => $value) {
            $Agence[$key]['SommeDepot'] = $this->managers->getManagerOf("Journal")->SoldeInitialCaisse(date('Y-m-d'), $value['RefAgency']);
            $reserveData = $this->managers->getManagerOf("Journal")->YesterdayReserve($value['RefAgency'], date('Y-m-d'));
            $Agence[$key]['YesterdayReserve'] = $reserveData['SoldeCompte'];
            $Agence[$key]['LastDate'] = $reserveData['DateSolde'] ?? null;
        }
        $this->page->addVar('Agence', $Agence);
        $this->page->addVar('Solde', $Solde);
        $this->page->addVar('SoldeGlobal', $SoldeGlobal);
        $this->page->addVar('SommeVersement', $SommeVersement);
        $this->page->addVar('SommeRetrait', $SommeRetrait);
        $this->page->addVar('SommeVersementGlobal', $SommeVersement + $SommeRemittanceDepot);
        $this->page->addVar('SommeRetraitGlobal', $SommeRetrait + $SommeRemittanceRetrait);
        $this->page->addVar('SommeRemittanceDepot', $SommeRemittanceDepot);
        $this->page->addVar('SommeRemittanceRetrait', $SommeRemittanceRetrait);
        $this->page->addVar('SoldeRemittance', $SoldeRemittance);
    }

    public function executeCalculateSolde(\Library\HTTPRequest $request)
    {
        try {
            $date = date('Y-m-d');
            $filters = [
                'Country' => $request->postData('Country'),
                'Agency' => $request->postData('Agency'),
                'Caisse' => $request->postData('Caisse')
            ];

            // Optimisation du calcul des soldes avec une seule requête SQL
            $query = "SELECT 
                SUM(CASE WHEN type_operation IN ('depot', 'remittance_depot') THEN montant ELSE 0 END) as total_versement,
                SUM(CASE WHEN type_operation IN ('retrait', 'remittance_retrait') THEN montant ELSE 0 END) as total_retrait,
                SUM(CASE 
                    WHEN type_operation IN ('depot', 'remittance_depot') THEN montant 
                    WHEN type_operation IN ('retrait', 'remittance_retrait') THEN -montant 
                    ELSE 0 
                END) as solde_global
                FROM operations 
                WHERE date = :date";

            // Ajouter les filtres conditionnellement
            $params = ['date' => $date];
            if (!empty($filters['Country'])) {
                $query .= " AND ref_pays = :country";
                $params['country'] = $filters['Country'];
            }
            if (!empty($filters['Agency'])) {
                $query .= " AND ref_agency = :agency";
                $params['agency'] = $filters['Agency'];
            }
            if (!empty($filters['Caisse'])) {
                $query .= " AND ref_caisse = :caisse";
                $params['caisse'] = $filters['Caisse'];
            }

            // Exécuter la requête optimisée
            $result = $this->managers->getManagerOf('Journal')->executeQuery($query, $params);

            $this->JsonResponse([
                'sommeVersementGlobal' => (float)$result['total_versement'] ?? 0,
                'sommeRetraitGlobal' => (float)$result['total_retrait'] ?? 0,
                'soldeGlobal' => (float)$result['solde_global'] ?? 0
            ]);

        } catch (\Exception $e) {
            $this->JsonResponse([
                'error' => $e->getMessage()
            ], false, 500);
        }
    }

    private function JsonResponse($data, $success = true, $statusCode = 200)
    {
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code($statusCode);
        }
        
        echo json_encode([
            'success' => $success,
            'data' => $data
        ]);
        exit;
    }
}
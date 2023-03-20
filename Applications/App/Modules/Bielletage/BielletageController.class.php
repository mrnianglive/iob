<?php

namespace Applications\App\Modules\Bielletage;

class BielletageController extends \Library\BackController
{

    public function executeIndex(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Accueil"); // Titre de la page

        // Récupération des données pour l'affichage de l'accueil
        $Country = isset($_POST['RefPays']) ? $_POST['RefPays'] : '';
        $Agency = isset($_POST['RefAgency']) ? $_POST['RefAgency'] : '';
        $Caisse = isset($_POST['RefCaisse']) ? $_POST['RefCaisse'] : '';
        $data = $this->getHomeData($Country, $Agency, $Caisse);


        // Ajout des données à la vue
        $this->page->addVar("CheckOuverture", $data['checkOuverture']);
        $this->page->addVar('Operation', $data['operations']);
        $this->page->addVar('Agence', $data['agence']);
        $this->page->addVar('Solde', $data['solde']);
        $this->page->addVar('SoldeGlobal', $data['soldeGlobal']);
        $this->page->addVar('SommeVersement', $data['sommeVersement']);
        $this->page->addVar('SommeRetrait', $data['sommeRetrait']);
        $this->page->addVar('SommeVersementGlobal', $data['sommeVersementGlobal']);
        $this->page->addVar('SommeRetraitGlobal', $data['sommeRetraitGlobal']);
        $this->page->addVar('SommeRemittanceDepot', $data['sommeRemittanceDepot']);
        $this->page->addVar('SommeRemittanceRetrait', $data['sommeRemittanceRetrait']);
        $this->page->addVar('SoldeRemittance', $data['soldeRemittance']);
        $this->page->addVar('links', $data['links']);
        $this->page->addVar('Pays', $data['Pays']);
        $this->page->addVar('ListeAgence', $data['ListeAgence']);
        $this->page->addVar('ListeCaisse', $data['ListeCaisse']);
        $this->page->addVar('Country', $data['Country']);
        $this->page->addVar('Agency', $data['Agency']);
        $this->page->addVar('Caisse', $data['Caisse']);
    }

    private function getHomeData($Country = NULL, $Agency = NULL, $Caisse = NULL)
    {

        $Pays = $this->managers->getManagerOf("Pannel")->ListePays();
        $ListeAgence  = $this->managers->getManagerOf("Pannel")->ListeAgence();
        $ListeCaisse  = $this->managers->getManagerOf("Pannel")->ListeCaisse();


        // Récupération des données pour l'affichage de l'accueil
        $checkOuverture = $this->managers->getManagerOf("Bielletage")->CheckOuverture();
        $operations = $this->managers->getManagerOf('Bielletage')->GetCaisse(date('Y-m-d'), $Country, $Agency, $Caisse);
        $usersCaisse = $this->managers->getManagerOf("Journal")->UserCaisse(date('Y-m-d'), $Country, $Agency, $Caisse);

        // Calcul des totaux
        $solde = 0;
        $soldeGlobal = 0;
        $sommeVersement = 0;
        $sommeRetrait = 0;
        $sommeRemittanceDepot = 0;
        $sommeRemittanceRetrait = 0;
        $soldeRemittance = 0;
        foreach ($usersCaisse as $user) {
            $solde += $user['SoldeDisponible'];
            $soldeGlobal += $user['SoldeDisponibleGlobal'];
            $sommeVersement += $user['TotalVersement'];
            $sommeRetrait += $user['TotalRetrait'];
            $sommeRemittanceDepot += $user['SommeVersementRemittance'];
            $sommeRemittanceRetrait += $user['SommeRetraitRemittance'];
            $soldeRemittance += $user['SoldeRemittance'];
        }
        $sommeVersementGlobal = $sommeVersement + $sommeRemittanceDepot;
        $sommeRetraitGlobal = $sommeRetrait + $sommeRemittanceRetrait;
        // Récupération des données pour les agences
        $agence  = $this->managers->getManagerOf("Pannel")->UserAgence();
        foreach ($agence as $key => $value) {
            $agence[$key]['SommeDepot'] = $this->managers->getManagerOf("Journal")->SoldeInitialAgence(date('Y-m-d'), $value['RefAgency']);
            $agence[$key]['YesterdayReserve'] = $this->managers->getManagerOf("Journal")->YesterdayReserve($value['RefAgency'], date('Y-m-d'));
        }

        // Récupération des liens pour le menu
        $links = $this->managers->getManagerOf('Pannel')->GetLinks();

        return array(
            'checkOuverture' => $checkOuverture,
            'operations' => $operations,
            'agence' => $agence,
            'solde' => $solde,
            'soldeGlobal' => $soldeGlobal,
            'sommeVersement' => $sommeVersement,
            'sommeRetrait' => $sommeRetrait,
            'sommeVersementGlobal' => $sommeVersementGlobal,
            'sommeRetraitGlobal' => $sommeRetraitGlobal,
            'sommeRemittanceDepot' => $sommeRemittanceDepot,
            'sommeRemittanceRetrait' => $sommeRemittanceRetrait,
            'soldeRemittance' => $soldeRemittance,
            'links' => $links,
            'Pays' => $Pays,
            'ListeAgence' => $ListeAgence,
            'ListeCaisse' => $ListeCaisse,
            'Country' => $Country,
            'Agency' => $Agency,
            'Caisse' => $Caisse
        );
    }



    public function executeStopcaisse(\Library\HTTPRequest $request)
    {
        // Get manager objects
        $managerArreter = $this->managers->getManagerOf('Arreter');

        // Calculate Solde
        $Solde = $this->managers->getManagerOf('Journal')->ArreterSingleCaisse($id = $request->getData('id'), date('Y-m-d'));

        // StopCaisse
        $Date = date('Y-m-d H:i:s');
        $managerArreter->StopCaisse($id, $Solde, $Date);

        // Redirect
        $this->app()->httpResponse()->redirect('/Arreter/index');
    }

    public function executeBielletage(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Nouvelle Opération");

        $manager = $this->managers->getManagerOf("Bielletage");
        $Chmod = ($_GET['id'] == 3) ? $manager->CheckOuverture(1) : $manager->CheckOuverture();
        $this->page->addVar("CheckOuverture", $Chmod);

        $TypeAppro = $this->managers->getManagerOf("Journal")->TypeAppro();
        $this->page->addVar("TypeAppro", $TypeAppro);

        $TypeRetrait = $manager->TypeRetrait();
        $this->page->addVar("TypeRetrait", $TypeRetrait);

        $AllPermissions = $this->managers->getManagerOf('Pannel')->UserPermission();
        $permissions = array_column($AllPermissions, 'access');
        $this->page->addVar('permission', $permissions);
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
        $getResetStatus = $this->managers->getManagerOf("Bielletage")->getResetStatus($reference);
        $this->page->addVar("getResetStatus", $getResetStatus); // Creation de la variable, ajout d'une variable a la vue
        var_dump(intval($Invoice['MontantVersement']));
        $numberToLetter = $this->managers->getManagerOf('Arreter')->NumberToLetter(intval(20000));
        $this->page->addVar("numberToLetter", $numberToLetter); // Creation de la variable, ajout d'une variable a la vue
    }
    public function executeAdd(\Library\HTTPRequest $request)
    {
        $GetAgencyUsingCaisseID = $this->managers->getManagerOf("Pannel")->GetAgencyUsingCaisseID($request->postData('RefCaisse'));
        $YesterdayReserve = $this->managers->getManagerOf("Journal")->YesterdayReserve($GetAgencyUsingCaisseID['RefAgency'], date('Y-m-d'));
        $VerifAppro  = $this->managers->getManagerOf("Journal")->TotalApproAgenceGlobal(date('Y-m-d'), $GetAgencyUsingCaisseID['RefAgency']);

        if (!empty($request->postData('Antidate'))) {
            //Antidate Operation
            $this->managers->getManagerOf("Bielletage")->Add(); //Recuperation de la liste
        } else {

            if ($VerifAppro == 0 && ($request->postData('RefType') == 1 || $request->postData('RefType') == 2)) {
                $_SESSION['message']['type'] = 'warning';
                $_SESSION['message']['text'] = 'Vous devez approvisionner la caisse avant de pouvoir effectuer une opération';
                $_SESSION['message']['number'] = 2;
                $this->app()->httpResponse()->redirect('/bielletage/' . $request->postData('RefType'));
            } else {

                if ($request->postData('RefType') == 3 && $request->postData('TypeAppro') == 1) {
                    if ($request->postData('MontantVersement') <= $YesterdayReserve) {
                        $this->managers->getManagerOf("Bielletage")->Add(); //Recuperation de la liste
                    } else {
                        $_SESSION['message']['type'] = 'warning';
                        $_SESSION['message']['text'] = 'Le Montant de la transaction est supérieur au solde de la reserve.';
                        $_SESSION['message']['number'] = 2;
                        $this->app()->httpResponse()->redirect('/bielletage/' . $request->postData('RefType'));
                    }
                } elseif ($request->postData('RefType') == 4 or $request->postData('RefType') == 2 or $request->postData('RefType') == 5) {
                    $SoldeActuelleCaisse = $this->managers->getManagerOf("Journal")->SoldeActuelleCaisse(date('Y-m-d'), $request->postData('RefCaisse'));
                    if ($request->postData('MontantVersement') <= $SoldeActuelleCaisse) {
                        $this->managers->getManagerOf("Bielletage")->Add(); //Recuperation de la liste
                    } else {
                        $_SESSION['message']['type'] = 'warning';
                        $_SESSION['message']['text'] = 'Le Montant de la transaction supérieur au solde de la caisse. Veuillez faire un appro de la caisse ou Contactez votre administrateur .';
                        $_SESSION['message']['number'] = 2;
                        $this->app()->httpResponse()->redirect('/bielletage/' . $request->postData('RefType'));
                    }
                } else {
                    $this->managers->getManagerOf("Bielletage")->Add(); //Recuperation de la liste
                }
            }
        }
    }

    public function executeDashboard(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Dashboard"); // Titre de la page
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

        $Agence  = $this->managers->getManagerOf("Pannel")->UserAgence(); //Recuperation de la liste
        foreach ($Agence as $key => $value) {
            $Agence[$key]['SommeDepot'] = $this->managers->getManagerOf("Journal")->SoldeInitialCaisse(date('Y-m-d'), $value['RefAgency']);
            $Agence[$key]['YesterdayReserve'] = $this->managers->getManagerOf("Journal")->YesterdayReserve($value['RefAgency'], date('Y-m-d'));
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
}

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
        // $Biellet = $this->managers->getManagerOf('Arreter')->GetDailyBielletage(date('Y-m-d'));
        // $this->page->addVar('Biellet', $Biellet);
        // $DailyVersement = $this->managers->getManagerOf('Bielletage')->DailyVersement();
        // $this->page->addVar('DailyVersement', $DailyVersement);
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


        $links = $this->managers->getManagerOf('Pannel')->GetLinks();
        $this->page->addVar('links', $links);
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
        $this->managers->getManagerOf('Arreter')->StopCaisse($request->getData('id'), $Solde, ('Y-m-d H:i:s'));
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

        $TypeRetrait  = $this->managers->getManagerOf("Bielletage")->TypeRetrait(); //Recuperation de la liste
        $this->page->addVar("TypeRetrait", $TypeRetrait); // Creation de la variable, ajout d'une variable a la vue
        $permissions = array();
        $AllPermissions = $this->managers->getManagerOf('Pannel')->UserPermission();
        foreach ($AllPermissions as $key => $value) {
            $permissions[] = $value['access'];
        }
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

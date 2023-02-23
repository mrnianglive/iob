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
        $ListeAgence  = $this->managers->getManagerOf("Pannel")->ListeAgence();
        $this->page->addVar("ListeAgence", $ListeAgence);
        if (!empty($request->postData('RefAgency')) or isset($_GET['value'])) {
            if (isset($_GET['debut']) && isset($_GET['fin']) && isset($_GET['value'])) {
                $Operations = $this->managers->getManagerOf('Journal')->GetOperations($_GET['debut'], $_GET['fin'], $_GET['value']);
                $this->page->addVar('Debut', $_GET['debut']);
                $this->page->addVar('Fin', $_GET['fin']);
                $this->page->addVar('Value', $_GET['value']);
            } else {
                $Operations = $this->managers->getManagerOf('Journal')->GetOperations($request->postData('Debut'), $request->postData('Fin'), $request->postData('RefAgency'));
                $this->page->addVar('Debut', $request->postData('Debut'));
                $this->page->addVar('Fin', $request->postData('Fin'));
                $this->page->addVar('Value', $request->postData('RefAgency'));
            }
            $this->page->addVar('Operations', $Operations);


            $SoldeRemittanceVersementAgencePeriode = $this->managers->getManagerOf('Journal')->SoldeRemittanceVersementAgencePeriode($request->postData('Debut'), $request->postData('Fin'), $request->postData('RefAgency'));
            $SoldeRemittanceRetraitAgencePeriode = $this->managers->getManagerOf('Journal')->SoldeRemittanceRetraitAgencePeriode($request->postData('Debut'), $request->postData('Fin'), $request->postData('RefAgency'));
            $SoldeRemittanceAgence = $SoldeRemittanceVersementAgencePeriode - $SoldeRemittanceRetraitAgencePeriode;

            $sommeVersementPeriode = $this->managers->getManagerOf('Journal')->sommeVersementPeriode($request->postData('Debut'), $request->postData('Fin'), $request->postData('RefAgency'));
            $this->page->addVar('sommeVersementPeriode', $sommeVersementPeriode);
            $sommeRetraitPeriode = $this->managers->getManagerOf('Journal')->sommeRetraitPeriode($request->postData('Debut'), $request->postData('Fin'), $request->postData('RefAgency'));
            $this->page->addVar('sommeRetraitPeriode', $sommeRetraitPeriode);
            $sommeVersementPeriodeAvecAppro = $this->managers->getManagerOf('Journal')->sommeVersementPeriodeAvecAppro($request->postData('Debut'), $request->postData('Fin'), $request->postData('RefAgency'));
            $this->page->addVar('sommeVersementPeriodeAvecAppro', $sommeVersementPeriodeAvecAppro);
            $sommeRetraitPeriodeAvecSortie = $this->managers->getManagerOf('Journal')->sommeRetraitPeriodeAvecSortie($request->postData('Debut'), $request->postData('Fin'), $request->postData('RefAgency'));
            $this->page->addVar('sommeRetraitPeriodeAvecSortie', $sommeRetraitPeriodeAvecSortie);
            $Yesterday = $this->managers->getManagerOf('Journal')->YesterdaySoldeAgence($request->postData('Debut'), $request->postData('Fin'), $request->postData('RefAgency'));
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
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Opération validée avec succès';
        $_SESSION['message']['number'] = 2;
        if (!empty($request->postData('Debut')) && !empty($request->postData('Fin'))) {
            $this->app()->httpResponse()->redirect("/Journal/index/" . $request->postData('Debut') . "/" . $request->postData('Fin') . "/" . $request->postData('RefAgency')); //Retour en arriere
        } else {
            $this->app()->httpResponse()->redirect("/Journal/index"); //Retour en arriere
        }
    }
    public function executeCancelvalidate(\Library\HTTPRequest $request)
    {
        $this->managers->getManagerOf("Journal")->CancelValidate($request->getData('id'));
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Validation annulée avec succès';
        $_SESSION['message']['number'] = 2;
        $this->app()->httpResponse()->redirect("/Journal/index"); //Retour en arriere
    }

    public function executeDelete(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Suppresion "); // Titre de la page
        $this->managers->getManagerOf("Journal")->DeleteOperations($request->getData('id'));
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Opération supprimée avec succès';
        $_SESSION['message']['number'] = 2;
        $this->app()->httpResponse()->redirect('/Journal/index'); //Retour en arriere
    }

    public function executePetitecaisse(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Petite Caisse"); // Titre de la page
        $Agence  = $this->managers->getManagerOf("Pannel")->UserAgence(); //Recuperation de la liste
        foreach ($Agence as $key => $value) {
            if (!empty($request->postData('jour'))) {
                $date = $request->postData('jour');
                $this->page->addVar('day', $request->postData('jour'));
            } else {
                $date = date('Y-m-d');
                $this->page->addVar('day', $date);
            }
            $Agence[$key]['SommeDepotRemittance'] = $this->managers->getManagerOf("Journal")->SoldeRemittanceVersementAgence($date, $value['RefAgency']);
            $Agence[$key]['SommeRetraitRemittance'] = $this->managers->getManagerOf("Journal")->SoldeRemittanceRetraitAgence($date, $value['RefAgency']);

            $Agence[$key]['SoldeRemittanceAgence'] = $Agence[$key]['SommeDepotRemittance'] - $Agence[$key]['SommeRetraitRemittance'];
            $Agence[$key]['Afficher'] = $this->managers->getManagerOf("Journal")->CaisseAgence($value['RefAgency'], $date);
            $Agence[$key]['validate'] = $this->managers->getManagerOf("Journal")->CheckDailyClose($value['RefAgency'], $date);
            $Agence[$key]['YesterdayReserve'] = $this->managers->getManagerOf("Journal")->YesterdayReserve($value['RefAgency'], $date);

            $Agence[$key]['SommeDepot'] = $this->managers->getManagerOf("Journal")->SommeDepotAgence($date, $value['RefAgency']);
            $Agence[$key]['SommeSortie'] = $this->managers->getManagerOf("Journal")->SommeRetraitAgence($date, $value['RefAgency']);

            $Agence[$key]['SommeDepotWithRemittance'] = $this->managers->getManagerOf("Journal")->SommeDepotAgence($date, $value['RefAgency']) + $Agence[$key]['SommeDepotRemittance'];
            $Agence[$key]['SommeSortieWithRemittance'] = $this->managers->getManagerOf("Journal")->SommeRetraitAgence($date, $value['RefAgency']) + $Agence[$key]['SommeRetraitRemittance'];

            $Agence[$key]['TotalAppoAgenceSansApproInitial'] = $this->managers->getManagerOf("Journal")->TotalApproAgenceSansApproInitial($date, $value['RefAgency']);
            $Agence[$key]['TotalSortieAgence'] = $this->managers->getManagerOf("Journal")->TotalSortieAgence($date, $value['RefAgency']);
            $Agence[$key]['ReserveActuelle'] = $Agence[$key]['YesterdayReserve'] + $Agence[$key]['SommeDepot'] - $Agence[$key]['SommeSortie'] +
                $Agence[$key]['TotalAppoAgenceSansApproInitial'] - $Agence[$key]['TotalSortieAgence'] + $Agence[$key]['SoldeRemittanceAgence'];
            $Agence[$key]['DayReserve'] =  $Agence[$key]['YesterdayReserve'] - $this->managers->getManagerOf("Journal")->TotalApproAgenceAvecApproInitial($date, $value['RefAgency']);

            $Agence[$key]['SommeDepotProduit'] = $this->managers->getManagerOf("Journal")->SommeDepotProduitAgence($date, $value['RefAgency']);
            $Agence[$key]['SommeSortieProduit'] = $this->managers->getManagerOf("Journal")->SommeRetraitProduitAgence($date, $value['RefAgency']);
        }
        $this->page->addVar('Agence', $Agence);
    }

    public function executeCancelFermeture(\Library\HTTPRequest $request)
    {
        $this->managers->getManagerOf("Journal")->CancelFermeture($request->getData('id'), $request->getData('RefAgency'), $request->getData('day'));
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Fermeture annulée avec succès';
        $_SESSION['message']['number'] = 3;
        $this->app()->httpResponse()->redirect("/Journal/petite_caisse"); //Retour en arriere
    }
}

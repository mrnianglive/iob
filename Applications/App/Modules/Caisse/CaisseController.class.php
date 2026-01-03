<?php

namespace Applications\App\Modules\Caisse;

class CaisseController extends \Library\BackController
{
    public function executeIndex(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Ma Caisse"); // Titre de la page
        
        $Chmod  = $this->managers->getManagerOf("Bielletage")->CheckOuverture(); //Recuperation de la liste
        $this->page->addVar("CheckOuverture", $Chmod); // Creation de la variable, ajout d'une variable a la vue
        
        // Récupérer les caisses de l'utilisateur
        $UserCaisse  = $this->managers->getManagerOf("Caisse")->UserCaisse(date('Y-m-d')); //Recuperation de la liste
        $this->page->addVar("UserCaisse", $UserCaisse); // Creation de 
        
        // Déterminer la caisse à afficher
        $refCaisse = null;
        if (!empty($request->postData('RefCaisse'))) {
            $refCaisse = $request->postData('RefCaisse');
        } elseif (!empty($UserCaisse) && isset($UserCaisse[0]['RefCaisse'])) {
            $refCaisse = $UserCaisse[0]['RefCaisse'];
        }
        
        // OPTIMISATION: Utiliser GetOperationsWithTotals au lieu de GetOperations + plusieurs requêtes de totaux
        if ($refCaisse) {
            $data = $this->managers->getManagerOf('Caisse')->GetOperationsWithTotals($refCaisse);
            $this->page->addVar("GetOperations", $data['operations']);
            
            // Ajouter les totaux pour la vue
            $this->page->addVar("TotalVersement", $data['totaux']['TotalVersement']);
            $this->page->addVar("TotalRetrait", $data['totaux']['TotalRetrait']);
            $this->page->addVar("NbOperations", $data['totaux']['NbOperations']);
        } else {
            $this->page->addVar("GetOperations", []);
            $this->page->addVar("TotalVersement", 0);
            $this->page->addVar("TotalRetrait", 0);
            $this->page->addVar("NbOperations", 0);
        }
    }
    public function executeTransfertfond(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Liste des Transfert de fond"); // Titre de la page

        $UserCaisse  = $this->managers->getManagerOf("Journal")->UserCaisse(date('Y-m-d')); //Recuperation de la liste
        $this->page->addVar("ListeCaisse", $UserCaisse); // Creation de la variable, ajout d'une variable a la vue
        $Agence  = $this->managers->getManagerOf("Pannel")->UserAgence();
        $this->page->addVar('UserAgence', $Agence);

        // Valeurs par défaut pour éviter les undefined variable
        $this->page->addVar('Debut', $request->postData('Debut') ?? date('Y-m-d'));
        $this->page->addVar('Fin', $request->postData('Fin') ?? date('Y-m-d'));
        $this->page->addVar('RefAgency', $request->postData('RefAgency') ?? '');

        if (!empty($request->postData('RefAgency')) && !empty($request->postData('Debut')) && !empty($request->postData('Fin'))) {
            $ListeFond = $this->managers->getManagerOf("Caisse")->ListeFond(
                $request->postData('RefAgency'),
                $request->postData('Debut'),
                $request->postData('Fin')
            );
            $this->page->addVar("ListeFond", $ListeFond);
        } else {
            $ListeFond = $this->managers->getManagerOf("Caisse")->ListeFond();
            $this->page->addVar("ListeFond", $ListeFond);
        }
    }
    public function executeApprocaisse(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Liste des Appro"); // Titre de la page
        $Annee = $request->getData('Annee') ?? date('Y');
        $this->page->addVar("Annee", $Annee);
        $Appro  = $this->managers->getManagerOf("Caisse")->ListeAppro($Annee); //Recuperation de la liste
        $this->page->addVar("ListeAppro", $Appro); // Creation d
        $UserCaisse  = $this->managers->getManagerOf("Journal")->UserCaisse(date('Y-m-d')); //Recuperation de la liste
        $this->page->addVar("ListeCaisse", $UserCaisse); // Creation de la variable, ajout d'une variable a la vue
        if ($request->method() == 'POST' && !empty($request->postData('RefCaisse')) && !empty($request->postData('MontantAppro'))) {
            $AddAppro  = $this->managers->getManagerOf("Caisse")->AddAppro($request); //Recuperation de la liste
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Ajout réussie !';
            $_SESSION['message']['number'] = 2;
            $this->app()->httpResponse()->redirect('/Caisse/ApproCaisse'); //Retour en arriere
        }
    }
    public function executeDeleteTransfert(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Delete Transfert"); // Titre de la page
        $this->managers->getManagerOf("Caisse")->DeleteTransfert($request->getData('id')); //Recuperation de la liste
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Suppression réussie !';
        $_SESSION['message']['number'] = 2;
        $this->app()->httpResponse()->redirect('/Caisse/transfertfond'); //Retour en arriere
    }
    public function executeDeleteAppro(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Delete Appro"); // Titre de la page
        $this->managers->getManagerOf("Caisse")->DeleteAppro($request->getData('id')); //Recuperation de la liste
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Suppression réussie !';
        $_SESSION['message']['number'] = 2;
        $this->app()->httpResponse()->redirect('/Caisse/ApproCaisse'); //Retour en arriere
    }
}
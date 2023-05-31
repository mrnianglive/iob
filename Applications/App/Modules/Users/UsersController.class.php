<?php

namespace Applications\App\Modules\Users;

class UsersController extends \Library\BackController
{
    public function executeIndex(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Accueil"); // Titre de la page
        $Users  = $this->managers->getManagerOf("User")->ListeUsers();
        $ListeCaisse  = $this->managers->getManagerOf("User")->ListeCaisse();

        $ListePays  = $this->managers->getManagerOf("Pannel")->ListePays();
        $this->page->addVar("ListePays", $ListePays);
        $ListeBanque  = $this->managers->getManagerOf("Pannel")->ListeBanque();
        $this->page->addVar("ListeBanque", $ListeBanque);
        $caisse = array();
        $VerifAppro = array();

        foreach ($Users as $key => $value) {
            foreach ($ListeCaisse as $key1 => $value1) {
                $caisse[$value['RefUsers']][$value1['RefCaisse']] = $this->managers->getManagerOf('User')->VerifCaisse($value1['RefCaisse'], $value['RefUsers']);
                $VerifAppro[$value['RefUsers']][$value1['RefCaisse']] = $this->managers->getManagerOf('User')->VerifCaisseAppro($value1['RefCaisse'], $value['RefUsers']);
            }
        }
        $this->page->addVar("ListeUsers", $Users); // Creation de la variable, ajout d'une variable a la vue
        $this->page->addVar("ListeCaisse", $ListeCaisse);
        $this->page->addVar('VerifCaisse', $caisse);
        $this->page->addVar('VerifCaisseAppro', $VerifAppro);
        $ListeStatut  = $this->managers->getManagerOf("User")->ListeStatut();
        $this->page->addVar("ListeStatut", $ListeStatut);
        if ($request->method() == 'POST' && empty($request->postData('RefUsers'))) {
            $this->managers->getManagerOf('User')->AddUser($request);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Ajout réussie !';
            $_SESSION['message']['number'] = 2;
            $this->app()->httpResponse()->redirect('/Users/index'); //Retour en arriere
        } elseif ($request->method() == 'POST' && !empty($request->postData('RefUsers'))) {
            $this->managers->getManagerOf('User')->AddChmod($request);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Ajout réussie !';
            $_SESSION['message']['number'] = 2;
            $this->app()->httpResponse()->redirect('/Users/index'); //Retour en arriere
        }
    }

    public function executeProfile(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Mon Profile"); // Titre de la page
        $MyProfile = $this->managers->getManagerOf('User')->MyProfile();
        $this->page->addVar('Info', $MyProfile);
        if ($request->method() == "POST" && empty($request->postData('password'))) {
            $this->managers->getManagerOf('User')->UpdateInfo();
            $this->app()->httpResponse()->redirect('/Users/myprofile'); //Retour en arriere
        } elseif (!empty($request->postData('password'))) {
            $this->managers->getManagerOf('User')->CheckPassword($request);
        }
    }
    public function executeNewpassword(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Entrer un nouveau mot de passe"); // Titre de la page
        if ($request->method() == "POST") {
            $this->managers->getManagerOf('User')->ValidPassword($request);
            $this->app()->httpResponse()->redirect('/Users/myprofile'); //Retour en arriere
        }
    }
    public function executeDeleteusers(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Suppresssion user"); // Titre de la page
        $this->managers->getManagerOf('User')->DeleteUsers($request->getData('id'));
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Suppression réussie !';
        $_SESSION['message']['number'] = 2;
        $this->app()->httpResponse()->redirect('/Users/index'); //Retour en arriere

    }


    public function executeDelogger(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Delogger user"); // Titre de la page
        $this->managers->getManagerOf('User')->UpdateLog($request->getData('id'), 1);
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'réussie !';
        $_SESSION['message']['number'] = 2;
        $this->app()->httpResponse()->redirect('/Users/index'); //Retour en arriere

    }

    public function executeUpdateusers(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Mettre à jour les informations "); // Titre de la page
        $Info = $this->managers->getManagerOf('User')->GetUserInfo($request->getData('id'));
        $this->page->addVar('Info', $Info);
        $ListeStatut  = $this->managers->getManagerOf("User")->ListeStatut();
        $this->page->addVar("ListeStatut", $ListeStatut);
        $ListePays  = $this->managers->getManagerOf("Pannel")->ListePays();
        $this->page->addVar("ListePays", $ListePays);
        $ListeBanque  = $this->managers->getManagerOf("Pannel")->ListeBanque();
        $this->page->addVar("ListeBanque", $ListeBanque);

        if ($request->method() == 'POST') {
            $this->managers->getManagerOf("User")->UpdateUsers($request);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Modification réussie !';
            $_SESSION['message']['number'] = 2;
            $this->app()->httpResponse()->redirect('/Users/index'); //Retour en arriere

        }
    }

    public function executeDoubleauth(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Double authentification"); // Titre de la page
        $Info = $this->managers->getManagerOf('User')->GetUserInfo($request->getData('id'));
        $this->page->addVar('Info', $Info);
        if ($request->method() == 'POST') {
            $this->managers->getManagerOf("User")->DoubleAuth($request);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Activation du 2FA réussie !';
            $_SESSION['message']['number'] = 2;
            $this->app()->httpResponse()->redirect('/Users/index'); //Retour en arriere
        }
    }
    public function executeResetauth(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Double authentification"); // Titre de la page
        $Info = $this->managers->getManagerOf('User')->GetUserInfo($request->getData('id'));
        $this->managers->getManagerOf("User")->ResetAuth($Info['RefUsers']);
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Réinitialisation du 2FA réussie !';
        $_SESSION['message']['number'] = 2;

        $this->app()->httpResponse()->redirect('/Users/doubleauth/' . $Info['RefUsers']); //Retour en arriere   
    }
}

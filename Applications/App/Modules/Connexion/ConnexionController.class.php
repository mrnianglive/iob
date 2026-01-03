<?php

namespace Applications\App\Modules\Connexion;

class ConnexionController extends \Library\BackController
{
    // public function executeIndex(\Library\HTTPRequest $request)
    // {
    //     $this->page->addVar("titles", "Page de Connexion"); // Titre de la page
    //     $this->page->setTemplate('login');
    //     if ($request->method() == 'POST' && !empty($request->postData('login')) && !empty($request->postData('password'))) {
    //         $User = $this->managers->getManagerOf('User')->login($request->postData('login'), $request->postData('password'));
    //         if (!empty($User)) {
    //             if (!empty($User['secret'])) {
    //                 $this->app()->user()->setAuthenticated();
    //                 if (!empty($User['RefPays']) && $User['RefPays'] != 0) {
    //                     $getPaysName = $this->managers->getManagerOf('Pannel')->getPaysName($User['RefPays']);
    //                     $_SESSION['RefPays'] = $User['RefPays'];
    //                     $_SESSION['nomPays'] = $getPaysName['nomPays'];
    //                     $_SESSION['logoPays'] = $getPaysName['logo'];
    //                 }
    //                 $_SESSION['RefUsers'] = $User['RefUsers'];
    //                 $_SESSION['login'] = $User['login'];
    //                 $_SESSION['NomUsers'] = $User['NomUsers'];
    //                 $_SESSION['PrenomUsers'] = $User['PrenomUsers'];
    //                 $_SESSION['statut'] = $User['Name'];
    //                 $_SESSION['secret'] = true;
    //                 $this->app()->httpResponse()->redirect('/connexion/doubleauth');
    //             } else {
    //                 $this->app()->user()->setAuthenticated();
    //                 if (!empty($User['RefPays']) && $User['RefPays'] != 0) {
    //                     $getPaysName = $this->managers->getManagerOf('Pannel')->getPaysName($User['RefPays']);
    //                     $_SESSION['RefPays'] = $User['RefPays'];
    //                     $_SESSION['nomPays'] = $getPaysName['nomPays'];
    //                     $_SESSION['logoPays'] = $getPaysName['logo'];
    //                 }
    //                 $_SESSION['login'] = $User['login'];
    //                 $_SESSION['NomUsers'] = $User['NomUsers'];
    //                 $_SESSION['PrenomUsers'] = $User['PrenomUsers'];
    //                 $_SESSION['statut'] = $User['Name'];
    //                 $_SESSION['RefUsers'] = $User['RefUsers'];
    //                 $this->app()->httpResponse()->redirect('/');
    //             }
    //         }
    //     }
    // }

    public function executeIndex(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Page de Connexion"); // Titre de la page
        $this->page->setTemplate('login');

        $IP = $this->managers->getManagerOf('User')->getIPAddress();

        if ($request->method() == 'POST' && !empty($request->postData('login')) && !empty($request->postData('password'))) {
            $User = $this->managers->getManagerOf('User')->login($request->postData('login'), $request->postData('password'));
            if (!empty($User)) {
                if (!empty($User['secret'])) {
                    $_SESSION['first_login'] = !isset($_SESSION['first_login']);
                    $this->app()->user()->setAuthenticated();
                    if (!empty($User['RefPays']) && $User['RefPays'] != 0) {
                        $getPaysName = $this->managers->getManagerOf('Pannel')->getPaysName($User['RefPays']);
                        $_SESSION['RefPays'] = $User['RefPays'];
                        $_SESSION['nomPays'] = $getPaysName['nomPays'];
                        $_SESSION['logoPays'] = $getPaysName['logo'];
                    }
                    $_SESSION['RefUsers'] = $User['RefUsers'];
                    $_SESSION['login'] = $User['login'];
                    $_SESSION['NomUsers'] = $User['NomUsers'];
                    $_SESSION['PrenomUsers'] = $User['PrenomUsers'];
                    $_SESSION['statut'] = $User['Name'];
                    $_SESSION['secret'] = true;
                    $redirectPath = '/connexion/doubleauth';
                } else {
                    $_SESSION['first_login'] = !isset($_SESSION['first_login']);
                    $this->app()->user()->setAuthenticated();
                    if (!empty($User['RefPays']) && $User['RefPays'] != 0) {
                        $getPaysName = $this->managers->getManagerOf('Pannel')->getPaysName($User['RefPays']);
                        $_SESSION['RefPays'] = $User['RefPays'];
                        $_SESSION['nomPays'] = $getPaysName['nomPays'];
                        $_SESSION['logoPays'] = $getPaysName['logo'];
                    }
                    $_SESSION['login'] = $User['login'];
                    $_SESSION['NomUsers'] = $User['NomUsers'];
                    $_SESSION['PrenomUsers'] = $User['PrenomUsers'];
                    $_SESSION['statut'] = $User['Name'];
                    $_SESSION['RefUsers'] = $User['RefUsers'];
                    $redirectPath = '/';
                }
                //add to log LogConnexion  using User and IP getIPAddress()

                $this->managers->getManagerOf('User')->LogConnexion($User['RefUsers'], $IP);
                $this->app()->httpResponse()->redirect($redirectPath);
            }
        }
    }

    public function executeLogout(\Library\HTTPRequest $request)
    {
        $this->page->addVar('titles', 'Logout');
        $this->app()->user()->setAuthenticated(false); //deconnexion de user
        
        // Vider la session avant de la détruire
        $_SESSION = array();
        
        // Supprimer le cookie de session si utilisé
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Détruire la session
        session_destroy();
        
        // Démarrer une nouvelle session pour le message flash
        session_start();
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Déconnexion réussie !';
        $_SESSION['message']['number'] = 2;
        
        $this->app()->httpResponse()->redirect('/');
    }

    public function executeDoubleauth(\Library\HTTPRequest $request)
    {
        $this->page->addVar('titles', '2FA');
        $this->page->setTemplate('login');
        if ($request->method() == 'POST' && !empty($request->postData('tfa_code'))) {
            $this->managers->getManagerOf("User")->VerifDoubleAuth($request);
        }
    }

    public function executeAuthMicrosoft(\Library\HTTPRequest $request)
    {
        //authentification avec microsoft
        $this->page->addVar('titles', 'Authentification Microsoft');

        $this->managers->getManagerOf("User")->AuthMicrosoft($request);
    }
}
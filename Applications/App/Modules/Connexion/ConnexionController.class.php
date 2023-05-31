<?php

namespace Applications\App\Modules\Connexion;

class ConnexionController extends \Library\BackController
{
    public function executeIndex(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Page de Connexion"); // Titre de la page
        $this->page->setTemplate('login');
        if ($request->method() == 'POST' && !empty($request->postData('login')) && !empty($request->postData('password'))) {
            $User = $this->managers->getManagerOf('User')->login($request->postData('login'), $request->postData('password'));
            if (!empty($User)) {
                if ($User['RefStatut'] == 8) {
                    $_SESSION['message']['type'] = 'danger';
                    $_SESSION['message']['text'] = 'Compte désactivé , veuillez contacter l\'administrateur !';
                    $_SESSION['message']['number'] = 2;
                    $this->app()->httpResponse()->redirect('/connexion');
                }
                if (!empty($User['secret'])) {
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
                    $this->app()->httpResponse()->redirect('/connexion/doubleauth');
                } else {
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
                    $this->app()->httpResponse()->redirect('/');
                }
            }
        }
    }
    public function executeLogout(\Library\HTTPRequest $request)
    {
        $this->page->addVar('titles', 'Logout');
        $this->app()->user()->setAuthenticated(false); //deconnexion de user
        session_destroy(); //on détruit la session
        $_SESSION = array(); //on vide le tableau de session
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
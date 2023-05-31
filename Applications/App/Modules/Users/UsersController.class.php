<?php

namespace Applications\App\Modules\Users;

class UsersController extends \Library\BackController
{

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
}

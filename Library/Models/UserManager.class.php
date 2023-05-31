<?php

namespace Library\Models;

use \Library\Entities\User;

abstract class UserManager extends \Library\Manager
{
    abstract public function login($Login, $Password);
    abstract  protected function MyProfile();
    abstract protected function UpdateInfo();
    abstract protected function CheckPassword();
    abstract protected function ValidPassword();

    abstract protected function SendUserinfo($to, $login, $Password);
    abstract protected function GetUserInfo($Users);
}

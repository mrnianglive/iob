<?php

namespace Library\Models;

use \Library\Entities\User;

require __DIR__ . '/../../Web/vendor/autoload.php';
require __DIR__ . '/../../vendor/autoload.php';


use RobThree\Auth\TwoFactorAuth;

use myPHPnotes\Microsoft\Auth;


class UserManagerPDO extends UserManager
{
    public function login($login, $Password)
    {
        //before login, check if the post data is not empty

        $IP = $this->getIPAddress();
        if (!empty($login) && !empty($Password)) {
            $requete = $this->dao->prepare("SELECT *  FROM TbleUsers INNER JOIN TbleStatut ON TbleStatut.RefStatut=TbleUsers.RefStatut WHERE login=:login AND TbleUsers.RefStatut =8");
            $requete->bindValue(':login', $login, \PDO::PARAM_STR);
            $requete->execute();
            $resultat = $requete->fetch();
            if (!empty($resultat['password']) && password_verify($Password, $resultat['password'])) {
                return $resultat;
            }
        } else {
            return false;
        }
    }
    public function SendUserinfo($to, $login, $Password)
    {
        $subject = "Identifiants de connexion | CAISSE MLC";
        $message = "Veuillez recevoir vos Identifiants de connexion. Votre login est: $login et le mot de passe est: $Password  le lien d'acces du site est : https://app.malicreances-sa.com/  Merci de modifier votre mot de passe dès reception de ce mail. ";
        $headers = 'From: no-reply@malicreances-sa.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        mail($to, $subject, $message, $headers);
    }
    public function MyProfile()
    {
        $requete = $this->dao->prepare('SELECT * FROM TbleUsers INNER JOIN TbleStatut ON TbleStatut.RefStatut=TbleUsers.RefStatut WHERE RefUsers=:RefUsers');
        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->execute();
        $display = $requete->fetch();
        return $display;
    }
    public function UpdateInfo()
    {
        $requete = $this->dao->prepare("UPDATE TbleUsers SET email=:email,AgenceUsers=:AgenceUsers WHERE RefUsers=:RefUsers");
        $requete->bindValue(':email', $_POST['email'], \PDO::PARAM_STR);
        $requete->bindValue(':AgenceUsers', $_POST['AgenceUsers'], \PDO::PARAM_STR);
        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->execute();
    }
    public function CheckPassword()
    {
        $query = $this->dao->prepare("SELECT * FROM TbleUsers WHERE RefUsers=:RefUsers ");
        $query->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $query->execute();
        $data = $query->fetch();
        if (password_verify($_POST['password'], $data['password'])) {
            header('Location: /Users/NewPassword');
        }
    }
    public function ValidPassword()
    {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $requete = $this->dao->prepare("UPDATE TbleUsers SET password=:password WHERE RefUsers=:RefUsers");
        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->bindValue(':password', $password, \PDO::PARAM_STR);
        $requete->execute();
    }

    public function GetUserInfo($Users)
    {
        $requete = $this->dao->prepare('SELECT * FROM TbleUsers INNER JOIN TbleStatut ON TbleStatut.RefStatut=TbleUsers.RefStatut WHERE RefUsers=:RefUsers');
        $requete->bindValue(':RefUsers', $Users, \PDO::PARAM_INT);
        $requete->execute();
        $display = $requete->fetch();
        return $display;
    }
    public function UpdateUsers()
    {
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $requete = $this->dao->prepare("UPDATE TbleUsers SET password=:password,NomUsers=:NomUsers,PrenomUsers=:PrenomUsers,email=:email,RefStatut=:RefStatut,RefPays=:RefPays WHERE RefUsers=:RefUsers");
            $requete->bindValue(':password', $password, \PDO::PARAM_STR);
            $requete->bindValue(':NomUsers', $_POST['NomUsers'], \PDO::PARAM_STR);
            $requete->bindValue(':PrenomUsers', $_POST['PrenomUsers'], \PDO::PARAM_STR);
            $requete->bindValue(':email', $_POST['email'], \PDO::PARAM_STR);
            $requete->bindValue(':RefStatut', $_POST['RefStatut'], \PDO::PARAM_INT);
            $requete->bindValue(':RefUsers', $_POST['RefUsers'], \PDO::PARAM_INT);
            $requete->bindValue(':RefPays', $_POST['RefPays'], \PDO::PARAM_INT);
            $requete->execute();
        } else {
            $requete = $this->dao->prepare("UPDATE TbleUsers SET NomUsers=:NomUsers,PrenomUsers=:PrenomUsers,email=:email,RefStatut=:RefStatut,RefPays=:RefPays WHERE RefUsers=:RefUsers");
            $requete->bindValue(':NomUsers', $_POST['NomUsers'], \PDO::PARAM_STR);
            $requete->bindValue(':PrenomUsers', $_POST['PrenomUsers'], \PDO::PARAM_STR);
            $requete->bindValue(':email', $_POST['email'], \PDO::PARAM_STR);
            $requete->bindValue(':RefStatut', $_POST['RefStatut'], \PDO::PARAM_INT);
            $requete->bindValue(':RefPays', $_POST['RefPays'], \PDO::PARAM_INT);
            $requete->bindValue(':RefUsers', $_POST['RefUsers'], \PDO::PARAM_INT);

            $requete->execute();
        }
    }
    public function AddChmod()
    {
        if ($_POST['Type'] == 'appro') {
            $requeteDelete = $this->dao->prepare('DELETE FROM TbleChmodAppro WHERE RefUsers=:users');
            $requeteDelete->bindValue(':users', $_POST['RefUsers'], \PDO::PARAM_INT);
            $requeteDelete->execute();
            if (!empty($_POST['RefCaisse'])) {
                foreach ($_POST['RefCaisse'] as $key => $value) {
                    $requeteInsert = $this->dao->prepare('INSERT INTO TbleChmodAppro(RefCaisse,RefUsers) VALUES(:RefCaisse,:RefUsers)');
                    $requeteInsert->bindValue(':RefCaisse', $value, \PDO::PARAM_INT);
                    $requeteInsert->bindValue(':RefUsers', $_POST['RefUsers'], \PDO::PARAM_INT);
                    $requeteInsert->execute();
                }
            }
        } elseif ($_POST['Type'] == 'chmod') {
            $requeteDelete = $this->dao->prepare('DELETE FROM TbleChmod WHERE RefUsers=:users');
            $requeteDelete->bindValue(':users', $_POST['RefUsers'], \PDO::PARAM_INT);
            $requeteDelete->execute();
            if (!empty($_POST['RefCaisse'])) {
                foreach ($_POST['RefCaisse'] as $key => $value) {
                    $requeteInsert = $this->dao->prepare('INSERT INTO TbleChmod(RefCaisse,RefUsers) VALUES(:RefCaisse,:RefUsers)');
                    $requeteInsert->bindValue(':RefCaisse', $value, \PDO::PARAM_INT);
                    $requeteInsert->bindValue(':RefUsers', $_POST['RefUsers'], \PDO::PARAM_INT);
                    $requeteInsert->execute();
                }
            }
        }
    }

    public function UpdateLog($Users, $value, $LastLog = NULL)
    {
        $requete = $this->dao->prepare("UPDATE TbleUsers SET log='$value',LastLogID= '$LastLog'  WHERE RefUsers=:RefUsers");
        $requete->bindValue(':RefUsers', $Users, \PDO::PARAM_INT);
        $requete->execute();
    }

    public function LastConnexionUpdate($Last)
    {
        $requete = $this->dao->prepare("UPDATE  LogConnexion SET LogoutH=:hour WHERE RefLog=:RefLog");
        $requete->bindValue(':RefLog', $Last, \PDO::PARAM_INT);
        $requete->bindValue(':hour', gmdate("H:i:s"), \PDO::PARAM_STR);
        $requete->execute();
    }
    public function LogConnexion($Users, $IP)
    {
        $requete = $this->dao->prepare("INSERT INTO LogConnexion(RefUsers,IP,LogH) VALUES(:RefUsers,:IP,:LogH)");
        $requete->bindValue(':RefUsers', $Users, \PDO::PARAM_INT);
        $requete->bindValue(':IP', $IP, \PDO::PARAM_STR);
        $requete->bindValue(':LogH', gmdate("H:i:s"), \PDO::PARAM_STR);
        $requete->execute();
        $last = $this->dao->lastInsertId();
        $_SESSION['LogID'] = $last;
        return $last;
    }

    public function getLastConnexionTime($LastLogID)
    {
        $requete = $this->dao->prepare("SELECT * FROM LogConnexion WHERE RefLog=:RefLog");
        $requete->bindValue(':RefLog', $LastLogID, \PDO::PARAM_INT);
        $requete->execute();
        $display = $requete->fetch();
        return $display;
    }

    public function getIPAddress()
    {
        if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            return $_SERVER["HTTP_CLIENT_IP"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED"])) {
            return $_SERVER["HTTP_X_FORWARDED"];
        } elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])) {
            return $_SERVER["HTTP_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_FORWARDED"])) {
            return $_SERVER["HTTP_FORWARDED"];
        } else {
            return $_SERVER["REMOTE_ADDR"];
        }
    }



    public function VerifDoubleAuth()
    {
        $tfa = new TwoFactorAuth();
        $user = $this->GetUserInfo($_SESSION['RefUsers']);
        if ($tfa->verifyCode($user['secret'], $_POST['tfa_code'])) {
            $_SESSION['DoubleAuth'] = true;
            header('Location: /');
        } else {
            $_SESSION['message']['type'] = 'warning';
            $_SESSION['message']['text'] = 'Le code est incorrect,Try again  !';
            $_SESSION['message']['number'] = 2;
            header('Location: /connexion/doubleauth');
        }
    }
}
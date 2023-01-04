<?php
require("db.php");

require __DIR__ . '/../Web/vendor/autoload.php';

use RobThree\Auth\TwoFactorAuth;

$query = $baseDeDonnee->prepare("SELECT * FROM TbleUsers WHERE RefUsers = :RefUsers");
$query->bindValue(":RefUsers", 1, PDO::PARAM_INT);
$query->execute();
$data = $query->fetch();

$tfa = new TwoFactorAuth();
$user = $this->GetUserInfo($_SESSION['RefUsers']);
if ($tfa->verifyCode($user['secret'], $_POST['tfa_code'])) {

    $codeExists = $stmt->fetch() !== false;

    // Renvoie le résultat au script AJAX sous forme de JSON
    header('Content-Type: application/json');
    echo json_encode(['codeExists' => $codeExists]);
}
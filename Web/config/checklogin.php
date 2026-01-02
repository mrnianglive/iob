<?php
require("db.php");

if (isset($_GET['login'])) {
    $login = $_GET['login'];
    $query = $baseDeDonnee->prepare("SELECT * FROM TbleUsers WHERE login = :login");
    $query->bindValue(':login', $login, \PDO::PARAM_STR);
    $query->execute();
    $data = $query->fetch();
    echo json_encode($data['login']);
}
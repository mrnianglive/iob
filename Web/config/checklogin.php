<?php
require("db.php");

if (isset($_GET['login'])) {
    $query = $baseDeDonnee->prepare("SELECT * FROM TbleUsers WHERE login = '" . $_GET['login'] . " AND TbleUsers.RefStatut =8'");
    $query->execute();
    $data = $query->fetch();
    echo json_encode($data['login']);
}

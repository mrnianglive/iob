<?php
require("db.php");

if (isset($_GET['NumCompte'])) {
    $query = $baseDeDonnee->prepare("SELECT * FROM TbleOperations WHERE NumCompte=:NumCompte ");
    $query->bindValue(':NumCompte', $_GET['NumCompte'], PDO::PARAM_INT);
    $query->execute();
    $data = $query->fetch();
    echo json_encode($data['NameClient']);
}

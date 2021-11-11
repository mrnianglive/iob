<?php
require("db.php");

if (isset($_GET['RefCaisse'])) {
    $query = $baseDeDonnee->prepare("SELECT * FROM TbleCaisse WHERE RefCaisse=:RefCaisse ");
    $query->bindValue(':RefCaisse', $_GET['RefCaisse'], PDO::PARAM_INT);
    $query->execute();
    $data = $query->fetch();
    echo json_encode($data);
}
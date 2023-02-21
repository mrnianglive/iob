<?php

require("db.php");

if (isset($_GET['Agence'])) {
    $tableau = array();
    $requete = $baseDeDonnee->prepare('SELECT * FROM TbleCaisse WHERE (TbleCaisse.RefAgency=:RefAgency)');
    $requete->bindValue(':RefAgency', $_GET['Agence'], PDO::PARAM_INT);
    $requete->execute();
    $resultat = $requete->fetchAll();
    foreach ($resultat as $key => $value) {
        $tableau[$value['RefCaisse']][] = $value['NameCaisse'];
    }
    echo json_encode($tableau);
}

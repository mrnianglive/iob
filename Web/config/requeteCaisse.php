<?php

require("db.php");

if (isset($_GET['Agence'])) {
    $tableau = array();
    $requete = $baseDeDonnee->prepare('SELECT * FROM TbleCaisse WHERE (TbleCaisse.RefAgency=:Agence)');
    $requete->bindValue(':Agence', $_GET['RefAgency'], PDO::PARAM_INT);
    $requete->execute();
    $resultat = $requete->fetchAll();
    foreach ($resultat as $key => $value) {
        $tableau[$value['RefCaisse']][] = $value['NameCaisse'];
    }
    echo json_encode($tableau);
}

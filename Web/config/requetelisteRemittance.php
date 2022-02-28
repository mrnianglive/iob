<?php

require("db.php");

if (isset($_GET['Caisse'])) {
    $tableau = array();
    $requete = $baseDeDonnee->prepare('SELECT * FROM TbleProduit INNER JOIN TbleChmodProduit ON TbleChmodProduit.RefProduit=TbleProduit.RefProduit WHERE (TbleChmodProduit.RefCaisse=:RefCaisse) ');
    $requete->bindValue(':RefCaisse', $_GET['Caisse'], PDO::PARAM_INT);
    $requete->execute();
    $resultat = $requete->fetchAll();
    foreach ($resultat as $key => $value) {
        if ($value['RefProduit'] != 1) {
            $tableau[$value['RefProduit']][] = $value['NameProduit'];
        }
    }
    echo json_encode($tableau);
}
<?php

require("db.php");

if (isset($_GET['Caisse'])) {
    $tableau = array();
    $requete = $baseDeDonnee->prepare('SELECT * FROM TbleProduit INNER JOIN TbleChmodProduit ON TbleChmodProduit.RefProduit=TbleProduit.RefProduit INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleChmodProduit.RefCaisse WHERE TbleCaisse.RefAgency=:RefAgency GROUP BY TbleProduit.RefProduit');
    $requete->bindValue(':RefAgency', $_GET['Caisse'], PDO::PARAM_INT);
    $requete->execute();
    $resultat = $requete->fetchAll();
    foreach ($resultat as $key => $value) {
        if ($value['StatutProduit'] == 'banque') { // Sauf Ecobank
            $tableau[$value['RefProduit']][] = $value['NameProduit'];
        }
    }
    echo json_encode($tableau);
}

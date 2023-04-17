<?php

require("db.php");

if (isset($_GET['Caisse'])) {
    $tableau = array();
    $requete = $baseDeDonnee->prepare('SELECT * FROM TbleProduit INNER JOIN TbleChmodProduit ON TbleChmodProduit.RefProduit=TbleProduit.RefProduit WHERE (TbleChmodProduit.RefCaisse=:RefCaisse) ');
    $requete->bindValue(':RefCaisse', $_GET['Caisse'], PDO::PARAM_INT);
    $requete->execute();
    $resultat = $requete->fetchAll();
    foreach ($resultat as $key => $value) {
        if ($value['StatutProduit'] == 'banque') { // Uniquement Ecobank
            $tableau[$value['RefProduit']] = [
                'nom' => $value['NameProduit'],
                'image' => $value['ImageProduit'] // Remplacez 'ImageProduit' par le nom de la colonne correspondante dans votre base de données
            ];
        }
    }
    echo json_encode($tableau);
}

<?php

require("db.php");

if (isset($_GET['Pays'])) {
    $tableau = array();
    $requete = $baseDeDonnee->prepare('SELECT * FROM TbleAgency WHERE (TbleAgency.RefPays=:RefPays) ');
    $requete->bindValue(':RefPays', $_GET['Pays'], PDO::PARAM_INT);
    $requete->execute();
    $resultat = $requete->fetchAll();
    foreach ($resultat as $key => $value) {
        $tableau[$value['RefAgency']][] = $value['NameAgency'];
    }
    echo json_encode($tableau);
}

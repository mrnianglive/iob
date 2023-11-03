<?php
require("db.php");

if (isset($_GET['NumCompte'])) {
    $montant = $_GET['MontantVersement'];
    $query = $baseDeDonnee->prepare("SELECT * FROM TbleOperations WHERE NumCompte=:NumCompte ");
    $query->bindValue(':NumCompte', $_GET['NumCompte'], PDO::PARAM_INT);
    $query->execute();
    $data = $query->fetch();

    //Faire la moyenne des opérations du client et le comparer au versement en cours MontantVersmet
    //Si le montant est supérieur à la moyenne, on  reourne un message d'alerte 
    $requete = $baseDeDonnee->prepare("SELECT AVG(MontantVersement) FROM TbleOperations WHERE NumCompte=:NumCompte AND  AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL ORDER BY DateTransaction DESC LIMIT 5");
    $requete->bindValue(':NumCompte', $_GET['NumCompte'], PDO::PARAM_INT);
    $requete->execute();
    $moyenne = $requete->fetch();

    if ($montant > $moyenne) {
        //"Alerte: Le client a effectué une transaction qui est considérablement plus élevée que ses 5 dernières transactions.";
        echo json_encode("Alerte: Le client a effectué une transaction qui est considérablement plus élevée que ses 5 dernières transactions.");
    } else {
        echo json_encode("Le montant est inférieur à la moyenne, pas d'alerte");
    }



    echo json_encode($data['NameClient']);
}

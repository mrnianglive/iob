<?php
require("db.php");

require("db.php");

$response = ['message' => '']; // Structure de réponse initiale

if (isset($_GET['NumCompte'], $_GET['MontantVersement'])) {
    $NumCompte = $_GET['NumCompte'];
    $montant = (float) $_GET['MontantVersement']; // Cast pour s'assurer que c'est un nombre

    // Calculer la moyenne des 5 derniers versements
    $requete = $baseDeDonnee->prepare(
        "SELECT AVG(MontantVersement) as moyenne 
         FROM TbleOperations 
         WHERE NumCompte=:NumCompte AND Approve2_Id IS NOT NULL AND Reset_Id IS NULL 
         ORDER BY Approve2_Time DESC 
         LIMIT 5"
    );
    $requete->bindValue(':NumCompte', $NumCompte, PDO::PARAM_INT);
    $requete->execute();
    $resultat = $requete->fetch(PDO::FETCH_ASSOC);

    $moyenne = $resultat ? $resultat['moyenne'] : 0; // Vérifier si le résultat est non nul avant d'accéder à la clé

    if ($montant > $moyenne) {
        $response['message'] = "Alerte: Transaction inhabituelle! Versement de $montant, supérieur à la moyenne de $moyenne. Vérifiez avec le déposant.";
    } else {
        $response['message'] = "Le montant est inférieur ou égal à la moyenne, pas d'alerte.";
    }
} else {
    $response['message'] = "Erreur: Les données nécessaires ne sont pas fournies.";
}

// Encoder la réponse en JSON
echo json_encode($response);


// Fermer la connexion à la base de données
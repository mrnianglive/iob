<?php
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

    // Vérifier si une moyenne a été calculée
    if ($resultat && $resultat['moyenne'] !== null) {
        $moyenne = (float) $resultat['moyenne'];
        if ($montant > $moyenne) {
            $response['message'] = "Alerte: Transaction inhabituelle détectée! Le montant est supérieur à la moyenne des transactions précédentes.";
        } else {
            $response['message'] = "Avis : Transaction normale ! Le montant est dans la plage attendue basée sur l'historique.";
        }
    } else {
        // Aucune transaction précédente trouvée - cela pourrait être un nouveau client
        $response['message'] = "Avis : Aucun historique de transactions trouvé. Il se peut que ce soit un nouveau client. Veuillez vérifier le numéro de compte avec le déposant avant de procéder.";
    }
} else {
    $response['message'] = "Erreur: Les données nécessaires pour effectuer l'analyse ne sont pas fournies.";
}

// Encoder la réponse en JSON
echo json_encode($response);
// Fermer la connexion à la base de données, si nécessaire
// $baseDeDonnee = null;
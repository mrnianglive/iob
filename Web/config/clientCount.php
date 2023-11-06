<?php
require("db.php");
if (isset($_POST['NumCompte'])) {
    $Nucompte = $_POST['NumCompte'];
    $query = $baseDeDonnee->prepare("SELECT COUNT(*) as count FROM TbleOperations WHERE MONTH(Approve2_Time) = MONTH(CURRENT_DATE()) AND YEAR(Approve2_Time) = YEAR(CURRENT_DATE()) AND NumCompte = :NumCompte");
    $query->bindValue(':NumCompte', $Nucompte, \PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetch();

    if ($result['count'] > 1) {
        // Utilisez la fonction json_encode pour renvoyer une réponse JSON
        echo json_encode(['message' => 'Vous avez déjà effectué une opération ce mois-ci.']);
    } else {
        echo json_encode(['message' => 'C\'est votre première opération ce mois-ci.']);
    }
}
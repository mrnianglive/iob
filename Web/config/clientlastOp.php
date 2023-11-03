<?php
require("db.php");

$response = [];

if (isset($_GET['NumCompte']) && isset($_GET['MontantVersement'])) {
    $NumCompte = $_GET['NumCompte'];
    $newTransactionAmount = (float) $_GET['MontantVersement'];

    $query = $baseDeDonnee->prepare("SELECT MontantVersement FROM TbleOperations WHERE NumCompte=:NumCompte AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL ORDER BY DateTransaction DESC LIMIT 5");
    $query->bindValue(':NumCompte', $NumCompte, PDO::PARAM_INT);
    $query->execute();

    $recentTransactions = $query->fetchAll(PDO::FETCH_COLUMN, 0);
    $averageRecentTransaction = array_sum($recentTransactions) / count($recentTransactions);

    // Si le nouveau montant est, disons, 3 fois supérieur à la moyenne des transactions récentes, on génère une alerte
    if ($newTransactionAmount > $averageRecentTransaction * 3) {
        $response['message'] = "Alerte: Le client a effectué une transaction qui est considérablement plus élevée que ses 5 dernières transactions.";
    }

    // Récupérer le nom du client
    $query = $baseDeDonnee->prepare("SELECT NameClient FROM TbleOperations WHERE NumCompte=:NumCompte LIMIT 1");
    $query->bindValue(':NumCompte', $NumCompte, PDO::PARAM_INT);
    $query->execute();

    $data = $query->fetch();
    $response['nameClient'] = $data['NameClient'];

    echo json_encode($response);
}

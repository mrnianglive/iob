<?php
require("db.php");

if (isset($_GET['NumCompte'])) {
    $query = $baseDeDonnee->prepare("SELECT * FROM TbleOperations WHERE NumCompte=:NumCompte AND TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL ORDER BY DateOperation DESC LIMIT 5");
    $query->bindValue(':NumCompte', $_GET['NumCompte'], PDO::PARAM_INT);
    $query->execute();
    $operations = $query->fetchAll();

    $message = "";
    if (count($operations) > 0) {
        $total = 0;
        foreach ($operations as $operation) {
            $total += $operation['Montant'];
        }
        $moyenne = $total / count($operations);

        $operationElevée = false;
        foreach ($operations as $operation) {
            if ($operation['Montant'] > 2 * $moyenne) {
                $operationElevée = true;
                break;
            }
        }

        if ($operationElevée) {
            $message = "Alerte : Changement soudain du comportement de transaction. Ce client, qui avait généralement des transactions de faible valeur, a effectué une transaction de grande valeur récemment. Veuillez vérifier les détails de la transaction et contacter le client pour confirmer.";
        }
    }

    echo json_encode(['message' => $message]);
}

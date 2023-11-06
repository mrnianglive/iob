<?php
require("db.php");
if (isset($_GET['NumCompte'])) {
    $Nucompte = $_GET['NumCompte'];
    $query = $baseDeDonnee->prepare("SELECT COUNT(*) as count FROM TbleOperations WHERE MONTH(Approve2_Time) = MONTH(CURRENT_DATE()) AND YEAR(Approve2_Time) = YEAR(CURRENT_DATE()) AND NumCompte = :NumCompte");
    $query->bindValue(':NumCompte', $Nucompte, \PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetch();

    // Utilisez la fonction json_encode pour renvoyer une réponse JSON
    echo json_encode(['count' => $result['count']]);
}
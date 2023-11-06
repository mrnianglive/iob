<?php
require("db.php");
if (isset($_POST['NumCompte'])) {
    $Nucompte = $_POST['NumCompte'];
    $query = $baseDeDonnee->prepare("SELECT COUNT(*) as count FROM TbleOperations WHERE MONTH(Approve2_Time) = MONTH(CURRENT_DATE()) AND YEAR(Approve2_Time) = YEAR(CURRENT_DATE()) AND NumCompte = :NumCompte");
    $query->bindValue(':NumCompte', $Nucompte, \PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetch();
    if ($result['count'] > 1) {
        return json_encode(['message' => 'Vous avez déjà effectué une opération ce mois-ci.']);
    }
    return  json_encode(['message' => 'C\'est votre première opération ce mois-ci.']);
}
<?php
/**
 * Endpoint AJAX pour récupérer les produits accessibles par une agence
 * via la table TbleChmodProduit
 */

require("db.php");

if (isset($_GET['RefAgency']) && !empty($_GET['RefAgency'])) {
    $refAgency = intval($_GET['RefAgency']);
    
    // Récupérer les produits auxquels les caisses de cette agence ont accès
    $sql = "SELECT DISTINCT p.RefProduit, p.NameProduit
            FROM TbleProduit p
            INNER JOIN TbleChmodProduit cp ON cp.RefProduit = p.RefProduit
            INNER JOIN TbleCaisse c ON c.RefCaisse = cp.RefCaisse
            WHERE c.RefAgency = :RefAgency
            ORDER BY p.NameProduit";
    
    $requete = $baseDeDonnee->prepare($sql);
    $requete->bindValue(':RefAgency', $refAgency, PDO::PARAM_INT);
    $requete->execute();
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
    
    $tableau = array();
    foreach ($resultat as $value) {
        $tableau[$value['RefProduit']] = $value['NameProduit'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($tableau);
} else {
    header('Content-Type: application/json');
    echo json_encode(array());
}
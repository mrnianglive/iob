<?php
/**
 * Script CRON de fermeture automatique des caisses
 * A executer chaque jour a minuit: 0 0 * * * /usr/bin/php /chemin/vers/Web/config/caissecron.php
 * 
 * Ce script :
 * 1. Recupere toutes les caisses non fermees pour la journee
 * 2. Calcule automatiquement le solde de chaque caisse
 * 3. Insere la fermeture dans TbleSolde avec AutoClose=1
 * 4. Ferme aussi les reserves d'agences dans TbleCompte
 * 5. Log toutes les operations
 */

require_once("db.php");

// Date du jour a fermer (la veille si execution a minuit)
$dateToClose = date('Y-m-d');

// Log
$logFile = __DIR__ . '/../../logs/cron_' . date('Y-m-d') . '.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    echo "[$timestamp] $message\n";
}

logMessage("=== Debut CRON fermeture automatique ===");

try {
    // 1. Recuperer toutes les caisses
    $stmtCaisses = $baseDeDonnee->query("SELECT RefCaisse, NameCaisse FROM TbleCaisse");
    $caisses = $stmtCaisses->fetchAll(PDO::FETCH_ASSOC);
    
    logMessage("Nombre de caisses a verifier: " . count($caisses));
    
    $caissesFermees = 0;
    $caissesDejaFermees = 0;
    
    foreach ($caisses as $caisse) {
        $refCaisse = $caisse['RefCaisse'];
        $nameCaisse = $caisse['NameCaisse'];
        
        // 2. Verifier si deja fermee aujourd'hui
        $checkStmt = $baseDeDonnee->prepare("SELECT RefSolde FROM TbleSolde 
            WHERE RefCaisse = :caisse AND DATE(DateSolde) = :dateClose");
        $checkStmt->execute([':caisse' => $refCaisse, ':dateClose' => $dateToClose]);
        
        if ($checkStmt->rowCount() > 0) {
            $caissesDejaFermees++;
            continue; // Deja fermee
        }
        
        // 3. Calculer le solde de la caisse
        $solde = calculerSoldeCaisse($baseDeDonnee, $refCaisse, $dateToClose);
        
        // 4. Inserer la fermeture automatique
        $insertStmt = $baseDeDonnee->prepare("INSERT INTO TbleSolde 
            (RefCaisse, Solde, DateSolde, RefUsers, AutoClose) 
            VALUES (:caisse, :solde, :dateClose, 0, 1)");
        $insertStmt->execute([
            ':caisse' => $refCaisse,
            ':solde' => $solde,
            ':dateClose' => $dateToClose . ' 23:59:59'
        ]);
        
        $caissesFermees++;
        logMessage("Caisse '$nameCaisse' (ID: $refCaisse) fermee automatiquement. Solde: " . number_format($solde, 0, ',', ' ') . " FCFA");
    }
    
    logMessage("Caisses fermees automatiquement: $caissesFermees");
    logMessage("Caisses deja fermees: $caissesDejaFermees");
    
    // 5. Fermer les reserves d'agences non fermees
    $stmtAgences = $baseDeDonnee->query("SELECT RefAgency, NameAgency FROM TbleAgency");
    $agences = $stmtAgences->fetchAll(PDO::FETCH_ASSOC);
    
    $agencesFermees = 0;
    
    foreach ($agences as $agence) {
        $refAgency = $agence['RefAgency'];
        $nameAgency = $agence['NameAgency'];
        
        // Verifier si deja fermee
        $checkAgence = $baseDeDonnee->prepare("SELECT RefCompte FROM TbleCompte 
            WHERE RefAgency = :agency AND DATE(DateSolde) = :dateClose");
        $checkAgence->execute([':agency' => $refAgency, ':dateClose' => $dateToClose]);
        
        if ($checkAgence->rowCount() > 0) {
            continue;
        }
        
        // Calculer le solde reserve
        $soldeReserve = calculerSoldeReserve($baseDeDonnee, $refAgency, $dateToClose);
        
        // Inserer
        $insertReserve = $baseDeDonnee->prepare("INSERT INTO TbleCompte 
            (RefAgency, SoldeCompte, DateSolde, RefUsers, AutoClose) 
            VALUES (:agency, :solde, :dateClose, 0, 1)");
        $insertReserve->execute([
            ':agency' => $refAgency,
            ':solde' => $soldeReserve,
            ':dateClose' => $dateToClose . ' 23:59:59'
        ]);
        
        $agencesFermees++;
        logMessage("Reserve agence '$nameAgency' (ID: $refAgency) fermee. Solde: " . number_format($soldeReserve, 0, ',', ' ') . " FCFA");
    }
    
    logMessage("Reserves agences fermees: $agencesFermees");
    
} catch (Exception $e) {
    logMessage("ERREUR: " . $e->getMessage());
}

logMessage("=== Fin CRON fermeture automatique ===");

/**
 * Calcule le solde d'une caisse pour une date donnee
 */
function calculerSoldeCaisse($db, $refCaisse, $date) {
    // Solde initial (solde de la veille)
    $stmtSoldeVeille = $db->prepare("SELECT Solde FROM TbleSolde 
        WHERE RefCaisse = :caisse AND DATE(DateSolde) < :date 
        ORDER BY DateSolde DESC LIMIT 1");
    $stmtSoldeVeille->execute([':caisse' => $refCaisse, ':date' => $date]);
    $soldeVeille = $stmtSoldeVeille->fetch(PDO::FETCH_ASSOC);
    $soldeInitial = $soldeVeille ? floatval($soldeVeille['Solde']) : 0;
    
    // Versements du jour (RefType=1)
    $stmtVersements = $db->prepare("SELECT COALESCE(SUM(MontantVersement), 0) AS total 
        FROM TbleOperations 
        WHERE RefCaisse = :caisse AND RefType = 1 
        AND DATE(Approve2_Time) = :date 
        AND Approve2_Id IS NOT NULL AND Reset_Id IS NULL");
    $stmtVersements->execute([':caisse' => $refCaisse, ':date' => $date]);
    $versements = floatval($stmtVersements->fetch(PDO::FETCH_ASSOC)['total']);
    
    // Retraits du jour (RefType=2)
    $stmtRetraits = $db->prepare("SELECT COALESCE(SUM(MontantVersement), 0) AS total 
        FROM TbleOperations 
        WHERE RefCaisse = :caisse AND RefType = 2 
        AND DATE(Approve2_Time) = :date 
        AND Approve2_Id IS NOT NULL AND Reset_Id IS NULL");
    $stmtRetraits->execute([':caisse' => $refCaisse, ':date' => $date]);
    $retraits = floatval($stmtRetraits->fetch(PDO::FETCH_ASSOC)['total']);
    
    // Sorties de fonds (RefType=4)
    $stmtSorties = $db->prepare("SELECT COALESCE(SUM(MontantVersement), 0) AS total 
        FROM TbleOperations 
        WHERE RefCaisse = :caisse AND RefType = 4 
        AND DATE(Approve2_Time) = :date 
        AND Approve2_Id IS NOT NULL AND Reset_Id IS NULL");
    $stmtSorties->execute([':caisse' => $refCaisse, ':date' => $date]);
    $sorties = floatval($stmtSorties->fetch(PDO::FETCH_ASSOC)['total']);
    
    // Appro caisse (RefType=3)
    $stmtAppro = $db->prepare("SELECT COALESCE(SUM(MontantVersement), 0) AS total 
        FROM TbleOperations 
        WHERE RefCaisse = :caisse AND RefType = 3 
        AND DATE(Approve2_Time) = :date 
        AND Approve2_Id IS NOT NULL AND Reset_Id IS NULL");
    $stmtAppro->execute([':caisse' => $refCaisse, ':date' => $date]);
    $appro = floatval($stmtAppro->fetch(PDO::FETCH_ASSOC)['total']);
    
    // Remittance versement
    $stmtRemitV = $db->prepare("SELECT COALESCE(SUM(MontantTransaction), 0) AS total 
        FROM TbleRemittance 
        WHERE RefCaisse = :caisse AND RefType = 1 
        AND DATE(Insert_time) = :date AND Reset_Id IS NULL");
    $stmtRemitV->execute([':caisse' => $refCaisse, ':date' => $date]);
    $remitVersement = floatval($stmtRemitV->fetch(PDO::FETCH_ASSOC)['total']);
    
    // Remittance retrait
    $stmtRemitR = $db->prepare("SELECT COALESCE(SUM(MontantTransaction), 0) AS total 
        FROM TbleRemittance 
        WHERE RefCaisse = :caisse AND RefType = 2 
        AND DATE(Insert_time) = :date AND Reset_Id IS NULL");
    $stmtRemitR->execute([':caisse' => $refCaisse, ':date' => $date]);
    $remitRetrait = floatval($stmtRemitR->fetch(PDO::FETCH_ASSOC)['total']);
    
    // Calcul final
    $solde = $soldeInitial + $appro + $versements - $retraits - $sorties + ($remitVersement - $remitRetrait);
    
    return $solde;
}

/**
 * Calcule le solde de la reserve d'une agence pour une date donnee
 */
function calculerSoldeReserve($db, $refAgency, $date) {
    // Solde veille
    $stmtVeille = $db->prepare("SELECT SoldeCompte FROM TbleCompte 
        WHERE RefAgency = :agency AND DATE(DateSolde) < :date 
        ORDER BY DateSolde DESC LIMIT 1");
    $stmtVeille->execute([':agency' => $refAgency, ':date' => $date]);
    $veille = $stmtVeille->fetch(PDO::FETCH_ASSOC);
    $soldeVeille = $veille ? floatval($veille['SoldeCompte']) : 0;
    
    // Depots agence du jour
    $stmtDepots = $db->prepare("SELECT COALESCE(SUM(o.MontantVersement), 0) AS total 
        FROM TbleOperations o
        INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
        WHERE c.RefAgency = :agency AND o.RefType = 1 
        AND DATE(o.Approve2_Time) = :date 
        AND o.Approve2_Id IS NOT NULL AND o.Reset_Id IS NULL");
    $stmtDepots->execute([':agency' => $refAgency, ':date' => $date]);
    $depots = floatval($stmtDepots->fetch(PDO::FETCH_ASSOC)['total']);
    
    // Retraits agence du jour
    $stmtRetraits = $db->prepare("SELECT COALESCE(SUM(o.MontantVersement), 0) AS total 
        FROM TbleOperations o
        INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
        WHERE c.RefAgency = :agency AND o.RefType = 2 
        AND DATE(o.Approve2_Time) = :date 
        AND o.Approve2_Id IS NOT NULL AND o.Reset_Id IS NULL");
    $stmtRetraits->execute([':agency' => $refAgency, ':date' => $date]);
    $retraits = floatval($stmtRetraits->fetch(PDO::FETCH_ASSOC)['total']);
    
    // Appro sans appro initial
    $stmtAppro = $db->prepare("SELECT COALESCE(SUM(o.MontantVersement), 0) AS total 
        FROM TbleOperations o
        INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
        WHERE c.RefAgency = :agency AND o.RefType = 3 AND o.TypeAppro != 1
        AND DATE(o.Approve2_Time) = :date 
        AND o.Approve2_Id IS NOT NULL AND o.Reset_Id IS NULL");
    $stmtAppro->execute([':agency' => $refAgency, ':date' => $date]);
    $appro = floatval($stmtAppro->fetch(PDO::FETCH_ASSOC)['total']);
    
    // Sortie agence
    $stmtSortie = $db->prepare("SELECT COALESCE(SUM(o.MontantVersement), 0) AS total 
        FROM TbleOperations o
        INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
        WHERE c.RefAgency = :agency AND o.RefType = 4 
        AND DATE(o.Approve2_Time) = :date 
        AND o.Approve2_Id IS NOT NULL AND o.Reset_Id IS NULL");
    $stmtSortie->execute([':agency' => $refAgency, ':date' => $date]);
    $sortie = floatval($stmtSortie->fetch(PDO::FETCH_ASSOC)['total']);
    
    // Remittance
    $stmtRemitV = $db->prepare("SELECT COALESCE(SUM(r.MontantTransaction), 0) AS total 
        FROM TbleRemittance r
        INNER JOIN TbleCaisse c ON c.RefCaisse = r.RefCaisse
        WHERE c.RefAgency = :agency AND r.RefType = 1 
        AND DATE(r.Insert_time) = :date AND r.Reset_Id IS NULL");
    $stmtRemitV->execute([':agency' => $refAgency, ':date' => $date]);
    $remitV = floatval($stmtRemitV->fetch(PDO::FETCH_ASSOC)['total']);
    
    $stmtRemitR = $db->prepare("SELECT COALESCE(SUM(r.MontantTransaction), 0) AS total 
        FROM TbleRemittance r
        INNER JOIN TbleCaisse c ON c.RefCaisse = r.RefCaisse
        WHERE c.RefAgency = :agency AND r.RefType = 2 
        AND DATE(r.Insert_time) = :date AND r.Reset_Id IS NULL");
    $stmtRemitR->execute([':agency' => $refAgency, ':date' => $date]);
    $remitR = floatval($stmtRemitR->fetch(PDO::FETCH_ASSOC)['total']);
    
    $solde = $soldeVeille + $depots - $retraits + $appro - $sortie + ($remitV - $remitR);
    
    return $solde;
}
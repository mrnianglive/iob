<?php
/**
 * Script de peuplement initial de TbleClients
 * A executer une seule fois apres la migration SQL
 * 
 * Usage: php scripts/populate_clients.php
 */

// Configuration - Support Docker ou local
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'iob';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';

echo "=================================================\n";
echo "  IOB - Peuplement Initial TbleClients + Stats   \n";
echo "=================================================\n\n";

try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "[OK] Connexion base de donnees reussie\n\n";
} catch (PDOException $e) {
    die("[ERREUR] Connexion impossible: " . $e->getMessage() . "\n");
}

// Verifier que les tables existent
$tables = ['TbleClients', 'TbleClientStats', 'TbleSeuilsLCB'];
foreach ($tables as $table) {
    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
    if ($stmt->rowCount() == 0) {
        die("[ERREUR] Table $table n'existe pas. Executez d'abord migrations/003_lcb_crm.sql\n");
    }
}
echo "[OK] Tables CRM/LCB existent\n\n";

// Recuperer les seuils
$stmt = $pdo->query("SELECT CodeSeuil, Valeur FROM TbleSeuilsLCB WHERE Actif = 1");
$seuils = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$seuilVipVolume = $seuils['CRM_SEUIL_VIP_VOLUME'] ?? 20000000;
$seuilVipNbOps = $seuils['CRM_SEUIL_VIP_NB_OPS'] ?? 50;
$joursDormant = $seuils['CRM_JOURS_DORMANT'] ?? 30;
$joursPerdu = $seuils['CRM_JOURS_PERDU'] ?? 90;

echo "Seuils charges:\n";
echo "  - VIP Volume: " . number_format($seuilVipVolume, 0, ',', ' ') . " F\n";
echo "  - VIP Nb Ops: $seuilVipNbOps ops/mois\n";
echo "  - Dormant: $joursDormant jours\n";
echo "  - Perdu: $joursPerdu jours\n\n";

// =================================================================
// ETAPE 1: Peupler TbleClients depuis TbleOperations
// =================================================================
echo "ETAPE 1: Peuplement de TbleClients...\n";

$sql = "
INSERT INTO TbleClients 
    (NumCompte, NomClient, RefAgencyPrincipale, DatePremiereOp, DateDerniereOp, 
     NbTotalOperations, VolumeTotalDepot, VolumeTotalRetrait, MontantMoyenOperation, Segment)
SELECT 
    o.NumCompte,
    MAX(o.NameClient) AS NomClient,
    (SELECT c2.RefAgency FROM TbleOperations o2 
     INNER JOIN TbleCaisse c2 ON c2.RefCaisse = o2.RefCaisse 
     WHERE o2.NumCompte = o.NumCompte AND o2.Approve2_Id IS NOT NULL 
     GROUP BY c2.RefAgency ORDER BY COUNT(*) DESC LIMIT 1) AS RefAgencyPrincipale,
    MIN(DATE(o.Approve2_Time)) AS DatePremiereOp,
    MAX(DATE(o.Approve2_Time)) AS DateDerniereOp,
    COUNT(*) AS NbTotalOperations,
    SUM(CASE WHEN o.RefType = 1 THEN o.MontantVersement ELSE 0 END) AS VolumeTotalDepot,
    SUM(CASE WHEN o.RefType = 2 THEN o.MontantVersement ELSE 0 END) AS VolumeTotalRetrait,
    AVG(o.MontantVersement) AS MontantMoyenOperation,
    'NOUVEAU' AS Segment
FROM TbleOperations o
WHERE o.NumCompte IS NOT NULL 
  AND o.NumCompte != ''
  AND o.Approve2_Id IS NOT NULL 
  AND o.Reset_Id IS NULL
  AND o.RefType IN (1, 2)
GROUP BY o.NumCompte
ON DUPLICATE KEY UPDATE
    NomClient = COALESCE(VALUES(NomClient), NomClient),
    DateDerniereOp = VALUES(DateDerniereOp),
    NbTotalOperations = VALUES(NbTotalOperations),
    VolumeTotalDepot = VALUES(VolumeTotalDepot),
    VolumeTotalRetrait = VALUES(VolumeTotalRetrait),
    MontantMoyenOperation = VALUES(MontantMoyenOperation)
";

$pdo->exec($sql);
$countClients = $pdo->query("SELECT COUNT(*) FROM TbleClients")->fetchColumn();
echo "[OK] $countClients clients crees/mis a jour\n\n";

// =================================================================
// ETAPE 2: Peupler TbleClientStats (stats mensuelles)
// =================================================================
echo "ETAPE 2: Peuplement de TbleClientStats...\n";

$sql = "
INSERT INTO TbleClientStats 
    (NumCompte, AnneeMois, RefAgency, NbOperations, NbDepots, NbRetraits, 
     VolumeDepot, VolumeRetrait, VolumeNet, MaxOperation)
SELECT 
    o.NumCompte,
    DATE_FORMAT(o.Approve2_Time, '%Y-%m') AS AnneeMois,
    c.RefAgency,
    COUNT(*) AS NbOperations,
    SUM(CASE WHEN o.RefType = 1 THEN 1 ELSE 0 END) AS NbDepots,
    SUM(CASE WHEN o.RefType = 2 THEN 1 ELSE 0 END) AS NbRetraits,
    SUM(CASE WHEN o.RefType = 1 THEN o.MontantVersement ELSE 0 END) AS VolumeDepot,
    SUM(CASE WHEN o.RefType = 2 THEN o.MontantVersement ELSE 0 END) AS VolumeRetrait,
    SUM(CASE WHEN o.RefType = 1 THEN o.MontantVersement ELSE -o.MontantVersement END) AS VolumeNet,
    MAX(o.MontantVersement) AS MaxOperation
FROM TbleOperations o
INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
WHERE o.NumCompte IS NOT NULL 
  AND o.NumCompte != ''
  AND o.Approve2_Id IS NOT NULL 
  AND o.Reset_Id IS NULL
  AND o.RefType IN (1, 2)
GROUP BY o.NumCompte, DATE_FORMAT(o.Approve2_Time, '%Y-%m'), c.RefAgency
ON DUPLICATE KEY UPDATE
    NbOperations = VALUES(NbOperations),
    NbDepots = VALUES(NbDepots),
    NbRetraits = VALUES(NbRetraits),
    VolumeDepot = VALUES(VolumeDepot),
    VolumeRetrait = VALUES(VolumeRetrait),
    VolumeNet = VALUES(VolumeNet),
    MaxOperation = VALUES(MaxOperation)
";

$pdo->exec($sql);
$countStats = $pdo->query("SELECT COUNT(*) FROM TbleClientStats")->fetchColumn();
echo "[OK] $countStats enregistrements stats mensuels crees\n\n";

// =================================================================
// ETAPE 3: Recalculer les segments
// =================================================================
echo "ETAPE 3: Recalcul des segments clients...\n";

$clients = $pdo->query("SELECT NumCompte, DateDerniereOp, NbTotalOperations FROM TbleClients")->fetchAll(PDO::FETCH_ASSOC);
$segmentCounts = ['VIP' => 0, 'REGULIER' => 0, 'OCCASIONNEL' => 0, 'DORMANT' => 0, 'PERDU' => 0, 'NOUVEAU' => 0];

$updateStmt = $pdo->prepare("UPDATE TbleClients SET Segment = :segment WHERE NumCompte = :numCompte");
$moisActuel = date('Y-m');

// Recuperer les stats du mois pour tous les clients
$statsStmt = $pdo->prepare("
    SELECT COALESCE(SUM(VolumeDepot + VolumeRetrait), 0) AS VolumeMois, COALESCE(SUM(NbOperations), 0) AS NbOpsMois
    FROM TbleClientStats WHERE NumCompte = :numCompte AND AnneeMois = :mois
");

foreach ($clients as $client) {
    $numCompte = $client['NumCompte'];
    
    // Stats du mois
    $statsStmt->execute([':numCompte' => $numCompte, ':mois' => $moisActuel]);
    $statsMois = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    $joursInactif = $client['DateDerniereOp'] 
        ? (strtotime('now') - strtotime($client['DateDerniereOp'])) / 86400 
        : 999;
    
    // Determiner le segment
    if ($joursInactif >= $joursPerdu) {
        $segment = 'PERDU';
    } elseif ($joursInactif >= $joursDormant) {
        $segment = 'DORMANT';
    } elseif ($statsMois['VolumeMois'] >= $seuilVipVolume || $statsMois['NbOpsMois'] >= $seuilVipNbOps) {
        $segment = 'VIP';
    } elseif ($statsMois['NbOpsMois'] >= 4) {
        $segment = 'REGULIER';
    } elseif ($client['NbTotalOperations'] <= 2 && $joursInactif <= 30) {
        $segment = 'NOUVEAU';
    } else {
        $segment = 'OCCASIONNEL';
    }
    
    $updateStmt->execute([':segment' => $segment, ':numCompte' => $numCompte]);
    $segmentCounts[$segment]++;
}

echo "[OK] Segments recalcules:\n";
foreach ($segmentCounts as $seg => $count) {
    echo "     - $seg: $count clients\n";
}

// =================================================================
// ETAPE 4: Mise a jour des telephones depuis TbleOperations
// =================================================================
echo "\nETAPE 4: Mise a jour des telephones clients...\n";

$sql = "
UPDATE TbleClients c
SET TelClient = (
    SELECT o.TelDeposant FROM TbleOperations o 
    WHERE o.NumCompte = c.NumCompte 
      AND o.TelDeposant IS NOT NULL 
      AND o.TelDeposant != ''
    ORDER BY o.Approve2_Time DESC 
    LIMIT 1
)
WHERE c.TelClient IS NULL
";
$affected = $pdo->exec($sql);
echo "[OK] $affected telephones mis a jour\n";

// =================================================================
// RESUME
// =================================================================
echo "\n=================================================\n";
echo "  RESUME\n";
echo "=================================================\n";
echo "  Clients crees:        $countClients\n";
echo "  Stats mensuelles:     $countStats\n";
echo "  Segments:\n";
foreach ($segmentCounts as $seg => $count) {
    $pct = $countClients > 0 ? round($count / $countClients * 100, 1) : 0;
    echo "    - $seg: $count ($pct%)\n";
}
echo "\n[TERMINE] Peuplement initial effectue avec succes!\n";
echo "Vous pouvez maintenant acceder au module CRM: /crm/index\n";


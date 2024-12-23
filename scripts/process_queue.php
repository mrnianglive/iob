<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Inclure le fichier d'autoload de Composer

// Configuration du logging
$logFile = __DIR__ . '/../logs/queue.log';
if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}

function writeLog($message) {
    global $logFile;
    $date = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$date] $message\n", FILE_APPEND);
}

// Créer le répertoire tmp s'il n'existe pas
$tmpDir = __DIR__ . '/../tmp';
if (!is_dir($tmpDir)) {
    mkdir($tmpDir, 0755, true);
}

// Acquérir un verrou
$lockFile = $tmpDir . '/process_queue.lock';
if (file_exists($lockFile) && (time() - filemtime($lockFile)) < 300) { // 5 minutes
    writeLog("Un autre processus est en cours d'exécution");
    exit;
}
touch($lockFile);

try {
    writeLog("Début du traitement des opérations en attente");
    
    // Vérifier que la classe existe
    if (!class_exists('\Library\Models\JournalManagerPDO')) {
        throw new Exception("La classe JournalManagerPDO n'est pas chargée correctement");
    }
    
    $manager = new \Library\Models\JournalManagerPDO($dao);
    $count = $manager->processQueuedOperations();
    
    writeLog("Fin du traitement - $count opérations traitées");
} catch (Exception $e) {
    writeLog("ERREUR: " . $e->getMessage());
} finally {
    unlink($lockFile);
}
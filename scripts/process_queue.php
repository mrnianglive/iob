<?php
require_once __DIR__ . '/../bootstrap.php';

// Définir le fichier de verrouillage
$lockFile = '/tmp/process_queue.lock';

// Tenter d'acquérir le verrou
$lock = fopen($lockFile, 'w');
if (!$lock) {
    exit("Impossible d'acquérir le verrou de fichier\n");
}

if (!flock($lock, LOCK_EX | LOCK_NB)) {
    fclose($lock);
    exit("Un autre processus est en cours d'exécution\n");
}

// Le verrou est acquis, créer le fichier de verrouillage avec un timestamp
touch($lockFile);

try {
    // Ajouter un logging d'initialisation pour savoir quand le processus démarre
    error_log('Process started at: ' . date('Y-m-d H:i:s'), 3, '/tmp/process_queue.log');
    
    // Traiter les opérations en file d'attente
    $manager = new \Library\Models\JournalManagerPDO($dao);
    $manager->processQueuedOperations();

    // Ajouter un logging de fin de traitement
    error_log('Process completed at: ' . date('Y-m-d H:i:s'), 3, '/tmp/process_queue.log');
} catch (\Exception $e) {
    // Loguer les erreurs si une exception est levée
    error_log('Error: ' . $e->getMessage(), 3, '/tmp/process_queue_error.log');
} finally {
    // Toujours libérer le verrou à la fin
    flock($lock, LOCK_UN);
    fclose($lock);
    unlink($lockFile);  // Supprimer le fichier de verrouillage
}
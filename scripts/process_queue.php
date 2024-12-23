<?php
require_once __DIR__ . '/../Library/DBFactory.class.php';

// Autoloader
spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    require_once __DIR__ . '/../' . $class . '.class.php';
});

$dao = \Library\DBFactory::MySQLPDO();
$manager = new \Library\Models\JournalManagerPDO($dao);

try {
    $manager->processQueuedOperations();
    echo "Traitement de la file d'attente terminé.";
} catch (Exception $e) {
    echo "Erreur lors du traitement de la file d'attente: " . $e->getMessage();
}
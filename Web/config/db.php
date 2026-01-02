<?php
ob_start();
try {
    // Support Docker environment variables ou valeurs par défaut
    $host = getenv('DB_HOST') ?: 'localhost';
    $dbname = getenv('DB_NAME') ?: 'iob';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';

    $baseDeDonnee = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $baseDeDonnee->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $baseDeDonnee->exec('SET NAMES utf8');
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}
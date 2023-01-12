<?php

// Inclusion de la bibliothèque QR code
require_once 'phpqrcode/qrlib.php';

// Récupération du texte à encoder dans le QR code
$text = $_GET['text'];

// Configuration de la taille de l'image QR code
$size = 400;

// Génération de l'image QR code
QRcode::png($text, false, QR_ECLEVEL_L, 80, 2);
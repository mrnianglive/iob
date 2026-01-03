<?php

// Récupération du texte à encoder dans le QR code
$text = isset($_GET['text']) ? urlencode($_GET['text']) : 'default';
$size = isset($_GET['size']) ? intval($_GET['size']) : 150;

// Utiliser l'API externe QR Server (gratuit et fiable)
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$text}";

// Rediriger vers l'image QR
header("Location: {$qrUrl}");
exit;
<!DOCTYPE html>
<html>

<style type="text/css" media="print">
@page {
    size: auto;
    /* auto is the initial value */
    margin: 0mm;
    /* this affects the margin in the printer settings */

}
</style>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titles; ?></title>
    <link href="/bordereau/bootstrap.min.css" rel="stylesheet">
    <link href="/bordereau/animate.css" rel="stylesheet">
    <link href="/bordereau/style2.css" rel="stylesheet">
    <link href="/bordereau/invoice.css" rel="stylesheet">

</head>

<body class="white-bg">

    <style>
    h2,
    h3 {
        margin: 4px;
    }

    td {
        padding: 2px 4px !important;
    }

    hr {
        border-top: 1px dotted black;
    }

    /* S'assurer que le contenu est visible */
    body {
        background: white !important;
    }

    .invoice-wrapper {
        visibility: visible !important;
        opacity: 1 !important;
    }
    </style>

    <?= $content; ?>

    <!-- Mainly scripts -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="/bordereau/bootstrap.min.js"></script>
    <script src="/bordereau/metisMenu/jquery.metisMenu.js"></script>
    <script src="/bordereau/inspinia.js"></script>

    <script type="text/javascript">
    // Attendre que la page soit complètement chargée avant d'imprimer
    window.onload = function() {
        // Attendre un peu que les images (QR code) soient chargées
        setTimeout(function() {
            window.print();
        }, 500);
    };
    </script>

</body>

</html>
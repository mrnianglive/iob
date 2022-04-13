<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="/images/mlc.ico">

    <title>App - <?= $titles; ?></title>
    <link rel="canonical" href="https://getbootstrap.com/docs/4.0/examples/sign-in/">

    <!-- Bootstrap core CSS -->
    <link href="/css/login/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="/css/login/signin.css" rel="stylesheet">
    <link href="/js/sweetalert2/sweetalert2.css" rel="stylesheet" type="text/css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous">

    </script>


</head>

<body class="text-center">

    <?= $content; ?>

</body>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="/js/sweetalert2/sweetalert2.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
</script>
<script src="/js/scripts.js"></script>
<script src="/scripts/loginpage.js"></script>
<?php if (!empty($_SESSION['message']) && $_SESSION['message']['number'] > 0) { ?>
<script>
$(function() {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000
    });

    Toast.fire({
        type: '<?= $_SESSION['message']['type']; ?>',
        title: '<?= $_SESSION['message']['text']; ?>'
    });
});
</script>
<?php $_SESSION['message']['number']--;
} ?>


</html>
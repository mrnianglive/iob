<?php
if (!isset($_SESSION['DoubleAuth']) && isset($_SESSION['secret'])) {
    header('Location: /connexion/doubleauth');
}

?>

<!DOCTYPE html>
<html dir="ltr" lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="keywords"
        content="wrappixel, admin dashboard, html css dashboard, web dashboard, bootstrap 4 admin, bootstrap 4, css3 dashboard, bootstrap 4 dashboard, Ample lite admin bootstrap 4 dashboard, frontend, responsive bootstrap 4 admin template, Ample admin lite dashboard bootstrap 4 dashboard template">
    <meta name="description"
        content="Ample Admin Lite is powerful and clean admin dashboard template, inpired from Bootstrap Framework">
    <meta name="robots" content="noindex,nofollow">
    <title>App - <?= $titles; ?></title>
    <!-- Favicon icon -->
    <link rel="icon" href="/images/mlc.ico">

    <!-- Préchargement des ressources critiques -->
    <link rel="preconnect" href="https://code.jquery.com" crossorigin>
    <link rel="preconnect" href="https://cdn.datatables.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preload" href="/css/style.min.css" as="style">
    <link rel="preload" href="/images/mlc.png" as="image">

    <!-- CSS critique (chargement synchrone) -->
    <link href="/css/style.min.css" rel="stylesheet">

    <!-- CSS non-critique (chargement asynchrone) -->
    <link href="/css/wizard.css" rel="stylesheet" type="text/css" media="print" onload="this.media='all'">
    <link href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css"
        media="print" onload="this.media='all'">
    <link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" rel="stylesheet"
        type="text/css" media="print" onload="this.media='all'">
    <link href="/js/sweetalert2/sweetalert2.css" rel="stylesheet" type="text/css" media="print"
        onload="this.media='all'">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"
        type="text/css">
    <!-- Classes utilitaires Tailwind-like sans le CDN lourd -->
    <style>
    /* Utility classes essentielles (remplace Tailwind CDN) */
    .flex {
        display: flex;
    }

    .items-center {
        align-items: center;
    }

    .justify-center {
        justify-content: center;
    }

    .justify-between {
        justify-content: space-between;
    }

    .gap-2 {
        gap: 0.5rem;
    }

    .gap-3 {
        gap: 0.75rem;
    }

    .gap-4 {
        gap: 1rem;
    }

    .p-2 {
        padding: 0.5rem;
    }

    .p-3 {
        padding: 0.75rem;
    }

    .p-4 {
        padding: 1rem;
    }

    .m-0 {
        margin: 0;
    }

    .mb-2 {
        margin-bottom: 0.5rem;
    }

    .mb-3 {
        margin-bottom: 0.75rem;
    }

    .mb-4 {
        margin-bottom: 1rem;
    }

    .mt-2 {
        margin-top: 0.5rem;
    }

    .mt-3 {
        margin-top: 0.75rem;
    }

    .mt-4 {
        margin-top: 1rem;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .text-sm {
        font-size: 0.875rem;
    }

    .text-lg {
        font-size: 1.125rem;
    }

    .text-xl {
        font-size: 1.25rem;
    }

    .font-bold {
        font-weight: 700;
    }

    .font-semibold {
        font-weight: 600;
    }

    .rounded {
        border-radius: 0.25rem;
    }

    .rounded-lg {
        border-radius: 0.5rem;
    }

    .shadow {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
    }

    .shadow-lg {
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }

    .w-full {
        width: 100%;
    }

    .h-full {
        height: 100%;
    }

    .overflow-hidden {
        overflow: hidden;
    }

    .overflow-auto {
        overflow: auto;
    }

    .grid {
        display: grid;
    }

    .grid-cols-2 {
        grid-template-columns: repeat(2, 1fr);
    }

    .grid-cols-3 {
        grid-template-columns: repeat(3, 1fr);
    }

    .grid-cols-4 {
        grid-template-columns: repeat(4, 1fr);
    }

    .bg-white {
        background-color: #fff;
    }

    .bg-gray-100 {
        background-color: #f7fafc;
    }

    .text-gray-600 {
        color: #718096;
    }

    .text-gray-800 {
        color: #2d3748;
    }

    .border {
        border: 1px solid #e2e8f0;
    }

    .border-b {
        border-bottom: 1px solid #e2e8f0;
    }
    </style>

    <!-- Styles pour sidebar collapsible et formulaires améliorés -->
    <style>
    /* Sidebar Collapse */
    #main-wrapper.mini-sidebar .left-sidebar {
        width: 70px;
    }

    #main-wrapper.mini-sidebar .left-sidebar .sidebar-nav .sidebar-item .sidebar-link {
        padding: 12px 15px;
    }

    #main-wrapper.mini-sidebar .left-sidebar .sidebar-nav .sidebar-item .sidebar-link .hide-menu,
    #main-wrapper.mini-sidebar .left-sidebar .sidebar-nav .sidebar-item .sidebar-link .badge {
        display: none;
    }

    #main-wrapper.mini-sidebar .left-sidebar .sidebar-nav .sidebar-item .sidebar-link i {
        font-size: 1.3em;
    }

    #main-wrapper.mini-sidebar .page-wrapper {
        margin-left: 70px;
    }

    #main-wrapper.mini-sidebar .navbar-header {
        width: 70px;
    }

    #main-wrapper.mini-sidebar .navbar-header .logo-text {
        display: none;
    }

    /* Toggle Button */
    .sidebar-toggle-btn {
        position: fixed;
        left: 240px;
        top: 70px;
        z-index: 1001;
        width: 30px;
        height: 30px;
        background: #1e88e5;
        border: none;
        border-radius: 50%;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }

    .sidebar-toggle-btn:hover {
        background: #1565c0;
        transform: scale(1.1);
    }

    #main-wrapper.mini-sidebar .sidebar-toggle-btn {
        left: 55px;
    }

    .sidebar-toggle-btn i {
        transition: transform 0.3s ease;
    }

    #main-wrapper.mini-sidebar .sidebar-toggle-btn i {
        transform: rotate(180deg);
    }

    /* Transition smooth */
    .left-sidebar,
    .page-wrapper,
    .navbar-header,
    .sidebar-toggle-btn {
        transition: all 0.3s ease;
    }

    /* Forms améliorés */
    .form-control,
    select.form-control {
        color: #212529 !important;
        font-weight: 500 !important;
        background-color: #fff !important;
    }

    select.form-control option {
        color: #212529;
        background: #fff;
        padding: 10px;
    }

    /* Pour mobile, cacher le toggle */
    @media (max-width: 767px) {
        .sidebar-toggle-btn {
            display: none;
        }
    }
    </style>

</head>

<body>
    <!-- Preloader optimisé - disparaît rapidement -->
    <div class="preloader" id="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    <script>
    // Masquer le preloader dès que le DOM est prêt (pas besoin d'attendre toutes les ressources)
    document.addEventListener('DOMContentLoaded', function() {
        var preloader = document.getElementById('preloader');
        if (preloader) {
            preloader.style.opacity = '0';
            setTimeout(function() {
                preloader.style.display = 'none';
            }, 200);
        }
    });
    </script>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <header class="topbar" data-navbarbg="skin5">
            <nav class="navbar top-navbar navbar-expand-md navbar-dark">
                <div class="navbar-header" data-logobg="skin6">
                    <!-- ============================================================== -->
                    <!-- Logo -->
                    <!-- ============================================================== -->
                    <a class="navbar-brand" href="/">
                        <!-- Logo icon -->

                        <!--End Logo icon -->
                        <!-- Logo text -->
                        <span class="logo-text">
                            <!-- dark Logo text -->
                            <img src="/images/mlc.png" alt="homepage" width="50%" />
                        </span>
                    </a>
                    <!-- ============================================================== -->
                    <!-- End Logo -->
                    <!-- ============================================================== -->
                    <!-- ============================================================== -->
                    <!-- toggle and nav items -->
                    <!-- ============================================================== -->
                    <a class="nav-toggler waves-effect waves-light text-dark d-block d-md-none"
                        href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">
                    <ul class="navbar-nav d-none d-md-block d-lg-none">
                        <li class="nav-item">
                            <a class="nav-toggler nav-link waves-effect waves-light text-white"
                                href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
                        </li>
                    </ul>
                    <!-- ============================================================== -->
                    <!-- Right side toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav ml-auto d-flex align-items-center">

                        <!-- ============================================================== -->
                        <!-- Search -->
                        <!-- ============================================================== -->
                        <li class=" in">
                            <form method="POST" action="/bordereau/" class="app-search d-none d-md-block mr-3"
                                target="_blank">
                                <?= \Library\CSRF::getInput(); ?>
                                <input type="text" placeholder="Bordereau N°..." class="form-control mt-0" name="id">
                                <a href="#" class="active">
                                    <i class="fa fa-search"></i>
                                </a>
                            </form>
                        </li>
                        <!-- ============================================================== -->
                        <!-- User profile and search -->
                        <!-- ============================================================== -->
                        <li>
                            <a class="profile-pic" href="#">
                                <img src="/images/mlc.png" alt="user-img" width="36" class="img-circle"><span
                                    class="text-white font-medium"><?= $_SESSION['login']; ?></span></a>
                        </li>
                        <!-- ============================================================== -->
                        <!-- User profile and search -->
                        <!-- ============================================================== -->
                    </ul>
                </div>
            </nav>
        </header>
        <!-- Toggle Sidebar Button -->
        <button class="sidebar-toggle-btn" id="sidebarToggle" title="Réduire/Agrandir le menu">
            <i class="fas fa-chevron-left"></i>
        </button>

        <aside class="left-sidebar" data-sidebarbg="skin6">
            <!-- Sidebar scroll-->
            <div class="scroll-sidebar">
                <!-- Sidebar navigation-->
                <nav class="sidebar-nav">
                    <ul id="sidebarnav">
                        <!-- User Profile-->
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="/"
                                aria-expanded="false"><i class="fas fa-home fa-fw" aria-hidden="true"></i><span
                                    class="hide-menu">Accueil</span></a></li>
                        <?php if ($_SESSION['statut'] == 'admin' || $_SESSION['statut'] == 'superadmin' || (!empty($CheckOuverture) && $_SESSION['statut'] != 'Niveau1' && $_SESSION['statut'] != 'Control' && $_SESSION['statut'] != 'Head')) { ?>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="/bielletage/1" aria-expanded="false"><i class="fa fa-plus"
                                    aria-hidden="true"></i><span class="hide-menu">Versement</span></a></li>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="/bielletage/2" aria-expanded="false"><i class="fa fa-minus"
                                    aria-hidden="true"></i><span class="hide-menu">Retrait</span></a></li>
                        <?php } ?>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="/remittances/index" aria-expanded="false"><i class="fas fa-exchange"
                                    aria-hidden="true"></i><span class="hide-menu">Remittance</span> <span
                                    class="badge badge-danger"> Nouveau ! </span></a></li>

                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="/Journal/petite_caisse" aria-expanded="false"><i class="fa fa-columns"
                                    aria-hidden="true"></i><span class="hide-menu">Petite Caisse</span></a></li>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="/Journal/index" aria-expanded="false"><i class="fa fa-table"
                                    aria-hidden="true"></i><span class="hide-menu">Journal de Caisse</span></a></li>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="/Arreter/index" aria-expanded="false"><i class="fa fa-lock"
                                    aria-hidden="true"></i><span class="hide-menu">Arreter de Caisse </span></a></li>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="/Caisse/transfertfond" aria-expanded="false"><i class="fa fa-share"
                                    aria-hidden="true"></i><span class="hide-menu">Sortie de Fond</span></a></li>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="/Caisse/ApproCaisse" aria-expanded="false"><i class="fa fa-columns"
                                    aria-hidden="true"></i><span class="hide-menu">Appro Caisse</span></a></li>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="/Analytics/performance" aria-expanded="false"><i class="fa fa-columns"
                                    aria-hidden="true"></i><span class="hide-menu">Performance</span> <span
                                    class="badge badge-danger"> Nouveau ! </span></a></li></a></li>
                        <?php if ($_SESSION['statut'] == 'admin' || $_SESSION['statut'] == 'superadmin' || $_SESSION['statut'] == 'Niveau1' || $_SESSION['statut'] == 'Head' || $_SESSION['statut'] == 'Control') { ?>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="/Analytics/chart" aria-expanded="false"><i class="fa fa-columns"
                                    aria-hidden="true"></i><span class="hide-menu">Chart</span></a></li>

                        <?php } ?>

                        <?php if ($_SESSION['statut'] == 'admin' || $_SESSION['statut'] == 'superadmin' || $_SESSION['statut'] == 'ChefCaisse' || $_SESSION['statut'] == 'Head' || $_SESSION['statut'] == 'Niveau1' || $_SESSION['statut'] == 'Control') { ?>
                        <!-- CRM Clients -->
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="/crm/index" aria-expanded="false"><i class="fas fa-user-friends"
                                    aria-hidden="true"></i><span class="hide-menu">CRM Clients</span> <span
                                    class="badge badge-success"> Nouveau ! </span></a></li>
                        <?php } ?>

                        <?php if ($_SESSION['statut'] == 'admin' || $_SESSION['statut'] == 'superadmin' || $_SESSION['statut'] == 'Control' || $_SESSION['statut'] == 'Niveau1') { ?>
                        <!-- LCB-FT Anti-Blanchiment -->
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="/lcb/index" aria-expanded="false"><i class="fas fa-shield-alt"
                                    aria-hidden="true"></i><span class="hide-menu">LCB-FT</span> <span
                                    class="badge badge-warning"> Contrôle </span></a></li>
                        <?php } ?>

                        <?php if ($_SESSION['statut'] == 'admin' || $_SESSION['statut'] == 'superadmin') { ?>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="/Analytics/index" aria-expanded="false"><i class="fa fa-columns"
                                    aria-hidden="true"></i><span class="hide-menu">Analytics</span></a></li>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="/Caisse/solde" aria-expanded="false"><i class="fa fa-globe"
                                    aria-hidden="true"></i><span class="hide-menu">Ma Caisse</span></a></li>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="/Pannel/Produit" aria-expanded="false"><i class="fa fa-table"
                                    aria-hidden="true"></i><span class="hide-menu">Liste Produit</span></a></li>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="/Pannel/Caisse" aria-expanded="false"><i class="fa fa-table"
                                    aria-hidden="true"></i><span class="hide-menu">Liste Caisse</span></a></li>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="/Pannel/Agence" aria-expanded="false"><i class="fa fa-table"
                                    aria-hidden="true"></i><span class="hide-menu">Liste Agence</span></a></li>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="/Pannel/Banque" aria-expanded="false"><i class="fa fa-table"
                                    aria-hidden="true"></i><span class="hide-menu">Liste Partenaire</span></a></li>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="/Pannel/fonds_roulement" aria-expanded="false"><i class="fa fa-money"
                                    aria-hidden="true"></i><span class="hide-menu">Fonds de Roulement</span></a></li>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="/Users/index" aria-expanded="false"><i class="fa fa-users"
                                    aria-hidden="true"></i><span class="hide-menu">Liste Users</span></a></li>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="/Pannel/links" aria-expanded="false"><i class="fa fa-users"
                                    aria-hidden="true"></i><span class="hide-menu">Links</span></a></li>
                        <?php } ?>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="/Users/myprofile" aria-expanded="false"><i class="fa fa-user"
                                    aria-hidden="true"></i><span class="hide-menu">Mon Profile</span></a></li>
                        <li class="text-center p-20 upgrade-btn">
                            <a href="/logout" class="btn btn-block btn-danger text-white">Se Déconnecter</a>
                        </li>
                    </ul>
                </nav>
                <!-- End Sidebar navigation -->
            </div>
            <!-- End Sidebar scroll-->
        </aside>
        <!-- ============================================================== -->
        <!-- End Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper" style="min-height: calc(100vh - 70px);">
            <!-- Container fluid - Contenu principal (LCP target) -->
            <div class="container-fluid" style="min-height: 400px;">
                <!-- Contenu principal - priorité haute pour LCP -->
                <main id="main-content">
                    <?= $content; ?>
                </main>
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
            <footer class="footer text-center"> Dernière Connexion : <?= $_SESSION['LastConnexion'] ?? 'N/A'; ?> |
                <?= date('Y'); ?>
                © <a href="https://malicreances-sa.com" target="_blank">MALI
                    CREANCES SA</a> CONNCEPTION BY <a href="https://niangaly.ml" target="_blank">NIANGALY</a>
            </footer>
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>
    <!-- Scripts critiques (jQuery en premier) -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="/plugins/bower_components/popper.js/dist/umd/popper.min.js"></script>
    <script src="/bootstrap/dist/js/bootstrap.min.js"></script>

    <!-- DataTables - chargé AVANT l'initialisation -->
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js"></script>

    <!-- Scripts UI -->
    <script src="/js/waves.js"></script>
    <script src="/js/sidebarmenu.js"></script>
    <script src="/js/custom.js"></script>
    <script src="/js/sweetalert2/sweetalert2.min.js"></script>

    <!-- Scripts métier (différés) -->
    <script src="/js/wizard.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/smartwizard@5/dist/js/jquery.smartWizard.min.js" defer></script>
    <script src="/scripts/billetage.js" defer></script>
    <script src="/scripts/Checklogin.js" defer></script>
    <script src="/scripts/clientName.js" defer></script>
    <script src="/scripts/produitlist.js" defer></script>
    <script src="/scripts/produitlistRemittance.js" defer></script>
    <script src="/scripts/hidden.js" defer></script>

    <!-- Initialisation DataTables -->
    <script>
    $(document).ready(function() {
        // Init DataTables seulement si les tables existent
        if ($('#dataTable').length) {
            $('#dataTable').DataTable({
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', 'excel', 'print'],
                language: {
                    url: '/js/dataTables.french.json'
                }
            });
        }
        if ($('#dataTable1').length) {
            $('#dataTable1').DataTable({
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', 'excel', 'print']
            });
        }
        if ($('#dataTable2').length) {
            $('#dataTable2').DataTable({
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', 'excel', 'print']
            });
        }
    });
    </script>
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
    <script type="text/javascript" src="/js/idle-timer/idle-timer.min.js"></script>
    <script>
    $(document).ready(function() {
        $(document).idleTimer(960000);

        // Sidebar Toggle
        var savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState === 'true') {
            $('#main-wrapper').addClass('mini-sidebar');
        }

        $('#sidebarToggle').on('click', function() {
            $('#main-wrapper').toggleClass('mini-sidebar');
            var isCollapsed = $('#main-wrapper').hasClass('mini-sidebar');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });
    });
    $(document).on("idle.idleTimer", function(event, elem, obj) {
        window.location = "/logout";
    });
    </script>
</body>

</html>
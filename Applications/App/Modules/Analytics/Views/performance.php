<div class="row">
    <div class="col-md-12">

        <div class="white-box">
            <form method="POST" id="formulaire">
                <div class="input-group">

                    <div class="col-md-3">Du
                        <input type="date" id="Debut" name="Debut" value="<?= $debut; ?>" class="form-control ">
                    </div>
                    <div class="col-md-3">Au
                        <input type="date" id="Fin" name="Fin" value="<?= $fin; ?>" class="form-control">
                    </div>
                    <div class=""></br>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </form><br />
            <h3 class="box-title">Performance Journaliere</h3>

            <div class="table-responsive">
                <table id="dataTable1" class="display nowrap" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="border-top-0">Agence</th>
                            <th class="border-top-0">Caisse</th>
                            <th class="border-top-0">VL|DEPOT</th>
                            <th class="border-top-0">VL|RETRAIT</th>
                            <th class="border-top-0">NB|OP</th>
                            <th class="border-top-0">NB|OP</th>
                            <th class="border-top-0">NB|OP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($Agence as $value) { ?>
                        <tr>
                            <td><?= $value['NameAgency']; ?></td>
                            <td>
                                <ul>
                                    <?php foreach ($value['Afficher'] as $print) { ?>
                                    <li>
                                        <span class="btn btn-primary" data-toggle="modal"
                                            data-target="#depotModal-<?= $print['RefCaisse']; ?>" data-whatever="@mdo"
                                            title="Cliquer pour voir les details">
                                            <?= $print['NameCaisse']; ?>
                                        </span>
                                    </li>
                                    <div class="modal fade" id="depotModal-<?= $print['RefCaisse']; ?>" tabindex="-1"
                                        role="dialog" aria-labelledby="AddCaisse">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">Volume/Produit
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <ul>
                                                        <h3>Depot</h3>
                                                        <?php foreach ($print['SommeDepotProduitCaisse'] as $product => $total) { ?>
                                                        <li><?php echo $product; ?> :
                                                            <?php echo number_format($total, 0, '.', '.'); ?>
                                                        </li>
                                                        <?php } ?>
                                                    </ul>
                                                    <ul>
                                                        <h3>Retrait</h3>
                                                        <?php foreach ($print['SommeRetraitProduitCaisse'] as $product => $total) { ?>
                                                        <li><?php echo $product; ?> :
                                                            <?php echo number_format($total, 0, '.', '.'); ?>
                                                        </li>
                                                        <?php } ?>
                                                    </ul>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-danger"
                                                        data-dismiss="modal">Fermer</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    &nbsp;
                                    <?php } ?>
                                </ul>

                            </td>
                            <td>
                                <ul>
                                    <?php
                                        $sommeVersement = 0;
                                        foreach ($value['Afficher'] as $afficher) {
                                            $sommeVersement += $afficher['TotalVersement'];
                                        ?>
                                    <li><?= number_format($afficher['TotalVersement'], 0, '.', ','); ?></li>
                                    <?php } ?>
                                    <li> Total : <?= number_format($sommeVersement, 0, '.', ','); ?></li>
                                </ul>
                            </td>
                            <td>
                                <ul>
                                    <?php
                                        $sommeRetrait = 0;
                                        foreach ($value['Afficher'] as $afficher) {
                                            $sommeRetrait += $afficher['TotalRetrait'];
                                        ?>
                                    <li><?= number_format($afficher['TotalRetrait'], 0, '.', ','); ?></li>
                                    <?php } ?>
                                    <li> Total :<?= number_format($sommeRetrait, 0, '.', ','); ?></li>
                                </ul>
                            </td>
                            <td>
                                <ul>
                                    <?php foreach ($value['Afficher'] as $afficher) { ?>
                                    <li>DEPOT :<?= $afficher['NbreDepot']; ?></li>
                                    <li>RETRAIT :<?= $afficher['NbreRetrait']; ?></li>
                                    <?php } ?>
                                </ul>
                            </td>
                            <td>
                                <ul>
                                    <?php foreach ($value['Afficher'] as $afficher) { ?>
                                    <li><?= $afficher['NbreOperation']; ?></li>
                                    <?php } ?>
                                </ul>
                            </td>
                            <td><?= $value['NbreOP']; ?></td>

                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php if ($_SESSION['statut'] == 'admin' or $_SESSION['statut'] == 'Niveau1' or $_SESSION['statut'] == 'Head' or $_SESSION['statut'] == 'Control') { ?>

<div class="row">
    <div class="col-md-6">
        <div class="white-box">
            <h3 class="box-title">Performance Validation</h3>
            <div class="table-responsive">
                <table id="dataTable" class="display nowrap" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="border-top-0">OP VALIDATEE | <?= date('d/m/Y'); ?> </th>
                            <th class="border-top-0">Semaine(D-7)</th>
                            <th class="border-top-0"><?= date('M/Y'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= $DailyValidate; ?></td>
                            <td><?= $CountWeekValidate; ?>/<?= $CountWeekOperations; ?></td>
                            <td><?= $MonthValidate; ?>/<?= $MonthOperations; ?> </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="white-box">
            <canvas id="myChart" width="50" height="1"></canvas>
            <script>
            var ctx = document.getElementById('myChart');
            var myChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Nbre Validate | Semaine', 'Nbre OP  Week | Semaine'],
                    datasets: [{
                        label: '# of Votes',
                        data: [<?= $CountWeekValidate; ?>, <?= $CountWeekOperations; ?>],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)'
                        ],
                        borderWidth: 1,
                        hoverOffset: 4
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            </script>
        </div>
    </div>

</div>

<?php } ?>
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

<div class="row">
    <div class="col-md-12">

        <div class="white-box">
            <h3 class="box-title">Performance Journaliere</h3>
            <form method="POST">
                <div class="input-group">
                    <div class="col-md-3">
                        <input type="date" id="jour" name="jour" value="<?= $day; ?>" class="form-control">
                    </div>
                    <div class="col-md-1"></br>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                    </div>
                </div>
            </form><br />
            <div class="table-responsive">
                <table id="dataTable1" class="display nowrap" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="border-top-0">Agence</th>
                            <th class="border-top-0">Caisse</th>
                            <th class="border-top-0">NB|OP|TODAY</th>
                            <th class="border-top-0">NB|OP|TODAY</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($Agence as $value) { ?>
                        <tr>
                            <td><?= $value['NameAgency']; ?></td>
                            <td>
                                <ul>
                                    <?php foreach ($value['Afficher'] as $print) { ?>
                                    <li><?= $print['NameCaisse']; ?></li>
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
                            <td><?= $value['NbreOP']; ?></td </tr>
                            <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
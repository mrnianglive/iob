<form method="POST" id="formulaire">
    <div class="input-group">
        <div class="">
            <select class="form-control" name="RefPays" tabindex="1" id="RefPays">
                <option value="0">Pays</option>
                <?php foreach ($Pays as $key => $Pays) { ?>
                    <option value="<?= $Pays['RefPays']; ?>" <?= (isset($_POST['RefPays']) and $_POST['RefPays'] == $Pays['RefPays']) ? 'selected' : '' ?>>
                        <?= $Pays['nomPays']; ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        &nbsp;
        <div class="">
            <select class="form-control" name="RefAgency" tabindex="1" id="RefAgency">
                <option value="" data-desired-agency="<?= (isset($Agence)) ? $Agence : 'Agence' ?>">
                    Agence
                </option>
            </select>
        </div>
        &nbsp;
        <div class="">
            <select class="form-control" name="RefCaisse" tabindex="1" id="RefCaisse">
                <option value="" data-desired-caisse="<?= (isset($Caisse)) ? $Caisse : 'Caisse' ?>">
                    Caisse
                </option>
            </select>
        </div>
        &nbsp;
        <div class="">
            <button type="submit" class="btn btn-primary" data-toggle="tooltip" title="Cliquez ici pour lancer la recherche">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </div>
</form>

&nbsp;


<div class="row">
    <div class="col-md-12">
        <div class="white-box">
            <h3 class="box-title">Situation Globale | <?= date('Y'); ?> </h3>
            <canvas id="myChart" width="200" height="50"></canvas>
            <script>
                var ctx = document.getElementById('myChart').getContext('2d');
                var chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juilet', 'Aout',
                            'Septembre', 'Octobre', 'Novembre', 'Décembre'
                        ],
                        datasets: [{
                                label: 'Depot',
                                backgroundColor: '#d81b60',
                                bordeHeight: 0.3,
                                borderWidht: 0.3,
                                borderColor: '#d1c4e9',
                                data: [<?= $Chart['Janvier']; ?>, <?= $Chart['Fevrier']; ?>,
                                    <?= $Chart['Mars']; ?>, <?= $Chart['Avril']; ?>,
                                    <?= $Chart['Mai']; ?>, <?= $Chart['Juin']; ?>,
                                    <?= $Chart['Juillet']; ?>, <?= $Chart['Aout']; ?>,
                                    <?= $Chart['Septembre']; ?>, <?= $Chart['Octobre']; ?>,
                                    <?= $Chart['Novembre']; ?>, <?= $Chart['Decembre']; ?>
                                ]
                            },
                            {
                                label: 'Retrait',
                                backgroundColor: '#d1c4e9',
                                bordeHeight: 0.3,
                                borderWidht: 0.3,
                                borderColor: '#d1c4e9',
                                data: [<?= $Chart['RJanvier']; ?>, <?= $Chart['RFevrier']; ?>,
                                    <?= $Chart['RMars']; ?>, <?= $Chart['RAvril']; ?>,
                                    <?= $Chart['RMai']; ?>, <?= $Chart['RJuin']; ?>,
                                    <?= $Chart['RJuillet']; ?>, <?= $Chart['RAout']; ?>,
                                    <?= $Chart['RSeptembre']; ?>, <?= $Chart['ROctobre']; ?>,
                                    <?= $Chart['RNovembre']; ?>, <?= $Chart['RDecembre']; ?>
                                ]
                            }
                        ]
                    },
                    options: {}
                });
            </script>
        </div>

    </div>
</div>


<div class="row">
    <div class="col-md-12">
        <div class="white-box">
            <h3 class="box-title">Situation des Agences | <?= date('M/Y'); ?> </h3>
            <canvas id="check" width="200" height="50"></canvas>
            <script>
                var ctx = document.getElementById('check').getContext('2d');
                var chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: [
                            <?php foreach ($ListeAgence as $key => $value) { ?> '<?= $value['NameAgency']; ?>',
                            <?php } ?>
                        ],
                        datasets: [{
                                label: 'Depot',
                                backgroundColor: '#d81b60',
                                bordeHeight: 0.3,
                                borderWidht: 0.3,
                                borderColor: '#d1c4e9',
                                data: [
                                    <?php foreach ($ListeAgence as $key => $value) {
                                        echo $value['SommeVersement']; ?>, <?php } ?>
                                ]
                            },
                            {
                                label: 'Retrait',
                                backgroundColor: '#d1c4e9',
                                bordeHeight: 0.3,
                                borderWidht: 0.3,
                                borderColor: '#d1c4e9',
                                data: [<?php foreach ($ListeAgence as $key => $value) {
                                            echo $value['SommeRetrait']; ?>, <?php } ?>]
                            }
                        ]
                    },
                    options: {}
                });
            </script>
        </div>

    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="white-box">
            <h3 class="box-title">Situation des Caisses | <?= date('M/Y'); ?> </h3>
            <canvas id="caisse" width="250" height="60"></canvas>
            <script>
                var ctx = document.getElementById('caisse').getContext('2d');
                var chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: [
                            <?php foreach ($ListeCaisse as $key => $value) { ?> '<?= $value['NameCaisse'] . '|' . $value['NameAgency']; ?>',
                            <?php } ?>
                        ],
                        datasets: [{
                                label: 'Depot',
                                backgroundColor: '#d81b60',
                                bordeHeight: 0.3,
                                borderWidht: 0.3,
                                borderColor: '#d1c4e9',
                                data: [
                                    <?php foreach ($ListeCaisse as $key => $value) {
                                        echo $value['SommeVersement']; ?>, <?php } ?>
                                ]
                            },
                            {
                                label: 'Retrait',
                                backgroundColor: '#d1c4e9',
                                bordeHeight: 0.3,
                                borderWidht: 0.3,
                                borderColor: '#d1c4e9',
                                data: [<?php foreach ($ListeCaisse as $key => $value) {
                                            echo $value['SommeRetrait']; ?>, <?php } ?>]
                            }
                        ]
                    },
                    options: {}
                });
            </script>
        </div>

    </div>
</div>
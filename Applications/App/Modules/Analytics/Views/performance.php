<div class="row">
    <div class="col-md-12">

        <div class="white-box">
            <h3 class="box-title">Performance Validation</h3>
            <div class="table-responsive">
                <table id="dataTable" class="display nowrap" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="border-top-0">Jour</th>
                            <th class="border-top-0">Semaine</th>
                            <th class="border-top-0">Mois</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="white-box">
            <h3 class="box-title">Petite Caisse</h3>
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
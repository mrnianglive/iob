<div class="row">
    <div class="col-md-12 col-lg-12 col-sm-12">
        <div class="white-box">
            <div class="d-md-flex mb-3">
                <h3 class="box-title mb-0">OPERATIONS DU <?= date('d-m-Y'); ?> </h3>
            </div>
            <div class="table-responsive">
                <table id="dataTable" class="display nowrap" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="border-top-0">RECU</th>
                            <th class="border-top-0">REF</th>
                            <th class="border-top-0">AGENCE</th>
                            <th class="border-top-0">PRODUIT</th>
                            <th class="border-top-0">CAISSE</th>
                            <th class="border-top-0">OPERATION</th>
                            <th class="border-top-0">CLIENT</th>
                            <th class="border-top-0">N°COMPTE</th>
                            <th class="border-top-0">MONTANT</th>
                            <th class="border-top-0">REMARQUE</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($Operation as $key => $value) { ?>
                            <tr class="advance-table-row">
                                <td><a href="/bordereau/<?= $value['RefOperations']; ?>" target="_blank" class="btn btn-primary" data-toggle="tooltip" title="Cliquez ici pour imprimer le bordereau"><i class="fa fa-print"></i> </td>

                                <td> <?= $value['RefOperations']; ?></td>
                                <td> <?= $value['NameAgency']; ?></td>
                                <td> <?= $value['NameProduit']; ?></td>
                                <td> <?= $value['NameCaisse']; ?></td>
                                <td><?= $value['NameType']; ?></td>
                                <td><?= $value['NameClient']; ?></td>
                                <td><?= $value['NumCompte']; ?></td>
                                <td class="counter text-danger">
                                    <?= number_format($value['MontantVersement'], 0, '.', '.'); ?></td>
                                <td><?= $value['Remarque']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
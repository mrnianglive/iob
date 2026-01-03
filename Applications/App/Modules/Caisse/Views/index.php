<div class="row">
    <div class="col-md-5">
        <form method="POST" id="formulaire">
            <?= $page->getCsrfInput(); ?>
            <select class="form-control" name="RefCaisse" tabindex="1" required=""
                onchange="document.getElementById('formulaire').submit();">
                <option>Veuillez Choisir</option>
                <?php foreach ($UserCaisse as $key => $Caisse) { ?>
                <option value="<?= $Caisse['RefCaisse']; ?>">
                    <?= $Caisse['NameCaisse'] . " " . $Caisse['NameAgency']; ?></option>
                <?php }   ?>
            </select>
        </form>
    </div>
    <input class="form-control col-md-3" readonly="" value="<?//= number_format($Solde, 0, '.', ','); ?>">
    <div class="col-md-12">
        <br />
        <div class="white-box">
            <h3 class="box-title">Operations </h3>
            <div class="table-responsive">
                <table id="dataTable" class="display nowrap" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="border-top-0">Client</th>
                            <th class="border-top-0">Operation</th>
                            <th class="border-top-0">Montant</th>
                            <th class="border-top-0">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($GetOperations as $key => $value) {   ?>
                        <tr>
                            <td><?= $value['NameClient']; ?></td>
                            <td><?= $value['NameType']; ?></td>
                            <td><?= $value['MontantVersement']; ?></td>
                            <td><?= $value['Insert_Time']; ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
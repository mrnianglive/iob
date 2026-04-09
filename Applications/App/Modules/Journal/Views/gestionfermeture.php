<div class="row">
    <div class="col-md-12">
        <div class="input-group">
            <div class="col-md-3">Journée du:
                <input type="date" id="jour" name="jour" value="<?= $day; ?>" class="form-control">
            </div>
            <div class=""><br>
                <button type="button" id="loadDataBtn" class="btn btn-primary" data-toggle="tooltip"
                    title="Cliquer ici pour charger les informations"><i class="fa fa-search"></i></button>
            </div>
        </div><br />
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="white-box">
            <h3 class="box-title">Gestion des Fermetures</h3>
            <div class="table-responsive">
                <table id="dataTable1" class="display nowrap" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="border-top-0">Agence</th>
                            <th class="border-top-0">Solde Reserve(J-1)</th>
                            <th class="border-top-0">Depot</th>
                            <th class="border-top-0">Retrait</th>
                            <?php if ($_SESSION['RefPays'] != 1) { ?>
                            <th class="border-top-0">Frais Timbre</th>
                            <?php } ?>
                            <th class="border-top-0">Solde Agence</th>
                            <th class="border-top-0">Statut</th>
                            <?php if ($_SESSION['statut'] == 'superadmin' or $_SESSION['statut'] == 'admin' or $_SESSION['statut'] == 'ChefCaisse' or $_SESSION['statut'] == 'Control') { ?>
                            <th class="border-top-0">Action</th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($Agence as $value) { ?>
                        <tr>
                            <td><?= $value['NameAgency']; ?></td>
                            <td><?= number_format($value['YesterdayReserve'], 0, '.', '.'); ?><br>
                                <small>
                                    <?= $value['LastDate']; ?>
                                </small>
                            </td>
                            <td><?= number_format($value['SommeDepotWithRemittance'], 0, '.', '.'); ?></td>
                            <td><?= number_format($value['SommeSortieWithRemittance'], 0, '.', '.'); ?></td>
                            <?php if ($_SESSION['RefPays'] != 1) { ?>
                            <td><?= number_format($value['SommeTimbre'], 0, '.', '.'); ?></td>
                            <?php } ?>
                            <td><strong><?= number_format($value['ReserveActuelle'], 0, '.', '.'); ?></strong></td>
                            <td>
                                <?php if (!empty($value['validate'])) { ?>
                                    <span class="label label-success"><i class="fa fa-check"></i> Fermé</span>
                                <?php } else { ?>
                                    <span class="label label-danger"><i class="fa fa-times"></i> Non fermé</span>
                                <?php } ?>
                            </td>
                            <?php if ($_SESSION['statut'] == 'superadmin' or $_SESSION['statut'] == 'admin' or $_SESSION['statut'] == 'ChefCaisse' or $_SESSION['statut'] == 'Control') { ?>
                            <td>
                                <?php if (!empty($value['validate'])) { ?>
                                    <a href="/Arreter/cancel/<?= $value['validate']['RefCompte']; ?>/<?= $value['RefAgency']; ?>/<?= $day; ?>"
                                       class="btn btn-success btn-sm" data-toggle="tooltip"
                                       title="Cliquez ici pour réouvrir l'agence"><i class="fa fa-unlock"></i> Réouvrir</a>
                                <?php } else { ?>
                                    <form method="POST" action="/Journal/fermerAgence" style="display:inline;">
                                        <input type="hidden" value="<?= $value['ReserveActuelle']; ?>" name="ReserveActuelle">
                                        <input type="hidden" value="<?= $day; ?>" name="daycloture">
                                        <input type="hidden" value="<?= $value['RefAgency']; ?>" name="RefAgency">
                                        <button type="submit" class="btn btn-danger btn-sm" data-toggle="tooltip"
                                            title="Cliquez ici pour fermer l'agence"
                                            onclick="return confirm('Confirmer la fermeture de <?= $value['NameAgency']; ?> ?');">
                                            <i class="fa fa-lock"></i> Fermer
                                        </button>
                                    </form>
                                <?php } ?>
                            </td>
                            <?php } ?>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Wait for jQuery to be loaded
function waitForJQuery(callback) {
    if (typeof $ !== 'undefined') {
        callback();
    } else {
        setTimeout(function() {
            waitForJQuery(callback);
        }, 100);
    }
}

waitForJQuery(function() {
    $(document).ready(function() {
        // Load data via AJAX when button is clicked
        $('#loadDataBtn').on('click', function() {
            var date = $('#jour').val();
            if (!date) {
                alert('Veuillez sélectionner une date');
                return;
            }

            // Show loading indicator
            $('#loadDataBtn').prop('disabled', true).html(
                '<i class="fa fa-spinner fa-spin"></i>');

            // Submit via POST form to reload the page with new date
            var form = $('<form>', {
                'method': 'POST',
                'action': '/Journal/gestion_fermeture'
            });
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'jour',
                'value': date
            }));
            $('body').append(form);
            form.submit();
        });

        // Also trigger on date change
        $('#jour').on('change', function() {
            $('#loadDataBtn').click();
        });
    });
});
</script>
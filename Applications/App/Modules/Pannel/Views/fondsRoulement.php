<div class="row">
    <div class="col-md-12">
        <div class="white-box">
            <h3 class="box-title">Gestion Fonds de Roulement</h3>
            <p class="text-muted">Définir le plafond de fonds de roulement par agence et initialiser les soldes pour
                nouvelle année</p>

            <div class="table-responsive">
                <table id="dataTable" class="display nowrap" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="border-top-0">Agence</th>
                            <th class="border-top-0">Plafond Fonds Roulement</th>
                            <th class="border-top-0">Solde Omni Référence</th>
                            <th class="border-top-0">Dernière Initialisation</th>
                            <th class="border-top-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ListeAgence as $agence) { ?>
                        <tr>
                            <td><?= $agence['NameAgency']; ?></td>
                            <td>
                                <form method="POST" action="/Pannel/fonds_roulement" style="display: inline-flex;">
                                    <input type="hidden" name="action" value="update_plafond">
                                    <input type="hidden" name="RefAgency" value="<?= $agence['RefAgency']; ?>">
                                    <input type="number" class="form-control form-control-sm"
                                        name="PlafondFondsRoulement"
                                        value="<?= number_format($agence['PlafondFondsRoulement'] ?? 0, 0, '', ''); ?>"
                                        step="1000" min="0" required style="width: 150px;">
                                    <button type="submit" class="btn btn-primary btn-sm ml-1"
                                        title="Mettre à jour le plafond">
                                        <i class="fa fa-save"></i>
                                    </button>
                                </form>
                            </td>
                            <td><?= number_format($agence['SoldeOmniReference'] ?? 0, 0, '.', '.'); ?></td>
                            <td>
                                <?php if (!empty($agence['DerniereInitialisation'])) { ?>
                                <?= date('d/m/Y', strtotime($agence['DerniereInitialisation']['DateFonds'])); ?><br>
                                <small class="text-muted">
                                    Espèces:
                                    <?= number_format($agence['DerniereInitialisation']['SoldeEspeces'], 0, '.', '.'); ?>
                                    |
                                    Omni:
                                    <?= number_format($agence['DerniereInitialisation']['SoldeOmni'], 0, '.', '.'); ?>
                                </small>
                                <?php } else { ?>
                                <span class="text-danger">Jamais initialisé</span>
                                <?php } ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal"
                                    data-target="#initModal<?= $agence['RefAgency']; ?>"
                                    title="Initialiser les soldes pour nouvelle année">
                                    <i class="fa fa-calendar"></i> Initialiser
                                </button>
                                <?php if (!empty($agence['HistoriqueFonds'])) { ?>
                                <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                    data-target="#histModal<?= $agence['RefAgency']; ?>" title="Voir l'historique">
                                    <i class="fa fa-history"></i> Historique
                                </button>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modals en dehors du tableau -->
<?php foreach ($ListeAgence as $agence) { ?>
<!-- Modal Initialisation -->
<div class="modal fade" id="initModal<?= $agence['RefAgency']; ?>" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Initialisation Fonds de Roulement - <?= $agence['NameAgency']; ?></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST" action="/Pannel/fonds_roulement">
                <input type="hidden" name="action" value="initialiser">
                <input type="hidden" name="RefAgency" value="<?= $agence['RefAgency']; ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Plafond Fonds de Roulement</label>
                        <input type="number" class="form-control"
                            value="<?= number_format($agence['PlafondFondsRoulement'] ?? 0, 0, '', ''); ?>" readonly>
                        <small class="text-muted">Total autorisé (Espèces + Omni)</small>
                    </div>
                    <div class="form-group">
                        <label>Solde Espèces Initial <span class="text-danger">*</span></label>
                        <input type="number" class="form-control solde-especes" name="SoldeEspeces"
                            data-agency="<?= $agence['RefAgency']; ?>" step="1000" min="0" required>
                        <small class="text-muted">Montant en espèces physique</small>
                    </div>
                    <div class="form-group">
                        <label>Solde Omni Initial <span class="text-danger">*</span></label>
                        <input type="number" class="form-control solde-omni" name="SoldeOmni"
                            data-agency="<?= $agence['RefAgency']; ?>" step="1000" min="0" required>
                        <small class="text-muted">Solde sur plateforme ECOBANK</small>
                    </div>
                    <div class="alert alert-info">
                        <strong>Total:</strong> <span id="totalInit<?= $agence['RefAgency']; ?>">0</span><br>
                        <small>Espèces + Omni doit être égal au plafond</small>
                    </div>
                    <div class="form-group">
                        <label>Commentaire</label>
                        <textarea class="form-control" name="Commentaire" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Initialiser</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Historique -->
<?php if (!empty($agence['HistoriqueFonds'])) { ?>
<div class="modal fade" id="histModal<?= $agence['RefAgency']; ?>" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Historique Fonds de Roulement - <?= $agence['NameAgency']; ?></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Espèces</th>
                            <th>Omni</th>
                            <th>Total</th>
                            <th>Commentaire</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agence['HistoriqueFonds'] as $hist) { ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($hist['DateFonds'])); ?></td>
                            <td>
                                <?php 
                                $types = [
                                    'INITIALISATION' => '<span class="label label-success">Initialisation</span>',
                                    'CLOTURE' => '<span class="label label-info">Clôture</span>',
                                    'AJUSTEMENT' => '<span class="label label-warning">Ajustement</span>'
                                ];
                                echo $types[$hist['TypeMouvement']] ?? $hist['TypeMouvement'];
                                ?>
                            </td>
                            <td><?= number_format($hist['SoldeEspeces'], 0, '.', '.'); ?></td>
                            <td><?= number_format($hist['SoldeOmni'], 0, '.', '.'); ?></td>
                            <td><strong><?= number_format($hist['FondsTotal'], 0, '.', '.'); ?></strong></td>
                            <td><?= htmlspecialchars($hist['Commentaire'] ?? ''); ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
<?php } ?>
<?php } ?>

<script>
$(document).ready(function() {
    // Calcul dynamique du total pour chaque modal
    $('.solde-especes, .solde-omni').on('input', function() {
        var agencyId = $(this).data('agency');
        var modal = $(this).closest('.modal');
        var especes = parseFloat(modal.find('.solde-especes').val()) || 0;
        var omni = parseFloat(modal.find('.solde-omni').val()) || 0;
        var total = especes + omni;
        $('#totalInit' + agencyId).text(total.toLocaleString('fr-FR'));
    });
});
</script>
<style>
    .page-header {
        background: linear-gradient(135deg, #dc3545 0%, #721c24 100%);
        color: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
    }
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    }
    .alert-table {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    }
    .severity-badge {
        padding: 4px 10px;
        border-radius: 10px;
        font-size: 0.75em;
        font-weight: 700;
    }
    .severity-CRITIQUE { background: #dc3545; color: #fff; }
    .severity-HAUTE { background: #ffc107; color: #000; }
    .severity-MOYENNE { background: #17a2b8; color: #fff; }
    .severity-INFO { background: #6c757d; color: #fff; }
    .statut-badge {
        padding: 4px 10px;
        border-radius: 10px;
        font-size: 0.75em;
    }
    .statut-NOUVELLE { background: #f8d7da; color: #721c24; }
    .statut-EN_COURS { background: #fff3cd; color: #856404; }
    .statut-TRAITEE { background: #d4edda; color: #155724; }
    .statut-DECLAREE_CENTIF { background: #cce5ff; color: #004085; }
    .statut-CLASSEE { background: #e2e3e5; color: #383d41; }
</style>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <a href="/lcb/index" class="text-white mb-2 d-inline-block">
                <i class="fas fa-arrow-left mr-2"></i>Retour au Dashboard
            </a>
            <h2 class="mt-2">
                <i class="fas fa-bell mr-2"></i>Alertes LCB-FT
            </h2>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="filter-card">
    <div class="row align-items-center">
        <div class="col-md-8">
            <a href="/lcb/alertes" class="btn btn-<?= !$StatutFiltre ? 'dark' : 'outline-dark' ?> m-1">Toutes</a>
            <a href="/lcb/alertes?statut=NOUVELLE" class="btn btn-<?= $StatutFiltre == 'NOUVELLE' ? 'danger' : 'outline-danger' ?> m-1">Nouvelles</a>
            <a href="/lcb/alertes?statut=EN_COURS" class="btn btn-<?= $StatutFiltre == 'EN_COURS' ? 'warning' : 'outline-warning' ?> m-1">En cours</a>
            <a href="/lcb/alertes?statut=TRAITEE" class="btn btn-<?= $StatutFiltre == 'TRAITEE' ? 'success' : 'outline-success' ?> m-1">Traitées</a>
            <a href="/lcb/alertes?statut=DECLAREE_CENTIF" class="btn btn-<?= $StatutFiltre == 'DECLAREE_CENTIF' ? 'info' : 'outline-info' ?> m-1">CENTIF</a>
        </div>
        <div class="col-md-4 text-right">
            <span class="badge badge-dark p-2">
                <?= count($Alertes) ?> alertes
            </span>
        </div>
    </div>
</div>

<!-- Tableau des alertes -->
<div class="alert-table">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>Date</th>
                    <th>Code</th>
                    <th>Sévérité</th>
                    <th>Client</th>
                    <th>Agence</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Traité par</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($Alertes)): ?>
                <tr><td colspan="9" class="text-center py-4 text-muted">Aucune alerte</td></tr>
                <?php else: ?>
                <?php foreach ($Alertes as $alerte): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($alerte['DateCreation'])) ?></td>
                    <td><code><?= $alerte['CodeAlerte'] ?></code></td>
                    <td>
                        <span class="severity-badge severity-<?= $alerte['Severite'] ?>">
                            <?= $alerte['Severite'] ?>
                        </span>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($alerte['NomClient'] ?: 'N/A') ?></strong>
                        <br><small class="text-muted"><?= $alerte['NumCompte'] ?></small>
                    </td>
                    <td><?= htmlspecialchars($alerte['NameAgency'] ?: 'N/A') ?></td>
                    <td class="font-weight-bold">
                        <?php if ($alerte['Montant']): ?>
                        <?= number_format($alerte['Montant'], 0, ',', ' ') ?> F
                        <?php elseif ($alerte['MontantCumul']): ?>
                        <?= number_format($alerte['MontantCumul'], 0, ',', ' ') ?> F
                        <br><small class="text-muted">(cumul <?= $alerte['PeriodeCumul'] ?>)</small>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="statut-badge statut-<?= $alerte['Statut'] ?>">
                            <?= str_replace('_', ' ', $alerte['Statut']) ?>
                        </span>
                    </td>
                    <td>
                        <?= $alerte['TraitePar'] ?: '-' ?>
                        <?php if ($alerte['DateTraitement']): ?>
                        <br><small class="text-muted"><?= date('d/m', strtotime($alerte['DateTraitement'])) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/lcb/detail/<?= $alerte['RefAlerteLCB'] ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


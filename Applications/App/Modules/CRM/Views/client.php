<style>
.client-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 25px;
}

.info-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    margin-bottom: 20px;
}

.info-card .card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    font-weight: 600;
    padding: 15px 20px;
}

.stat-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 20px;
    border-bottom: 1px solid #f1f1f1;
}

.stat-row:last-child {
    border-bottom: none;
}

.stat-label {
    color: #6c757d;
}

.stat-value {
    font-weight: 700;
}

.segment-badge {
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.9em;
    font-weight: 600;
}

.segment-VIP {
    background: #ffd700;
    color: #000;
}

.segment-REGULIER {
    background: #28a745;
    color: #fff;
}

.segment-OCCASIONNEL {
    background: #17a2b8;
    color: #fff;
}

.segment-DORMANT {
    background: #ffc107;
    color: #000;
}

.segment-PERDU {
    background: #dc3545;
    color: #fff;
}

.segment-NOUVEAU {
    background: #6f42c1;
    color: #fff;
}

.risque-badge {
    padding: 4px 10px;
    border-radius: 10px;
    font-size: 0.8em;
}

.risque-FAIBLE {
    background: #d4edda;
    color: #155724;
}

.risque-MOYEN {
    background: #fff3cd;
    color: #856404;
}

.risque-ELEVE {
    background: #f8d7da;
    color: #721c24;
}

.chart-container {
    height: 250px;
}

.op-type-depot {
    color: #28a745;
}

.op-type-retrait {
    color: #dc3545;
}
</style>

<div class="client-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <a href="/crm/index" class="text-white mb-2 d-inline-block">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
            <h2 class="mt-2">
                <i class="fas fa-user mr-2"></i>
                <?= htmlspecialchars($Client['NomClient'] ?: 'Client') ?>
            </h2>
            <div class="mt-2">
                <span class="mr-3"><i class="fas fa-credit-card mr-1"></i>
                    <?= htmlspecialchars($Client['NumCompte']) ?></span>
                <?php if ($Client['TelClient']): ?>
                <span class="mr-3"><i class="fas fa-phone mr-1"></i>
                    <?= htmlspecialchars($Client['TelClient']) ?></span>
                <?php endif; ?>
                <span><i class="fas fa-building mr-1"></i>
                    <?= htmlspecialchars($Client['NameAgency'] ?: 'N/A') ?></span>
            </div>
        </div>
        <div class="col-md-4 text-right">
            <span class="segment-badge segment-<?= $Client['Segment'] ?> mr-2"><?= $Client['Segment'] ?></span>
            <?php if ($Client['EstSurveille']): ?>
            <span class="badge badge-danger"><i class="fas fa-eye mr-1"></i>Surveillé</span>
            <?php endif; ?>
            <br>
            <span class="risque-badge risque-<?= $Client['NiveauRisque'] ?> mt-2 d-inline-block">
                Risque: <?= $Client['NiveauRisque'] ?>
            </span>
        </div>
    </div>
</div>

<div class="row">
    <!-- Statistiques globales -->
    <div class="col-lg-4">
        <div class="info-card">
            <div class="card-header">
                <i class="fas fa-chart-bar mr-2"></i>Statistiques
            </div>
            <div class="card-body p-0">
                <div class="stat-row">
                    <span class="stat-label">Première opération</span>
                    <span
                        class="stat-value"><?= $Client['DatePremiereOp'] ? date('d/m/Y', strtotime($Client['DatePremiereOp'])) : 'N/A' ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Dernière opération</span>
                    <span
                        class="stat-value"><?= $Client['DateDerniereOp'] ? date('d/m/Y', strtotime($Client['DateDerniereOp'])) : 'N/A' ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Nombre d'opérations</span>
                    <span class="stat-value"><?= number_format($Client['NbTotalOperations'], 0, ',', ' ') ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Volume total dépôts</span>
                    <span class="stat-value text-success"><?= number_format($Client['VolumeTotalDepot'], 0, ',', ' ') ?>
                        F</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Volume total retraits</span>
                    <span
                        class="stat-value text-danger"><?= number_format($Client['VolumeTotalRetrait'], 0, ',', ' ') ?>
                        F</span>
                </div>
                <div class="stat-row" style="background: #f8f9fa;">
                    <span class="stat-label"><strong>Volume total</strong></span>
                    <span
                        class="stat-value"><?= number_format($Client['VolumeTotalDepot'] + $Client['VolumeTotalRetrait'], 0, ',', ' ') ?>
                        F</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Moyenne par opération</span>
                    <span class="stat-value"><?= number_format($Client['MontantMoyenOperation'], 0, ',', ' ') ?>
                        F</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique évolution -->
    <div class="col-lg-8">
        <div class="info-card">
            <div class="card-header">
                <i class="fas fa-chart-line mr-2"></i>Évolution Mensuelle
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="evolutionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dernières opérations -->
<div class="info-card">
    <div class="card-header">
        <i class="fas fa-history mr-2"></i>Dernières Opérations
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Montant</th>
                        <th>Agence</th>
                        <th>Caisse</th>
                        <th>Caissier</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($Operations)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Aucune opération</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($Operations as $op): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($op['Approve2_Time'])) ?></td>
                        <td>
                            <span class="op-type-<?= $op['RefType'] == 1 ? 'depot' : 'retrait' ?>">
                                <i class="fas fa-<?= $op['RefType'] == 1 ? 'arrow-down' : 'arrow-up' ?> mr-1"></i>
                                <?= $op['NameType'] ?>
                            </span>
                        </td>
                        <td class="font-weight-bold op-type-<?= $op['RefType'] == 1 ? 'depot' : 'retrait' ?>">
                            <?= number_format($op['MontantVersement'], 0, ',', ' ') ?> F
                        </td>
                        <td><?= htmlspecialchars($op['NameAgency']) ?></td>
                        <td><?= htmlspecialchars($op['NameCaisse']) ?></td>
                        <td><?= htmlspecialchars($op['Caissier']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($AlertesLCB)): ?>
<!-- Alertes LCB -->
<div class="info-card border-danger">
    <div class="card-header bg-danger text-white">
        <i class="fas fa-exclamation-triangle mr-2"></i>Alertes LCB-FT
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Date</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Sévérité</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($AlertesLCB as $alerte): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($alerte['DateCreation'])) ?></td>
                        <td><code><?= $alerte['CodeAlerte'] ?></code></td>
                        <td><?= htmlspecialchars(substr($alerte['Description'], 0, 100)) ?>...</td>
                        <td>
                            <span
                                class="badge badge-<?= $alerte['Severite'] == 'CRITIQUE' ? 'danger' : ($alerte['Severite'] == 'HAUTE' ? 'warning' : 'info') ?>">
                                <?= $alerte['Severite'] ?>
                            </span>
                        </td>
                        <td><?= $alerte['Statut'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(function() {
    // Données pour le graphique
    var labels = [];
    var depots = [];
    var retraits = [];

    <?php foreach ($StatsMensuelles as $stat): ?>
    labels.push('<?= $stat['AnneeMois'] ?>');
    depots.push(<?= floatval($stat['VolumeDepot']) ?>);
    retraits.push(<?= floatval($stat['VolumeRetrait']) ?>);
    <?php endforeach; ?>

    if (labels.length > 0) {
        new Chart(document.getElementById('evolutionChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                        label: 'Dépôts',
                        data: depots,
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgb(40, 167, 69)',
                        borderWidth: 1
                    },
                    {
                        label: 'Retraits',
                        data: retraits,
                        backgroundColor: 'rgba(220, 53, 69, 0.7)',
                        borderColor: 'rgb(220, 53, 69)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return (value / 1000000).toFixed(0) + 'M';
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' +
                                    new Intl.NumberFormat('fr-FR').format(context.raw) + ' F';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
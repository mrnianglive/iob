<style>
.crm-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 25px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    text-align: center;
    margin-bottom: 20px;
}

.stat-value {
    font-size: 2em;
    font-weight: 700;
    color: #212529;
}

.stat-label {
    color: #6c757d;
    font-size: 0.9em;
    text-transform: uppercase;
}

.segment-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.8em;
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

.client-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    margin-bottom: 20px;
}

.client-card .card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    font-weight: 600;
}

.client-row {
    padding: 12px 15px;
    border-bottom: 1px solid #f1f1f1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.client-row:last-child {
    border-bottom: none;
}

.client-row:hover {
    background: #f8f9fa;
}

.volume-text {
    font-weight: 700;
    color: #28a745;
}
</style>

<div class="crm-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-users mr-2"></i> CRM Clients</h2>
            <p class="mb-0">Suivi et fidélisation de votre portefeuille clients</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="/crm/recherche" class="btn btn-light">
                <i class="fas fa-search mr-1"></i> Rechercher
            </a>
        </div>
    </div>
</div>

<!-- Statistiques globales -->
<div class="row">
    <div class="col-md-2 col-6">
        <div class="stat-card">
            <div class="stat-value"><?= number_format($Stats['TotalClients'] ?? 0, 0, ',', ' ') ?></div>
            <div class="stat-label">Total Clients</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="stat-card">
            <div class="stat-value text-success"><?= number_format($Stats['NbActifs'] ?? 0, 0, ',', ' ') ?></div>
            <div class="stat-label">Actifs (30j)</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="stat-card">
            <div class="stat-value text-warning"><?= number_format($Stats['NbInactifs'] ?? 0, 0, ',', ' ') ?></div>
            <div class="stat-label">Inactifs</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="stat-card">
            <div class="stat-value" style="color: #ffd700;"><?= number_format($Stats['NbVIP'] ?? 0, 0, ',', ' ') ?>
            </div>
            <div class="stat-label">VIP</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="stat-card">
            <div class="stat-value text-info"><?= number_format($Stats['NbNouveau'] ?? 0, 0, ',', ' ') ?></div>
            <div class="stat-label">Nouveaux</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="stat-card">
            <div class="stat-value text-danger"><?= number_format($Stats['NbPerdu'] ?? 0, 0, ',', ' ') ?></div>
            <div class="stat-label">Perdus</div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Top Clients -->
    <div class="col-lg-6">
        <div class="client-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-trophy text-warning mr-2"></i> Top 10 Clients (ce mois)</span>
                <a href="/crm/top" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($TopClients)): ?>
                <div class="text-center py-4 text-muted">Aucun client trouvé</div>
                <?php else: ?>
                <?php foreach ($TopClients as $index => $client): ?>
                <div class="client-row">
                    <div>
                        <strong class="mr-2">#<?= $index + 1 ?></strong>
                        <a href="/crm/client/<?= urlencode($client['NumCompte']) ?>">
                            <?= htmlspecialchars($client['NomClient'] ?: $client['NumCompte']) ?>
                        </a>
                        <span class="segment-badge segment-<?= $client['Segment'] ?> ml-2">
                            <?= $client['Segment'] ?>
                        </span>
                    </div>
                    <div class="volume-text">
                        <?= number_format($client['VolumePeriode'] ?? 0, 0, ',', ' ') ?> F
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Clients à relancer -->
    <div class="col-lg-6">
        <div class="client-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-phone text-danger mr-2"></i> Clients à Relancer (inactifs +30j)</span>
                <a href="/crm/inactifs" class="btn btn-sm btn-outline-warning">Voir tout</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($ClientsInactifs)): ?>
                <div class="text-center py-4 text-muted">Aucun client inactif</div>
                <?php else: ?>
                <?php foreach ($ClientsInactifs as $client): ?>
                <div class="client-row">
                    <div>
                        <a href="/crm/client/<?= urlencode($client['NumCompte']) ?>">
                            <?= htmlspecialchars($client['NomClient'] ?: $client['NumCompte']) ?>
                        </a>
                        <br>
                        <small class="text-muted">
                            <i class="fas fa-clock mr-1"></i>
                            Inactif depuis <?= $client['JoursInactif'] ?> jours
                        </small>
                    </div>
                    <div class="text-right">
                        <div class="volume-text"><?= number_format($client['VolumeTotal'] ?? 0, 0, ',', ' ') ?> F</div>
                        <?php if ($client['TelClient']): ?>
                        <small><i class="fas fa-phone mr-1"></i><?= $client['TelClient'] ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Nouveaux clients -->
    <div class="col-lg-6">
        <div class="client-card">
            <div class="card-header">
                <i class="fas fa-user-plus text-success mr-2"></i> Nouveaux Clients (ce mois)
            </div>
            <div class="card-body p-0">
                <?php if (empty($NouveauxClients)): ?>
                <div class="text-center py-4 text-muted">Aucun nouveau client</div>
                <?php else: ?>
                <?php foreach ($NouveauxClients as $client): ?>
                <div class="client-row">
                    <div>
                        <a href="/crm/client/<?= urlencode($client['NumCompte']) ?>">
                            <?= htmlspecialchars($client['NomClient'] ?: $client['NumCompte']) ?>
                        </a>
                        <br>
                        <small class="text-muted">
                            1ère op: <?= date('d/m/Y', strtotime($client['DatePremiereOp'])) ?>
                        </small>
                    </div>
                    <div class="volume-text">
                        <?= number_format($client['VolumeTotal'] ?? 0, 0, ',', ' ') ?> F
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Segments -->
    <div class="col-lg-6">
        <div class="client-card">
            <div class="card-header">
                <i class="fas fa-chart-pie mr-2"></i> Répartition par Segment
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4 mb-3">
                        <a href="/crm/segment/vip" class="text-decoration-none">
                            <div class="segment-badge segment-VIP px-3 py-2 d-inline-block">VIP</div>
                            <div class="mt-2 font-weight-bold"><?= $Stats['NbVIP'] ?? 0 ?></div>
                        </a>
                    </div>
                    <div class="col-4 mb-3">
                        <a href="/crm/segment/regulier" class="text-decoration-none">
                            <div class="segment-badge segment-REGULIER px-3 py-2 d-inline-block">Régulier</div>
                            <div class="mt-2 font-weight-bold"><?= $Stats['NbRegulier'] ?? 0 ?></div>
                        </a>
                    </div>
                    <div class="col-4 mb-3">
                        <a href="/crm/segment/occasionnel" class="text-decoration-none">
                            <div class="segment-badge segment-OCCASIONNEL px-3 py-2 d-inline-block">Occasionnel</div>
                            <div class="mt-2 font-weight-bold"><?= $Stats['NbOccasionnel'] ?? 0 ?></div>
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="/crm/segment/dormant" class="text-decoration-none">
                            <div class="segment-badge segment-DORMANT px-3 py-2 d-inline-block">Dormant</div>
                            <div class="mt-2 font-weight-bold"><?= $Stats['NbDormant'] ?? 0 ?></div>
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="/crm/segment/perdu" class="text-decoration-none">
                            <div class="segment-badge segment-PERDU px-3 py-2 d-inline-block">Perdu</div>
                            <div class="mt-2 font-weight-bold"><?= $Stats['NbPerdu'] ?? 0 ?></div>
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="/crm/segment/nouveau" class="text-decoration-none">
                            <div class="segment-badge segment-NOUVEAU px-3 py-2 d-inline-block">Nouveau</div>
                            <div class="mt-2 font-weight-bold"><?= $Stats['NbNouveau'] ?? 0 ?></div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
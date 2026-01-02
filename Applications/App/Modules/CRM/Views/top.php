<style>
    .page-header {
        background: linear-gradient(135deg, #f5af19 0%, #f12711 100%);
        color: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
    }
    .client-table {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    }
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    }
    .segment-badge {
        padding: 3px 10px;
        border-radius: 15px;
        font-size: 0.75em;
        font-weight: 600;
    }
    .segment-VIP { background: #ffd700; color: #000; }
    .segment-REGULIER { background: #28a745; color: #fff; }
    .segment-OCCASIONNEL { background: #17a2b8; color: #fff; }
    .segment-DORMANT { background: #ffc107; color: #000; }
    .segment-PERDU { background: #dc3545; color: #fff; }
    .segment-NOUVEAU { background: #6f42c1; color: #fff; }
    .rank-1 { background: linear-gradient(135deg, #FFD700, #FFA500); color: #000; }
    .rank-2 { background: linear-gradient(135deg, #C0C0C0, #A0A0A0); color: #000; }
    .rank-3 { background: linear-gradient(135deg, #CD7F32, #8B4513); color: #fff; }
    .rank-badge {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
    }
</style>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <a href="/crm/index" class="text-white mb-2 d-inline-block">
                <i class="fas fa-arrow-left mr-2"></i>Retour au CRM
            </a>
            <h2 class="mt-2">
                <i class="fas fa-trophy mr-2"></i>Top Clients
            </h2>
            <p class="mb-0">Classement par volume d'opérations - <?= ucfirst($Periode) ?></p>
        </div>
        <div class="col-md-4 text-right">
            <span class="badge badge-light p-2">
                <i class="fas fa-users mr-1"></i>
                <?= count($Clients) ?> clients
            </span>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="filter-card">
    <form method="GET" action="/crm/top" class="row align-items-end">
        <div class="col-md-4">
            <label>Période</label>
            <select name="periode" class="form-control" onchange="this.form.submit()">
                <option value="semaine" <?= $Periode == 'semaine' ? 'selected' : '' ?>>Cette semaine</option>
                <option value="mois" <?= $Periode == 'mois' ? 'selected' : '' ?>>Ce mois</option>
                <option value="all" <?= $Periode == 'all' ? 'selected' : '' ?>>Tout l'historique</option>
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter mr-1"></i> Filtrer
            </button>
        </div>
    </form>
</div>

<!-- Podium (Top 3) -->
<?php if (count($Clients) >= 3): ?>
<div class="row mb-4 text-center">
    <!-- 2ème place -->
    <div class="col-md-4">
        <div class="info-card p-4" style="background: white; border-radius: 12px; box-shadow: 0 2px 15px rgba(0,0,0,0.08);">
            <span class="rank-badge rank-2 mb-3">2</span>
            <h5 class="mt-2"><?= htmlspecialchars($Clients[1]['NomClient'] ?: $Clients[1]['NumCompte']) ?></h5>
            <p class="text-success font-weight-bold mb-0">
                <?= number_format($Clients[1]['VolumePeriode'], 0, ',', ' ') ?> F
            </p>
            <small class="text-muted"><?= $Clients[1]['NbOpsPeriode'] ?> opérations</small>
        </div>
    </div>
    <!-- 1ère place -->
    <div class="col-md-4">
        <div class="info-card p-4" style="background: linear-gradient(135deg, #fff9e6, #fff3cc); border-radius: 12px; box-shadow: 0 2px 20px rgba(255,215,0,0.3); transform: scale(1.05);">
            <span class="rank-badge rank-1 mb-3">1</span>
            <i class="fas fa-crown text-warning mb-2" style="font-size: 1.5em;"></i>
            <h5 class="mt-2"><?= htmlspecialchars($Clients[0]['NomClient'] ?: $Clients[0]['NumCompte']) ?></h5>
            <p class="text-success font-weight-bold mb-0" style="font-size: 1.2em;">
                <?= number_format($Clients[0]['VolumePeriode'], 0, ',', ' ') ?> F
            </p>
            <small class="text-muted"><?= $Clients[0]['NbOpsPeriode'] ?> opérations</small>
        </div>
    </div>
    <!-- 3ème place -->
    <div class="col-md-4">
        <div class="info-card p-4" style="background: white; border-radius: 12px; box-shadow: 0 2px 15px rgba(0,0,0,0.08);">
            <span class="rank-badge rank-3 mb-3">3</span>
            <h5 class="mt-2"><?= htmlspecialchars($Clients[2]['NomClient'] ?: $Clients[2]['NumCompte']) ?></h5>
            <p class="text-success font-weight-bold mb-0">
                <?= number_format($Clients[2]['VolumePeriode'], 0, ',', ' ') ?> F
            </p>
            <small class="text-muted"><?= $Clients[2]['NbOpsPeriode'] ?> opérations</small>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Liste complète -->
<div class="client-table">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Client</th>
                    <th>Compte</th>
                    <th>Agence</th>
                    <th>Volume période</th>
                    <th>Nb ops</th>
                    <th>Segment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($Clients)): ?>
                <tr><td colspan="8" class="text-center py-4 text-muted">Aucun client trouvé</td></tr>
                <?php else: ?>
                <?php foreach ($Clients as $index => $client): ?>
                <tr>
                    <td>
                        <?php if ($index < 3): ?>
                        <span class="rank-badge rank-<?= $index + 1 ?>"><?= $index + 1 ?></span>
                        <?php else: ?>
                        <span class="text-muted font-weight-bold"><?= $index + 1 ?></span>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= htmlspecialchars($client['NomClient'] ?: 'N/A') ?></strong></td>
                    <td><code><?= htmlspecialchars($client['NumCompte']) ?></code></td>
                    <td><?= htmlspecialchars($client['NameAgency'] ?: 'N/A') ?></td>
                    <td class="font-weight-bold text-success">
                        <?= number_format($client['VolumePeriode'], 0, ',', ' ') ?> F
                    </td>
                    <td><?= $client['NbOpsPeriode'] ?></td>
                    <td>
                        <span class="segment-badge segment-<?= $client['Segment'] ?>">
                            <?= $client['Segment'] ?>
                        </span>
                    </td>
                    <td>
                        <a href="/crm/client/<?= urlencode($client['NumCompte']) ?>" 
                           class="btn btn-sm btn-outline-primary" title="Voir fiche">
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


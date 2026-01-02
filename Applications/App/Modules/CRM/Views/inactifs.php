<style>
    .page-header {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
    .days-badge {
        background: #f8d7da;
        color: #721c24;
        padding: 3px 10px;
        border-radius: 10px;
        font-size: 0.85em;
    }
</style>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <a href="/crm/index" class="text-white mb-2 d-inline-block">
                <i class="fas fa-arrow-left mr-2"></i>Retour au CRM
            </a>
            <h2 class="mt-2">
                <i class="fas fa-user-clock mr-2"></i>Clients Inactifs
            </h2>
            <p class="mb-0">Clients sans opération depuis <?= $Jours ?> jours - À relancer</p>
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
    <form method="GET" action="/crm/inactifs" class="row align-items-end">
        <div class="col-md-4">
            <label>Inactifs depuis (jours)</label>
            <select name="jours" class="form-control" onchange="this.form.submit()">
                <option value="30" <?= $Jours == 30 ? 'selected' : '' ?>>30 jours</option>
                <option value="60" <?= $Jours == 60 ? 'selected' : '' ?>>60 jours</option>
                <option value="90" <?= $Jours == 90 ? 'selected' : '' ?>>90 jours</option>
                <option value="180" <?= $Jours == 180 ? 'selected' : '' ?>>6 mois</option>
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter mr-1"></i> Filtrer
            </button>
        </div>
    </form>
</div>

<!-- Liste des clients -->
<div class="client-table">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>Client</th>
                    <th>Compte</th>
                    <th>Téléphone</th>
                    <th>Agence</th>
                    <th>Dernière op.</th>
                    <th>Jours inactif</th>
                    <th>Volume total</th>
                    <th>Segment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($Clients)): ?>
                <tr><td colspan="9" class="text-center py-4 text-muted">Aucun client inactif sur cette période</td></tr>
                <?php else: ?>
                <?php foreach ($Clients as $client): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($client['NomClient'] ?: 'N/A') ?></strong></td>
                    <td><code><?= htmlspecialchars($client['NumCompte']) ?></code></td>
                    <td>
                        <?php if ($client['TelClient']): ?>
                        <a href="tel:<?= $client['TelClient'] ?>">
                            <i class="fas fa-phone mr-1"></i><?= $client['TelClient'] ?>
                        </a>
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($client['NameAgency'] ?: 'N/A') ?></td>
                    <td><?= date('d/m/Y', strtotime($client['DateDerniereOp'])) ?></td>
                    <td>
                        <span class="days-badge">
                            <i class="fas fa-clock mr-1"></i><?= $client['JoursInactif'] ?> j
                        </span>
                    </td>
                    <td class="font-weight-bold text-success">
                        <?= number_format($client['VolumeTotal'], 0, ',', ' ') ?> F
                    </td>
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


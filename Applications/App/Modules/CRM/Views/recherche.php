<style>
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 25px;
}

.search-card {
    background: white;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 25px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
}

.client-table {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
}

.segment-badge {
    padding: 3px 10px;
    border-radius: 15px;
    font-size: 0.75em;
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
</style>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <a href="/crm/index" class="text-white mb-2 d-inline-block">
                <i class="fas fa-arrow-left mr-2"></i>Retour au CRM
            </a>
            <h2 class="mt-2">
                <i class="fas fa-search mr-2"></i>Recherche Client
            </h2>
        </div>
    </div>
</div>

<!-- Barre de recherche -->
<div class="search-card">
    <form method="POST" action="/crm/recherche">
        <?= $page->getCsrfInput(); ?>
        <div class="input-group input-group-lg">
            <input type="text" name="terme" class="form-control"
                placeholder="Rechercher par nom, numéro de compte ou téléphone..."
                value="<?= htmlspecialchars($Terme ?? '') ?>" autofocus>
            <div class="input-group-append">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search mr-2"></i>Rechercher
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Résultats -->
<?php if ($Terme): ?>
<div class="client-table">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>Client</th>
                    <th>Compte</th>
                    <th>Téléphone</th>
                    <th>Agence</th>
                    <th>Volume total</th>
                    <th>Nb ops</th>
                    <th>Segment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($Resultats)): ?>
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="fas fa-search fa-2x mb-2 d-block"></i>
                        Aucun client trouvé pour "<?= htmlspecialchars($Terme) ?>"
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($Resultats as $client): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($client['NomClient'] ?: 'N/A') ?></strong></td>
                    <td><code><?= htmlspecialchars($client['NumCompte']) ?></code></td>
                    <td><?= htmlspecialchars($client['TelClient'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($client['NameAgency'] ?: 'N/A') ?></td>
                    <td class="font-weight-bold text-success">
                        <?= number_format($client['VolumeTotal'], 0, ',', ' ') ?> F
                    </td>
                    <td><?= $client['NbTotalOperations'] ?></td>
                    <td>
                        <span class="segment-badge segment-<?= $client['Segment'] ?>">
                            <?= $client['Segment'] ?>
                        </span>
                    </td>
                    <td>
                        <a href="/crm/client/<?= urlencode($client['NumCompte']) ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye mr-1"></i>Voir
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
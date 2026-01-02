<style>
    .page-header {
        background: linear-gradient(135deg, #6c757d 0%, #343a40 100%);
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
    .risque-badge {
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 0.8em;
        font-weight: 600;
    }
    .risque-FAIBLE { background: #d4edda; color: #155724; }
    .risque-MOYEN { background: #fff3cd; color: #856404; }
    .risque-ELEVE { background: #f8d7da; color: #721c24; }
</style>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <a href="/lcb/index" class="text-white mb-2 d-inline-block">
                <i class="fas fa-arrow-left mr-2"></i>Retour au Dashboard
            </a>
            <h2 class="mt-2">
                <i class="fas fa-user-secret mr-2"></i>Clients Sous Surveillance
            </h2>
            <p class="mb-0">Clients identifiés comme présentant un risque LCB-FT</p>
        </div>
        <div class="col-md-4 text-right">
            <span class="badge badge-light p-2">
                <i class="fas fa-users mr-1"></i>
                <?= count($Clients) ?> clients
            </span>
        </div>
    </div>
</div>

<div class="client-table">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>Client</th>
                    <th>Compte</th>
                    <th>Agence</th>
                    <th>Niveau Risque</th>
                    <th>Alertes</th>
                    <th>Dernière Alerte</th>
                    <th>Volume Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($Clients)): ?>
                <tr>
                    <td colspan="8" class="text-center py-4 text-success">
                        <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
                        Aucun client sous surveillance
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($Clients as $client): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($client['NomClient'] ?: 'N/A') ?></strong></td>
                    <td><code><?= htmlspecialchars($client['NumCompte']) ?></code></td>
                    <td><?= htmlspecialchars($client['NameAgency'] ?: 'N/A') ?></td>
                    <td>
                        <span class="risque-badge risque-<?= $client['NiveauRisque'] ?>">
                            <?= $client['NiveauRisque'] ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-danger"><?= $client['NbAlertes'] ?></span>
                    </td>
                    <td>
                        <?= $client['DerniereAlerte'] ? date('d/m/Y', strtotime($client['DerniereAlerte'])) : '-' ?>
                    </td>
                    <td class="font-weight-bold">
                        <?= number_format($client['VolumeTotalDepot'] + $client['VolumeTotalRetrait'], 0, ',', ' ') ?> F
                    </td>
                    <td>
                        <a href="/crm/client/<?= urlencode($client['NumCompte']) ?>" 
                           class="btn btn-sm btn-outline-primary" title="Voir profil">
                            <i class="fas fa-user"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


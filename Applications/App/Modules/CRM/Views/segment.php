<style>
    .page-header {
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
        color: white;
    }
    .page-header.VIP { background: linear-gradient(135deg, #FFD700, #FFA500); color: #000; }
    .page-header.REGULIER { background: linear-gradient(135deg, #28a745, #20c997); }
    .page-header.OCCASIONNEL { background: linear-gradient(135deg, #17a2b8, #6610f2); }
    .page-header.DORMANT { background: linear-gradient(135deg, #ffc107, #ff9800); color: #000; }
    .page-header.PERDU { background: linear-gradient(135deg, #dc3545, #c82333); }
    .page-header.NOUVEAU { background: linear-gradient(135deg, #6f42c1, #e83e8c); }
    .client-table {
        background: white;
        border-radius: 12px;
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
</style>

<?php
$segmentDescriptions = [
    'VIP' => 'Clients à fort volume (> 20M/mois ou > 50 ops/mois)',
    'REGULIER' => 'Clients actifs avec 1-5 opérations par semaine',
    'OCCASIONNEL' => 'Clients avec 1-4 opérations par mois',
    'DORMANT' => 'Clients inactifs depuis 30-90 jours',
    'PERDU' => 'Clients inactifs depuis plus de 90 jours',
    'NOUVEAU' => 'Nouveaux clients (moins de 3 opérations)'
];
?>

<div class="page-header <?= $Segment ?>">
    <div class="row align-items-center">
        <div class="col-md-8">
            <a href="/crm/index" class="mb-2 d-inline-block" style="color: inherit; opacity: 0.8;">
                <i class="fas fa-arrow-left mr-2"></i>Retour au CRM
            </a>
            <h2 class="mt-2">
                <?php if ($Segment == 'VIP'): ?>
                <i class="fas fa-crown mr-2"></i>
                <?php elseif ($Segment == 'PERDU'): ?>
                <i class="fas fa-user-slash mr-2"></i>
                <?php elseif ($Segment == 'NOUVEAU'): ?>
                <i class="fas fa-user-plus mr-2"></i>
                <?php else: ?>
                <i class="fas fa-users mr-2"></i>
                <?php endif; ?>
                Clients <?= ucfirst(strtolower($Segment)) ?>
            </h2>
            <p class="mb-0"><?= $segmentDescriptions[$Segment] ?? '' ?></p>
        </div>
        <div class="col-md-4 text-right">
            <span class="badge badge-light p-2">
                <i class="fas fa-users mr-1"></i>
                <?= count($Clients) ?> clients
            </span>
        </div>
    </div>
</div>

<!-- Autres segments -->
<div class="mb-4 text-center">
    <a href="/crm/segment/vip" class="btn btn-sm <?= $Segment == 'VIP' ? 'btn-warning' : 'btn-outline-warning' ?> m-1">VIP</a>
    <a href="/crm/segment/regulier" class="btn btn-sm <?= $Segment == 'REGULIER' ? 'btn-success' : 'btn-outline-success' ?> m-1">Régulier</a>
    <a href="/crm/segment/occasionnel" class="btn btn-sm <?= $Segment == 'OCCASIONNEL' ? 'btn-info' : 'btn-outline-info' ?> m-1">Occasionnel</a>
    <a href="/crm/segment/dormant" class="btn btn-sm <?= $Segment == 'DORMANT' ? 'btn-warning' : 'btn-outline-secondary' ?> m-1">Dormant</a>
    <a href="/crm/segment/perdu" class="btn btn-sm <?= $Segment == 'PERDU' ? 'btn-danger' : 'btn-outline-danger' ?> m-1">Perdu</a>
    <a href="/crm/segment/nouveau" class="btn btn-sm <?= $Segment == 'NOUVEAU' ? 'btn-primary' : 'btn-outline-primary' ?> m-1">Nouveau</a>
</div>

<!-- Liste -->
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
                    <th>Volume total</th>
                    <th>Nb ops</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($Clients)): ?>
                <tr><td colspan="8" class="text-center py-4 text-muted">Aucun client dans ce segment</td></tr>
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
                    <td><?= $client['DateDerniereOp'] ? date('d/m/Y', strtotime($client['DateDerniereOp'])) : 'N/A' ?></td>
                    <td class="font-weight-bold text-success">
                        <?= number_format($client['VolumeTotal'], 0, ',', ' ') ?> F
                    </td>
                    <td><?= $client['NbTotalOperations'] ?></td>
                    <td>
                        <a href="/crm/client/<?= urlencode($client['NumCompte']) ?>" 
                           class="btn btn-sm btn-outline-primary">
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


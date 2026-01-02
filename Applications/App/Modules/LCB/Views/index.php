<style>
    .lcb-header {
        background: linear-gradient(135deg, #dc3545 0%, #721c24 100%);
        color: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
    }
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        text-align: center;
        margin-bottom: 20px;
    }
    .stat-value {
        font-size: 2.2em;
        font-weight: 700;
    }
    .stat-label {
        color: #6c757d;
        font-size: 0.85em;
        text-transform: uppercase;
    }
    .alert-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }
    .alert-card .card-header {
        background: #f8d7da;
        border-bottom: 1px solid #f5c6cb;
        color: #721c24;
        font-weight: 600;
    }
    .alert-row {
        padding: 12px 15px;
        border-bottom: 1px solid #f1f1f1;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .alert-row:last-child { border-bottom: none; }
    .alert-row:hover { background: #fff3f4; }
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
    .code-badge {
        background: #f8f9fa;
        padding: 3px 8px;
        border-radius: 5px;
        font-family: monospace;
        font-size: 0.85em;
    }
    .info-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }
    .info-card .card-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        font-weight: 600;
    }
    .risque-FAIBLE { color: #28a745; }
    .risque-MOYEN { color: #ffc107; }
    .risque-ELEVE { color: #dc3545; }
</style>

<div class="lcb-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-shield-alt mr-2"></i> LCB-FT Anti-Blanchiment</h2>
            <p class="mb-0">Surveillance des opérations et conformité CENTIF Mali</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="/lcb/alertes" class="btn btn-light mr-2">
                <i class="fas fa-bell mr-1"></i> Toutes les alertes
            </a>
            <?php if ($_SESSION['statut'] == 'admin'): ?>
            <a href="/lcb/seuils" class="btn btn-outline-light">
                <i class="fas fa-cog"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Statistiques -->
<div class="row">
    <div class="col-md-2 col-6">
        <div class="stat-card">
            <div class="stat-value text-danger"><?= $Stats['Nouvelles'] ?? 0 ?></div>
            <div class="stat-label">Nouvelles</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="stat-card">
            <div class="stat-value text-warning"><?= $Stats['EnCours'] ?? 0 ?></div>
            <div class="stat-label">En cours</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="stat-card">
            <div class="stat-value text-success"><?= $Stats['Traitees'] ?? 0 ?></div>
            <div class="stat-label">Traitées</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="stat-card">
            <div class="stat-value text-info"><?= $Stats['Declarees'] ?? 0 ?></div>
            <div class="stat-label">CENTIF</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="stat-card">
            <div class="stat-value"><?= $Stats['AlertesAujourdhui'] ?? 0 ?></div>
            <div class="stat-label">Aujourd'hui</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="stat-card">
            <div class="stat-value"><?= $Stats['AlertesMois'] ?? 0 ?></div>
            <div class="stat-label">Ce mois</div>
        </div>
    </div>
</div>

<!-- Alertes urgentes -->
<?php if (($Stats['CritiquesNonTraitees'] ?? 0) > 0 || ($Stats['HautesNonTraitees'] ?? 0) > 0): ?>
<div class="alert alert-danger d-flex align-items-center mb-4">
    <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
    <div>
        <strong><?= $Stats['CritiquesNonTraitees'] ?> alerte(s) critique(s)</strong> et 
        <strong><?= $Stats['HautesNonTraitees'] ?> alerte(s) haute(s)</strong> en attente de traitement.
        <a href="/lcb/alertes?statut=NOUVELLE" class="alert-link ml-2">Traiter maintenant</a>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <!-- Alertes récentes -->
    <div class="col-lg-8">
        <div class="alert-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-bell mr-2"></i>Alertes Non Traitées</span>
                <a href="/lcb/alertes?statut=NOUVELLE" class="btn btn-sm btn-danger">Voir tout</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($AlertesCritiques)): ?>
                <div class="text-center py-4 text-success">
                    <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
                    Aucune alerte en attente
                </div>
                <?php else: ?>
                    <?php foreach (array_slice($AlertesCritiques, 0, 8) as $alerte): ?>
                    <div class="alert-row">
                        <span class="severity-badge severity-<?= $alerte['Severite'] ?>">
                            <?= $alerte['Severite'] ?>
                        </span>
                        <span class="code-badge"><?= $alerte['CodeAlerte'] ?></span>
                        <div class="flex-grow-1">
                            <strong><?= htmlspecialchars($alerte['NomClient'] ?: $alerte['NumCompte']) ?></strong>
                            <br>
                            <small class="text-muted"><?= htmlspecialchars(substr($alerte['Description'], 0, 80)) ?>...</small>
                        </div>
                        <div class="text-right">
                            <small class="text-muted d-block"><?= date('d/m H:i', strtotime($alerte['DateCreation'])) ?></small>
                            <a href="/lcb/detail/<?= $alerte['RefAlerteLCB'] ?>" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Clients surveillés -->
    <div class="col-lg-4">
        <div class="info-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-user-secret mr-2"></i>Clients Surveillés</span>
                <a href="/lcb/surveilles" class="btn btn-sm btn-outline-secondary">Voir tout</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($ClientsSurveilles)): ?>
                <div class="text-center py-4 text-muted">Aucun client sous surveillance</div>
                <?php else: ?>
                    <?php foreach (array_slice($ClientsSurveilles, 0, 6) as $client): ?>
                    <div class="alert-row">
                        <div class="flex-grow-1">
                            <strong><?= htmlspecialchars($client['NomClient'] ?: $client['NumCompte']) ?></strong>
                            <br>
                            <small>
                                <span class="risque-<?= $client['NiveauRisque'] ?>">
                                    <i class="fas fa-exclamation-circle mr-1"></i><?= $client['NiveauRisque'] ?>
                                </span>
                                • <?= $client['NbAlertes'] ?> alertes
                            </small>
                        </div>
                        <a href="/crm/client/<?= urlencode($client['NumCompte']) ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-user"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Seuils actuels -->
        <div class="info-card">
            <div class="card-header">
                <i class="fas fa-sliders-h mr-2"></i>Seuils Actifs
            </div>
            <div class="card-body p-0">
                <?php 
                $seuilsImportants = ['LCB_SEUIL_DECLARATION', 'LCB_CUMUL_JOURNALIER', 'LCB_CUMUL_HEBDO'];
                foreach ($Seuils as $seuil): 
                    if (!in_array($seuil['CodeSeuil'], $seuilsImportants)) continue;
                ?>
                <div class="alert-row">
                    <div class="flex-grow-1">
                        <small class="text-muted"><?= $seuil['LibelleSeuil'] ?></small>
                    </div>
                    <strong><?= number_format($seuil['Valeur'], 0, ',', ' ') ?> F</strong>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Légende des codes -->
<div class="info-card mt-4">
    <div class="card-header">
        <i class="fas fa-info-circle mr-2"></i>Légende des Codes d'Alerte
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <code>AML-001</code> Transaction ≥ seuil déclaration CENTIF
            </div>
            <div class="col-md-4">
                <code>AML-002</code> Cumul journalier client élevé
            </div>
            <div class="col-md-4">
                <code>AML-003</code> Cumul hebdomadaire client élevé
            </div>
            <div class="col-md-4 mt-2">
                <code>AML-004</code> Suspicion de fractionnement
            </div>
            <div class="col-md-4 mt-2">
                <code>AML-005</code> Dépôt suivi de retrait rapide
            </div>
            <div class="col-md-4 mt-2">
                <code>AML-006</code> Utilisation multi-agences même jour
            </div>
        </div>
    </div>
</div>


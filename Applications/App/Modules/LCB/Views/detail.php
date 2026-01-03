<style>
.page-header {
    background: linear-gradient(135deg, #dc3545 0%, #721c24 100%);
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

.severity-badge {
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 0.9em;
    font-weight: 700;
}

.severity-CRITIQUE {
    background: #dc3545;
    color: #fff;
}

.severity-HAUTE {
    background: #ffc107;
    color: #000;
}

.severity-MOYENNE {
    background: #17a2b8;
    color: #fff;
}

.severity-INFO {
    background: #6c757d;
    color: #fff;
}

.statut-badge {
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 0.9em;
}

.statut-NOUVELLE {
    background: #f8d7da;
    color: #721c24;
}

.statut-EN_COURS {
    background: #fff3cd;
    color: #856404;
}

.statut-TRAITEE {
    background: #d4edda;
    color: #155724;
}

.statut-DECLAREE_CENTIF {
    background: #cce5ff;
    color: #004085;
}

.action-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    padding: 25px;
}
</style>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <a href="/lcb/alertes" class="text-white mb-2 d-inline-block">
                <i class="fas fa-arrow-left mr-2"></i>Retour aux alertes
            </a>
            <h2 class="mt-2">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Alerte <?= $Alerte['CodeAlerte'] ?>
            </h2>
            <p class="mb-0">Créée le <?= date('d/m/Y à H:i', strtotime($Alerte['DateCreation'])) ?></p>
        </div>
        <div class="col-md-4 text-right">
            <span class="severity-badge severity-<?= $Alerte['Severite'] ?> mr-2">
                <?= $Alerte['Severite'] ?>
            </span>
            <span class="statut-badge statut-<?= $Alerte['Statut'] ?>">
                <?= str_replace('_', ' ', $Alerte['Statut']) ?>
            </span>
        </div>
    </div>
</div>

<div class="row">
    <!-- Détails de l'alerte -->
    <div class="col-lg-6">
        <div class="info-card">
            <div class="card-header">
                <i class="fas fa-info-circle mr-2"></i>Détails de l'Alerte
            </div>
            <div class="card-body p-0">
                <div class="stat-row">
                    <span>Code</span>
                    <code><?= $Alerte['CodeAlerte'] ?></code>
                </div>
                <div class="stat-row">
                    <span>Sévérité</span>
                    <span class="severity-badge severity-<?= $Alerte['Severite'] ?>"><?= $Alerte['Severite'] ?></span>
                </div>
                <div class="stat-row">
                    <span>Client</span>
                    <span>
                        <?= htmlspecialchars($Alerte['NomClient'] ?: 'N/A') ?>
                        <br><small class="text-muted"><?= $Alerte['NumCompte'] ?></small>
                    </span>
                </div>
                <div class="stat-row">
                    <span>Agence</span>
                    <span><?= htmlspecialchars($Alerte['NameAgency'] ?: 'N/A') ?></span>
                </div>
                <?php if ($Alerte['Montant']): ?>
                <div class="stat-row">
                    <span>Montant opération</span>
                    <strong class="text-danger"><?= number_format($Alerte['Montant'], 0, ',', ' ') ?> F</strong>
                </div>
                <?php endif; ?>
                <?php if ($Alerte['MontantCumul']): ?>
                <div class="stat-row">
                    <span>Montant cumulé (<?= $Alerte['PeriodeCumul'] ?>)</span>
                    <strong class="text-danger"><?= number_format($Alerte['MontantCumul'], 0, ',', ' ') ?> F</strong>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Description -->
        <div class="info-card">
            <div class="card-header">
                <i class="fas fa-file-alt mr-2"></i>Description
            </div>
            <div class="card-body">
                <p class="mb-0"><?= nl2br(htmlspecialchars($Alerte['Description'])) ?></p>
            </div>
        </div>

        <?php if ($Alerte['ActionPrise']): ?>
        <!-- Action prise -->
        <div class="info-card border-success">
            <div class="card-header bg-success text-white">
                <i class="fas fa-check-circle mr-2"></i>Action Prise
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Par:</strong> <?= htmlspecialchars($Alerte['TraitePar']) ?></p>
                <p class="mb-1"><strong>Date:</strong> <?= date('d/m/Y H:i', strtotime($Alerte['DateTraitement'])) ?>
                </p>
                <hr>
                <p class="mb-0"><?= nl2br(htmlspecialchars($Alerte['ActionPrise'])) ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Client et actions -->
    <div class="col-lg-6">
        <?php if ($Client): ?>
        <!-- Profil client -->
        <div class="info-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-user mr-2"></i>Profil Client</span>
                <a href="/crm/client/<?= urlencode($Client['NumCompte']) ?>" class="btn btn-sm btn-outline-primary">
                    Voir fiche complète
                </a>
            </div>
            <div class="card-body p-0">
                <div class="stat-row">
                    <span>Segment</span>
                    <span class="badge badge-info"><?= $Client['Segment'] ?></span>
                </div>
                <div class="stat-row">
                    <span>Niveau de risque</span>
                    <span
                        class="text-<?= $Client['NiveauRisque'] == 'ELEVE' ? 'danger' : ($Client['NiveauRisque'] == 'MOYEN' ? 'warning' : 'success') ?>">
                        <strong><?= $Client['NiveauRisque'] ?></strong>
                    </span>
                </div>
                <div class="stat-row">
                    <span>Volume total</span>
                    <strong><?= number_format($Client['VolumeTotalDepot'] + $Client['VolumeTotalRetrait'], 0, ',', ' ') ?>
                        F</strong>
                </div>
                <div class="stat-row">
                    <span>Nombre d'opérations</span>
                    <span><?= $Client['NbTotalOperations'] ?></span>
                </div>
                <div class="stat-row">
                    <span>Sous surveillance</span>
                    <span class="text-<?= $Client['EstSurveille'] ? 'danger' : 'success' ?>">
                        <?= $Client['EstSurveille'] ? 'Oui' : 'Non' ?>
                    </span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($Operations)): ?>
        <!-- Dernières opérations -->
        <div class="info-card">
            <div class="card-header">
                <i class="fas fa-history mr-2"></i>Dernières Opérations du Client
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($Operations as $op): ?>
                            <tr>
                                <td><?= date('d/m H:i', strtotime($op['Approve2_Time'])) ?></td>
                                <td class="text-<?= $op['RefType'] == 1 ? 'success' : 'danger' ?>">
                                    <?= $op['NameType'] ?>
                                </td>
                                <td><?= number_format($op['MontantVersement'], 0, ',', ' ') ?> F</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Formulaire de traitement -->
        <?php if ($Alerte['Statut'] != 'TRAITEE' && $Alerte['Statut'] != 'CLASSEE'): ?>
        <div class="action-card">
            <h5><i class="fas fa-gavel mr-2"></i>Traiter cette alerte</h5>
            <form action="/lcb/traiter/<?= $Alerte['RefAlerteLCB'] ?>" method="POST">
                <?= $page->getCsrfInput(); ?>
                <div class="form-group">
                    <label>Nouveau statut</label>
                    <select name="statut" class="form-control" required>
                        <option value="">-- Sélectionner --</option>
                        <option value="EN_COURS">En cours de traitement</option>
                        <option value="TRAITEE">Traitée (rien à signaler)</option>
                        <option value="DECLAREE_CENTIF">Déclaration CENTIF</option>
                        <option value="CLASSEE">Classée sans suite</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Commentaire / Action prise</label>
                    <textarea name="commentaire" class="form-control" rows="4" required
                        placeholder="Décrivez les actions entreprises..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-check mr-2"></i>Valider le traitement
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>
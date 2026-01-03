<style>
.reopen-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
}

.caisse-closed-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    padding: 20px;
    margin-bottom: 15px;
    border-left: 5px solid #dc3545;
    transition: all 0.3s ease;
}

.caisse-closed-card:hover {
    transform: translateX(5px);
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
}

.caisse-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.caisse-name {
    font-size: 1.2em;
    font-weight: 700;
    color: #212529;
}

.caisse-agency {
    color: #6c757d;
    font-size: 0.9em;
}

.caisse-solde {
    background: #f8f9fa;
    padding: 8px 15px;
    border-radius: 8px;
    font-weight: 700;
}

.caisse-time {
    color: #6c757d;
    font-size: 0.85em;
}

.btn-reopen {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    color: white;
    padding: 10px 25px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-reopen:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
    color: white;
}

.history-table {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.history-table th {
    background: #f8f9fa;
    font-weight: 600;
    padding: 15px;
}

.history-table td {
    padding: 12px 15px;
    vertical-align: middle;
}

.empty-state {
    text-align: center;
    padding: 50px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
}

.empty-state i {
    font-size: 4em;
    color: #28a745;
    margin-bottom: 20px;
}

.badge-auto {
    background: #17a2b8;
}

.badge-manual {
    background: #6c757d;
}
</style>

<div class="row">
    <div class="col-12">
        <div class="reopen-card">
            <h2><i class="fas fa-unlock-alt mr-2"></i> Réouverture de Caisse</h2>
            <p class="mb-0">Rouvrez une caisse fermée par erreur. Cette action est tracée pour audit.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <h4 class="mb-3"><i class="fas fa-lock text-danger mr-2"></i> Caisses fermées aujourd'hui</h4>

        <?php if (empty($ClosedCaisses)): ?>
        <div class="empty-state">
            <i class="fas fa-check-circle"></i>
            <h4>Aucune caisse fermée</h4>
            <p class="text-muted">Toutes les caisses sont ouvertes ou aucune n'a été fermée aujourd'hui.</p>
        </div>
        <?php else: ?>
        <?php foreach ($ClosedCaisses as $caisse): ?>
        <div class="caisse-closed-card">
            <div class="caisse-info">
                <div>
                    <div class="caisse-name">
                        <i class="fas fa-cash-register mr-2"></i>
                        <?= htmlspecialchars($caisse['NameCaisse']) ?>
                    </div>
                    <div class="caisse-agency">
                        <i class="fas fa-building mr-1"></i>
                        <?= htmlspecialchars($caisse['NameAgency']) ?>
                    </div>
                    <div class="caisse-time mt-1">
                        <i class="fas fa-clock mr-1"></i>
                        Fermée le <?= date('d/m/Y à H:i', strtotime($caisse['DateSolde'])) ?>
                        <span class="badge <?= $caisse['AutoClose'] == 1 ? 'badge-auto' : 'badge-manual' ?> ml-2">
                            <?= $caisse['TypeFermeture'] ?>
                        </span>
                    </div>
                </div>

                <div class="caisse-solde text-success">
                    Solde: <?= number_format($caisse['Solde'], 0, ',', ' ') ?> FCFA
                </div>

                <div>
                    <button type="button" class="btn btn-reopen"
                        onclick="openReopenModal(<?= $caisse['RefCaisse'] ?>, '<?= htmlspecialchars($caisse['NameCaisse']) ?>')">
                        <i class="fas fa-unlock mr-2"></i> Rouvrir
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($_SESSION['statut'] == 'admin' || $_SESSION['statut'] == 'superadmin'): ?>
    <div class="col-lg-4">
        <h4 class="mb-3"><i class="fas fa-history mr-2"></i> Historique des réouvertures</h4>

        <?php if (!empty($ReouvertureHistory)): ?>
        <div class="history-table">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Caisse</th>
                        <th>Par</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ReouvertureHistory as $item): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($item['NameCaisse']) ?></strong>
                            <br><small class="text-muted"><?= htmlspecialchars($item['NameAgency']) ?></small>
                        </td>
                        <td>
                            <?= htmlspecialchars($item['NomComplet']) ?>
                            <br><small class="text-muted" title="<?= htmlspecialchars($item['Motif']) ?>">
                                <?= substr(htmlspecialchars($item['Motif']), 0, 20) ?>...
                            </small>
                        </td>
                        <td>
                            <small><?= date('d/m H:i', strtotime($item['DateReouverture'])) ?></small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-2"></i>
            Aucune réouverture enregistrée.
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Modal de confirmation -->
<div class="modal fade" id="reopenModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-unlock-alt mr-2"></i>
                    Confirmer la réouverture
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST" action="/bielletage/do_reopen">
                <?= $page->getCsrfInput(); ?>
                <div class="modal-body">
                    <input type="hidden" name="RefCaisse" id="reopenCaisseId">

                    <p>Vous allez rouvrir la caisse <strong id="reopenCaisseName"></strong>.</p>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Cette action sera enregistrée pour audit.
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Motif de la réouverture *</label>
                        <select class="form-control" name="Motif" required>
                            <option value="">Sélectionner un motif</option>
                            <option value="Fermeture par erreur">Fermeture par erreur</option>
                            <option value="Opération oubliée">Opération oubliée à enregistrer</option>
                            <option value="Correction de solde">Correction de solde nécessaire</option>
                            <option value="Demande du superviseur">Demande du superviseur</option>
                            <option value="Autre">Autre raison</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-unlock mr-1"></i> Confirmer la réouverture
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openReopenModal(caisseId, caisseName) {
    // Verifier d'abord si on peut rouvrir
    $.get('/bielletage/check_reopen/' + caisseId, function(data) {
        if (data.canReopen) {
            $('#reopenCaisseId').val(caisseId);
            $('#reopenCaisseName').text(caisseName);
            $('#reopenModal').modal('show');
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Réouverture impossible',
                text: data.reason,
                confirmButtonColor: '#dc3545'
            });
        }
    }).fail(function() {
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: 'Impossible de vérifier la caisse. Veuillez réessayer.'
        });
    });
}
</script>
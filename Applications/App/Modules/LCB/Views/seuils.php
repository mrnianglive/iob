<style>
.page-header {
    background: linear-gradient(135deg, #343a40 0%, #212529 100%);
    color: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 25px;
}

.seuil-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    margin-bottom: 20px;
}

.seuil-card .card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    font-weight: 600;
}

.seuil-row {
    padding: 15px 20px;
    border-bottom: 1px solid #f1f1f1;
}

.seuil-row:last-child {
    border-bottom: none;
}

.seuil-value {
    font-size: 1.3em;
    font-weight: 700;
    color: #dc3545;
}
</style>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <a href="/lcb/index" class="text-white mb-2 d-inline-block">
                <i class="fas fa-arrow-left mr-2"></i>Retour au Dashboard
            </a>
            <h2 class="mt-2">
                <i class="fas fa-sliders-h mr-2"></i>Configuration des Seuils LCB-FT
            </h2>
            <p class="mb-0">Ajustez les seuils de détection anti-blanchiment</p>
        </div>
    </div>
</div>

<?php
$categories = [
    'LCB' => ['title' => 'Seuils LCB (Anti-Blanchiment)', 'icon' => 'shield-alt', 'color' => 'danger'],
    'CRM' => ['title' => 'Seuils CRM (Segmentation)', 'icon' => 'users', 'color' => 'primary']
];
?>

<?php foreach ($categories as $prefix => $cat): ?>
<div class="seuil-card">
    <div class="card-header">
        <i class="fas fa-<?= $cat['icon'] ?> mr-2 text-<?= $cat['color'] ?>"></i>
        <?= $cat['title'] ?>
    </div>
    <div class="card-body p-0">
        <?php foreach ($Seuils as $seuil): ?>
        <?php if (strpos($seuil['CodeSeuil'], $prefix) !== 0) continue; ?>
        <div class="seuil-row">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <strong><?= $seuil['LibelleSeuil'] ?></strong>
                    <br><code class="text-muted"><?= $seuil['CodeSeuil'] ?></code>
                </div>
                <div class="col-md-3">
                    <span class="seuil-value">
                        <?= number_format($seuil['Valeur'], 0, ',', ' ') ?>
                        <?php if ($seuil['TypeValeur'] == 'MONTANT'): ?> F
                        <?php elseif ($seuil['TypeValeur'] == 'JOURS'): ?> jours
                        <?php elseif ($seuil['TypeValeur'] == 'NOMBRE'): ?> ops
                        <?php endif; ?>
                    </span>
                </div>
                <div class="col-md-4">
                    <form action="/lcb/seuils" method="POST" class="form-inline">
                        <?= $page->getCsrfInput(); ?>
                        <input type="hidden" name="code" value="<?= $seuil['CodeSeuil'] ?>">
                        <input type="number" name="valeur" class="form-control form-control-sm mr-2"
                            value="<?= $seuil['Valeur'] ?>" style="width: 120px;">
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-save"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<div class="alert alert-info">
    <i class="fas fa-info-circle mr-2"></i>
    <strong>Note:</strong> Les modifications de seuils prennent effet immédiatement pour les nouvelles opérations.
    Les alertes existantes ne sont pas recalculées.
</div>
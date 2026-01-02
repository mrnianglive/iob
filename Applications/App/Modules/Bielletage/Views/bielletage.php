<?php
// Determiner le type d'operation et ses couleurs
$typeId = $_GET['id'];
$types = [
    1 => ['name' => 'Dépôt', 'color' => 'success', 'icon' => 'fa-arrow-down', 'bg' => '#28a745'],
    2 => ['name' => 'Retrait', 'color' => 'danger', 'icon' => 'fa-arrow-up', 'bg' => '#dc3545'],
    3 => ['name' => 'Appro Caisse', 'color' => 'info', 'icon' => 'fa-wallet', 'bg' => '#17a2b8'],
    4 => ['name' => 'Sortie de Fond', 'color' => 'warning', 'icon' => 'fa-sign-out-alt', 'bg' => '#ffc107'],
    5 => ['name' => 'Transfert Caisse', 'color' => 'primary', 'icon' => 'fa-exchange-alt', 'bg' => '#007bff']
];
$currentType = $types[$typeId] ?? $types[1];
?>

<style>
:root {
    --op-color: <?=$currentType['bg'] ?>;
}

.operation-header {
    background: linear-gradient(135deg, var(--op-color) 0%, <?=$currentType['bg'] ?>dd 100%);
    color: white;
    padding: 20px;
    border-radius: 15px 15px 0 0;
    margin: -15px -15px 20px -15px;
}

.operation-header h2 {
    margin: 0;
    font-weight: 700;
}

.billetage-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    padding: 25px;
    margin-bottom: 20px;
}

.billetage-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 8px;
}

.billetage-table th {
    background: #f8f9fa;
    padding: 12px 15px;
    font-weight: 600;
    color: #495057;
    text-align: center;
    border-radius: 8px;
}

.billetage-table td {
    padding: 8px 10px;
    vertical-align: middle;
}

.denomination-badge {
    background: linear-gradient(135deg, var(--op-color), <?=$currentType['bg'] ?>cc);
    color: white;
    padding: 10px 20px;
    border-radius: 25px;
    font-weight: 700;
    font-size: 1.1em;
    display: inline-block;
    min-width: 100px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.qty-input {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 1.1em;
    font-weight: 600;
    text-align: center;
    transition: all 0.3s ease;
}

.qty-input:focus {
    border-color: var(--op-color);
    box-shadow: 0 0 0 3px <?=$currentType['bg'] ?>33;
    outline: none;
}

.subtotal-display {
    background: #f8f9fa;
    padding: 12px 15px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 1.1em;
    text-align: right;
    color: #212529;
}

.grand-total-card {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    color: white;
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    position: sticky;
    top: 80px;
    z-index: 100;
}

.grand-total-label {
    font-size: 0.9em;
    text-transform: uppercase;
    letter-spacing: 2px;
    opacity: 0.8;
    margin-bottom: 5px;
}

.grand-total-value {
    font-size: 2.5em;
    font-weight: 800;
    letter-spacing: 1px;
}

.form-section {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    padding: 25px;
    margin-top: 20px;
}

.form-section .section-title {
    font-size: 1.1em;
    font-weight: 700;
    color: #495057;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e9ecef;
}

.modern-input {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 12px 15px;
    transition: all 0.3s ease;
}

.modern-input:focus {
    border-color: var(--op-color);
    box-shadow: 0 0 0 3px <?=$currentType['bg'] ?>33;
}

.modern-select {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 12px 15px;
    appearance: none;
    background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23495057' d='M6 9L1 4h10z'/%3E%3C/svg%3E") no-repeat right 15px center;
    background-color: white;
}

.btn-submit {
    background: linear-gradient(135deg, var(--op-color), <?=$currentType['bg'] ?>dd);
    border: none;
    color: white;
    padding: 15px 40px;
    border-radius: 12px;
    font-size: 1.1em;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px <?=$currentType['bg'] ?>44;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px <?=$currentType['bg'] ?>66;
    color: white;
}

.billets-section,
.pieces-section {
    margin-bottom: 20px;
}

.section-label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 700;
    color: #495057;
    margin-bottom: 15px;
    font-size: 1em;
}

.section-label i {
    width: 35px;
    height: 35px;
    background: var(--op-color);
    color: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Animation pour le total */
@keyframes pulse {

    0%,
    100% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.02);
    }
}

.total-updated {
    animation: pulse 0.3s ease;
}

/* Responsive */
@media (max-width: 768px) {
    .denomination-badge {
        padding: 8px 12px;
        font-size: 0.9em;
        min-width: 70px;
    }

    .grand-total-value {
        font-size: 1.8em;
    }
}
</style>

<form method="POST" action='/bielletage/add' id="operationForm">
    <input type="hidden" name="RefType" value="<?= $typeId ?>">
    <?= $page->getCsrfInput(); ?>

    <div class="row">
        <!-- Colonne Billetage -->
        <div class="col-lg-8">
            <div class="billetage-card">
                <div class="operation-header">
                    <h2><i class="fas <?= $currentType['icon'] ?> mr-2"></i> <?= $currentType['name'] ?></h2>
                    <small>Saisissez le billetage de l'opération</small>
                </div>

                <!-- BILLETS -->
                <div class="billets-section">
                    <div class="section-label">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>BILLETS</span>
                    </div>

                    <table class="billetage-table">
                        <thead>
                            <tr>
                                <th style="width: 30%">Coupure (FCFA)</th>
                                <th style="width: 35%">Quantité</th>
                                <th style="width: 35%">Sous-total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $billets = [
                                ['id' => 'a', 'val' => 10000],
                                ['id' => 'b', 'val' => 5000],
                                ['id' => 'c', 'val' => 2000],
                                ['id' => 'd', 'val' => 1000],
                                ['id' => 'e', 'val' => 500]
                            ];
                            foreach ($billets as $b): ?>
                            <tr>
                                <td>
                                    <span class="denomination-badge"><?= number_format($b['val'], 0, '', ' ') ?></span>
                                    <input type="hidden" id="<?= $b['id'] ?>1" name="<?= $b['id'] ?>1"
                                        value="<?= $b['val'] ?>">
                                </td>
                                <td>
                                    <input type="number" class="qty-input" id="<?= $b['id'] ?>2" name="<?= $b['id'] ?>2"
                                        placeholder="0" min="0" data-value="<?= $b['val'] ?>" autocomplete="off">
                                </td>
                                <td>
                                    <div class="subtotal-display" id="<?= $b['id'] ?>3">0</div>
                                    <input type="hidden" name="<?= $b['id'] ?>3" id="<?= $b['id'] ?>3_hidden" value="0">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- PIECES -->
                <div class="pieces-section">
                    <div class="section-label">
                        <i class="fas fa-coins"></i>
                        <span>PIÈCES</span>
                    </div>

                    <table class="billetage-table">
                        <thead>
                            <tr>
                                <th style="width: 30%">Coupure (FCFA)</th>
                                <th style="width: 35%">Quantité</th>
                                <th style="width: 35%">Sous-total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $pieces = [
                                ['id' => 'f', 'val' => 250],
                                ['id' => 'g', 'val' => 200],
                                ['id' => 'h', 'val' => 100],
                                ['id' => 'i', 'val' => 50],
                                ['id' => 'j', 'val' => 25],
                                ['id' => 'k', 'val' => 10],
                                ['id' => 'l', 'val' => 5],
                                ['id' => 'm', 'val' => 1]
                            ];
                            foreach ($pieces as $p): ?>
                            <tr>
                                <td>
                                    <span class="denomination-badge"><?= number_format($p['val'], 0, '', ' ') ?></span>
                                    <input type="hidden" id="<?= $p['id'] ?>1" name="<?= $p['id'] ?>1"
                                        value="<?= $p['val'] ?>">
                                </td>
                                <td>
                                    <input type="number" class="qty-input" id="<?= $p['id'] ?>2" name="<?= $p['id'] ?>2"
                                        placeholder="0" min="0" data-value="<?= $p['val'] ?>" autocomplete="off">
                                </td>
                                <td>
                                    <div class="subtotal-display" id="<?= $p['id'] ?>3">0</div>
                                    <input type="hidden" name="<?= $p['id'] ?>3" id="<?= $p['id'] ?>3_hidden" value="0">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Colonne Total + Formulaire -->
        <div class="col-lg-4">
            <!-- Total flottant -->
            <div class="grand-total-card" id="totalCard">
                <div class="grand-total-label">MONTANT TOTAL</div>
                <div class="grand-total-value" id="grandTotal">0</div>
                <small>FCFA</small>
                <input type="hidden" name="MontantVersement" id="totalInput" value="0">
            </div>

            <!-- Formulaire infos -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-info-circle mr-2"></i>Informations de l'opération
                </div>

                <?php if ($typeId == 5): // Transfert Caisse2Caisse ?>
                <div class="form-group">
                    <label class="font-weight-bold">Caisse Source</label>
                    <select class="form-control modern-select" name="RefCaisse" required>
                        <?php foreach ($CheckOuverture as $Caisse): 
                                if ($Caisse['caisse'] != $Caisse['RefCaisse']): ?>
                        <option value="<?= $Caisse['RefCaisse'] ?>">
                            <?= $Caisse['NameCaisse'] . " - " . $Caisse['NameAgency'] ?>
                        </option>
                        <?php endif; endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Caisse Destination</label>
                    <select class="form-control modern-select" name="Destination" required>
                        <?php foreach ($CheckOuverture as $Caisse): 
                                if ($Caisse['caisse'] != $Caisse['RefCaisse']): ?>
                        <option value="<?= $Caisse['RefCaisse'] ?>">
                            <?= $Caisse['NameCaisse'] . " - " . $Caisse['NameAgency'] ?>
                        </option>
                        <?php endif; endforeach; ?>
                    </select>
                </div>
                <input type="hidden" name="Remarque" value="Transfert Caisse2Caisse">
                <input type="hidden" name="NameDeposant"
                    value="<?= $_SESSION['PrenomUsers'] . " " . $_SESSION['NomUsers'] ?>">
                <input type="hidden" name="TelDeposant" value="NULL">

                <?php elseif ($typeId == 3): // Appro Caisse ?>
                <div class="form-group">
                    <label class="font-weight-bold">Caisse</label>
                    <select class="form-control modern-select" name="RefCaisse" required>
                        <?php foreach ($CheckOuverture as $Caisse): 
                                if ($Caisse['caisse'] != $Caisse['RefCaisse']): ?>
                        <option value="<?= $Caisse['RefCaisse'] ?>">
                            <?= $Caisse['NameCaisse'] . " - " . $Caisse['NameAgency'] ?>
                        </option>
                        <?php endif; endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Type d'Appro</label>
                    <select class="form-control modern-select" name="TypeAppro" required>
                        <?php 
                            $isAdmin = in_array($_SESSION['statut'], ['admin', 'Control', 'Head']);
                            foreach ($TypeAppro as $type): 
                                if ($isAdmin || $type['RefTypeAppro'] == 1): ?>
                        <option value="<?= $type['RefTypeAppro'] ?>"><?= $type['NameTypeAppro'] ?></option>
                        <?php endif; endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Numéro de compte</label>
                    <input type="text" class="form-control modern-input" name="NumCompte" id="NumCompte"
                        autocomplete="off">
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Client</label>
                    <input type="text" class="form-control modern-input" name="NameClient" id="NameClient" required
                        autocomplete="off">
                </div>
                <input type="hidden" name="Remarque" value="NULL">
                <input type="hidden" name="NameDeposant"
                    value="<?= $_SESSION['PrenomUsers'] . " " . $_SESSION['NomUsers'] ?>">
                <input type="hidden" name="TelDeposant" value="NULL">

                <?php elseif ($typeId == 4): // Sortie de fond ?>
                <div class="form-group">
                    <label class="font-weight-bold">Caisse</label>
                    <select class="form-control modern-select" name="RefCaisse" required>
                        <?php foreach ($CheckOuverture as $Caisse): 
                                if ($Caisse['caisse'] != $Caisse['RefCaisse']): ?>
                        <option value="<?= $Caisse['RefCaisse'] ?>">
                            <?= $Caisse['NameCaisse'] . " - " . $Caisse['NameAgency'] ?>
                        </option>
                        <?php endif; endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Numéro de compte</label>
                    <input type="text" class="form-control modern-input" name="NumCompte" id="NumCompte" required
                        autocomplete="off">
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Client</label>
                    <input type="text" class="form-control modern-input" name="NameClient" id="NameClient" required
                        autocomplete="off">
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Remarque</label>
                    <input type="text" class="form-control modern-input" name="Remarque" required autocomplete="off">
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Déposant</label>
                            <input type="text" class="form-control modern-input" name="NameDeposant" required
                                autocomplete="off">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Téléphone</label>
                            <input type="text" class="form-control modern-input" name="TelDeposant" required
                                autocomplete="off">
                        </div>
                    </div>
                </div>

                <?php else: // Depot (1) ou Retrait (2) ?>
                <div class="form-group">
                    <label class="font-weight-bold">Caisse</label>
                    <select class="form-control modern-select" name="RefCaisse" id="RefCaisse" required>
                        <?php foreach ($CheckOuverture as $Caisse): 
                                if ($Caisse['caisse'] != $Caisse['RefCaisse']): ?>
                        <option value="<?= $Caisse['RefCaisse'] ?>">
                            <?= $Caisse['NameCaisse'] . " - " . $Caisse['NameAgency'] ?>
                        </option>
                        <?php endif; endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Produit</label>
                    <select class="form-control modern-select" name="RefProduit" id="RefProduit" required>
                        <option value="">Sélectionner un produit</option>
                    </select>
                </div>
                <div class="form-group" id="numCompteGroup" style="display:none;">
                    <label class="font-weight-bold">Numéro de compte</label>
                    <input type="text" class="form-control modern-input" name="NumCompte" id="NumCompte"
                        autocomplete="off">
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Client</label>
                    <input type="text" class="form-control modern-input" name="NameClient" id="NameClient" required
                        autocomplete="off">
                </div>

                <?php if ($typeId == 2): // Retrait - afficher les frais ?>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Frais</label>
                            <input type="text" class="form-control modern-input" id="frais" name="frais" readonly
                                value="0">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Net à payer</label>
                            <input type="text" class="form-control modern-input bg-light" id="mtotal" readonly
                                value="0">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Type de retrait</label>
                    <select class="form-control modern-select" name="TypeRetrait" id="TypeRetrait" required>
                        <?php foreach ($TypeRetrait as $type): ?>
                        <option value="<?= $type['RefTypeRetrait'] ?>"><?= $type['NameTypeRetrait'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="font-weight-bold">Remarque</label>
                    <input type="text" class="form-control modern-input" name="Remarque" required autocomplete="off">
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Déposant</label>
                            <input type="text" class="form-control modern-input" name="NameDeposant" required
                                autocomplete="off">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Téléphone</label>
                            <input type="text" class="form-control modern-input" name="TelDeposant" required
                                autocomplete="off">
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (in_array(3, $permission)): ?>
                <div class="form-group">
                    <label class="font-weight-bold"><i class="fas fa-calendar-alt mr-1"></i> Antidate</label>
                    <input type="date" class="form-control modern-input" name="Antidate">
                </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-submit btn-block mt-4">
                    <i class="fas fa-check-circle mr-2"></i> VALIDER L'OPÉRATION
                </button>

                <a href="/" class="btn btn-outline-secondary btn-block mt-2">
                    <i class="fas fa-arrow-left mr-2"></i> Annuler
                </a>
            </div>
        </div>
    </div>
</form>

<script>
$(function() {
    // Calcul automatique des sous-totaux et du total
    function calculateTotals() {
        let grandTotal = 0;

        // Parcourir tous les inputs de quantité
        $('.qty-input').each(function() {
            const qty = parseInt($(this).val()) || 0;
            const value = parseInt($(this).data('value'));
            const subtotal = qty * value;
            const id = $(this).attr('id').charAt(0);

            // Mettre à jour l'affichage du sous-total
            $('#' + id + '3').text(subtotal.toLocaleString('fr-FR'));
            $('#' + id + '3_hidden').val(subtotal);

            grandTotal += subtotal;
        });

        // Mettre à jour le total général avec animation
        $('#grandTotal').addClass('total-updated').text(grandTotal.toLocaleString('fr-FR'));
        $('#totalInput').val(grandTotal);

        // Pour les retraits, calculer les frais
        <?php if ($typeId == 2): ?>
        const frais = Math.round(grandTotal * 0.01); // 1% de frais exemple
        const netAPayer = grandTotal - frais;
        $('#frais').val(frais.toLocaleString('fr-FR'));
        $('#mtotal').val(netAPayer.toLocaleString('fr-FR'));
        <?php endif; ?>

        setTimeout(() => $('#grandTotal').removeClass('total-updated'), 300);
    }

    // Écouter les changements sur les inputs
    $('.qty-input').on('input change', calculateTotals);

    // Navigation clavier améliorée
    $('.qty-input').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const inputs = $('.qty-input');
            const currentIndex = inputs.index(this);
            if (currentIndex < inputs.length - 1) {
                inputs.eq(currentIndex + 1).focus().select();
            }
        }
    });

    // Focus automatique sur le premier champ
    $('.qty-input').first().focus();

    // Charger les produits selon la caisse (pour dépôt/retrait)
    <?php if ($typeId == 1 || $typeId == 2): ?>

    function loadProducts() {
        const caisseId = $('#RefCaisse').val();
        if (caisseId) {
            $.get('/config/requeteliste.php', {
                id: caisseId
            }, function(data) {
                $('#RefProduit').html(data);
            });
        }
    }

    $('#RefCaisse').on('change', loadProducts);
    loadProducts(); // Charger au démarrage

    // Afficher/masquer le numéro de compte selon le produit
    $('#RefProduit').on('change', function() {
        if ($(this).val() == '1') { // Ecobank
            $('#numCompteGroup').slideDown();
            $('#NumCompte').prop('required', true);
        } else {
            $('#numCompteGroup').slideUp();
            $('#NumCompte').prop('required', false);
        }
    });
    <?php endif; ?>

    // Validation avant soumission
    $('#operationForm').on('submit', function(e) {
        const total = parseInt($('#totalInput').val()) || 0;
        if (total <= 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Montant invalide',
                text: 'Le montant total doit être supérieur à 0',
                confirmButtonColor: '<?= $currentType['bg'] ?>'
            });
            return false;
        }
    });
});
</script>
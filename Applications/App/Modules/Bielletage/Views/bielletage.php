<?php
// Determiner le type d'operation et ses couleurs
$typeId = $_GET['id'];
$types = [
    1 => ['name' => 'Dépôt', 'color' => 'success', 'icon' => 'fa-arrow-down', 'bg' => '#10b981'],
    2 => ['name' => 'Retrait', 'color' => 'danger', 'icon' => 'fa-arrow-up', 'bg' => '#f43f5e'],
    3 => ['name' => 'Appro Caisse', 'color' => 'info', 'icon' => 'fa-wallet', 'bg' => '#0ea5e9'],
    4 => ['name' => 'Sortie de Fond', 'color' => 'warning', 'icon' => 'fa-sign-out-alt', 'bg' => '#f59e0b'],
    5 => ['name' => 'Transfert', 'color' => 'primary', 'icon' => 'fa-exchange-alt', 'bg' => '#6366f1']
];
$currentType = $types[$typeId] ?? $types[1];

// Filtrer les caisses ouvertes
$caissesOuvertes = [];
if (isset($CheckOuverture) && is_array($CheckOuverture)) {
    $caissesOuvertes = array_filter($CheckOuverture, function($c) {
        return $c['caisse'] != $c['RefCaisse'];
    });
}
$nbCaisses = count($caissesOuvertes);
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.io-app {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: #f1f5f9;
    padding: 10px;
    min-height: calc(100vh - 100px);
}

/* Compact Header */
.io-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: white;
    padding: 12px 20px;
    border-radius: 12px;
    margin-bottom: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid #e2e8f0;
}
.io-header-title {
    display: flex;
    align-items: center;
    gap: 15px;
}
.io-type-badge {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    background: <?= $currentType['bg'] ?>;
    font-size: 1.2rem;
}
.io-title-text h1 {
    font-size: 1.1rem;
    font-weight: 800;
    margin: 0;
    color: #1e293b;
    text-transform: uppercase;
}
.io-title-text p {
    font-size: 0.75rem;
    color: #64748b;
    margin: 0;
    font-weight: 500;
}

/* Total Monitor Fixed Size */
.io-monitor {
    background: #0f172a;
    border-radius: 10px;
    padding: 10px 25px;
    min-width: 250px;
    text-align: right;
    border: 1px solid #334155;
}
.io-monitor-label {
    font-size: 0.65rem;
    color: #94a3b8;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 1px;
}
.io-monitor-val {
    font-family: 'JetBrains Mono', monospace;
    font-size: 1.7rem;
    font-weight: 700;
    color: #22c55e;
    line-height: 1;
}

/* Layout Grid */
.io-main-layout {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 12px;
    align-items: start;
}

@media (max-width: 1200px) {
    .io-main-layout { grid-template-columns: 1fr; }
}

/* Sections */
.io-section {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.io-section-header {
    background: #f8fafc;
    padding: 10px 15px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.io-section-header i { color: <?= $currentType['bg'] ?>; font-size: 0.9rem; }
.io-section-header span {
    font-size: 0.75rem;
    font-weight: 800;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Cash Counting Area - Side by Side */
.io-cash-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
}
.io-column-divider { border-right: 1px solid #f1f5f9; }

/* Table Styling - Ultra Tighter */
.io-table { width: 100%; border-collapse: collapse; }
.io-table th {
    font-size: 0.65rem;
    text-transform: uppercase;
    color: #94a3b8;
    background: #f8fafc;
    padding: 8px 12px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}
.io-table td {
    padding: 6px 12px;
    border-bottom: 1px solid #f8fafc;
    height: 44px;
}
.io-table tr:hover { background: #fdfdfd; }

/* Denomination Styling */
.io-denom {
    display: flex;
    align-items: center;
    gap: 10px;
}
.io-denom-pill {
    min-width: 65px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 3px 8px;
    text-align: center;
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.8rem;
    font-weight: 700;
    color: #475569;
}
.io-denom-pill.high { 
    background: <?= $currentType['bg'] ?>10;
    border-color: <?= $currentType['bg'] ?>30;
    color: <?= $currentType['bg'] ?>;
}

/* Input Styling */
.io-input-qty {
    width: 80px;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    padding: 5px 8px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-weight: 700;
    font-size: 0.9rem;
    text-align: center;
    transition: all 0.2s;
    background: #fff;
    color: #1e293b;
}
.io-input-qty:focus {
    outline: none;
    border-color: <?= $currentType['bg'] ?>;
    box-shadow: 0 0 0 3px <?= $currentType['bg'] ?>15;
    background: white;
}
.io-input-qty::placeholder { color: #cbd5e1; }

.io-subtotal {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.85rem;
    font-weight: 700;
    color: #64748b;
    text-align: right;
}
.io-subtotal.active { color: <?= $currentType['bg'] ?>; }

/* Right Panel Elements */
.io-form-body { padding: 15px; }
.io-field-group { margin-bottom: 12px; }
.io-label {
    display: block;
    font-size: 0.65rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    margin-bottom: 5px;
    letter-spacing: 0.5px;
}
.io-control {
    width: 100%;
    padding: 9px 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #1e293b;
    transition: border 0.2s;
}
.io-control:focus {
    outline: none;
    border-color: <?= $currentType['bg'] ?>;
    background: white;
}
.io-control::placeholder { color: #94a3b8; font-weight: 400; }

.io-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

/* Status Badge for Caisse */
.io-caisse-badge {
    padding: 8px 12px;
    background: white;
    border: 1px solid #22c55e20;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #166534;
    font-weight: 700;
    font-size: 0.85rem;
}
.io-dot { width: 8px; height: 8px; background: #22c55e; border-radius: 50%; box-shadow: 0 0 0 4px #22c55e15; animation: blink 2s infinite; }
@keyframes blink { 0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; } }

/* Fix Antidate */
.io-antidate {
    background: #fffbeb;
    padding: 12px;
    border-radius: 10px;
    border: 1px solid #fef3c7;
}

/* Actions */
.io-actions { padding: 15px; border-top: 1px solid #f1f5f9; background: #f8fafc; }
.io-btn-primary {
    width: 100%;
    padding: 12px;
    background: <?= $currentType['bg'] ?>;
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 800;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    cursor: pointer;
    box-shadow: 0 4px 12px <?= $currentType['bg'] ?>30;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}
.io-btn-primary:hover {
    filter: brightness(1.05);
    box-shadow: 0 6px 15px <?= $currentType['bg'] ?>40;
    transform: translateY(-1px);
}
.io-btn-cancel {
    display: block;
    width: 100%;
    text-align: center;
    padding: 8px;
    color: #94a3b8;
    font-size: 0.75rem;
    font-weight: 600;
    text-decoration: none;
    margin-top: 10px;
}
.io-btn-cancel:hover { color: #64748b; }

/* Clear Button */
.io-btn-clear {
    margin-left: auto;
    background: #f1f5f9;
    color: #64748b;
    border: 1px solid #e2e8f0;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.65rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 5px;
    text-transform: uppercase;
}
.io-btn-clear:hover {
    background: #fee2e2;
    color: #ef4444;
    border-color: #fecaca;
}

/* Hide arrows on number inputs */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
input[type=number] { -moz-appearance: textfield; }
</style>

<div class="io-app">
    <form method="POST" action='/bielletage/add' id="operationForm">
        <input type="hidden" name="RefType" value="<?= $typeId ?>">
        <?= $page->getCsrfInput(); ?>

        <!-- HEADER -->
        <header class="io-header">
            <div class="io-header-title">
                <div class="io-type-badge"><i class="fas <?= $currentType['icon'] ?>"></i></div>
                <div class="io-title-text">
                    <h1><?= $currentType['name'] ?></h1>
                    <p>Enregistrement de l'opération de caisse</p>
                </div>
            </div>
            <div class="io-monitor">
                <div class="io-monitor-label">Total G&eacute;n&eacute;ral</div>
                <div class="io-monitor-val" id="grandTotal">0</div>
                <input type="hidden" name="MontantVersement" id="totalInput" value="0">
            </div>
        </header>

        <div class="io-main-layout">
            <!-- LEFT AREA: Cash Entry -->
            <div class="io-section shadow-sm">
                <div class="io-section-header">
                    <i class="fas fa-calculator"></i>
                    <span>Comptage du num&eacute;raire</span>
                    <button type="button" class="io-btn-clear" id="btn-clear-all">
                        <i class="fas fa-trash-alt"></i>
                        Effacer
                    </button>
                </div>
                
                <div class="io-cash-grid">
                    <!-- Column 1: Billets -->
                    <div class="io-column-divider">
                        <table class="io-table">
                            <thead>
                                <tr>
                                    <th colspan="3">BILLETS</th>
                                </tr>
                                <tr>
                                    <th>Valeur</th>
                                    <th style="text-align:center">Qt&eacute;</th>
                                    <th style="text-align:right">Montant</th>
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
                                        <div class="io-denom">
                                            <div class="io-denom-pill high"><?= number_format($b['val'], 0, '', ' ') ?></div>
                                            <input type="hidden" id="<?= $b['id'] ?>1" name="<?= $b['id'] ?>1" value="<?= $b['val'] ?>">
                                        </div>
                                    </td>
                                    <td style="text-align:center">
                                        <input type="number" class="io-input-qty qty-input" id="<?= $b['id'] ?>2" name="<?= $b['id'] ?>2" 
                                               placeholder="0" min="0" data-value="<?= $b['val'] ?>" autocomplete="off">
                                    </td>
                                    <td style="text-align:right">
                                        <div class="io-subtotal" id="<?= $b['id'] ?>3">0</div>
                                        <input type="hidden" name="<?= $b['id'] ?>3" id="<?= $b['id'] ?>3_hidden" value="0">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Column 2: Pièces -->
                    <div>
                        <table class="io-table">
                            <thead>
                                <tr>
                                    <th colspan="3">PI&Egrave;CES</th>
                                </tr>
                                <tr>
                                    <th>Valeur</th>
                                    <th style="text-align:center">Qt&eacute;</th>
                                    <th style="text-align:right">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $pieces = [
                                    ['id' => 'f', 'val' => 250], ['id' => 'g', 'val' => 200],
                                    ['id' => 'h', 'val' => 100], ['id' => 'i', 'val' => 50],
                                    ['id' => 'j', 'val' => 25], ['id' => 'k', 'val' => 10],
                                    ['id' => 'l', 'val' => 5], ['id' => 'm', 'val' => 1]
                                ];
                                foreach ($pieces as $p): ?>
                                <tr>
                                    <td>
                                        <div class="io-denom">
                                            <div class="io-denom-pill"><?= $p['val'] ?></div>
                                            <input type="hidden" id="<?= $p['id'] ?>1" name="<?= $p['id'] ?>1" value="<?= $p['val'] ?>">
                                        </div>
                                    </td>
                                    <td style="text-align:center">
                                        <input type="number" class="io-input-qty qty-input" id="<?= $p['id'] ?>2" name="<?= $p['id'] ?>2" 
                                               placeholder="0" min="0" data-value="<?= $p['val'] ?>" autocomplete="off">
                                    </td>
                                    <td style="text-align:right">
                                        <div class="io-subtotal" id="<?= $p['id'] ?>3">0</div>
                                        <input type="hidden" name="<?= $p['id'] ?>3" id="<?= $p['id'] ?>3_hidden" value="0">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- RIGHT AREA: Details & Action -->
            <div class="io-section shadow-sm">
                <div class="io-section-header">
                    <i class="fas fa-info-circle"></i>
                    <span>Informations Op&eacute;ration</span>
                </div>
                
                <div class="io-form-body">
                    <?php if ($typeId == 5): // Transfert ?>
                        <div class="io-field-group">
                            <label class="io-label">Caisse Source</label>
                            <select class="io-control" name="RefCaisse" required>
                                <?php foreach ($CheckOuverture as $Caisse): if ($Caisse['caisse'] != $Caisse['RefCaisse']): ?>
                                <option value="<?= $Caisse['RefCaisse'] ?>"><?= $Caisse['NameCaisse'] ?></option>
                                <?php endif; endforeach; ?>
                            </select>
                        </div>
                        <div class="io-field-group">
                            <label class="io-label">Caisse Destination</label>
                            <select class="io-control" name="Destination" required>
                                <?php foreach ($CheckOuverture as $Caisse): if ($Caisse['caisse'] != $Caisse['RefCaisse']): ?>
                                <option value="<?= $Caisse['RefCaisse'] ?>"><?= $Caisse['NameCaisse'] ?></option>
                                <?php endif; endforeach; ?>
                            </select>
                        </div>
                        <input type="hidden" name="Remarque" value="Transfert Caisse2Caisse">
                        <input type="hidden" name="NameDeposant" value="<?= $_SESSION['PrenomUsers'] . " " . $_SESSION['NomUsers'] ?>">
                        <input type="hidden" name="TelDeposant" value="NULL">

                    <?php else: // Depot/Retrait/Appro/Sortie ?>
                        <div class="io-field-group">
                            <label class="io-label">Caisse / Agence</label>
                            <?php if ($nbCaisses == 1): $singleCaisse = reset($caissesOuvertes); ?>
                                <input type="hidden" name="RefCaisse" id="RefCaisse" value="<?= $singleCaisse['RefCaisse'] ?>">
                                <div class="io-caisse-badge">
                                    <div class="io-dot"></div>
                                    <span><?= $singleCaisse['NameCaisse'] ?></span>
                                </div>
                            <?php else: ?>
                                <select class="io-control" name="RefCaisse" id="RefCaisse" required>
                                    <?php foreach ($caissesOuvertes as $Caisse): ?>
                                    <option value="<?= $Caisse['RefCaisse'] ?>"><?= $Caisse['NameCaisse'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>

                        <?php if ($typeId == 1 || $typeId == 2): ?>
                        <div class="io-field-group">
                            <label class="io-label">Produit / Partenaire</label>
                            <select class="io-control" name="RefProduit" id="RefProduit" required>
                                <option value="">S&eacute;lectionner...</option>
                            </select>
                        </div>
                        
                        <div class="io-field-group hidden" id="numCompteGroup">
                            <label class="io-label">N° de Compte</label>
                            <input type="text" class="io-control" name="NumCompte" id="NumCompte" placeholder="Compte client">
                        </div>
                        <?php endif; ?>

                        <div class="io-field-group">
                            <label class="io-label">Nom du Client</label>
                            <input type="text" class="io-control" name="NameClient" id="NameClient" required placeholder="Nom complet">
                        </div>

                        <div class="io-grid-2">
                            <div class="io-field-group">
                                <label class="io-label">D&eacute;posant</label>
                                <input type="text" class="io-control" name="NameDeposant" required placeholder="Nom">
                            </div>
                            <div class="io-field-group">
                                <label class="io-label">T&eacute;l&eacute;phone</label>
                                <input type="text" class="io-control" name="TelDeposant" required placeholder="Mobile">
                            </div>
                        </div>

                        <div class="io-field-group">
                            <label class="io-label">Motif / Remarque</label>
                            <input type="text" class="io-control" name="Remarque" required placeholder="...">
                        </div>

                        <?php if ($typeId == 2): // Retrait ?>
                            <div class="io-grid-2">
                                <div class="io-field-group">
                                    <label class="io-label">Frais</label>
                                    <input type="text" class="io-control" id="frais" name="frais" readonly value="0" style="background:#f1f5f9;">
                                </div>
                                <div class="io-field-group">
                                    <label class="io-label">Net &agrave; Payer</label>
                                    <input type="text" class="io-control" id="mtotal" readonly value="0" style="background:#f1f5f9;">
                                </div>
                            </div>
                            <div class="io-field-group">
                                <label class="io-label">Type de Retrait</label>
                                <select class="io-control" name="TypeRetrait" id="TypeRetrait" required>
                                    <?php foreach ($TypeRetrait as $type): ?>
                                    <option value="<?= $type['RefTypeRetrait'] ?>"><?= $type['NameTypeRetrait'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (isset($permission) && in_array(3, $permission)): ?>
                        <div class="io-field-group io-antidate">
                            <label class="io-label"><i class="fas fa-calendar-alt"></i> Date r&eacute;troactive</label>
                            <input type="date" class="io-control" name="Antidate">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="io-actions">
                    <button type="submit" class="io-btn-primary">
                        <i class="fas fa-check-circle"></i>
                        Valider l'Op&eacute;ration
                    </button>
                    <a href="/" class="io-btn-cancel">Annuler la saisie</a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    // Bouton Clear All avec délégation pour être sûr qu'il soit capturé
    $(document).on('click', '#btn-clear-all', function(e) {
        e.preventDefault();
        
        // Vider tous les inputs
        $('.qty-input').each(function() {
            $(this).val('');
        });

        // Réinitialiser les affichages de sous-totaux
        $('.io-subtotal').text('0').removeClass('active');
        
        // Réinitialiser le total général
        $('#grandTotal').text('0');
        $('#totalInput').val('0');

        // Réinitialiser les frais (si présents)
        if($('#frais').length) $('#frais').val('0');
        if($('#mtotal').length) $('#mtotal').val('0');

        // Focus sur le premier champ
        $('.qty-input').first().focus();
        
        console.log('Billetage réinitialisé');
    });
});
</script>
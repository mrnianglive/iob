<?php
// --- Dynamic Page Title ---
$page_title = 'Bielletage';
$type_id = $_GET['id'] ?? 0;
$types = [
    1 => 'Versement',
    2 => 'Retrait',
    3 => 'Appro Caisse',
    4 => 'Sortie de Fond',
    5 => 'Transfert Caisse2Caisse'
];
if (isset($types[$type_id])) {
    $page_title .= ' - ' . $types[$type_id];
}
?>

<style>
.bielletage-container {
    max-width: 1200px;
    margin: 0 auto;
    font-family: sans-serif;
}

.bielletage-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.bielletage-table th,
.bielletage-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: center;
}

.bielletage-table th {
    background-color: #f2f2f2;
    font-weight: bold;
}

.bielletage-table input[type="number"] {
    width: 100%;
    padding: 5px;
    border: 1px solid coral;
    box-sizing: border-box;
}

.bielletage-table input[readonly] {
    background-color: #e9ecef;
    border: 1px solid #ced4da;
}

#total-row td {
    font-size: 1.2em;
    font-weight: bold;
}

#grand-total {
    color: #28a745;
}

.form-section {
    border: 1px solid #ddd;
    padding: 20px;
    border-radius: 5px;
    background-color: #f9f9f9;
}
</style>

<div class="bielletage-container">
    <h3><?php echo htmlspecialchars($page_title); ?></h3>

    <form method="POST" action="/bielletage/add" id="bielletage-form">
        <input type="hidden" name="RefType" value="<?= htmlspecialchars($type_id); ?>">

        <!-- Section for Bielletage -->
        <div class="form-section">
            <h4>Détail du Bielletage</h4>
            <div class="table-responsive">
                <table class="bielletage-table">
                    <thead>
                        <tr>
                            <th>Billet / Pièce</th>
                            <th>Quantité</th>
                            <th>Montant</th>
                        </tr>
                    </thead>
                    <tbody id="bielletage-body">
                        <?php
                        $denominations = [
                            'a' => 10000, 'b' => 5000, 'c' => 2000, 'd' => 1000, 'e' => 500,
                            'f' => 250, 'g' => 200, 'h' => 100, 'i' => 50, 'j' => 25,
                            'k' => 10, 'l' => 5, 'm' => 1
                        ];
                        foreach ($denominations as $prefix => $value) {
                            echo "<tr>";
                            echo "<td>" . number_format($value) . "</td>";
                            echo "<td><input type='number' class='form-control quantity-input' name='{$prefix}2' autocomplete='OFF'></td>";
                            echo "<td><input type='text' class='form-control amount-output' name='{$prefix}3' readonly></td>";
                            echo "<input type='hidden' name='{$prefix}1' value='{$value}'>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr id="total-row">
                            <td colspan="2" style="text-align: right;">Total Général</td>
                            <td id="grand-total">0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <br>

        <!-- Section for Operation Details -->
        <div class="form-section">
            <h4>Détails de l'Opération</h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group has-error">
                        <label class="control-label">Montant Total*</label>
                        <input type="text" id="total" class="form-control" name="MontantVersement" readonly required>
                    </div>
                </div>

                <?php if ($type_id == 1) : // Versement ?>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Compte</label>
                        <select class="form-control" name="RefCompte" tabindex="1" required>
                            <option value="">Veuillez Selectionner un Compte</option>
                            <?php foreach ($ListeCompte as $Compte) : ?>
                            <option value="<?= $Compte['RefCompte']; ?>">
                                <?= $Compte['NumCompte'] . " - " . $Compte['NameClient']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($type_id == 2) : // Retrait ?>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Compte</label>
                        <select class="form-control" name="RefCompte" id="RefCompte" tabindex="1" required>
                            <option value="">Veuillez Selectionner un Compte</option>
                            <?php foreach ($ListeCompte as $Compte) : ?>
                            <option value="<?= $Compte['RefCompte']; ?>">
                                <?= $Compte['NumCompte'] . " - " . $Compte['NameClient']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group has-error">
                        <label class="control-label">Solde Compte</label>
                        <input type="text" class="form-control" name="SoldeCompte" id="SoldeCompte" readonly
                            placeholder="Solde Compte">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group has-error">
                        <label class="control-label">Type de Retrait*</label>
                        <select class="form-control" name="TypeRetrait" id="TypeRetrait" required>
                            <?php foreach ($TypeRetrait as $type) : ?>
                            <option value="<?= $type['RefTypeRetrait']; ?>"><?= $type['NameTypeRetrait']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($type_id == 3 || $type_id == 4) : // Appro/Sortie Caisse ?>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Caisse</label>
                        <select class="form-control" name="RefCaisse" tabindex="1" required>
                            <?php foreach ($CheckOuverture as $Caisse) :
                                    if ($Caisse['caisse'] == $Caisse['RefCaisse']) : ?>
                            <option value="<?= $Caisse['RefCaisse']; ?>">
                                <?= $Caisse['NameCaisse'] . " " . $Caisse['NameAgency']; ?></option>
                            <?php endif;
                                endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($type_id == 5) : // Transfert ?>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Source</label>
                        <select class="form-control" name="RefCaisse" required>
                            <?php foreach ($CheckOuverture as $Caisse) {
                                    if ($Caisse['caisse'] != $Caisse['RefCaisse']) { ?>
                            <option value="<?= $Caisse['RefCaisse']; ?>">
                                <?= $Caisse['NameCaisse'] . " " . $Caisse['NameAgency']; ?></option>
                            <?php }
                                } ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Destination</label>
                        <select class="form-control" name="Destination" required>
                            <?php foreach ($CheckOuverture as $Caisse) {
                                    if ($Caisse['caisse'] != $Caisse['RefCaisse']) { ?>
                            <option value="<?= $Caisse['RefCaisse']; ?>">
                                <?= $Caisse['NameCaisse'] . " " . $Caisse['NameAgency']; ?></option>
                            <?php }
                                } ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

                <div class="col-md-6">
                    <div class="form-group has-error">
                        <label class="control-label">Remarque *</label>
                        <input type="text" class="form-control" name="Remarque" required placeholder="Remarque"
                            autocomplete="OFF"
                            <?php if ($type_id == 5) echo 'value="Transfert Caisse2Caisse" readonly'; ?>>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group has-error">
                        <label class="control-label">Déposant/Auteur Retrait *</label>
                        <input type="text" class="form-control" name="NameDeposant" required
                            placeholder="Nom du déposant/Auteur du retrait" autocomplete="OFF"
                            <?php if ($type_id == 5) echo 'value="' . $_SESSION['PrenomUsers'] . ' ' . $_SESSION['NomUsers'] . '" readonly'; ?>>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group has-error">
                        <label class="control-label">Téléphone *</label>
                        <input type="text" class="form-control" name="TelDeposant" id="TelDeposant" required
                            placeholder="Téléphone" autocomplete="OFF"
                            <?php if ($type_id == 5) echo 'value="Opération Interne" readonly'; ?>>
                    </div>
                </div>

                <?php if (in_array(3, $permission)) : ?>
                <div class="col-md-6">
                    <div class="form-group has-error">
                        <label class="control-label">Antidater l'opération</label>
                        <input type="date" class="form-control" name="Antidate">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group has-error">
                        <label class="control-label">Pays</label>
                        <select name="RefPays" class="form-control">
                            <option value="">Veuillez Choisir le Pays</option>
                            <?php foreach ($ListePays as $value) : ?>
                            <option value="<?= $value['RefPays']; ?>"><?= $value['nomPays']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div id="alertContainer" class="alert alert-danger" style="display: none; color: red; margin-top: 15px;">
            </div>
            <hr>
            <button type="submit" class="btn btn-primary">Valider l'Opération</button>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script>
$(document).ready(function() {
    function calculateTotal() {
        let grandTotal = 0;
        $('#bielletage-body tr').each(function() {
            const row = $(this);
            const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
            const denomination = parseFloat(row.find('input[type=hidden]').val()) || 0;
            const amount = quantity * denomination;
            row.find('.amount-output').val(amount.toLocaleString());
            grandTotal += amount;
        });
        $('#grand-total').text(grandTotal.toLocaleString());
        $('#total').val(grandTotal);
    }

    $('#bielletage-body').on('input', '.quantity-input', calculateTotal);

    $('#RefCompte').on('change', function() {
        const refCompte = $(this).val();
        if (refCompte) {
            $.ajax({
                url: "/bielletage/SoldeCompte",
                type: "POST",
                data: {
                    RefCompte: refCompte
                },
                success: function(response) {
                    const data = JSON.parse(response);
                    $('#SoldeCompte').val(data.Solde);
                    $('#TelDeposant').val(data.Tel);
                }
            });
        }
    });

    $('#bielletage-form').on('submit', function(e) {
        const total = parseFloat($('#total').val()) || 0;
        const solde = parseFloat($('#SoldeCompte').val()) || 0;
        const typeRetrait = $('#TypeRetrait').val();

        if (typeRetrait === '1' && total > solde) {
            e.preventDefault();
            $('#alertContainer').text(
                'Le montant du retrait ne peut pas être supérieur au solde du compte.').show();
        } else {
            $('#alertContainer').hide();
        }
    });

    calculateTotal(); // Initial calculation
});
</script>
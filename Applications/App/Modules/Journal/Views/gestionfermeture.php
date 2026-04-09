<div class="row">
    <div class="col-md-12">
        <div class="white-box">
            <h3 class="box-title">Gestion des Fermetures - Vue Journalière</h3>

            <form method="POST" id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <label>Agence</label>
                        <select name="RefAgency" id="RefAgency" class="form-control">
                            <?php foreach ($Agence as $agency): ?>
                            <option value="<?= $agency['RefAgency']; ?>"
                                <?= isset($selectedAgency) && $selectedAgency == $agency['RefAgency'] ? 'selected' : ''; ?>>
                                <?= $agency['NameAgency']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Mois</label>
                        <select name="month" id="month" class="form-control">
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i; ?>" <?= isset($month) && $month == $i ? 'selected' : ''; ?>>
                                <?= date('F', mktime(0, 0, 0, $i, 1)); ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Année</label>
                        <select name="year" id="year" class="form-control">
                            <?php for ($y = date('Y'); $y >= date('Y') - 2; $y--): ?>
                            <option value="<?= $y; ?>" <?= isset($year) && $year == $y ? 'selected' : ''; ?>>
                                <?= $y; ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-search"></i> Charger
                        </button>
                    </div>
                </div>
            </form>
            <br />
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="white-box">
            <div class="table-responsive">
                <table id="closureTable" class="display nowrap" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Jour</th>
                            <th>Statut</th>
                            <th>Solde</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="closureBody">
                        <tr>
                            <td colspan="5" class="text-center">Sélectionnez une agence et cliquez sur Charger</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour fermer une agence -->
<div class="modal fade" id="closeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Fermer l'agence pour cette journée</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="closeForm" method="POST" action="/Journal/fermerAgence">
                    <input type="hidden" name="RefAgency" id="modalRefAgency">
                    <input type="hidden" name="date" id="modalDate">
                    <input type="hidden" name="SoldeActuelle" id="modalSolde">

                    <div class="form-group">
                        <label>Date</label>
                        <input type="text" id="modalDateDisplay" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Solde Actuelle</label>
                        <input type="number" name="SoldeActuelle" id="modalSoldeInput" class="form-control" required>
                        <small class="text-muted">Le solde sera calculé automatiquement si disponible</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" onclick="submitClose()">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
// Wait for jQuery to be loaded
function waitForJQuery(callback) {
    if (typeof $ !== 'undefined') {
        callback();
    } else {
        setTimeout(function() {
            waitForJQuery(callback);
        }, 100);
    }
}

waitForJQuery(function() {
    $(document).ready(function() {
        // Charger les données initiales si une agence est sélectionnée
        if ($('#RefAgency').val()) {
            loadClosureData();
        }

        // Recharger quand le formulaire est soumis
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            loadClosureData();
        });
    });
});

function loadClosureData() {
    const agency = $('#RefAgency').val();
    const month = $('#month').val();
    const year = $('#year').val();

    if (!agency) return;

    $.ajax({
        url: '/Journal/getClosureStatus',
        type: 'POST',
        data: {
            RefAgency: agency,
            month: month,
            year: year
        },
        success: function(response) {
            renderClosureTable(response, year, month);
        },
        error: function(xhr) {
            console.error('Erreur:', xhr.responseText);
        }
    });
}

function renderClosureTable(data, year, month) {
    const closures = data.closures || {};
    const daysWithOps = data.daysWithOps || {};

    const daysInMonth = new Date(year, month, 0).getDate();
    let html = '';

    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = year + '-' + String(month).padStart(2, '0') + '-' + String(day).padStart(2, '0');
        const dateObj = new Date(year, month - 1, day);
        const dayName = dateObj.toLocaleDateString('fr-FR', {
            weekday: 'long'
        });

        let status = '';
        let solde = '-';
        let action = '';

        if (closures[day]) {
            // Fermé
            status = '<span class="badge badge-success">Fermé</span>';
            solde = number_format(closures[day].SoldeCompte, 0, '.', ' ');
            action = `<a href="/Arreter/cancel/${closures[day].RefCompte}/${$('#RefAgency').val()}/${dateStr}" 
                        class="btn btn-warning btn-sm" 
                        data-toggle="tooltip" 
                        title="Réouvrir">
                        <i class="fa fa-unlock"></i> Annuler
                     </a>`;
        } else if (daysWithOps[day]) {
            // Non fermé mais avec opérations
            status = '<span class="badge badge-danger">Non fermé</span>';
            action = `<button type="button" 
                        class="btn btn-danger btn-sm" 
                        onclick="showCloseModal('${dateStr}')"
                        data-toggle="tooltip" 
                        title="Fermer">
                        <i class="fa fa-lock"></i> Fermer
                     </button>`;
        } else {
            // Pas d'activité
            status = '<span class="badge badge-secondary">Pas d\'activité</span>';
        }

        html += `
            <tr>
                <td>${dateStr}</td>
                <td>${dayName.charAt(0).toUpperCase() + dayName.slice(1)}</td>
                <td>${status}</td>
                <td>${solde}</td>
                <td>${action}</td>
            </tr>
        `;
    }

    $('#closureBody').html(html);

    // Initialiser DataTable
    if ($.fn.DataTable.isDataTable('#closureTable')) {
        $('#closureTable').DataTable().destroy();
    }
    $('#closureTable').DataTable({
        order: [
            [0, 'desc']
        ],
        language: {
            search: "Rechercher:",
            lengthMenu: "Afficher _MENU_ entrées",
            info: "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
            paginate: {
                first: "Premier",
                last: "Dernier",
                next: "Suivant",
                previous: "Précédent"
            }
        }
    });
}

function showCloseModal(date) {
    $('#modalRefAgency').val($('#RefAgency').val());
    $('#modalDate').val(date);
    $('#modalDateDisplay').val(date);
    $('#modalSoldeInput').val('');

    // Récupérer le solde actuel si possible (AJAX)
    $.ajax({
        url: '/Journal/petite_caisse/data',
        type: 'POST',
        data: {
            date: date,
            refAgency: $('#RefAgency').val()
        },
        success: function(response) {
            if (response.ReserveActuelle !== undefined) {
                $('#modalSolde').val(response.ReserveActuelle);
                $('#modalSoldeInput').val(response.ReserveActuelle);
            }
        }
    });

    $('#closeModal').modal('show');
}

function submitClose() {
    const solde = $('#modalSoldeInput').val();
    if (!solde) {
        alert('Veuillez entrer le solde');
        return;
    }

    $('#modalSolde').val(solde);
    $('#closeForm').submit();
}

function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function(n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}
</script>
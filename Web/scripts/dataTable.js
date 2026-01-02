$(document).ready(function() {
    // Configuration DataTables par défaut
    $.extend(true, $.fn.dataTable.defaults, {
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
        },
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Tous']],
        dom: '<"top"Bf>rt<"bottom"lip>',
        buttons: [
            { extend: 'copy', className: 'btn btn-sm btn-outline-secondary' },
            { extend: 'csv', className: 'btn btn-sm btn-outline-secondary' },
            { extend: 'excel', className: 'btn btn-sm btn-outline-secondary' },
            { extend: 'print', className: 'btn btn-sm btn-outline-secondary' }
        ],
        responsive: true,
        autoWidth: false,
        initComplete: function() {
            var api = this.api();
            api.$('td').css('cursor', 'pointer');
        }
    });

    // Initialiser toutes les tables avec ID #dataTable ou #example
    $('#dataTable, #example').DataTable({
        columnDefs: [
            { targets: 'no-sort', orderable: false },
            { targets: 'text-center', className: 'text-center' },
            { targets: 'text-right', className: 'text-right' }
        ],
        order: [[0, 'desc']]
    });

    // Tables avec regroupement
    $('#dataTable-group').DataTable({
        columnDefs: [{
            visible: false,
            targets: 1
        }],
        order: [[1, 'asc']],
        drawCallback: function(settings) {
            var api = this.api();
            var rows = api.rows({ page: 'current' }).nodes();
            var last = null;

            api.column(1, { page: 'current' }).data().each(function(group, i) {
                if (last !== group) {
                    $(rows).eq(i).before(
                        '<tr class="group"><td colspan="' + api.columns().count() + '" class="bg-light font-weight-bold">' +
                        group + '</td></tr>'
                    );
                    last = group;
                }
            });
        }
    });

    // Tables simples sans boutons
    $('.dataTable-simple').DataTable({
        dom: 'rt<"bottom"lip>',
        buttons: []
    });

    // Gestion du clic sur les lignes pour les tables avec liens
    $('#dataTable tbody').on('click', 'tr', function() {
        var link = $(this).data('href');
        if (link) {
            window.location.href = link;
        }
    });

    // Export CSV personnalisé
    $.fn.dataTable.ext.buttons.csvHtml5 = {
        className: 'btn btn-sm btn-outline-secondary',
        text: '<i class="fas fa-file-csv"></i> CSV',
        action: function(e, dt, button, config) {
            dt.buttons.exportData($.extend({ body: null }, config));
        }
    };
});

// Fonction utilitaire pour formater les montants
function formatMontant(value) {
    if (!value) return '0';
    return new Intl.NumberFormat('fr-FR').format(value);
}

// Fonction utilitaire pour les dates
function formatDate(dateString) {
    if (!dateString) return '';
    var date = new Date(dateString);
    return date.toLocaleDateString('fr-FR');
}

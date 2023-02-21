$(function () {
    const $pays = $('#RefPays');
    const $agence = $('#RefAgency');
    const $caisse = $('#RefCaisse');

    // Update the available agencies when the selected country changes
    $pays.on('change', function () {
        const val = $(this).val();
        if (val != null) {
            $agence.empty();
            $caisse.empty(); // Clear the caisse select when changing the pays select
        }

        // Make an AJAX request to get the available agencies
        $.ajax({
            url: '/config/requeteAgence.php',
            data: { Pays: val },
            dataType: 'json',
            success: function (data) {
                $agence.empty().append('<option value="">Agence</option>');

                // Add each agency as an option
                $.each(data, function (index, value) {
                    $agence.append(`<option value="${index}">${value}</option>`);
                });
            },
            error: function () {
                console.error('Failed to load agencies');
            }
        });
    });

    // Update the available caisses when the selected agency changes
    $agence.on('change', function () {
        const val = $(this).val();
        if (val != null) {
            $caisse.empty(); // Clear the caisse select when changing the agency select
        }

        // Make an AJAX request to get the available caisses
        $.ajax({
            url: '/config/requeteCaisse.php',
            data: { Agence: val },
            dataType: 'json',
            success: function (data) {
                $caisse.empty().append('<option value="">Caisse</option>');

                // Add each caisse as an option
                $.each(data, function (index, value) {
                    $caisse.append(`<option value="${index}">${value}</option>`);
                });
            },
            error: function () {
                console.error('Failed to load caisses');
            }
        });
    });
});

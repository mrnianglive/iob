$(function () {
    const $pays = $('#RefPays');
    const $agence = $('#RefAgency');
    const $caisse = $('#RefCaisse');

    // Cache options
    const agenceOptions = $agence.children().clone();
    const caisseOptions = $caisse.children().clone();

    // Update the available agencies when the selected country changes
    $pays.on('change', function () {
        const val = $(this).val();

        // Clear the caisse select when changing the pays select
        $caisse.empty();

        // Make an AJAX request to get the available agencies
        $.ajax({
            url: '/config/requeteAgence.php',
            data: { Pays: val },
            dataType: 'json',
            success: function (data) {
                // Build options using a DocumentFragment
                const fragment = document.createDocumentFragment();
                fragment.appendChild($('<option value="">Agence</option>')[0]);

                $.each(data, function (index, value) {
                    const $option = $(`<option value="${index}">${value}</option>`);
                    // Set the "data-desired-agency" attribute for the desired option
                    if ($option.val() === $agence.data('desired-agency')) {
                        $option.attr('data-desired-agency', $agence.data('desired-agency'));
                    }
                    fragment.appendChild($option[0]);
                });
                // Replace options
                $agence.empty().append(fragment);

                // Check if the desired agency option is available and set it as selected
                const desiredAgencyValue = $agence.data('desired-agency');
                const desiredAgencyOption = $agence.find(`option[value="${desiredAgencyValue}"]`);
                if (desiredAgencyOption.length > 0) {
                    desiredAgencyOption.prop('selected', true);
                } else {
                    // Restore previous selection if available
                    if ($agence.data('index')) {
                        $agence.val($agence.data('index'));
                    }
                }
            },
            error: function () {
                console.error('Failed to load agencies');
            }
        });
    });

    // Update the available caisses when the selected agency changes
    $agence.on('change', function () {
        const val = $(this).val();

        // Clear the caisse select when changing the agency select
        $caisse.empty();

        // Make an AJAX request to get the available caisses
        $.ajax({
            url: '/config/requeteCaisse.php',
            data: { Agence: val },
            dataType: 'json',
            success: function (data) {
                // Build options using a DocumentFragment
                const fragment = document.createDocumentFragment();
                fragment.appendChild($('<option value="">Caisse</option>')[0]);

                $.each(data, function (index, value) {
                    const $option = $(`<option value="${index}">${value}</option>`);
                    // Set the "data-desired-caisse" attribute for the desired option
                    if ($option.val() === $caisse.data('desired-caisse')) {
                        $option.attr('data-desired-caisse', $caisse.data('desired-caisse'));
                    }
                    fragment.appendChild($option[0]);
                });

                // Replace options
                $caisse.empty().append(fragment);

                // Check if the desired caisse option is available and set it as selected
                const desiredCaisseValue = $caisse.data('desired-caisse');
                const desiredCaisseOption = $caisse.find(`option[value="${desiredCaisseValue}"]`);
                if (desiredCaisseOption.length > 0) {
                    desiredCaisseOption.prop('selected', true);
                } else {
                    // Restore previous selection if available
                    if ($caisse.data('index')) {
                        $caisse.val($caisse.data('index'));
                    }
                }
            },
            error: function () {
                console.error('Failed to load caisses');
            }
        });
    })

});


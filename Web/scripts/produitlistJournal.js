$(function () {
    var $Caisse = $('#RefAgency');
    var $liste = $('#RefProduit');
    $Caisse.on('click', function () {
        var val = $(this).val();
        if (val != null) $liste.empty();
        $.ajax({
            url: '/config/requetelisteJournal.php',
            data: 'Caisse=' + val,
            dataType: 'json',
            success: function (json) {
                $.each(json, function (index, value) {
                    $liste.append('<option value="' + index + '">' + value + '</option>');
                });
            }
        });

    })

});

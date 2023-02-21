$(function () {
    var $Pays = $('#RefPays');
    var $Agence = $('#RefAgency');
    $Pays.on('change', function () {
        var val = $(this).val();
        if (val != null) $Agence.empty();
        $.ajax({
            url: '/config/requeteAgence.php',
            data: 'Pays=' + val,
            dataType: 'json',
            success: function (json) {
                $Agence.append('<option value="">Agence');
                $.each(json, function (index, value) {
                    $Agence.append('<option value="' + index + '">' + value + '</option>');
                });
            }
        });

    })

});

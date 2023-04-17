$(function () {
    var $Agency = $('#RefAgency');
    var $liste = $('#RefProduit');
    $Agency.on('click', function () {
        var val = $(this).val();
        if (val != null) $liste.empty();
        $.ajax({
            url: '/config/requetelisteJournal.php',
            data: 'Agency=' + val,
            dataType: 'json',
            success: function (json) {
                $.each(json, function (index, value) {
                    $liste.append('<option value="' + index + '">' + value + '</option>');
                });
            }
        });

    })

});

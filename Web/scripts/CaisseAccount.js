$(function () {
    var $RefCaisse = $('#RefCaisse');
    var $NumCompte = $('#NumCompte');
    $RefCaisse.on('click', function () {
        var val = $(this).val();
        if (val != null) $NumCompte.empty();
        $.ajax({
            url: '/config/NumCaisse.php',
            data: 'RefCaisse=' + val,
            dataType: 'json',
            success: function (json) {
                if (json != null) {
                    $NumCompte.val(json['NUMCOMPTE']);
                } else {
                    $NumCompte.val('');
                }
            }
        });
    })
});
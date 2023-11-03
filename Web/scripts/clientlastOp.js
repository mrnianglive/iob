$(function () {
    var $NumCompte = $('#NumCompte');
    var $NameClient = $('#NameClient');
    var $MontantVersement = $('#total');
    var $alertContainer = $('#alert-container');

    $NumCompte.on('change', function () {
        var val = $(this).val();
        var montant = $MontantVersement.val();

        if (val != null) $NameClient.empty();

        $.ajax({
            url: '/config/clientlastOp.php',
            data: {
                'NumCompte': val,
                'MontantVersement': montant
            },
            dataType: 'json',
            method: 'POST',
            success: function (json) {
                if (json && json.message) {
                    $alertContainer.text(json.message).fadeIn();
                } else {
                    $alertContainer.fadeOut();
                }

                if (json && json.nameClient) {
                    $NameClient.val(json.nameClient);
                } else {
                    $NameClient.val('');
                }
            }
        });
    });
});

$(function () {
    var $NumCompte = $('#NumCompte');
    var $NameClient = $('#NameClient');
    var $alertContainer = $('#alert-container');

    $NumCompte.on('change', function () {
        var val = $(this).val();
        if (val != null) $NameClient.empty();

        $.ajax({
            url: '/config/clientlastOp.php',
            data: 'NumCompte=' + val,
            dataType: 'json',
            success: function (json) {
                if (json && json.message) {
                    $alertContainer.text(json.message).fadeIn(); // Affiche le message dans le conteneur.
                } else {
                    $alertContainer.fadeOut(); // Cache le conteneur si il n'y a pas de message.
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

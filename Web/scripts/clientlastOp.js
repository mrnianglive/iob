$(function () {
    var $NumCompte = $('#NumCompte');
    var $MontantVersement = $('#total'); // Assurez-vous d'avoir un champ pour entrer le montant du versement
    var $alertContainer = $('#alertContainer'); // Cet élément HTML affichera l'alerte

    // Cette fonction sera appelée lorsque la valeur du champ NumCompte changera
    $NumCompte.on('change', function () {
        var numCompteVal = $(this).val();
        var montantVal = $MontantVersement.val(); // Récupère la valeur du montant du versement
        if (numCompteVal) {
            $.ajax({
                url: '/config/clientlastOp.php',
                type: 'GET',
                data: {
                    NumCompte: numCompteVal,
                    MontantVersement: montantVal
                },
                dataType: 'json',
                success: function (response) {
                    var messageContainer = $('#alertContainer');
                    if (response.message.includes("Alerte")) {
                        messageContainer.css('color', 'red');
                    } else {
                        messageContainer.css('color', 'green');
                    }
                    messageContainer.text(response.message).show();
                },
                error: function (xhr, status, error) {
                    console.error("An error occurred: " + error);
                    $alertContainer.text("Erreur lors de la récupération de l'alerte.").show();
                }
            });
        }
    });
});

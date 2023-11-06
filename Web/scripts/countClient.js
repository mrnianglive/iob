$(document).ready(function () {
    $('.account').click(function () {
        var accountNumber = $(this).data('account');
        var $this = $(this);

        $.ajax({
            url: '/config/clientCount.php',
            type: 'GET',
            data: { 'NumCompte': accountNumber },
            success: function (data) {
                // Vérifiez la réponse en console pour le débogage
                console.log(data);

                // Assurez-vous que la réponse est au format JSON
                try {
                    var response = JSON.parse(data);
                    var message = `(${response.count}) ${accountNumber}`;
                    $this.text(message);
                } catch (e) {
                    console.error("Erreur d'analyse JSON : " + e);
                }
            },
            error: function (xhr, status, error) {
                console.error("Erreur AJAX : " + error);
            }
        });
    });
});

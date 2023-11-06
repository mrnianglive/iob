$(document).ready(function () {
    $('.account').hover(function () {
        var accountNumber = $(this).data('account');
        var $this = $(this);

        // Fermer les popovers précédents
        $('.account').not($this).popover('hide');

        $.ajax({
            url: '/config/clientCount.php',
            type: 'GET',
            data: { 'NumCompte': accountNumber },
            success: function (data) {
                // Vérifiez la réponse en console pour le débogage

                // Assurez-vous que la réponse est au format JSON
                try {
                    var response = JSON.parse(data);
                    $this.attr('data-content', response.message);
                    $this.popover('show');
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

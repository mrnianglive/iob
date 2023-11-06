$(document).ready(function () {
    $('.account').click(function () {
        var accountNumber = $(this).data('account');
        var $this = $(this);

        if ($this.data('original-content') === undefined) {
            // Sauvegardez le contenu d'origine
            $this.data('original-content', $this.text());

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
                        console.log(response);

                        if (response.count !== undefined) {
                            var message = `(${response.count}) ${accountNumber}`;
                            $this.text(message);
                        } else {
                            console.error("Champ 'count' manquant dans la réponse JSON.");
                        }
                    } catch (e) {
                        console.error("Erreur d'analyse JSON : " + e);
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Erreur AJAX : " + error);
                }
            });
        } else {
            // Restaurez le contenu d'origine
            $this.text($this.data('original-content'));
            $this.removeData('original-content');
        }
    });
});

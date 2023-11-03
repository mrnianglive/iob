$(function () {
    var $NumCompte = $('#NumCompte');
    var $NameClient = $('#NameClient');
    var $AlertMessage = $('#AlertMessage'); // Ajout d'une référence pour l'alerte

    $NumCompte.on('change', function () {
        var val = $(this).val();
        $NameClient.empty();
        $AlertMessage.empty(); // Vider le message précédent

        if (val) {
            $.ajax({
                url: '/config/clientlastOp.php', // Assurez-vous que l'URL est correcte
                data: { NumCompte: val }, // Envoyer les données comme un objet
                dataType: 'json',
                success: function (response) {
                    if (response.nameClient) {
                        $NameClient.val(response.nameClient);
                    } else {
                        $NameClient.val(''); // Vider le champ si aucune donnée n'est reçue
                    }

                    // Vérifier et afficher le message d'alerte si présent
                    if (response.message) {
                        $AlertMessage.text(response.message).show();
                    } else {
                        $AlertMessage.hide();
                    }
                },
                error: function (xhr, status, error) {
                    // Gérer l'erreur
                    console.error("Erreur AJAX: " + status + " - " + error);
                }
            });
        }
    });
});

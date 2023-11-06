$(document).ready(function () {
    $('.account').hover(function () {
        var accountNumber = $(this).data('account');
        var $this = $(this); // Stockez une référence à l'élément "account" survolé

        $.ajax({
            url: '/config/clientCount.php',
            type: 'POST',
            data: { 'NumCompte': accountNumber },
            success: function (data) {
                // Mettez à jour le contenu du popover
                $this.attr('data-content', data);
                $this.popover('show');
            }
        });
    });
});

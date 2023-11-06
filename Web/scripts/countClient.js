$(document).ready(function () {
    $('.account').hover(function () {
        var accountNumber = $(this).data('account');
        $.ajax({
            url: '/config/clientCount.php',
            type: 'POST',
            data: { 'NumCompte': accountNumber },
            success: function (data) {
                alert(data);
            }
        });
    });
});
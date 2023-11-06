$(document).ready(function () {
    $('.account').hover(function () {
        var accountNumber = $(this).data('account');
        $.ajax({
            url: '/config/clientCount.php',
            type: 'POST',
            data: { 'Nucompte': accountNumber },
            success: function (data) {
                alert(data);
            }
        });
    });
});
$(function () {
    var $RefCaisse = $('#RefCaisse');
    var $NumCompte = $('#NumCompte');
   var $NameClient = $('#NameClient');

    //var Name;
    
    $RefCaisse.on('click', function () {
        var val = $(this).val();
        if (val != null) $NumCompte.empty();
        $.ajax({
            url: '/config/NumCaisse.php',
            data: 'RefCaisse=' + val,
            dataType: 'json',
            success: function (json) {
                if (json != null) {
                    $NumCompte.val(json['NUMCOMPTE']);
                    // Name = json['NUMCOMPTE'];
                } else {
                    $NumCompte.val('');
                }
            }
        });

                 $.ajax({
            url: '/config/client.php',
            data: 'NumCompte=' + Name,
            dataType: 'json',
            success: function (json) {
                if (json != null) {
                    $NameClient.val(json);
                } else {
                    $NameClient.val('');
                }
            }
        });
  
    })
});
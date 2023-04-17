$(function () {
    var $Caisse = $('#RefCaisse');
    var $liste = $('#RefProduit');
    $Caisse.on('change', function () {
        var val = $(this).val();
        if (val != null) $liste.empty();
        $.ajax({
            url: '/config/requeteliste.php',
            data: 'Caisse=' + val,
            dataType: 'json',
            success: function (json) {
                $.each(json, function (index, value) {
                    var $produit = $('<div>').addClass('col-md-4 mb-4');
                    var $formCheck = $('<div>').addClass('form-check');
                    var $inputRadio = $('<input>').addClass('form-check-input').attr({
                        'type': 'radio',
                        'name': 'produit',
                        'id': 'produit' + index,
                        'value': index
                    });
                    var $label = $('<label>').addClass('form-check-label d-block bg-light p-3 rounded-circle').attr('for', 'produit' + index);
                    var $circleImg = $('<div>').addClass('circle-img');
                    var $img = $('<img>').attr({
                        'src': 'https://via.placeholder.com/150',
                        'alt': value,
                        'class': 'mx-auto mb-2'
                    });
                    var $h5 = $('<h5>').addClass('mb-0').text(value);

                    $label.append($circleImg.append($img)).append($h5);
                    $formCheck.append($inputRadio).append($label);
                    $produit.append($formCheck);
                    $liste.append($produit);
                });
            }
        });
    });
});

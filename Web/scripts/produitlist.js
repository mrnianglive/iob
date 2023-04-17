$(function () {
    var $Caisse = $('#RefCaisse');
    var $liste = $('#RefProduit');
    $Caisse.on('click', function () {
        var val = $(this).val();
        if (val != null) $liste.empty();
        $.ajax({
            url: '/config/requeteliste.php',
            data: 'Caisse=' + val,
            dataType: 'json',
            success: function (json) {
                $.each(json, function (index, value) {
                    var produitHtml = `
                        <div class="col-md-6 col-lg-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="produit" id="produit${index}" value="${index}">
                                <label class="form-check-label" for="produit${index}">
                                    <img src="https://via.placeholder.com/150" alt="${value}" class="rounded-circle circle-img mx-auto mb-2">
                                    <h5 class="mb-0">${value}</h5>
                                </label>
                            </div>
                        </div>
                    `;
                    $liste.append(produitHtml);
                });
            }
        });
    });
});

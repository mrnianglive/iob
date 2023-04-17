$(function () {
    var $Caisse = $('#RefCaisse');
    var $liste = $('#ProduitList');
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
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="RefProduit" id="produit${index}" value="${index}">
                            <label class="form-check-label d-block bg-light p-3 rounded-circle" for="produit${index}">
                                <div class="circle-img">
                                    <img src="https://via.placeholder.com/150" alt="${value}" class="mx-auto mb-2">
                                </div>
                                <h5 class="mb-0">${value}</h5>
                            </label>
                        </div>
                    `;
                    $liste.append(produitHtml);
                });
            }
        });
    });
});

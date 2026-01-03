/**
 * Billetage - Calcul automatique des montants
 */
$(document).ready(function() {
    console.log('Billetage.js chargé');

    // Fonction de calcul du total
    function calculateTotals() {
        var grandTotal = 0;

        // Parcourir tous les inputs de quantité avec classe qty-input
        $('.qty-input').each(function() {
            var $input = $(this);
            var qty = parseInt($input.val()) || 0;
            var value = parseInt($input.attr('data-value')) || 0;
            var subtotal = qty * value;
            var id = $input.attr('id').charAt(0);

            // Mettre à jour l'affichage du sous-total (div)
            $('#' + id + '3').text(formatNumber(subtotal));
            // Mettre à jour l'input hidden
            $('#' + id + '3_hidden').val(subtotal);

            grandTotal += subtotal;
        });

        // Mettre à jour le total général
        $('#grandTotal').text(formatNumber(grandTotal));
        $('#totalInput').val(grandTotal);

        // Calcul des frais si c'est un retrait
        updateFrais(grandTotal);

        console.log('Total calculé:', grandTotal);
    }

    // Formater les nombres avec séparateurs
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    // Mise à jour des frais (retraits)
    function updateFrais(total) {
        var $frais = $('#frais');
        var $mtotal = $('#mtotal');

        if ($frais.length && $mtotal.length) {
            var fraisTimbre = parseInt($('#fraisTimbre').val()) || 0;
            var fraisVal = 0; // Les frais sont généralement 0
            var netAPayer = total - fraisVal - fraisTimbre;

            $frais.val(formatNumber(fraisVal));
            $mtotal.val(formatNumber(netAPayer));
        }
    }

    // Attacher l'événement sur tous les inputs qty-input
    $(document).on('input keyup change', '.qty-input', function() {
        calculateTotals();
    });

    // Navigation clavier (Enter -> champ suivant)
    $(document).on('keydown', '.qty-input', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            var inputs = $('.qty-input');
            var currentIndex = inputs.index(this);
            if (currentIndex < inputs.length - 1) {
                inputs.eq(currentIndex + 1).focus().select();
            }
        }
    });

    // Chargement des produits selon la caisse
    function loadProducts() {
        var caisseId = $('#RefCaisse').val();
        var $produitSelect = $('#RefProduit');

        if (caisseId && $produitSelect.length) {
            $.get('/config/requeteliste.php', { Caisse: caisseId }, function(data) {
                // Parser le JSON et créer les options
                var options = '<option value="">Sélectionner un produit</option>';

                if (typeof data === 'string') {
                    try {
                        data = JSON.parse(data);
                    } catch (e) {
                        console.error('Erreur parsing JSON:', e);
                        return;
                    }
                }

                // data = { "1": {"nom": "Ecobank", "image": "..."}, ... }
                for (var refProduit in data) {
                    if (data.hasOwnProperty(refProduit)) {
                        var produit = data[refProduit];
                        var nom = produit.nom || produit[0] || 'Produit ' + refProduit;
                        options += '<option value="' + refProduit + '">' + nom + '</option>';
                    }
                }

                $produitSelect.html(options);

                // Avec Tailwind CSS, les selects natifs fonctionnent correctement
                // Pas besoin de forcer les styles, mais on s'assure que la classe Tailwind est appliquée
                $produitSelect.addClass('text-gray-900 bg-white');

                // Si un seul produit, le sélectionner automatiquement
                var nbProduits = Object.keys(data).length;
                if (nbProduits === 1) {
                    $produitSelect.find('option:last').prop('selected', true).trigger('change');
                }
            });
        }
    }

    // Événement changement de caisse (pour select)
    $(document).on('change', 'select#RefCaisse', loadProducts);

    // Charger les produits au démarrage (pour select ou input hidden)
    if ($('#RefCaisse').length && $('#RefProduit').length) {
        // Petit délai pour s'assurer que le DOM est prêt
        setTimeout(loadProducts, 100);
    }

    // Afficher/masquer le numéro de compte selon le produit
    $(document).on('change', '#RefProduit', function() {
        var $group = $('#numCompteGroup');
        if ($group.length) {
            if ($(this).val() == '1') {
                $group.removeClass('hidden').addClass('block');
                $('#NumCompte').prop('required', true);
            } else {
                $group.removeClass('block').addClass('hidden');
                $('#NumCompte').prop('required', false);
            }
        }
    });

    // Validation avant soumission
    $(document).on('submit', '#operationForm', function(e) {
        var total = parseInt($('#totalInput').val()) || 0;

        if (total <= 0) {
            e.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Montant invalide',
                    text: 'Le montant total doit être supérieur à 0'
                });
            } else {
                alert('Le montant total doit être supérieur à 0');
            }
            return false;
        }
    });

    // Focus initial sur le premier champ
    setTimeout(function() {
        if ($('.qty-input').length) {
            $('.qty-input').first().focus();
        }
    }, 300);

    // Calcul initial
    calculateTotals();
});
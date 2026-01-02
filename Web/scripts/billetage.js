$(function () {
    var $total = $('#total');
    var $type = $('#TypeRetrait');
    var $mtotal = $('#mtotal');
    var $mfrais = 0;
    var $frais = 0;
    var $fraisTimbre = $('#fraisTimbre');
    var $fraisM = $('#frais');

    function calculateTotal() {
        var sum = 0;
        for (var i = 97; i <= 109; i++) {
            var letter = String.fromCharCode(i);
            var subtotal = Number($('#' + letter + '3').val()) || 0;
            sum += subtotal;
        }
        $total.val(sum);
        $mfrais = sum * $frais;
        $mtotal.val(sum - $mfrais - Number($fraisTimbre.val()));
        $fraisM.val($mfrais);
    }

    function initBilletageInput(letter) {
        $('#' + letter + '2').on('change input', function () {
            var qty = Number($(this).val()) || 0;
            var value = Number($('#' + letter + '1').val()) || 0;
            $('#' + letter + '3').val(qty * value);
            calculateTotal();
        });
    }

    for (var i = 97; i <= 109; i++) {
        initBilletageInput(String.fromCharCode(i));
    }

    $type.on('change', function () {
        $frais = 0;
        $mfrais = $total.val() * $frais;
        $mtotal.val($total.val() - $mfrais - Number($fraisTimbre.val()));
        $fraisM.val($mfrais);
    });
});

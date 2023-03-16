
$(function () {
  var $total = $('#total');
  var $type = $('#TypeRetrait');
  var $mtotal = $('#mtotal');
  var $mfrais = 0;
  var $frais = 0;
  $fraisM = $('#frais');

  $type.on('change', function () {
    //if ($type.val() == 2) {
    //  $frais = 0.01;

    // } else {
    $frais = 100;
    // }
    $mfrais = $total.val() * $frais;
    $mtotal.val($total.val() - $mfrais);
    $fraisM.val($mfrais);

  });

  var $a1 = $('#a1');
  var $a2 = $('#a2');
  var $a3 = $('#a3');
  $a2.on('change', function () {
    $a3.val($a1.val() * $a2.val());
    $total.val(Number($a3.val()) + Number($b3.val()) + Number($c3.val()) + Number($d3.val()) + Number($e3.val()) + Number($f3.val()) + Number($g3.val()) + Number($h3.val()) + Number($i3.val()) + Number($j3.val()) + Number($k3.val()) + Number($l3.val()) + Number($m3.val()));
    $mfrais = $total.val() * $frais;
    $mtotal.val($total.val() - $mfrais);
    $fraisM.val($mfrais);


  });
  var $b1 = $('#b1');
  var $b2 = $('#b2');
  var $b3 = $('#b3');
  $b2.on('change', function () {
    $b3.val($b1.val() * $b2.val());
    $total.val(Number($a3.val()) + Number($b3.val()) + Number($c3.val()) + Number($d3.val()) + Number($e3.val()) + Number($f3.val()) + Number($g3.val()) + Number($h3.val()) + Number($i3.val()) + Number($j3.val()) + Number($k3.val()) + Number($l3.val()) + Number($m3.val()));
    $mfrais = $total.val() * $frais;
    $mtotal.val($total.val() - $mfrais);
    $fraisM.val($mfrais);
  });

  var $c1 = $('#c1');
  var $c2 = $('#c2');
  var $c3 = $('#c3');
  $c2.on('change', function () {
    $c3.val($c1.val() * $c2.val());
    $total.val(Number($a3.val()) + Number($b3.val()) + Number($c3.val()) + Number($d3.val()) + Number($e3.val()) + Number($f3.val()) + Number($g3.val()) + Number($h3.val()) + Number($i3.val()) + Number($j3.val()) + Number($k3.val()) + Number($l3.val()) + Number($m3.val()));
    $mfrais = $total.val() * $frais;
    $mtotal.val($total.val() - $mfrais);
    $fraisM.val($mfrais);
  });

  var $d1 = $('#d1');
  var $d2 = $('#d2');
  var $d3 = $('#d3');
  $d2.on('change', function () {
    $d3.val($d1.val() * $d2.val());
    $total.val(Number($a3.val()) + Number($b3.val()) + Number($c3.val()) + Number($d3.val()) + Number($e3.val()) + Number($f3.val()) + Number($g3.val()) + Number($h3.val()) + Number($i3.val()) + Number($j3.val()) + Number($k3.val()) + Number($l3.val()) + Number($m3.val()));
    $mfrais = $total.val() * $frais;
    $mtotal.val($total.val() - $mfrais);
    $fraisM.val($mfrais);
  });

  var $e1 = $('#e1');
  var $e2 = $('#e2');
  var $e3 = $('#e3');
  $e2.on('change', function () {
    $e3.val($e1.val() * $e2.val());
    $total.val(Number($a3.val()) + Number($b3.val()) + Number($c3.val()) + Number($d3.val()) + Number($e3.val()) + Number($f3.val()) + Number($g3.val()) + Number($h3.val()) + Number($i3.val()) + Number($j3.val()) + Number($k3.val()) + Number($l3.val()) + Number($m3.val()));
    $mfrais = $total.val() * $frais;
    $mtotal.val($total.val() - $mfrais);
    $fraisM.val($mfrais);

  });

  var $f1 = $('#f1');
  var $f2 = $('#f2');
  var $f3 = $('#f3');
  $f2.on('change', function () {
    $f3.val($f1.val() * $f2.val());
    $total.val(Number($a3.val()) + Number($b3.val()) + Number($c3.val()) + Number($d3.val()) + Number($e3.val()) + Number($f3.val()) + Number($g3.val()) + Number($h3.val()) + Number($i3.val()) + Number($j3.val()) + Number($k3.val()) + Number($l3.val()) + Number($m3.val()));
    $mfrais = $total.val() * $frais;
    $mtotal.val($total.val() - $mfrais);
    $fraisM.val($mfrais);
  });

  var $g1 = $('#g1');
  var $g2 = $('#g2');
  var $g3 = $('#g3');
  $g2.on('change', function () {
    $g3.val($g1.val() * $g2.val());
    $total.val(Number($a3.val()) + Number($b3.val()) + Number($c3.val()) + Number($d3.val()) + Number($e3.val()) + Number($f3.val()) + Number($g3.val()) + Number($h3.val()) + Number($i3.val()) + Number($j3.val()) + Number($k3.val()) + Number($l3.val()) + Number($m3.val()));
    $mfrais = $total.val() * $frais;
    $mtotal.val($total.val() - $mfrais);
    $fraisM.val($mfrais);
  });

  var $h1 = $('#h1');
  var $h2 = $('#h2');
  var $h3 = $('#h3');
  $h2.on('change', function () {
    $h3.val($h1.val() * $h2.val());
    $total.val(Number($a3.val()) + Number($b3.val()) + Number($c3.val()) + Number($d3.val()) + Number($e3.val()) + Number($f3.val()) + Number($g3.val()) + Number($h3.val()) + Number($i3.val()) + Number($j3.val()) + Number($k3.val()) + Number($l3.val()) + Number($m3.val()));

    $mfrais = $total.val() * $frais;
    $mtotal.val($total.val() - $mfrais);
    $fraisM.val($mfrais);
  });

  var $i1 = $('#i1');
  var $i2 = $('#i2');
  var $i3 = $('#i3');
  $i2.on('change', function () {
    $i3.val($i1.val() * $i2.val());
    $total.val(Number($a3.val()) + Number($b3.val()) + Number($c3.val()) + Number($d3.val()) + Number($e3.val()) + Number($f3.val()) + Number($g3.val()) + Number($h3.val()) + Number($i3.val()) + Number($j3.val()) + Number($k3.val()) + Number($l3.val()) + Number($m3.val()));
    $mfrais = $total.val() * $frais;
    $mtotal.val($total.val() - $mfrais);
    $fraisM.val($mfrais);
  });

  var $j1 = $('#j1');
  var $j2 = $('#j2');
  var $j3 = $('#j3');
  $j2.on('change', function () {
    $j3.val($j1.val() * $j2.val());
    $total.val(Number($a3.val()) + Number($b3.val()) + Number($c3.val()) + Number($d3.val()) + Number($e3.val()) + Number($f3.val()) + Number($g3.val()) + Number($h3.val()) + Number($i3.val()) + Number($j3.val()) + Number($k3.val()) + Number($l3.val()) + Number($m3.val()));
    $mfrais = $total.val() * $frais;
    $mtotal.val($total.val() - $mfrais);
    $fraisM.val($mfrais);
  });
  var $k1 = $('#k1');
  var $k2 = $('#k2');
  var $k3 = $('#k3');
  $k2.on('change', function () {
    $k3.val($k1.val() * $k2.val());
    $total.val(Number($a3.val()) + Number($b3.val()) + Number($c3.val()) + Number($d3.val()) + Number($e3.val()) + Number($f3.val()) + Number($g3.val()) + Number($h3.val()) + Number($i3.val()) + Number($j3.val()) + Number($k3.val()) + Number($l3.val()) + Number($m3.val()));
    $mfrais = $total.val() * $frais;
    $mtotal.val($total.val() - $mfrais);
    $fraisM.val($mfrais);
  });

  var $l1 = $('#l1');
  var $l2 = $('#l2');
  var $l3 = $('#l3');
  $l2.on('change', function () {
    $l3.val($l1.val() * $l2.val());
    $total.val(Number($a3.val()) + Number($b3.val()) + Number($c3.val()) + Number($d3.val()) + Number($e3.val()) + Number($f3.val()) + Number($g3.val()) + Number($h3.val()) + Number($i3.val()) + Number($j3.val()) + Number($k3.val()) + Number($l3.val()) + Number($m3.val()));
    $mfrais = $total.val() * $frais;
    $mtotal.val($total.val() - $mfrais);
    $fraisM.val($mfrais);
  });

  var $m1 = $('#m1');
  var $m2 = $('#m2');
  var $m3 = $('#m3');
  $m2.on('change', function () {
    $m3.val($m1.val() * $m2.val());
    $total.val(Number($a3.val()) + Number($b3.val()) + Number($c3.val()) + Number($d3.val()) + Number($e3.val()) + Number($f3.val()) + Number($g3.val()) + Number($h3.val()) + Number($i3.val()) + Number($j3.val()) + Number($k3.val()) + Number($l3.val()) + Number($m3.val()));
    $mfrais = $total.val() * $frais;
    $mtotal.val($total.val() - $mfrais);
    $fraisM.val($mfrais);
  });

});

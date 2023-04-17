<div id="smartwizard">
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link" href="#step-1">
                Bielletage-<?php if ($_GET['id'] == 1) { ?>Versement<?php } elseif ($_GET['id'] == 2) { ?>Retrait<?php } elseif ($_GET['id'] == 3) { ?>Appro
                Caisse <?php } elseif ($_GET['id'] == 4) { ?>Sortie de
                Fond<?php } elseif ($_GET['id'] == 5) { ?>Transfert Caisse2Caisse <?php } ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#step-2">
                Bielletage-<?php if ($_GET['id'] == 1) { ?>Versement<?php } elseif ($_GET['id'] == 2) { ?>Retrait<?php } elseif ($_GET['id'] == 3) { ?>Appro
                Caisse <?php } elseif ($_GET['id'] == 4) { ?>Sortie de
                Fond<?php } elseif ($_GET['id'] == 5) { ?>Transfert Caisse2Caisse <?php } ?> </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#step-3">
                Bielletage-<?php if ($_GET['id'] == 1) { ?>Versement<?php } elseif ($_GET['id'] == 2) { ?>Retrait<?php } elseif ($_GET['id'] == 3) { ?>Appro
                Caisse <?php } elseif ($_GET['id'] == 4) { ?>Sortie de
                Fond<?php } elseif ($_GET['id'] == 5) { ?>Transfert Caisse2Caisse <?php } ?> </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="#step-4">
                Produit
        </li>


    </ul>
    <form method="POST" action='/bielletage/add'>
        <br />
        <div class="tab-content">
            <div id="step-1" class="tab-pane" role="tabpanel" aria-labelledby="step-1">
                <input type="hidden" class="form-control" name="RefType" value="<?= $_GET['id']; ?>">

                <div class="row">
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="a2" class="form-control number-input" name="a2" autocomplete="OFF"
                                style="border: 1px solid coral;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="a1" class="form-control" name="a1" value="10000" readonly>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="a3" class="form-control" name="a3" readonly
                                style="border: 1px solid #44E922;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="b2" class="form-control number-input" name="b2" autocomplete="OFF"
                                style="border: 1px solid coral;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="b1" class="form-control" name="b1" value="5000" readonly>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="b3" class="form-control" name="b3" readonly
                                style="border: 1px solid  #44E922;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="c2" class="form-control number-input" name="c2" autocomplete="OFF"
                                style="border: 1px solid coral;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="c1" class="form-control" name="c1" value="2000" readonly>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="c3" class="form-control" name="c3" readonly
                                style="border: 1px solid  #44E922;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control number-input" id="d2" name="d2" autocomplete="OFF"
                                style="border: 1px solid coral;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control" id="d1" name="d1" value="1000" readonly>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="d3" class="form-control" name="d3" readonly
                                style="border: 1px solid  #44E922;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control number-input" id="e2" name="e2" autocomplete="OFF"
                                style="border: 1px solid coral;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control" id="e1" name="e1" value="500" readonly>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="e3" class="form-control" name="e3" readonly
                                style="border: 1px solid  #44E922;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control number-input" id="f2" name="f2" autocomplete="OFF"
                                style="border: 1px solid coral;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control" id="f1" name="f1" value="250" readonly>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="f3" class="form-control" name="f3" readonly
                                style="border: 1px solid  #44E922;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control number-input" id="g2" name="g2" autocomplete="OFF"
                                style="border: 1px solid coral;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control" id="g1" name="g1" value="200" readonly>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="g3" class="form-control" name="g3" readonly
                                style="border: 1px solid  #44E922;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control number-input" id="h2" name="h2" autocomplete="OFF"
                                style="border: 1px solid coral;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control" id="h1" name="h1" value="100" readonly>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="h3" class="form-control" name="h3" readonly
                                style="border: 1px solid  #44E922;">
                        </div>
                    </div>
                </div>
            </div>
            <div id="step-4" class="tab-pane" role="tabpanel" aria-labelledby="step-4">
                <div class="row">
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="a2" class="form-control number-input" name="a2" autocomplete="OFF"
                                style="border: 1px solid coral;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="a1" class="form-control" name="a1" value="10000" readonly>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="a3" class="form-control" name="a3" readonly
                                style="border: 1px solid #44E922;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="b2" class="form-control number-input" name="b2" autocomplete="OFF"
                                style="border: 1px solid coral;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="b1" class="form-control" name="b1" value="5000" readonly>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="b3" class="form-control" name="b3" readonly
                                style="border: 1px solid  #44E922;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="c2" class="form-control number-input" name="c2" autocomplete="OFF"
                                style="border: 1px solid coral;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="c1" class="form-control" name="c1" value="2000" readonly>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="c3" class="form-control" name="c3" readonly
                                style="border: 1px solid  #44E922;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control number-input" id="d2" name="d2" autocomplete="OFF"
                                style="border: 1px solid coral;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control" id="d1" name="d1" value="1000" readonly>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="d3" class="form-control" name="d3" readonly
                                style="border: 1px solid  #44E922;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control number-input" id="e2" name="e2" autocomplete="OFF"
                                style="border: 1px solid coral;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control" id="e1" name="e1" value="500" readonly>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="e3" class="form-control" name="e3" readonly
                                style="border: 1px solid  #44E922;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control number-input" id="f2" name="f2" autocomplete="OFF"
                                style="border: 1px solid coral;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control" id="f1" name="f1" value="250" readonly>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="f3" class="form-control" name="f3" readonly
                                style="border: 1px solid  #44E922;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control number-input" id="g2" name="g2" autocomplete="OFF"
                                style="border: 1px solid coral;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control" id="g1" name="g1" value="200" readonly>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="g3" class="form-control" name="g3" readonly
                                style="border: 1px solid  #44E922;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control number-input" id="h2" name="h2" autocomplete="OFF"
                                style="border: 1px solid coral;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control" id="h1" name="h1" value="100" readonly>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="h3" class="form-control" name="h3" readonly
                                style="border: 1px solid  #44E922;">
                        </div>
                    </div>
                </div>

            </div>


        </div>
    </form>
</div>
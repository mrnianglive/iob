<div id="smartwizard">
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link" href="#step-1">
                Bielletage-<?php if (isset($_GET['id'])) {
                                if ($_GET['id'] == 1) { ?>Versement<?php } elseif ($_GET['id'] == 2) { ?>Retrait<?php } elseif ($_GET['id'] == 3) { ?>Appro
                Caisse<?php } elseif ($_GET['id'] == 4) { ?>Sortie de
                Fond<?php } elseif ($_GET['id'] == 5) { ?>Transfert Caisse2Caisse<?php }
                                                                                                        } ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#step-2">
                Bielletage-<?php if (isset($_GET['id'])) {
                                if ($_GET['id'] == 1) { ?>Versement<?php } elseif ($_GET['id'] == 2) { ?>Retrait<?php } elseif ($_GET['id'] == 3) { ?>Appro
                Caisse<?php } elseif ($_GET['id'] == 4) { ?>Sortie de
                Fond<?php } elseif ($_GET['id'] == 5) { ?>Transfert Caisse2Caisse<?php }
                                                                                                        } ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#step-3">
                Bielletage-<?php if (isset($_GET['id'])) {
                                if ($_GET['id'] == 1) { ?>Versement<?php } elseif ($_GET['id'] == 2) { ?>Retrait<?php } elseif ($_GET['id'] == 3) { ?>Appro
                Caisse<?php } elseif ($_GET['id'] == 4) { ?>Sortie de
                Fond<?php } elseif ($_GET['id'] == 5) { ?>Transfert Caisse2Caisse<?php }
                                                                                                        } ?>
            </a>
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
            <div id="step-2" class="tab-pane" role="tabpanel" aria-labelledby="step-2">
                <div class="row">
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control number-input" id="i2" name="i2" autocomplete="OFF"
                                style="border: 1px solid coral;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control" id="i1" name="i1" value="50" readonly>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="i3" class="form-control" name="i3" readonly
                                style="border: 1px solid  #44E922;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control number-input" id="j2" name="j2" autocomplete="OFF"
                                style="border: 1px solid coral;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control" id="j1" name="j1" value="25" readonly>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="j3" class="form-control" name="j3" readonly
                                style="border: 1px solid  #44E922;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control number-input" id="k2" name="k2" autocomplete="OFF"
                                style="border: 1px solid coral;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control" id="k1" name="k1" value="10" readonly>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="k3" class="form-control" name="k3" readonly
                                style="border: 1px solid  #44E922;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control number-input" id="l2" name="l2" autocomplete="OFF"
                                style="border: 1px solid coral;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control" id="l1" name="l1" value="5" readonly>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="l3" class="form-control" name="l3" readonly
                                style="border: 1px solid  #44E922;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control number-input" id="m2" name="m2" autocomplete="OFF"
                                style="border: 1px solid coral;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control" id="m1" name="m1" value="1" readonly>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="m3" class="form-control" name="m3" readonly
                                style="border: 1px solid  #44E922;">
                        </div>
                    </div>
                </div>
            </div>
            <div id="step-3" class="tab-pane" role="tabpanel" aria-labelledby="step-3">
                <div class="row">

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">Source</label>
                            <select class="form-control" name="RefCaisse" tabindex="1" required="">
                                <?php foreach ($CheckOuverture as $key => $Caisse) {
                                    if ($Caisse['caisse'] != $Caisse['RefCaisse']) {
                                ?>
                                <option value="<?= $Caisse['RefCaisse']; ?>">
                                    <?= $Caisse['NameCaisse'] . " " . $Caisse['NameAgency']; ?></option>
                                <?php }
                                }   ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">Destination</label>
                            <select class="form-control" name="Destination" tabindex="1" required="">

                                <?php foreach ($CheckOuverture as $key => $Caisse) {
                                    if ($Caisse['caisse'] != $Caisse['RefCaisse']) {
                                ?>
                                <option value="<?= $Caisse['RefCaisse']; ?>">
                                    <?= $Caisse['NameCaisse'] . " " . $Caisse['NameAgency']; ?></option>
                                <?php }
                                }   ?>
                            </select>
                        </div>
                    </div>

                </div>
                <div class=" row">
                    <div class="col-md-6">
                        <div class="form-group has-error">
                            <label class="control-label">Montant *</label>
                            <input type="int" id="total" class="form-control" name="MontantVersement" readonly=""
                                required>
                        </div>
                    </div>


                    <div class="col-md-6">
                        <div class="form-group has-error">
                            <label class="control-label">Remarque *</label>
                            <input type="text" class="form-control" name="Remarque" value="Transfert Caisse2Caisse"
                                readonly="" placeholder="Remarque" autocomplete="OFF">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group has-error">
                            <label class="control-label">Deposant/Auteur Retrait *</label>
                            <input type="text" class="form-control" name="NameDeposant"
                                value="<?= $_SESSION['PrenomUsers'], " " . $_SESSION['NomUsers']; ?>" readonly=""
                                autocomplete="OFF">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group has-error">
                            <label class="control-label">Téléphone *</label>
                            <input type="text" class="form-control" name="TelDeposant" value="Opération Interne"
                                readonly="" placeholder="Téléphone" autocomplete="OFF">
                        </div>
                    </div>
                    <?php if (in_array(3, $permission)) { ?>
                    <div class="col-md-6">
                        <div class="form-group has-error">
                            <label class="control-label">Antidaté l'opération</label>
                            <input type="date" class="form-control" name="Antidate">
                        </div>
                    </div>
                    <?php } ?>
                </div>

            </div>

        </div>
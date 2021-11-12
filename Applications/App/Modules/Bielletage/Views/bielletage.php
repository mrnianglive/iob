<div id="smartwizard">
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link" href="#step-1">
                Bielletage-<?php if ($_GET['id'] == 1) { ?>Versement<?php } elseif ($_GET['id'] == 2) { ?>Retrait<?php } elseif ($_GET['id'] == 3) { ?>Appro
                Caisse <?php } elseif ($_GET['id'] == 4) { ?>Sortie de Fond<?php } ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#step-2">
                Bielletage-<?php if ($_GET['id'] == 1) { ?>Versement<?php } elseif ($_GET['id'] == 2) { ?>Retrait<?php } elseif ($_GET['id'] == 3) { ?>Appro
                Caisse <?php } elseif ($_GET['id'] == 4) { ?>Sortie de Fond<?php } ?> </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#step-3">
                Bielletage-<?php if ($_GET['id'] == 1) { ?>Versement<?php } elseif ($_GET['id'] == 2) { ?>Retrait<?php } elseif ($_GET['id'] == 3) { ?>Appro
                Caisse <?php } elseif ($_GET['id'] == 4) { ?>Sortie de Fond<?php } ?> </a>
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
                            <input type="int" id="a1" class="form-control" name="a1" value="10000" readonly>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="a2" class="form-control" name="a2">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="a3" class="form-control" name="a3" readonly>
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
                            <input type="int" id="b2" class="form-control" name="b2">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="b3" class="form-control" name="b3" readonly>
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
                            <input type="int" id="c2" class="form-control" name="c2">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="c3" class="form-control" name="c3" readonly>
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
                            <input type="int" class="form-control" id="d2" name="d2">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="d3" class="form-control" name="d3" readonly>
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
                            <input type="int" class="form-control" id="e2" name="e2">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="e3" class="form-control" name="e3" readonly>
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
                            <input type="int" class="form-control" id="f2" name="f2">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="f3" class="form-control" name="f3" readonly>
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
                            <input type="int" class="form-control" id="g2" name="g2">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="g3" class="form-control" name="g3" readonly>
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
                            <input type="int" class="form-control" id="h2" name="h2">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="h3" class="form-control" name="h3" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <div id="step-2" class="tab-pane" role="tabpanel" aria-labelledby="step-2">
                <div class="row">
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control" id="i1" name="i1" value="50" readonly>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" class="form-control" id="i2" name="i2">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="i3" class="form-control" name="i3" readonly>
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
                            <input type="int" class="form-control" id="j2" name="j2">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="j3" class="form-control" name="j3" readonly>
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
                            <input type="int" class="form-control" id="k2" name="k2">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="k3" class="form-control" name="k3" readonly>
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
                            <input type="int" class="form-control" id="l2" name="l2">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="l3" class="form-control" name="l3" readonly>
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
                            <input type="int" class="form-control" id="m2" name="m2">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label"></label>
                            <input type="int" id="m3" class="form-control" name="m3" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <div id="step-3" class="tab-pane" role="tabpanel" aria-labelledby="step-3">
                <div class="row">
                    <?php if ($_GET['id'] == 3 or $_GET['id'] == 4) { ?>
                    <?php if ($_GET['id'] == 3) { ?>
                    <div class="col-md-3">
                        <?php } else { ?>
                        <div class="col-md-6">
                            <?php } ?>
                            <div class="form-group">
                                <label class="control-label">Caisse</label>
                                <select class="form-control" name="RefCaisse" tabindex="1" required="">
                                    <?php
                                        foreach ($CheckOuverture as $key => $Caisse) {
                                            if ($Caisse['caisse'] != $Caisse['RefCaisse']) {
                                        ?>
                                    <option value="<?= $Caisse['RefCaisse']; ?>">
                                        <?= $Caisse['NameCaisse'] . " " . $Caisse['NameAgency']; ?></option>
                                    <?php }
                                        }   ?>
                                </select>
                            </div>
                        </div>
                        <?php if ($_GET['id'] == 3) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">Type Appro</label>
                                <select class="form-control" name="TypeAppro" tabindex="1" required="">
                                    <?php foreach ($TypeAppro as $key => $type) { ?>
                                    <option value="<?= $type['RefTypeAppro']; ?>">
                                        <?= $type['NameTypeAppro']; ?></option>
                                    <?php }   ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group has-error">
                                <label class="control-label">Numéro de compte</label>
                                <input type="int" id="NumCompte" class="form-control" name="NumCompte" required="">
                            </div>
                        </div>
                        <?php } ?>


                        <?php } else { ?>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Caisse</label>
                                <select class="form-control" name="RefCaisse" id="RefCaisse" tabindex="1" required="">

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
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">Produit</label>
                                <select class="form-control" name="RefProduit" tabindex="1" id="RefProduit" required="">
                                    <option></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3" style="display: none;" id="hidden">
                            <div class="form-group has-error"><label class="control-label" id="label">Numéro de
                                    compte</label><input type="int" id="NumCompte" class="form-control" name="NumCompte"
                                    required=""></div>
                        </div>
                        <?php } ?>
                    </div>
                    <div class=" row">
                        <div class="col-md-6">
                            <div class="form-group has-error">
                                <label class="control-label">Client</label>
                                <input type="text" id="NameClient" class="form-control" name="NameClient" required="">
                            </div>
                        </div>
                        <?php if ($_GET['id'] == 2) { ?>
                        <div class="col-md-2">
                            <div class="form-group has-error">
                                <label class="control-label">Montant</label>
                                <input type="int" id="total" class="form-control" name="total" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group has-error">
                                <label class="control-label">FRAIS</label>
                                <input type="int" id="frais" class="form-control" name="fraismad" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group has-error">
                                <label class="control-label">Montant à Payer</label>
                                <input type="int" id="mtotal" class="form-control" name="MontantVersement" readonly>
                            </div>
                        </div>
                        <?php } else { ?>
                        <div class="col-md-6">
                            <div class="form-group has-error">
                                <label class="control-label">Montant</label>
                                <input type="int" id="total" class="form-control" name="MontantVersement" readonly>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                    <?php if ($_GET['id'] == 3) { ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group has-error">
                                <label class="control-label">Remarque</label>
                                <input type="text" class="form-control" name="Remarque" value="NULL" readonly="">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group has-error">
                                <label class="control-label">Deposant/Auteur Retrait</label>
                                <input type="text" class="form-control" name="NameDeposant"
                                    value="<?= $_SESSION['PrenomUsers'], " " . $_SESSION['NomUsers']; ?>" readonly="">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group has-error">
                                <label class="control-label">Téléphone</label>
                                <input type="text" class="form-control" name="TelDeposant" value="NULL" readonly="">
                            </div>
                        </div>
                    </div>
                    <?php } else { ?>
                    <div class="row">
                        <?php if ($_GET['id'] == 2) { ?>
                        <div class="col-md-3">
                            <div class="form-group has-error">
                                <label class="control-label">Remarque</label>
                                <input type="text" class="form-control" name="Remarque" required="">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group has-error">
                                <label class="control-label">Type</label>
                                <select class="form-control" name="TypeRetrait" tabindex="1" required="">
                                    <?php foreach ($TypeRetrait as $type) { ?>
                                    <option value="<?= $type['RefTypeRetrait']; ?>"><?= $type['NameTypeRetrait']; ?>
                                    </option>
                                    <?php } ?>

                                </select>
                            </div>
                        </div>
                        <?php  } else { ?>
                        <div class="col-md-6">
                            <div class="form-group has-error">
                                <label class="control-label">Remarque</label>
                                <input type="text" class="form-control" name="Remarque" required="">
                            </div>
                        </div>
                        <?php } ?>
                        <div class="col-md-3">
                            <div class="form-group has-error">
                                <label class="control-label">Deposant/Auteur Retrait</label>
                                <input type="text" class="form-control" name="NameDeposant" required="">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group has-error">
                                <label class="control-label">Téléphone</label>
                                <input type="text" class="form-control" name="TelDeposant" required="">
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>

            </div>
    </form>
</div>
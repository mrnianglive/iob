<div class="row">

    <div class="col-md-12">
        <form method="POST" id="formulaire">
            <div class="input-group">
                <div class="col-md-3">Agence
                    <select class="form-control" name="RefAgency" tabindex="1" required="">
                        <?php foreach ($UserAgence as $key => $Agence) {
                        ?>
                        <option value="<?= $Agence['RefAgency']; ?>" <?php if ($Agence['RefAgency'] == $Value) { ?>
                            selected="" <?php } ?>>
                            <?= $Agence['NameAgency']; ?></option>
                        <?php }   ?>
                    </select>
                </div>
                <div class="col-md-3">Du
                    <input type="date" id="Debut" name="Debut" value="<?= $Debut; ?>" class="form-control ">
                </div>
                <div class="col-md-3">Au
                    <input type="date" id="Fin" name="Fin" value="<?= $Fin; ?>" class="form-control"
                        onchange="document.getElementById('formulaire').submit();">
                </div>

            </div>
        </form><br />
        <div class="white-box">
            <h3 class="box-title">Opérations</h3>
            <?php if ($_SESSION['statut'] == 'admin' or $_SESSION['statut'] == 'ChefCaisse' or $_SESSION['statut'] == 'Caissier') { ?>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target=".bd-example-modal-lg"><i
                    class="fa fa-plus"> Ajouter</i></button> <br /> <br />
            <?php } ?>
            <div class="table-responsive">
                <table id="dataTable" class="display nowrap" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="border-top-0">ID</th>
                            <?php if ($_SESSION['statut'] == 'admin' or $_SESSION['statut'] == 'Control') { ?>
                            <th class="border-top-0">Statut</th>
                            <?php } ?>
                            <th class="border-top-0">AGENCE</th>
                            <th class="border-top-0">CAISSE</th>
                            <th class="border-top-0">PRODUIT</th>
                            <th class="border-top-0">OPERATION</th>
                            <th class="border-top-0">CLIENT</th>
                            <th class="border-top-0">TEL</th>
                            <th class="border-top-0">MONTANT</th>
                            <th class="border-top-0">Date</th>
                            <?php if ($_SESSION['statut'] == 'admin') { ?>
                            <th class="border-top-0">Action</th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($Operation as $key => $value) { ?>
                        <tr>
                            <td
                                style="<?php if ($value['Validate'] == 2 && ($_SESSION['statut'] == 'Niveau1')) { ?> background-color:#7ace4c;  <?php } elseif ($value['Validate'] == 1 && ($_SESSION['statut'] == 'Niveau1')) { ?> background-color: #f33155; <?php   } ?>">
                                <?= $value['RefRemittance']; ?></td>
                            <?php if ($_SESSION['statut'] == 'admin' or $_SESSION['statut'] == 'Control') { ?>
                            <td> <?php if ($value['Validate'] == 1) { ?> <button class="btn btn-danger"
                                    data-toggle="modal" data-target="#modal-<?= $value['RefRemittance']; ?>">Non
                                    Vérifiée </button> <?php } else { ?> <a
                                    href="/remittances/cancelvalidate/<?= $value['RefRemittance']; ?>"
                                    class="btn btn-success"
                                    onclick="return confirm('Êtes-vous sûr de vouloir annuler cette vérifcation ?');">
                                    Verifiée le <span><?= $value['DateValidate']; ?></span></a>
                                <?php   } ?>
                            </td>
                            <?php } ?>
                            <td><?= $value['NameAgency']; ?></td>
                            <td> <?= $value['NameCaisse']; ?></td>
                            <td> <?= $value['NameProduit']; ?></td>
                            <td><?= $value['NameType']; ?></td>
                            <td><?= $value['NomComplet']; ?></td>
                            <td><?= $value['NumPhone']; ?></td>
                            <td class="counter text-danger">
                                <?= number_format($value['MontantTransaction'], 0, '.', ','); ?></td>
                            <td><?= date('d/m/Y', strtotime($value['Insert_time'])); ?></td>
                            <?php if ($_SESSION['statut'] == 'admin') { ?>
                            <td><a href="/remittances/delete/<?= $value['RefRemittance']; ?>"
                                    class="btn btn-xs btn-danger"
                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet élément ?');"><i
                                        class="fa fa-trash"></i></a>
                            </td>
                            <?php } ?>
                        </tr>

                        <div class="modal fade" id="modal-<?= $value['RefRemittance']; ?>" tabindex="-1" role="dialog"
                            aria-labelledby="modalStatut" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Confirmation de l'Opération
                                        </h5>
                                    </div>
                                    <form role="form" method="post" action="/remittances/validate">
                                        <div class=" modal-body">
                                            <div class="modal-body">
                                                <input type="hidden" class="form-control" name="RefRemittance"
                                                    value="<?= $value['RefRemittance']; ?>">

                                                <div class="form-group">
                                                    <label for="recipient-name" class="control-label">Date</label>
                                                    <input type="date" class="form-control" name="DateValidate"
                                                        required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="recipient-name" class="control-label">Agence</label>
                                                    <select name="SentFromAgency" class="form-control" required>
                                                        <option value="">Veuillez Choisir l'agence</option>
                                                        <?php foreach ($ListeAgence as $key => $agence) { ?>
                                                        <option value="<?= $agence['RefAgency']; ?>">
                                                            <?= $agence['NameAgency']; ?></option>
                                                        <?php   } ?>
                                                    </select>
                                                </div>
                                                <input type="hidden" id="Debut" name="Debut" value="<?= $Debut; ?>"
                                                    class="form-control ">
                                                <input type="hidden" id="Fin" name="Fin" value="<?= $Fin; ?>"
                                                    class="form-control ">
                                                <input type="hidden" id="RefAgency" name="RefAgency"
                                                    value="<?= $value['RefAgency']; ?>" class="form-control ">
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Fermer</button>
                                            <button type="submit" class="btn btn-primary">Confirmer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>





                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <form role="form" method="post" action="">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="recipient-name" class="control-label">Caisse
                                </label>
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

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Produit</label>
                                <select class="form-control" name="RefProduit" tabindex="1" id="RefProduitRemittance"
                                    required="">
                                    <option></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="recipient-name" class="control-label">Numéro de Téléphone </label>
                                <input type="text" class="form-control" name="NumPhone" id="recipient-name1"
                                    autocomplete="off" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="recipient-name" class="control-label">Nom complet </label>
                                <input type="text" class="form-control" name="NomComplet" id="recipient-name1"
                                    autocomplete="off" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="recipient-name" class="control-label">Montant </label>
                                <input type="text" class="form-control" name="MontantTransaction" id="recipient-name1"
                                    autocomplete="off" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="recipient-name" class="control-label">Type Opération </label>
                                <select name="RefType" class="form-control" required>
                                    <option>Veuillez Choisir</option>
                                    <?php foreach ($ListeType as $key => $value) {
                                        if ($value['RefType'] == 1 or $value['RefType'] == 2) {
                                    ?>
                                    <option value="<?= $value['RefType']; ?>"><?= $value['NameType']; ?></option>
                                    <?php   }
                                    } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" data-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Valider</button>
                </div>
            </form>
        </div>
    </div>
</div>
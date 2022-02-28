<div class="row">

    <div class="col-md-12">
        <form method="POST" id="formulaire">
            <div class="input-group">
                <div class="col-md-3">Date
                    <input type="date" id="jour" name="jour" value="<?= $day; ?>" class="form-control"
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
                            <th class="border-top-0">AGENCE</th>
                            <th class="border-top-0">CAISSE</th>
                            <th class="border-top-0">PRODUIT</th>
                            <th class="border-top-0">OPERATION</th>
                            <th class="border-top-0">CLIENT</th>
                            <th class="border-top-0">TEL</th>
                            <th class="border-top-0">MONTANT</th>
                            <?php if ($_SESSION['statut'] == 'admin') { ?>
                            <th class="border-top-0">Action</th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($Operation as $key => $value) { ?>
                        <tr>
                            <td><?= $value['RefRemittance']; ?></td>
                            <td><?= $value['NameAgency']; ?></td>
                            <td> <?= $value['NameCaisse']; ?></td>
                            <td> <?= $value['NameProduit']; ?></td>
                            <td><?= $value['NameType']; ?></td>
                            <td><?= $value['NomComplet']; ?></td>
                            <td><?= $value['NumPhone']; ?></td>
                            <td class="counter text-danger">
                                <?= number_format($value['MontantTransaction'], 0, '.', ','); ?></td>
                            <?php if ($_SESSION['statut'] == 'admin') { ?>
                            <td><a href="/remittances/delete/<?= $value['RefRemittance']; ?>"
                                    class="btn btn-xs btn-danger"
                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet élément ?');"><i
                                        class="fa fa-trash"></i></a>
                            </td>
                            <?php } ?>
                        </tr>
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
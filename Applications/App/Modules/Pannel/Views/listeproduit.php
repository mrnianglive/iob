<div class="row">
    <div class="col-md-12">

        <div class="white-box">
            <h3 class="box-title">Liste des Produits</h3>
            <button type="button" class="btn btn-primary" id="button" data-toggle="modal" data-target="#addAgence" data-whatever="@mdo"><i class="fa fa-plus"> Ajouter</i></button> <br /> <br />
            <div class="table-responsive">
                <table id="dataTable" class="display nowrap" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="border-top-0">ID</th>
                            <th class="border-top-0">Nom</th>
                            <th class="border-top-0">Banque</th>
                            <th class="border-top-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ListeProduit as $key => $value) { ?>
                            <tr>
                                <td><?= $value['RefProduit']; ?></td>
                                <td><?= $value['NameProduit']; ?></td>
                                <td><?= $value['NameBanque']; ?></td>
                                <td>
                                    <a href="/Pannel/Produit/delete/<?= $value['RefProduit']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet élément ?');"><i class="fa fa-trash"></i></a>
                                    <a class="btn btn-xs  btn-warning" data-toggle="modal" data-target="#ChmodProduit-<?= $value['RefProduit']; ?>" data-whatever="@mdo"><i class="fa fa-check"></i></a>

                                </td>
                            </tr>
                            <div class="modal fade" id="ChmodProduit-<?= $value['RefProduit']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        </div>
                                        <form role="form" method="post" action="">
                                            <input type="hidden" class="form-control" name="RefProduit" value="<?= $value['RefProduit']; ?>">
                                            <div class="modal-body">
                                                <?php foreach ($ListeCaisse as $key => $caisse) { ?>
                                                    <div class="checkbox checkbox-success checkbox-circle">
                                                        <input id="checkbox-10" type="checkbox" name="RefCaisse[]" multiple="" value="<?= $caisse['RefCaisse']; ?>" <?php if ($produits[$caisse['RefCaisse']][$value['RefProduit']] == $caisse['RefCaisse']) { ?> checked="" <?php } ?> />
                                                        <label for="checkbox-10">
                                                            <?= $caisse['NameCaisse'] . " | " . $caisse['NameAgency']; ?></label>
                                                    </div>
                                                <?php } ?>

                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
                                                <button type="submit" class="btn btn-primary">Valider</button>
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
<div class="modal fade" id="addAgence" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form role="form" method="post" action="">
                <div class="modal-body">

                    <div class="form-group">
                        <label for="recipient-name" class="control-label">Nom du Produit </label>
                        <input type="text" class="form-control" name="NameProduit" id="recipient-name1">
                    </div>
                    <label class="control-label">Banque</label>
                    <select name="RefBanque" class="form-control">
                        <option value="">Veuillez Choisir la Banque</option>
                        <?php foreach ($ListeBanque as $key => $value) { ?>
                            <option value="<?= $value['RefBanque']; ?>"><?= $value['NameBanque']; ?></option>
                        <?php   } ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Valider</button>
                </div>
            </form>
        </div>
    </div>
</div>
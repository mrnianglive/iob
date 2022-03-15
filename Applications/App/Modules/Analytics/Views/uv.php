<div class="row">
    <div class="col-md-12">

        <div class="white-box">
            <h3 class="box-title">Gestion d'UV</h3>
            <button type="button" class="btn btn-primary" id="button" data-toggle="modal" data-target="#addAgence"
                data-whatever="@mdo"><i class="fa fa-plus"> Ajouter</i></button> <br /> <br />
            <div class="table-responsive">
                <table id="dataTable" class="display nowrap" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="border-top-0">ID</th>
                            <th class="border-top-0">Agence</th>
                            <th class="border-top-0">Produit</th>
                            <th class="border-top-0">Montant</th>
                            <th class="border-top-0">Date</th>
                            <th class="border-top-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ListeDepot as $key => $value) {
                        ?>
                        <tr>
                            <td>
                                <?= $value['RefDepotUv']; ?>
                            </td>
                            <td>
                                <?= $value['NameAgency']; ?>
                            </td>
                            <td>
                                <?= $value['NameProduit']; ?>
                            </td>

                            <td>
                                <?= $value['MontantDepot'];
                                    ?>
                            </td>

                            <td>
                                <?= $value['DateDepot']; ?>
                            </td>
                            <td>
                                <a href="/Pannel/Produit/delete/<? //= $value['RefProduit']; 
                                                                    ?>" class="btn btn-xs btn-danger"
                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet élément ?');"><i
                                        class="fa fa-trash"></i></a>
                                <a class="btn btn-xs  btn-warning" data-toggle="modal"
                                    data-target="#ChmodProduit-<? //= $value['RefProduit']; 
                                                                                                                        ?>" data-whatever="@mdo"><i
                                        class="fa fa-check"></i></a>

                            </td>
                        </tr>
                        <?php  }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="addAgence" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header"> Ajout d'UV
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <form role="form" method="post" action="">
                <div class="modal-body">
                    <label class="control-label">Agence</label>
                    <select name="RefAgency" class="form-control">
                        <option value="">Veuillez Choisir l'Agence</option>
                        <?php foreach ($ListeAgence as $key => $value) { ?>
                        <option value="<?= $value['RefAgency']; ?>"><?= $value['NameAgency']; ?></option>
                        <?php   } ?>
                    </select>
                </div>
                <div class="modal-body">
                    <label class="control-label">Produit</label>
                    <select name="RefProduit" class="form-control">
                        <option value="">Veuillez Choisir la Banque</option>
                        <?php foreach ($ListeProduit as $key => $value) { ?>
                        <option value="<?= $value['RefProduit']; ?>"><?= $value['NameProduit']; ?></option>
                        <?php   } ?>
                    </select>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="recipient-name" class="control-label">Montant</label>
                        <input type="text" class="form-control" name="MontantDepot" id="recipient-name1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Valider</button>
                </div>
            </form>
        </div>
    </div>
</div>
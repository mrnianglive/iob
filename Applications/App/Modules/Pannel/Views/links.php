<div class="row">
    <div class="col-md-12">

        <div class="white-box">
            <h3 class="box-title">Liste des Liens</h3>
            <button type="button" class="btn btn-primary" id="button" data-toggle="modal" data-target="#addAgence"
                data-whatever="@mdo"><i class="fa fa-plus"> Ajouter</i></button> <br /> <br />
            <div class="table-responsive">
                <table id="dataTable" class="display nowrap" cellspacing="0" width="100%">
                    <thead>
                        <tr>

                            <th class="border-top-0">ID</th>
                            <th class="border-top-0">URL </th>
                            <th class="border-top-0">BTN</th>
                            <th class="border-top-0">ID</th>
                            <th class="border-top-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($links as $key => $value) { ?>
                        <tr>
                            <td><?= $value['url']; ?></td>
                            <td><?= $value['url_name']; ?></td>
                            <td><?= $value['btn']; ?></td>
                            <td><?= $value['nomPays']; ?></td>
                            <td>
                                <a href="/Pannel/links/delete/<?= $value['RefLinks']; ?>" class="btn btn-xs btn-danger"
                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet élément ?');"><i
                                        class="fa fa-trash"></i></a>
                            </td>
                        </tr>
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <form role="form" method="post" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label">Pays</label>
                        <select name="RefPays" class="form-control">
                            <option value="">Veuillez Choisir le Pays</option>
                            <?php foreach ($ListePays as $key => $value) { ?>
                            <option value="<?= $value['RefPays']; ?>"><?= $value['nomPays']; ?></option>
                            <?php   } ?>
                        </select>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="recipient-name" class="control-label">Nom </label>
                        <input type="text" class="form-control" name="url_name" id="recipient-name1">
                    </div>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="recipient-name" class="control-label">URL </label>
                        <input type="text" class="form-control" name="url" id="recipient-name1">
                    </div>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="recipient-name" class="control-label">BTN </label>
                        <input type="text" class="form-control" name="btn" id="recipient-name1">
                    </div>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="recipient-name" class="control-label">TARGET</label>
                        <input type="text" class="form-control" name="target" id="recipient-name1"
                            placeholder="1=>_blank,NULL=>_self">
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
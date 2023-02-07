<div class="row">
    <div class="col-md-12">
        <div class="white-box">
            <h3 class="box-title">Liste des Pays</h3>
            <button type="button" class="btn btn-primary" id="button" data-toggle="modal" data-target="#addPays"
                data-whatever="@mdo"><i class="fa fa-plus"> Ajouter</i></button> <br /> <br />
            <div class="table-responsive">
                <table id="dataTable" class="display nowrap" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="border-top-0">ID</th>
                            <th class="border-top-0">Nom</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ListePays as $value) {
                        ?>
                        <tr>
                            <td><?= $value['RefPays']; ?></td>
                            <td><?= $value['nomPays']; ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="addPays" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <form role="form" method="post" action="" enctype="multipart/form-data">
                <div class="modal-body">

                    <div class="form-group">
                        <label for="recipient-name" class="control-label">Nom</label>
                        <input type="text" class="form-control" name="nomPays" id="recipient-name1">
                    </div>

                    <div class="form-group">
                        <label for="recipient-name" class="control-label">Logo</label>
                        <input type="file" class="form-control" name="logo" id="recipient-name1">
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
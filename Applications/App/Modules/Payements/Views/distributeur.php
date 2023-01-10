<div class="row">
    <div class="col-md-12">

        <div class="white-box">
            <h3 class="box-title">Liste des Distributeurs</h3>
            <button type="button" class="btn btn-primary" id="button" data-toggle="modal" data-target="#AddBanque"
                data-whatever="@mdo"><i class="fa fa-plus"> Ajouter</i></button> <br /> <br />
            <div class="table-responsive">
                <table id="dataTable" class="display nowrap" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="border-top-0">ID</th>
                            <th class="border-top-0">Nom</th>
                            <th class="border-top-0">Prenom</th>
                            <th class="border-top-0">Telephone</th>
                            <th class="border-top-0">Adresse</th>
                            <th class="border-top-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ListeDist as $key => $value) { ?>
                        <tr>
                            <td><?= $value['RefDistributeur']; ?></td>
                            <td><?= $value['NomDistributeur']; ?></td>
                            <td><?= $value['PrenomDistributeur']; ?></td>
                            <td><?= $value['TypeDistributeur']; ?></td>
                            <td><?= $value['TelDistributeur']; ?></td>
                            <td><?= $value['AdresseDistributeur']; ?></td>
                            <td>
                                <a href="/payements/distributeur/delete/<?= $value['RefDistributeur']; ?>"
                                    class="btn btn-xs btn-danger"
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

<div class="modal fade" id="AddBanque" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <form role="form" method="post" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="recipient-name" class="control-label">Nom</label>
                        <input type="text" class="form-control" name="NomDistributeur" id="recipient-name1">
                    </div>

                    <div class="form-group">
                        <label for="recipient-name" class="control-label">Prenom</label>
                        <input type="text" class="form-control" name="PrenomBeneficiare" id="recipient-name1">
                    </div>

                    <div class="form-group">
                        <label for="recipient-name" class="control-label">Telephone</label>
                        <input type="text" class="form-control" name="TelDistributeur" id="recipient-name1">
                    </div>
                    <div class="form-group">
                        <label for="recipient-name" class="control-label">Adresse</label>
                        <input type="text" class="form-control" name="AdresseDistributeur" id="recipient-name1">
                    </div>

                    <div class="form-group">
                        <label for="recipient-name" class="control-label">Type</label>
                        <select name="TypeDistributeur" class="form-control">
                            <option value="">Veuillez Choisir</option>
                            <option value="physique">Personne Physique</option>
                            <option value="moral">Personne Moral</option>
                        </select>
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
<div class="row">
    <div class="col-md-12">

        <div class="white-box">
            <h3 class="box-title">Liste des Utilisateurs</h3>
            <button type="button" class="btn btn-primary" id="button" data-toggle="modal" data-target="#AddUsers"
                data-whatever="@mdo"><i class="fa fa-plus"> Ajouter</i></button> <br /> <br />
            <div class="table-responsive">
                <table id="dataTable" class="display nowrap" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="border-top-0">ID</th>
                            <th class="border-top-0">Nom</th>
                            <th class="border-top-0">Statut</th>
                            <th class="border-top-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ListeUsers as $key => $users) { ?>
                        <tr>
                            <th><?= $users['RefUsers']; ?></th>
                            <td><?= $users['NomUsers'] . " " . $users['PrenomUsers']; ?></td>
                            <td><?= $users['Name']; ?></td>
                            <td>
                                <button type="button" class="btn btn-info btn-outline btn-circle btn-lg m-r-5"
                                    data-toggle="modal" data-target="#Chmod-<?= $users['RefUsers']; ?>"
                                    data-whatever="@mdo"><i class="ticon ti-lock"></i></button>
                                <a href="/Users/deleteUsers/<?= $users['RefUsers']; ?>">
                                    <button type="button" class="btn btn-info btn-outline btn-circle btn-lg m-r-5"><i
                                            class="fa fa-trash"></i></button></a>
                                <a href="/Users/UpdateUsers/<?= $users['RefUsers']; ?>"
                                    class="btn btn-info btn-outline btn-circle btn-lg m-r-5"><i
                                        class="ti-pencil-alt"></i></a>
                                <button type="button" class="btn btn-warning btn-outline btn-circle btn-lg m-r-5"
                                    data-toggle="modal" data-target="#ChmodAppro-<?= $users['RefUsers']; ?>"
                                    data-whatever="@mdo"><i class="ticon ti-lock"></i></button>
                            </td>
                        </tr>
                        <div class="modal fade" id="Chmod-<?= $users['RefUsers']; ?>" tabindex="-1" role="dialog"
                            aria-labelledby="AddCaisse">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">Chmod Caisse
                                        <button type="button" class="close" data-dismiss="modal"
                                            aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <form role="form" method="post" action="">
                                        <div class="modal-body">

                                            <div class="form-group">
                                                <?php foreach ($ListeCaisse as $key => $value) {                                                    ?>
                                                <div class="checkbox checkbox-success checkbox-circle">
                                                    <input id="checkbox-10" type="checkbox" name="RefCaisse[]"
                                                        multiple="" value="<?= $value['RefCaisse']; ?>"
                                                        <?php if ($VerifCaisse[$users['RefUsers']][$value['RefCaisse']] == $value['RefCaisse']) { ?>
                                                        checked="" <?php } ?> />
                                                    <label for="checkbox-10">
                                                        <?= $value['NameCaisse'] . " | " . $value['NameAgency']; ?></label>
                                                </div>
                                                <?php } ?>
                                            </div>
                                            <input type="hidden" value="<?= $users['RefUsers']; ?>" name="RefUsers">
                                            <input type="hidden" value="chmod" name="Type">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default"
                                                data-dismiss="modal">Fermer</button>
                                            <button type="submit" class="btn btn-primary">Valider</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="ChmodAppro-<?= $users['RefUsers']; ?>" tabindex="-1" role="dialog"
                            aria-labelledby="AddCaisse">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header"> Chmod Appro et Sortie
                                        <button type="button" class="close" data-dismiss="modal"
                                            aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <form role="form" method="post" action="">
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <?php foreach ($ListeCaisse as $key => $value) {                                                    ?>
                                                <div class="checkbox checkbox-success checkbox-circle">
                                                    <input id="checkbox-10" type="checkbox" name="RefCaisse[]"
                                                        multiple="" value="<?= $value['RefCaisse']; ?>"
                                                        <?php if ($VerifCaisseAppro[$users['RefUsers']][$value['RefCaisse']] == $value['RefCaisse']) { ?>
                                                        checked="" <?php } ?> />
                                                    <label for="checkbox-10">
                                                        <?= $value['NameCaisse'] . " | " . $value['NameAgency']; ?></label>
                                                </div>
                                                <?php } ?>
                                            </div>
                                            <input type="hidden" value="<?= $users['RefUsers']; ?>" name="RefUsers">
                                            <input type="hidden" value="appro" name="Type">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default"
                                                data-dismiss="modal">Fermer</button>
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
<div class="modal fade" id="AddUsers" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <form role="form" method="post" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="login" class="control-label">Login</label>
                        <input type="text" class="form-control" name="login" id="login">
                        <span id="statut"></span>
                    </div>
                    <div class="form-group">
                        <label for="nom" class="control-label">Nom</label>
                        <input type="text" class="form-control" name="NomUsers" id="recipient-name1">
                    </div>
                    <div class="form-group">
                        <label for="prenom" class="control-label">Prenom</label>
                        <input type="text" class="form-control" name="PrenomUsers" id="recipient-name1">
                    </div>
                    <div class="form-group">
                        <label for="email" class="control-label">Email</label>
                        <input type="email" class="form-control" name="email" id="email">
                    </div>
                    <div class="form-group">
                        <label for="password" class="control-label">Password</label>
                        <input type="password" class="form-control" name="password" id="recipient-name1">
                    </div>
                    <div class="form-group">
                        <label for="prenom" class="control-label">Statut</label>
                        <select class="form-control" name="RefStatut" required>
                            <option>Veuillez Choisir</option>
                            <?php foreach ($ListeStatut as $key => $statut) { ?>
                            <option value="<?= $statut['RefStatut']; ?>"><?= $statut['Name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
                    <button type="submit" id="register" class="btn btn-primary">Valider</button>
                </div>
            </form>
        </div>
    </div>
</div>
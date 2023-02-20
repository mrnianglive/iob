<div class="row">
    <div class="col-md-12">

        <div class="white-box">
            <h3 class="box-title">Liste des Caisse</h3>
            <button type="button" class="btn btn-primary" id="button" data-toggle="modal" data-target="#AddTransfert" data-whatever="@mdo" title="Cliquer pour ajouter une nouvelle caisse"><i class="fa fa-plus">
                    Ajouter</i></button> <br /> <br />
            <div class="table-responsive">
                <table id="dataTable" class="display nowrap" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="border-top-0">ID</th>
                            <th class="border-top-0">Nom</th>
                            <th class="border-top-0">Agence</th>
                            <th class="border-top-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ListeCaisse as $value) {
                        ?>
                            <tr>
                                <td><?= $value['RefCaisse']; ?></td>
                                <td><?= $value['NameCaisse']; ?></td>
                                <td><?= $value['NameAgency']; ?></td>
                                <td> <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#AddTime-<?= $value['RefCaisse']; ?>" data-whatever="@mdo"><i class="fa fa-clock"></i></button></td>
                            </tr>


                            <div class="modal fade" id="AddTime-<?= $value['RefCaisse']; ?>" tabindex="-1" role="dialog" aria-labelledby="AddCaisse">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        </div>
                                        <form role="form" method="post" action="">
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <?php foreach ($ListeDays as $key => $jours) { ?>

                                                        <div class="checkbox checkbox-success checkbox-circle">
                                                            <input id="checkbox-10" type="checkbox" name="RefDays[<?= $jours['RefDays']; ?>]" multiple="" value="<?= $jours['RefDays']; ?>" <?php if (isset($Opening[$value['RefCaisse']][$jours['RefDays']]['RefDays']) && $Opening[$value['RefCaisse']][$jours['RefDays']]['RefDays'] == $jours['RefDays']) { ?> checked="" <?php } ?>>
                                                            <label for="checkbox-10"><?= $jours['NameDays']; ?> </label>
                                                            <input type="time" name="HeureDebut[<?= $jours['RefDays']; ?>]" multiple="" value="<?= $Opening[$value['RefCaisse']][$jours['RefDays']]['HeureDebut'] ?? NULL; ?>">
                                                            <input type="time" name="HeureFin[<?= $jours['RefDays']; ?>]" multiple="" value="<?= $Opening[$value['RefCaisse']][$jours['RefDays']]['HeureFin'] ?? NULL; ?>">
                                                        </div>
                                                        <input type="hidden" name="RefCaisse" value="<?= $value['RefCaisse']; ?>">
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


<div class="modal fade" id="AddTransfert" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form role="form" method="post" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="recipient-name" class="control-label">Agence</label>
                        <select name="RefAgency" class="form-control">
                            <option value="">Veuillez Choisir l'agence</option>
                            <?php foreach ($ListeAgence as $key => $value) { ?>
                                <option value="<?= $value['RefAgency']; ?>"><?= $value['NameAgency']; ?></option>
                            <?php   } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="recipient-name" class="control-label">Nom de la Caisse</label>
                        <input type="text" class="form-control" name="NameCaisse" id="recipient-name1">
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
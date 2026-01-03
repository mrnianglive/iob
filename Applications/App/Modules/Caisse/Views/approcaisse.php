<?php 
// Générer dynamiquement les années disponibles (de 2021 à l'année courante + 1)
$anneeDebut = 2021;
$anneeFin = (int)date('Y') + 1;
$anneeCourante = $Annee ?? date('Y');
?>
<div class="row">
    <div class="col-md-12">

        <div class="white-box">

            <h3 class="box-title">Appro Caisse - <?= $anneeCourante ?></h3>
            <div class="form-inline">
                <div class="form-group">
                    <label for="Annee">Année:</label>
                    <select name="Annee" id="Annee" class="form-control"
                        onchange="window.location.href='/Caisse/ApproCaisse?Annee='+this.value">
                        <?php for ($y = $anneeFin; $y >= $anneeDebut; $y--): ?>
                        <option value="<?= $y ?>" <?= $anneeCourante == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <span class="text-muted ml-3" style="margin-left: 15px;">
                    <i class="fa fa-info-circle"></i> Affichage limité aux 500 dernières opérations
                </span>
            </div>
            <br />
            <?php if ($_SESSION['statut'] == 'admin' or $_SESSION['statut'] == 'ChefCaisse' or $_SESSION['statut'] == 'Head' or $_SESSION['statut'] == 'superadmin') { ?>
            <a href="/bielletage/3" class="btn btn-primary" data-toggle="tooltip"
                title="Cliquez ici pour voir Initier "><i class="fa fa-plus"> Initier</i></a> <br /> <br />
            <?php } ?>
            <div class="table-responsive">
                <table id="dataTable" class="display nowrap" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="border-top-0">ID</th>
                            <th class="border-top-0">Caisse</th>
                            <th class="border-top-0">Montant</th>
                            <th class="border-top-0">Date</th>
                            <?php if ($_SESSION['statut'] == 'admin' or $_SESSION['statut'] == 'superadmin') { ?>
                            <th class="border-top-0">Actions</th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ListeAppro as $key => $value) { ?>
                        <tr>
                            <td>
                                <?= $value['RefOperations']; ?>
                            </td>
                            <td>
                                <?= $value['NameAgency'] . "  " . $value['NameCaisse']; ?>
                            </td>
                            <td>
                                <?= $value['MontantVersement']; ?>
                            </td>
                            <td>
                                <?= $value['Approve2_Time']; ?>
                            </td>
                            <?php if ($_SESSION['statut'] == 'admin' or $_SESSION['statut'] == 'superadmin') { ?>
                            <td>
                                <a href="/Journal/delete/<?= $value['RefOperations']; ?>" class="btn btn-xs btn-danger"
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

<div class="modal fade" id="AddAppro" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <form role="form" method="post" action="">
                <?= $page->getCsrfInput(); ?>
                <div class="modal-body">

                    <div class="form-group">
                        <label for="recipient-name" class="control-label">Caisse</label>
                        <select class="form-control" name="RefCaisse">
                            <?php foreach ($ListeCaisse as $key => $value) { ?>
                            <option value="<?= $value['RefCaisse']; ?>">
                                <?= $value['NameAgency'] . " " . $value['NameCaisse']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="recipient-name" class="control-label">Montant</label>
                        <input type="text" class="form-control" name="MontantAppro" id="recipient-name1">
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
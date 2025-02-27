  <div class="row">
      <div class="col-md-12">
          <form method="POST" id="formulaire">
              <div class="input-group">
                  <div class="col-md-3">Journée du:
                      <input type="date" id="jour" name="jour" value="<?= $day; ?>" class="form-control">
                  </div>
                  <div class=""></br>
                      <button type="submit" class="btn btn-primary" data-toggle="tooltip"
                          title="Cliquer ici pour charger les informations"><i class="fa fa-search"></i></button>
                  </div>
              </div>
          </form><br />

          <!-- Premier tableau - Petite Caisse -->
          <div class="white-box" id="petiteCaisseSection">
              <div class="d-flex justify-content-between align-items-center">
                  <h3 class="box-title">Petite Caisse</h3>
                  <div class="spinner-border text-primary d-none" id="petiteCaisseLoader" role="status">
                      <span class="sr-only">Chargement...</span>
                  </div>
              </div>
              <div class="table-responsive">
                  <table id="dataTable" class="display nowrap" cellspacing="0" width="100%">
                      <thead>
                          <tr>
                              <th class="border-top-0">Agence</th>
                              <th class="border-top-0">Caisse</th>
                              <th class="border-top-0">Appro Caisse</th>
                              <th class="border-top-0">Appro C2C</th>
                              <th class="border-top-0">Sortie de Fond</th>
                              <th class="border-top-0">Depot</th>
                              <th class="border-top-0">Retrait</th>
                              <?php if ($_SESSION['RefPays'] != 1) { ?>
                              <th class="border-top-0">Frais Timbre</th>
                              <?php } ?>
                              <th class="border-top-0">Solde Caisse</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php foreach ($PetiteCaisseData as $data) { ?>
                          <tr>
                              <td><?= $data['NameAgency']; ?></td>
                              <td>
                                  <ul class="list-unstyled">
                                      <?php foreach ($data['Afficher'] as $caisse) { ?>
                                      <li><?= $caisse['NameCaisse']; ?></li>
                                      <?php } ?>
                                  </ul>
                              </td>
                              <td>
                                  <ul class="list-unstyled">
                                      <?php foreach ($data['Afficher'] as $caisse) { ?>
                                      <li><?= number_format($caisse['SoldeInitial'], 0, '.', '.'); ?></li>
                                      <?php } ?>
                                  </ul>
                              </td>
                              <td>
                                  <ul class="list-unstyled">
                                      <?php foreach ($data['Afficher'] as $caisse) { ?>
                                      <li><?= number_format($caisse['TotalAppro'], 0, '.', '.'); ?></li>
                                      <?php } ?>
                                  </ul>
                              </td>
                              <td>
                                  <ul class="list-unstyled">
                                      <?php foreach ($data['Afficher'] as $caisse) { ?>
                                      <li><?= number_format($caisse['TotalSortieCaisse'], 0, '.', '.'); ?></li>
                                      <?php } ?>
                                  </ul>
                              </td>
                              <td>
                                  <ul class="list-unstyled">
                                      <?php foreach ($data['Afficher'] as $caisse) { ?>
                                      <li><?= number_format($caisse['TotalVersement'] + $caisse['SoldeRemittanceVersement'], 0, '.', '.'); ?>
                                      </li>
                                      <?php } ?>
                                  </ul>
                              </td>
                              <td>
                                  <ul class="list-unstyled">
                                      <?php foreach ($data['Afficher'] as $caisse) { ?>
                                      <li><?= number_format($caisse['TotalRetrait'] + $caisse['SoldeRemittanceRetrait'], 0, '.', '.'); ?>
                                      </li>
                                      <?php } ?>
                                  </ul>
                              </td>
                              <?php if ($_SESSION['RefPays'] != 1) { ?>
                              <td>
                                  <ul class="list-unstyled">
                                      <?php foreach ($data['Afficher'] as $caisse) { ?>
                                      <li><?= number_format($caisse['TotalFraisTimbre'], 0, '.', '.'); ?></li>
                                      <?php } ?>
                                  </ul>
                              </td>
                              <?php } ?>
                              <td>
                                  <ul class="list-unstyled">
                                      <?php foreach ($data['Afficher'] as $caisse) { ?>
                                      <li><?= number_format($caisse['SoldeDisponible'], 0, '.', '.'); ?></li>
                                      <?php } ?>
                                  </ul>
                              </td>
                          </tr>
                          <?php } ?>
                      </tbody>
                  </table>
              </div>
          </div>

          <!-- Deuxième tableau - Solde Reserve -->
          <div class="white-box" id="soldeReserveSection">
              <div class="d-flex justify-content-between align-items-center">
                  <h3 class="box-title">Solde Reserve</h3>
                  <div class="spinner-border text-primary d-none" id="soldeReserveLoader" role="status">
                      <span class="sr-only">Chargement...</span>
                  </div>
              </div>
              <div class="table-responsive">
                  <table id="dataTable1" class="display nowrap" cellspacing="0" width="100%">
                      <thead>
                          <tr>
                              <th class="border-top-0">Agence</th>
                              <th class="border-top-0">Solde Reserve(J-1)</th>
                              <th class="border-top-0">Solde Reserve</th>
                              <th class="border-top-0">Depot</th>
                              <th class="border-top-0">Retrait</th>
                              <?php if ($_SESSION['RefPays'] != 1) { ?>
                              <th class="border-top-0">Frais Timbre</th>
                              <?php } ?>
                              <th class="border-top-0">Solde Agence</th>
                              <?php if ($_SESSION['statut'] == 'superadmin' or $_SESSION['statut'] == 'admin' or $_SESSION['statut'] == 'ChefCaisse' or $_SESSION['statut'] == 'Caissier') { ?>
                              <th class="border-top-0">Action</th>
                              <?php } ?>
                          </tr>
                      </thead>
                      <tbody>
                          <?php foreach ($SoldeReserveData as $data) { ?>
                          <tr>
                              <td>
                                  <button type="button" class="btn btn-primary" data-toggle="modal"
                                      data-target="#depotModal-<?= $data['RefAgency']; ?>">
                                      <?= $data['NameAgency']; ?>
                                  </button>
                              </td>
                              <td>
                                  <?= number_format($data['YesterdayReserve'], 0, '.', '.'); ?>
                                  <br>
                                  <small class="text-muted"><?= $data['LastDate']; ?></small>
                              </td>
                              <td><?= number_format($data['DayReserve'], 0, '.', '.'); ?></td>
                              <td><?= number_format($data['SommeDepotWithRemittance'], 0, '.', '.'); ?></td>
                              <td><?= number_format($data['SommeSortieWithRemittance'], 0, '.', '.'); ?></td>
                              <?php if ($_SESSION['RefPays'] != 1) { ?>
                              <td><?= number_format($data['SommeTimbre'], 0, '.', '.'); ?></td>
                              <?php } ?>
                              <td><?= number_format($data['ReserveActuelle'], 0, '.', '.'); ?></td>
                              <?php if ($_SESSION['statut'] == 'superadmin' or $_SESSION['statut'] == 'admin' or $_SESSION['statut'] == 'ChefCaisse' or $_SESSION['statut'] == 'Caissier') { ?>
                              <td>
                                  <?php if (!empty($data['validate'])) { ?>
                                  <a href="/Arreter/cancel/<?= $data['validate']['RefCompte']; ?>/<?= $data['RefAgency']; ?>/<?= $day; ?>"
                                      class="btn btn-success" data-toggle="tooltip"
                                      title="Cliquez ici pour reouvrir l'agence">
                                      <i class="fa fa-lock"></i>
                                  </a>
                                  <?php } else { ?>
                                  <form method="POST" action="/Arreter/reserve">
                                      <input type="hidden" value="<?= $data['ReserveActuelle']; ?>"
                                          name="ReserveActuelle">
                                      <input type="hidden" value="<?= $day; ?>" name="daycloture">
                                      <input type="hidden" value="<?= $data['RefAgency']; ?>" name="RefAgency">
                                      <button type="submit" class="btn btn-danger" data-toggle="tooltip"
                                          title="Cliquez ici pour fermer les caisses de l'agence">
                                          <i class="fa fa-unlock"></i>
                                      </button>
                                  </form>
                                  <?php } ?>
                              </td>
                              <?php } ?>

                              <!-- Modal pour les détails des produits -->
                              <div class="modal fade" id="depotModal-<?= $data['RefAgency']; ?>" tabindex="-1"
                                  role="dialog">
                                  <div class="modal-dialog" role="document">
                                      <div class="modal-content">
                                          <div class="modal-header">
                                              <h5 class="modal-title">Volume Dépôt - Retrait Par Produit -
                                                  <?= $data['NameAgency']; ?></h5>
                                              <button type="button" class="close" data-dismiss="modal"
                                                  aria-label="Close">
                                                  <span aria-hidden="true">&times;</span>
                                              </button>
                                          </div>
                                          <div class="modal-body">
                                              <div class="row">
                                                  <div class="col-md-6">
                                                      <h6 class="text-primary">Dépôts</h6>
                                                      <ul class="list-group">
                                                          <?php foreach ($data['SommeDepotProduit'] as $produit => $montant) { ?>
                                                          <li
                                                              class="list-group-item d-flex justify-content-between align-items-center">
                                                              <?= $produit ?>
                                                              <span class="badge badge-primary badge-pill">
                                                                  <?= number_format($montant, 0, '.', '.') ?>
                                                              </span>
                                                          </li>
                                                          <?php } ?>
                                                      </ul>
                                                  </div>
                                                  <div class="col-md-6">
                                                      <h6 class="text-warning">Retraits</h6>
                                                      <ul class="list-group">
                                                          <?php foreach ($data['SommeSortieProduit'] as $produit => $montant) { ?>
                                                          <li
                                                              class="list-group-item d-flex justify-content-between align-items-center">
                                                              <?= $produit ?>
                                                              <span class="badge badge-warning badge-pill">
                                                                  <?= number_format($montant, 0, '.', '.') ?>
                                                              </span>
                                                          </li>
                                                          <?php } ?>
                                                      </ul>
                                                  </div>
                                              </div>
                                          </div>
                                          <div class="modal-footer">
                                              <button type="button" class="btn btn-secondary"
                                                  data-dismiss="modal">Fermer</button>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </tr>
                          <?php } ?>
                      </tbody>
                  </table>
              </div>
          </div>
      </div>
  </div>

  <style>
.list-unstyled {
    margin-bottom: 0;
}

.card {
    border-radius: 8px;
    margin-bottom: 20px;
}

.card-body {
    padding: 15px;
    text-align: center;
}

.modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

.list-group-item {
    padding: 0.5rem 1rem;
}

.badge-pill {
    font-size: 0.9em;
}

.white-box {
    background: #fff;
    padding: 25px;
    margin-bottom: 30px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
}

.table-responsive {
    margin-top: 15px;
}

.btn-primary {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px;
}

.spinner-border {
    width: 1.5rem;
    height: 1.5rem;
}
  </style>

  <script>
$(document).ready(function() {
    let petiteCaisseTable, soldeReserveTable;

    // Fonction pour initialiser les DataTables
    function initializeTables() {
        if ($.fn.DataTable.isDataTable('#dataTable')) {
            petiteCaisseTable.destroy();
        }
        if ($.fn.DataTable.isDataTable('#dataTable1')) {
            soldeReserveTable.destroy();
        }

        petiteCaisseTable = $('#dataTable').DataTable({
            "scrollX": true,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
            },
            "pageLength": 25,
            "order": [
                [0, "asc"]
            ]
        });

        soldeReserveTable = $('#dataTable1').DataTable({
            "scrollX": true,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
            },
            "pageLength": 25,
            "order": [
                [0, "asc"]
            ]
        });
    }

    // Initialisation des tables au chargement
    initializeTables();

    // Activation des tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Gestion des modales
    $('.modal').on('show.bs.modal', function() {
        var modalBody = $(this).find('.modal-body');
        var maxHeight = window.innerHeight * 0.7;
        modalBody.css('max-height', maxHeight + 'px');
    });

    // Gestion du formulaire
    $('#formulaire').on('submit', function(e) {
        e.preventDefault();

        // Afficher les spinners
        $('#petiteCaisseLoader, #soldeReserveLoader').removeClass('d-none');

        // Soumettre le formulaire
        this.submit();
    });
});
  </script>
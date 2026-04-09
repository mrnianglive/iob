  <div class="row">
      <div class="col-md-12">
          <div class="input-group">
              <div class="col-md-3">Journée du:
                  <input type="date" id="jour" name="jour" value="<?= $day; ?>" class="form-control">
              </div>
              <div class=""></br>
                  <button type="button" id="loadDataBtn" class="btn btn-primary" data-toggle="tooltip"
                      title="Cliquer ici pour charger les informations"><i class="fa fa-search"></i></button>
              </div>
          </div><br />
          <div class="white-box">
              <h3 class="box-title">Petite Caisse</h3>
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
                          <?php foreach ($Agence as $value) { ?>
                          <tr>
                              <td><?= $value['NameAgency']; ?></td>
                              <td>
                                  <ul>
                                      <?php foreach ($value['Afficher'] as $print) { ?>
                                      <li><?= $print['NameCaisse']; ?></li>
                                      <?php } ?>
                                  </ul>
                              </td>
                              <td>
                                  <ul>
                                      <?php foreach ($value['Afficher'] as $afficher) { ?>
                                      <li><?= number_format($afficher['SoldeInitial'], 0, '.', '.'); ?></li>
                                      <?php } ?>
                                  </ul>
                              </td>


                              <td>
                                  <ul>
                                      <?php foreach ($value['Afficher'] as $afficher) { ?>
                                      <li><?= number_format($afficher['TotalAppro'], 0, '.', '.'); ?></li>
                                      <?php } ?>
                                  </ul>
                              </td>
                              <td>
                                  <ul>
                                      <?php foreach ($value['Afficher'] as $afficher) { ?>
                                      <li><?= number_format($afficher['TotalSortieCaisse'], 0, '.', '.'); ?></li>
                                      <?php } ?>
                                  </ul>
                              </td>
                              <td>
                                  <ul>
                                      <?php foreach ($value['Afficher'] as $afficher) { ?>
                                      <li><?= number_format($afficher['TotalVersement'] + $afficher['SoldeRemittanceVersement'], 0, '.', '.'); ?>
                                      </li>
                                      <?php } ?>
                                  </ul>
                              </td>
                              <td>
                                  <ul>
                                      <?php foreach ($value['Afficher'] as $afficher) { ?>
                                      <li><?= number_format($afficher['TotalRetrait'] + $afficher['SoldeRemittanceRetrait'], 0, '.', '.'); ?>
                                      </li>
                                      <?php } ?>
                                  </ul>
                              </td>
                              <?php if ($_SESSION['RefPays'] != 1) { ?>
                              <td>
                                  <ul>
                                      <?php foreach ($value['Afficher'] as $afficher) { ?>
                                      <li><?= number_format($afficher['TotalFraisTimbre'], 0, '.', '.'); ?></li>
                                      <?php } ?>
                                  </ul>
                              </td>
                              <?php } ?>

                              <td>
                                  <ul>
                                      <?php foreach ($value['Afficher'] as $afficher) { ?>
                                      <li><?= number_format($afficher['SoldeDisponible'], 0, '.', '.'); ?></li>
                                      <?php } ?>
                                  </ul>
                              </td>
                          </tr>
                          <?php } ?>
                      </tbody>
                  </table>
              </div>
          </div>
      </div>
      <div class="col-md-12">
          <div class="white-box">
              <h3 class="box-title">Solde Reserve</h3>
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
                          <?php foreach ($Agence as $value) { ?>
                          <tr>
                              <td><span class="btn btn-primary" data-toggle="modal"
                                      data-target="#depotModal-<?= $value['RefAgency']; ?>" data-whatever="@mdo"
                                      title="Cliquer pour voir les details">
                                      <?= $value['NameAgency']; ?>
                                  </span> </td>
                              <td><?= number_format($value['YesterdayReserve'], 0, '.', '.'); ?><br>
                                  <small>
                                      <?= $value['LastDate']; ?>
                                  </small>
                              </td>
                              <td><?= number_format($value['DayReserve'], 0, '.', '.'); ?></td>
                              <td><?= number_format($value['SommeDepotWithRemittance'], 0, '.', '.'); ?></td>
                              <td> <?= number_format($value['SommeSortieWithRemittance'], 0, '.', '.'); ?>
                              </td>
                              <?php if ($_SESSION['RefPays'] != 1) { ?>
                              <td><?= number_format($value['SommeTimbre'], 0, '.', '.'); ?></td>
                              <?php } ?>
                              <td><?= number_format($value['ReserveActuelle'], 0, '.', '.'); ?></td>
                              <?php if ($_SESSION['statut'] == 'superadmin' or  $_SESSION['statut'] == 'admin' or $_SESSION['statut'] == 'ChefCaisse' or $_SESSION['statut'] == 'Caissier') { ?>
                              <td> <?php if (!empty($value['validate'])) { ?><a
                                      <?php if ($_SESSION['statut'] == 'superadmin' or  $_SESSION['statut'] == 'admin' or $_SESSION['statut'] == 'Control') { ?>
                                      href="/Arreter/cancel/<?= $value['validate']['RefCompte']; ?>/<?= $value['RefAgency']; ?>/<?= $day; ?>"
                                      <?php } ?> class="btn btn-secondary btn-sm" data-toggle="tooltip"
                                      title="Cliquez ici pour reouvrir l'agence"><i class="fa fa-undo"></i></a>
                                  <?php } else { ?>
                                  <form method="POST" action="/Arreter/reserve">
                                      <input type="hidden" value="<?= $value['ReserveActuelle']; ?>"
                                          name="ReserveActuelle">
                                      <input type="hidden" value="<?= $day; ?>" name="daycloture">
                                      <input type="hidden" value="<?= $value['RefAgency']; ?>" name="RefAgency">
                                      <button type="submit" class="btn btn-secondary btn-sm" data-toggle="tooltip"
                                          title="Cliquez ici pour fermer les caisses de l'agence"><i
                                              class="fa fa-lock"></i></button>
                                  </form>
                                  <?php } ?>
                              </td>
                              <?php } ?>
                              <div class='modal fade' id='depotModal-<?= $value['RefAgency']; ?>' tabindex='-1'
                                  role='dialog' aria-labelledby='AddCaisse'>
                                  <div class='modal-dialog' role='document'>
                                      <div class='modal-content'>
                                          <div class='modal-header'>Volume Dépôt - Retrait Par Produit
                                              -<?= $value['NameAgency']; ?>
                                              <button type='button' class='close' data-dismiss='modal'
                                                  aria-label='Close'>
                                                  <span aria-hidden='true'>&times;</span>
                                              </button>
                                          </div>
                                          <div class='modal-body'>
                                              <ul>
                                                  <?php foreach ([$value['SommeDepotProduit'] ?? [], $value['SommeSortieProduit'] ?? []] as $index => $products) : ?>
                                                  <h5><?= $index === 0 ? 'Dépôt' : 'Retrait' ?></h5>
                                                  <?php foreach ($products as $product => $total) : ?>
                                                  <li><?= $product ?> :
                                                      <?= is_numeric($total) ? number_format($total, 0, '.', '.') : ($total ?? '0') ?>
                                                  </li>
                                                  <?php endforeach; ?>
                                                  <hr>
                                                  <?php endforeach; ?>
                                              </ul>
                                          </div>
                                          <div class='modal-footer'>
                                              <button type='button' class='btn btn-danger'
                                                  data-dismiss='modal'>Fermer</button>
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

  <script>
// Wait for jQuery to be loaded
function waitForJQuery(callback) {
    if (typeof $ !== 'undefined') {
        callback();
    } else {
        setTimeout(function() {
            waitForJQuery(callback);
        }, 100);
    }
}

waitForJQuery(function() {
    $(document).ready(function() {
        // Store current data for comparison
        var currentData = <?= json_encode($Agence); ?>;

        // Load data via AJAX when button is clicked
        $('#loadDataBtn').on('click', function() {
            var date = $('#jour').val();
            if (!date) {
                alert('Veuillez sélectionner une date');
                return;
            }

            // Show loading indicator
            $('#loadDataBtn').prop('disabled', true).html(
                '<i class="fa fa-spinner fa-spin"></i>');

            $.ajax({
                url: '/Journal/getPetiteCaisseData',
                type: 'POST',
                data: {
                    jour: date
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Update the page by reloading with the new date
                        window.location.href = '/Journal/petite_caisse?jour=' +
                            date;
                    } else {
                        alert('Erreur: ' + (response.error || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    alert('Erreur de chargement: ' + error);
                },
                complete: function() {
                    $('#loadDataBtn').prop('disabled', false).html(
                        '<i class="fa fa-search"></i>');
                }
            });
        });

        // Also trigger on date change
        $('#jour').on('change', function() {
            $('#loadDataBtn').click();
        });
    });
});
  </script>
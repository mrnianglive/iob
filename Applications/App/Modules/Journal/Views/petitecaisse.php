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
                  <!-- Remplacez le contenu de la table par ceci -->
                  <tbody id="agencyTableBody">
                      <?php foreach ($Agence as $value) : ?>
                      <tr data-agency-id="<?= $value['RefAgency']; ?>">
                          <td><?= $value['NameAgency']; ?></td>
                          <td class="caisse-list"></td>
                          <td class="appro-caisse"></td>
                          <td class="appro-c2c"></td>
                          <td class="sortie-fond"></td>
                          <td class="depot"></td>
                          <td class="retrait"></td>
                          <?php if ($_SESSION['RefPays'] != 1) : ?>
                          <td class="frais-timbre"></td>
                          <?php endif; ?>
                          <td class="solde-caisse"></td>
                      </tr>
                      <?php endforeach; ?>
                  </tbody>

                  <!-- Ajoutez ce modal une seule fois à la fin du fichier -->
                  <div class="modal fade" id="depotModal" tabindex="-1" role="dialog" aria-labelledby="AddCaisse">
                      <div class="modal-dialog" role="document">
                          <div class="modal-content">
                              <div class="modal-header">
                                  Volume Dépôt - Retrait Par Produit <span id="modalAgencyName"></span>
                                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                      <span aria-hidden="true">&times;</span>
                                  </button>
                              </div>
                              <div class="modal-body" id="modalContent">
                                  <!-- Le contenu sera injecté ici par JavaScript -->
                              </div>
                              <div class="modal-footer">
                                  <button type="button" class="btn btn-danger" data-dismiss="modal">Fermer</button>
                              </div>
                          </div>
                      </div>
                  </div>

                  <!-- Ajoutez ce script à la fin du fichier -->
                  <script>
                  document.addEventListener('DOMContentLoaded', function() {
                      const tableBody = document.getElementById('agencyTableBody');
                      const date = '<?= $day; ?>';

                      function loadAgencyDetails(agencyId) {
                          fetch(`/api/agency-details?agencyId=${agencyId}&date=${date}`)
                              .then(response => response.json())
                              .then(data => {
                                  const row = tableBody.querySelector(`tr[data-agency-id="${agencyId}"]`);
                                  row.querySelector('.caisse-list').innerHTML = data.caisseList;
                                  row.querySelector('.appro-caisse').innerHTML = data.approCaisse;
                                  row.querySelector('.appro-c2c').innerHTML = data.approC2C;
                                  row.querySelector('.sortie-fond').innerHTML = data.sortieFond;
                                  row.querySelector('.depot').innerHTML = data.depot;
                                  row.querySelector('.retrait').innerHTML = data.retrait;
                                  if (data.fraisTimbre !== undefined) {
                                      row.querySelector('.frais-timbre').innerHTML = data.fraisTimbre;
                                  }
                                  row.querySelector('.solde-caisse').innerHTML = data.soldeCaisse;
                              });
                      }

                      tableBody.querySelectorAll('tr').forEach(row => {
                          const agencyId = row.dataset.agencyId;
                          loadAgencyDetails(agencyId);
                      });

                      // Gestionnaire d'événements pour le modal
                      $('#depotModal').on('show.bs.modal', function(event) {
                          const button = $(event.relatedTarget);
                          const agencyId = button.closest('tr').data('agency-id');
                          const modal = $(this);

                          fetch(`/api/agency-details?agencyId=${agencyId}&date=${date}`)
                              .then(response => response.json())
                              .then(data => {
                                  modal.find('#modalAgencyName').text(data.agencyName);
                                  let content = '<ul>';
                                  content += '<h5>Dépôt</h5>';
                                  for (const [product, total] of Object.entries(data
                                          .depotProduit)) {
                                      content += `<li>${product} : ${total}</li>`;
                                  }
                                  content += '<hr><h5>Retrait</h5>';
                                  for (const [product, total] of Object.entries(data
                                          .retraitProduit)) {
                                      content += `<li>${product} : ${total}</li>`;
                                  }
                                  content += '</ul>';
                                  modal.find('#modalContent').html(content);
                              });
                      });
                  });
                  </script>
              </div>
          </div>
      </div>
  </div>
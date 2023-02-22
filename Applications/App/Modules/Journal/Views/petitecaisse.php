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
                              <th class="border-top-0">Solde Agence</th>
                              <?php if ($_SESSION['statut'] == 'superadmin' or $_SESSION['statut'] == 'admin' or $_SESSION['statut'] == 'ChefCaisse' or $_SESSION['statut'] == 'Caissier') { ?>
                              <th class="border-top-0">Action</th>
                              <?php } ?>

                          </tr>
                      </thead>
                      <tbody>
                          <?php foreach ($Agence as $value) { ?>
                          <tr>
                              <td><?= $value['NameAgency']; ?></td>
                              <td><?= number_format($value['YesterdayReserve'], 0, '.', '.'); ?></td>
                              <td><?= number_format($value['DayReserve'], 0, '.', '.'); ?></td>
                              <td>
                                  <button type="button" class="btn btn-primary" data-toggle="modal"
                                      data-target="#depotModal-<?= $value['RefAgency']; ?>" data-whatever="@mdo"
                                      title="Cliquer pour voir les details">
                                      <?= number_format($value['SommeDepotWithRemittance'], 0, '.', '.'); ?>
                                  </button>

                              </td>
                              <td>
                                  <button type="button" class="btn btn-primary" data-toggle="modal"
                                      data-target="#retraitModal-<?= $value['RefAgency']; ?>" data-whatever="@mdo"
                                      title="Cliquer pour voir les details">
                                      <?= number_format($value['SommeSortieWithRemittance'], 0, '.', '.'); ?>
                                  </button>
                              </td>
                              <td><?= number_format($value['ReserveActuelle'], 0, '.', '.'); ?></td>
                              <?php if ($_SESSION['statut'] == 'superadmin' or  $_SESSION['statut'] == 'admin' or $_SESSION['statut'] == 'ChefCaisse' or $_SESSION['statut'] == 'Caissier') { ?>
                              <td> <?php if (!empty($value['validate'])) { ?><a
                                      <?php if ($_SESSION['statut'] == 'superadmin' or  $_SESSION['statut'] == 'admin') { ?>
                                      href="/Arreter/cancel/<?= $value['validate']['RefCompte']; ?>/<?= $value['RefAgency']; ?>/<?= $day; ?>"
                                      <?php } ?> class="btn btn-success" data-toggle="tooltip"
                                      title="Cliquez ici pour reouvrir l'agence"><i class="fa  fa-lock"></i></a>
                                  <?php } else { ?>
                                  <form method="POST" action="/Arreter/reserve">
                                      <input type="hidden" value="<?= $value['ReserveActuelle']; ?>"
                                          name="ReserveActuelle">
                                      <input type="hidden" value="<?= $day; ?>" name="daycloture">
                                      <input type="hidden" value="<?= $value['RefAgency']; ?>" name="RefAgency">
                                      <button type="submit" class="btn btn-danger" data-toggle="tooltip"
                                          title="Cliquez ici pour fermer les caisses de l'agence"><i
                                              class="fa fa-unlock"></i></button>
                                  </form>
                                  <?php } ?>
                              </td>
                              <?php } ?>

                              <div class="modal fade" id="depotModal-<?= $value['RefAgency']; ?>" tabindex="-1"
                                  role="dialog" aria-labelledby="AddCaisse">
                                  <div class="modal-dialog" role="document">
                                      <div class="modal-content">
                                          <div class="modal-header">Volume Depot Produit
                                              <button type="button" class="close" data-dismiss="modal"
                                                  aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                          </div>
                                          <div class="modal-body">
                                              <ul>
                                                  <?php foreach ($value['SommeDepotProduit'] as $product => $total) { ?>
                                                  <li><?php echo $product; ?> :
                                                      <?php number_format($total, 0, '.', '.'); ?>
                                                      <?php } ?>
                                              </ul>
                                          </div>
                                          <div class="modal-footer">
                                              <button type="button" class="btn btn-default"
                                                  data-dismiss="modal">Fermer</button>
                                          </div>
                                      </div>
                                  </div>
                              </div>

                              <div class="modal fade" id="retraitModal-<?= $value['RefAgency']; ?>" tabindex="-1"
                                  role="dialog" aria-labelledby="AddCaisse">
                                  <div class="modal-dialog" role="document">
                                      <div class="modal-content">
                                          <div class="modal-header">Volume Retrait Produit
                                              <button type="button" class="close" data-dismiss="modal"
                                                  aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                          </div>
                                          <div class="modal-body">
                                              <ul>
                                                  <?php foreach ($value['SommeSortieProduit'] as $product => $total) { ?>
                                                  <li><?php echo $product; ?> :
                                                      <?php echo number_format($total, 0, '.', '.'); ?>
                                                      <?php } ?>
                                              </ul>


                                          </div>
                                          <div class="modal-footer">
                                              <button type="button" class="btn btn-default"
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
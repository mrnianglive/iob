  <div class="row">
      <div class="col-md-12">
          <form method="POST">
              <div class="input-group">
                  <div class="col-md-3">Date
                      <input type="date" id="jour" name="jour" value="<?= $day; ?>" class="form-control">
                      <?= $time = date('H:i:s'); ?>
                  </div>
                  <div class="col-md-1"></br>
                      <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
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
                              <th class="border-top-0">Appro initial</th>
                              <th class="border-top-0">Appro Caisse</th>
                              <th class="border-top-0">Sortie de Fond</th>
                              <th class="border-top-0">Versement</th>
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
                                      <li><?= $afficher['SoldeInitial']; ?></li>
                                      <?php } ?>
                                  </ul>
                              </td>


                              <td>
                                  <ul>
                                      <?php foreach ($value['Afficher'] as $afficher) { ?>
                                      <li><?= $afficher['TotalAppro']; ?></li>
                                      <?php } ?>
                                  </ul>
                              </td>
                              <td>
                                  <ul>
                                      <?php foreach ($value['Afficher'] as $afficher) { ?>
                                      <li><?= $afficher['TotalSortieCaisse']; ?></li>
                                      <?php } ?>
                                  </ul>
                              </td>
                              <td>
                                  <ul>
                                      <?php foreach ($value['Afficher'] as $afficher) { ?>
                                      <li><?= $afficher['TotalVersement']; ?></li>
                                      <?php } ?>
                                  </ul>
                              </td>
                              <td>
                                  <ul>
                                      <?php foreach ($value['Afficher'] as $afficher) { ?>
                                      <li><?= $afficher['TotalRetrait']; ?></li>
                                      <?php } ?>
                                  </ul>
                              </td>
                              <td>
                                  <ul>
                                      <?php foreach ($value['Afficher'] as $afficher) { ?>
                                      <li><?= $afficher['SoldeDisponible']; ?></li>
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
                              <th class="border-top-0">Versement</th>
                              <th class="border-top-0">Retrait</th>
                              <th class="border-top-0">Solde Agence</th>
                              <?php if ($_SESSION['statut'] == 'admin' or $_SESSION['statut'] == 'ChefCaisse' or $_SESSION['statut'] == 'Caissier') { ?>
                              <th class="border-top-0">Action</th>
                              <?php } ?>

                          </tr>
                      </thead>
                      <tbody>
                          <?php foreach ($Agence as $value) { ?>
                          <tr>
                              <td><?= $value['NameAgency']; ?></td>
                              <td><?= $value['YesterdayReserve']; ?></td>
                              <td><?= $value['DayReserve']; ?></td>
                              <td><?= $value['SommeDepot']; ?></td>
                              <td><?= $value['SommeSortie']; ?></td>
                              <td><?= $value['ReserveActuelle']; ?></td>
                              <?php if ($_SESSION['statut'] == 'admin' or $_SESSION['statut'] == 'ChefCaisse' or $_SESSION['statut'] == 'Caissier') { ?>
                              <td> <?php if (!empty($value['validate'])) { ?><a class="btn btn-success"><i
                                          class="fa  fa-lock"></i></a> <?php } else { ?>
                                  <form method="POST" action="/Arreter/reserve">
                                      <input type="hidden" value="<?= $value['ReserveActuelle']; ?>"
                                          name="ReserveActuelle">

                                      <input type="hidden" value="<?= $day; ?>" name="daycloture">
                                      <input type="hidden" value="<?= $value['RefAgency']; ?>" name="RefAgency">
                                      <button type="submit" class="btn btn-danger"><i class="fa fa-unlock"></i></button>
                                  </form> <?php } ?>
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
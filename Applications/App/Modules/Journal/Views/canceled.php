  <div class="row">
      <div class="col-md-12">
          <form method="POST" action="/Journal/canceled" id="formulaire">
              <div class="input-group">
                  <div class="">Agence
                      <select class="form-control" name="RefAgency" tabindex="1" required="" id="RefAgency">
                          <?php foreach ($UserAgence as $key => $Agence) {
                            ?>
                              <option value="<?= $Agence['RefAgency']; ?>" <?php if ($Agence['RefAgency'] == $Value) { ?> selected="" <?php } ?>>
                                  <?= $Agence['NameAgency']; ?></option>
                          <?php }   ?>
                      </select>
                  </div>
                  <div class="col-md-2">Du
                      <input type="date" id="Debut" name="Debut" value="<?= $Debut; ?>" class="form-control ">
                  </div>
                  <div class="col-md-2">Au
                      <input type="date" id="Fin" name="Fin" value="<?= $Fin; ?>" class="form-control">
                  </div>
                  <div class=""></br>
                      <button type="submit" class="btn btn-primary" data-toggle="tooltip" title="Cliquez ici pour lancer la recherche"><i class="fas fa-search"></i></button>
                  </div>
              </div>
          </form>
          <br />
          <div class="white-box">
              <h3 class="box-title">Journal de Caisse des Opérations annulées</h3>
              <div class="table-responsive">
                  <table id="dataTable" class="display nowrap" cellspacing="0" width="100%">
                      <thead>
                          <tr>
                              <th class="border-top-0">ID</th>
                              <th class="border-top-0">Agence</th>
                              <th class="border-top-0">Produit</th>
                              <th class="border-top-0">Operation</th>
                              <th class="border-top-0">Client</th>
                              <th class="border-top-0">Numero de Compte</th>
                              <th class="border-top-0">Montant</th>
                              <th class="border-top-0">Remarque</th>
                              <th class="border-top-0">Date</th>
                              <th class="border-top-0">Caissier</th>
                              <th class="border-top-0">RECU</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php foreach ($Operations as $key => $value) { ?>
                              <tr>

                                  <td> <?= $value['RefOperations']; ?></td>

                                  <td><?= $value['NameAgency']; ?></td>
                                  <td><?= $value['NameProduit']; ?></td>
                                  <td><?= $value['NameType']; ?></td>
                                  <td><?= $value['NameClient']; ?></td>
                                  <td> <?= $value['NumCompte']; ?>
                                  </td>
                                  <td><?= $value['MontantVersement']; ?></td>
                                  <td><?= $value['Remarque']; ?></td>
                                  <td><?= date('d/m/Y', strtotime($value['Approve2_Time'])); ?></td>
                                  <td><?= $value['login']; ?></td>
                                  <td><a href="/bordereau/<?= $value['RefOperations']; ?>" target="_blank" class="btn btn-secondary" data-toggle="tooltip" title="Cliquez ici pour imprimer le bordereau"><i class="fa fa-print">
                                              Reçu</i> </td>
                              </tr>
                          <?php } ?>
                      </tbody>
                  </table>
              </div>
          </div>
      </div>
  </div>



  <div class="row">
      <div class="col-md-12">
          <div class="white-box">

              <h3 class="box-title"> Journal de Caisse des Opérations annulées par Agence</h3>

              <div class="table-responsive">
                  <table id="dataTable1" class="display nowrap" cellspacing="0" width="100%">
                      <thead>
                          <tr>
                              <th class="border-top-0">Agence</th>
                              <th class="border-top-0">Caisse</th>
                              <th class="border-top-0">Nbre Annulation</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php foreach ($Operations['operations'] as $key => $value) { ?>
                              <tr>
                                  <td><?= $value['NameAgency']; ?></td>
                                  <td>
                                      <ul>
                                          <?php foreach ($value as $print) { ?>
                                              <li>
                                                  <span class="btn btn-primary">
                                                      <?= $print['NameCaisse']; ?>
                                                  </span>
                                              </li>
                                              &nbsp;
                                          <?php } ?>
                                      </ul>
                                  </td>
                                  <td>
                                      <ul>
                                          <?php foreach ($value['NbreAnnulation'] as $print) { ?>
                                              <li>
                                                  <span class="btn btn-primary">
                                                      <?= $print['NbreAnnulation']; ?>
                                                  </span>
                                              </li>
                                              &nbsp;
                                          <?php } ?>
                                      </ul>
                                  </td>
                              </tr>
                          <?php } ?>
                      </tbody>
                      <tfoot>
                          <?php foreach ($Operations['counts'] as $count) { ?>
                              <tr>
                                  <td><?= $count['RefAgency']; ?></td>
                                  <td></td> <!-- Ajoutez une cellule vide si vous n'avez pas de données pour la caisse -->
                                  <td><?= $count['OperationCount']; ?></td>
                              </tr>
                          <?php } ?>
                      </tfoot>
                  </table>
              </div>
          </div>
      </div>
  </div>
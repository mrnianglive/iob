  <div class="row">
      <div class="col-md-12">

          <div class="white-box">
              <h3 class="box-title">Journal des opérations non vérifiées</h3>
              <div class="table-responsive">
                  <table id="dataTable" class="display nowrap" cellspacing="0" width="100%">
                      <thead>
                          <tr>
                              <th class="border-top-0">ID</th>
                              <?php if ($_SESSION['statut'] == 'superadmin' or $_SESSION['statut'] == 'admin' or $_SESSION['statut'] == 'Control') { ?>
                              <th class="border-top-0">Statut</th>
                              <?php } ?>
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

                              <td
                                  style="<?php if ($value['Validate'] == 2 && ($_SESSION['statut'] == 'Niveau1')) { ?> background-color:#7ace4c;  <?php } elseif ($value['Validate'] == 1 && ($_SESSION['statut'] == 'Niveau1')) { ?> background-color: #f33155; <?php   } ?>">
                                  <?= $value['RefOperations']; ?></td>
                              <?php if ($_SESSION['statut'] == 'superadmin' or  $_SESSION['statut'] == 'admin' or $_SESSION['statut'] == 'Control') { ?>
                              <td> <?php if ($value['Validate'] == 1) { ?><button class="btn btn-danger"
                                      data-toggle="modal" data-target="#modal"
                                      data-operation-id="<?= $value['RefOperations']; ?>"
                                      data-ref-agency="<?= $value['RefAgency']; ?>"
                                      data-ref-produit="<?= $value['RefProduit']; ?>"
                                      title="Cliquez ici pour confirmer l'opération">
                                      Non Vérifiée
                                  </button><?php } ?>
                              </td>
                              <?php } ?>
                              <td><?= $value['NameAgency']; ?></td>
                              <td><?= $value['NameProduit']; ?></td>
                              <td><?= $value['NameType']; ?></td>
                              <td><?= $value['NameClient']; ?></td>
                              <td class="account" data-account="<?= $value['NumCompte']; ?>">
                                  <?= $value['NumCompte']; ?>
                              </td>
                              <td><?= $value['MontantVersement']; ?></td>
                              <td><?= $value['Remarque']; ?></td>
                              <td><?= date('d/m/Y', strtotime($value['Approve2_Time'])); ?></td>
                              <td><?= $value['login']; ?></td>
                              <td><a href="/bordereau/<?= $value['RefOperations']; ?>" target="_blank"
                                      class="btn btn-secondary" data-toggle="tooltip"
                                      title="Cliquez ici pour imprimer le bordereau"><i class="fa fa-print">
                                          Reçu</i> </td>

                          </tr>
                          <?php } ?>
                      </tbody>
                  </table>
              </div>
          </div>
      </div>
  </div>
  <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modal-title" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Confirmation de l'opération <span id="modal-id"></span>
                  </h5>
              </div>
              <form role="form" method="post" action="/Journal/validate">
                  <div class="modal-body">
                      <div class="modal-body">
                          <input type="hidden" class="form-control" id="modal-operation-id" name="RefOperations"
                              value="">
                          <div class="form-group">
                              <label for="recipient-name" class="control-label">Date</label>
                              <input type="date" class="form-control" name="DateValidate" required>
                          </div>
                          <div class="form-group">
                              <label for="recipient-name" class="control-label">Agence</label>
                              <select name="SentFromAgency" class="form-control" required>
                                  <option value="">Veuillez Choisir l'agence</option>
                                  <?php foreach ($ListeAgence as $key => $agence) { ?>
                                  <option value="<?= $agence['RefAgency']; ?>">
                                      <?= $agence['NameAgency']; ?></option>
                                  <?php   } ?>
                              </select>
                          </div>
                          <input type="hidden" class="form-control" id="modal-ref-agency" name="RefAgency" readonly>
                          <input type="hidden" id="modal-ref-produit" name="RefProduit" class="form-control" readonly>
                      </div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                      <button type="submit" class="btn btn-primary">Confirmer</button>
                  </div>
              </form>
          </div>
      </div>
  </div>
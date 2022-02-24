  <div class="row">
      <div class="col-md-12">
          <form method="POST" action="/Analytics/index" id="formulaire">
              <div class="input-group">
                  <div class="col-md-2">Du
                      <input type="date" id="Debut" name="Debut" value="<?= $Debut; ?>" class="form-control ">
                  </div>
                  <div class="col-md-2">Au
                      <input type="date" id="Fin" name="Fin" value="<?= $Fin; ?>" class="form-control"
                          onchange="document.getElementById('formulaire').submit();">
                  </div>
                  <div class="col-md-2">Total Depot
                      <input type="text" value="<?= number_format($totalVersement, 0, '.', ','); ?>"
                          class="form-control" readonly>
                  </div>

                  <div class="col-md-2">Comission Depot
                      <input type="text" value="<?= number_format($CommissionDepot, 0, '.', ',');   ?>"
                          class="form-control" readonly>
                  </div>

                  <div class="col-md-2">Total Retrait
                      <input type="text" value="<?= number_format($totalRetrait, 0, '.', ',');   ?>"
                          class="form-control" readonly>
                  </div>
                  <div class="col-md-2">Commision Retrait
                      <input type="text" value="<?= number_format($CommissionRetrait, 0, '.', ',');   ?>"
                          class="form-control" readonly>
                  </div>
              </div>
          </form>
          <br />
          <div class="white-box">
              <h3 class="box-title">Opérations </h3>
              <?php if (isset($_POST['Debut']) && isset($_POST['Fin'])) { ?>
              <div class="table-responsive">
                  <table id="dataTable" class="display nowrap" cellspacing="0" width="100%">
                      <thead>
                          <tr>
                              <th class="border-top-0">ID</th>
                              <th class="border-top-0">Agence</th>
                              <th class="border-top-0">Operation</th>
                              <th class="border-top-0">Client</th>
                              <th class="border-top-0">Numero de Compte</th>
                              <th class="border-top-0">Montant</th>
                              <th class="border-top-0">Remarque</th>
                              <th class="border-top-0">Date</th>
                              <th class="border-top-0">Caissier</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php foreach ($Operations as $key => $value) { ?>
                          <tr>
                              <td><?= $value['RefOperations']; ?></td>
                              <td><?= $value['NameAgency']; ?></td>
                              <td><?= $value['NameType']; ?></td>
                              <td><?= $value['NameClient']; ?></td>
                              <td><?= $value['NumCompte']; ?></td>
                              <td><?= $value['MontantVersement']; ?></td>
                              <td><?= $value['Remarque']; ?></td>
                              <td><?= date('d/m/Y', strtotime($value['Approve2_Time'])); ?></td>
                              <td><?= $value['login']; ?></td>
                          </tr>
                          <?php } ?>
                      </tbody>
                  </table>
              </div>
              <?php } ?>
          </div>
      </div>
  </div>
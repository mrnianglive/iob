  <div class="row">
      <div class="col-md-12">
          <div class="white-box">
              <h3 class="box-title">Arreter de Caisse </h3>
              <div class="table-responsive">
                  <table id="dataTable" class="display nowrap" cellspacing="0" width="100%">
                      <thead>
                          <tr>
                              <th class="border-top-0">Date</th>
                              <th class="border-top-0">Solde Fermeture</th>
                              <th class="border-top-0">Caissier</th>
                              <?php if ($_SESSION['statut'] == 'admin') { ?>
                                  <th class="border-top-0">Action</th>
                              <?php } ?>
                          </tr>
                      </thead>
                      <tbody>
                          <?php foreach ($ListeSolde as $key => $value) { ?>
                              <tr>
                                  <td><?= date('d/m/Y', strtotime($value['DateSolde'])); ?></td>
                                  <td><?= $value['Solde']; ?></td>
                                  <td><?= $value['login']; ?></td>
                                  <?php if ($_SESSION['statut'] == 'admin') { ?>
                                      <td>
                                          <a href="/Arreter/delete/<?= $value['RefSolde']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet élément ?');"><i class="fa fa-trash"></i></a>
                                      </td>
                                  <?php } ?>
                              </tr>
              </div>
          <?php } ?>
          </tbody>
          </table>
          </div>
      </div>
  </div>
  </div>
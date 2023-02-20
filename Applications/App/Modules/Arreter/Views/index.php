  <div class="row">
      <div class="col-md-12">
          <div class="white-box">
              <h3 class="box-title">Arreter de Caisse </h3>
              <div class="table-responsive">
                  <table id="dataTable" class="display nowrap" cellspacing="0" width="100%">
                      <thead>
                          <tr>
                              <th class="border-top-0">Nom</th>
                              <th class="border-top-0">Agence</th>
                              <th class="border-top-0">Actions</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php foreach ($Caisse as $key => $value) { ?>
                          <tr>
                              <td><?= $value['NameCaisse']; ?></td>
                              <td><?= $value['NameAgency']; ?></td>
                              <td>
                                  <?php if (!empty($value['Valide'])) { ?><a
                                      <?php if ($_SESSION['statut'] == 'superadmin' or $_SESSION['statut'] == 'admin') { ?>
                                      href="/Arreter/delete/<?= $value['Valide']['RefSolde']; ?>" <?php } ?>
                                      class="btn btn-success" data-toggle="tooltip"
                                      title="Cliquez ici pour arreter la caisse"><i class="fa  fa-lock"></i></a>
                                  <?php } else { ?><a
                                      <?php if ($_SESSION['statut'] == 'superadmin' or  $_SESSION['statut'] == 'admin' or $_SESSION['statut'] == 'ChefCaisse') { ?>
                                      href="/Arreter/close/<?= $value['RefCaisse']; ?>" <?php } ?>
                                      class="btn btn-danger" data-toggle="tooltip"
                                      title="Cliquez ici pour annuler l'arrêté de Caisse"><i
                                          class="fa fa-unlock"></i></a> <?php } ?>
                                  <a href="/Arreter/View/<?= $value['RefCaisse']; ?>" class="btn btn-primary"
                                      data-toggle="tooltip" title="Cliquez ici pour voir tous les arretes de Caisse"><i
                                          class="fa fa-eye"></i></a>
                              </td>
                              </td>
                          </tr>
                          <?php } ?>
                      </tbody>
                  </table>
              </div>
          </div>
      </div>
  </div>
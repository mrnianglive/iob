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
                                  <?php
                                        $isAdminOrSuperadmin = $_SESSION['statut'] == 'superadmin' || $_SESSION['statut'] == 'admin';
                                        $isChefCaisseOrCaissier = $_SESSION['statut'] == 'ChefCaisse' || $_SESSION['statut'] == 'Caissier';

                                        if (!empty($value['Valide'])) {
                                            if ($isAdminOrSuperadmin) {
                                                $href = "/Arreter/delete/" . $value['Valide']['RefSolde'];
                                            } else {
                                                $href = "#";
                                            }

                                            $class = "btn btn-success";
                                            $title = "Cliquez ici pour annuler l'arrêté de Caisse";
                                            $icon = "fa fa-lock";
                                        } else {
                                            if ($isAdminOrSuperadmin || $isChefCaisseOrCaissier) {
                                                $href = "/Arreter/close/" . $value['RefCaisse'];
                                            } else {
                                                $href = "#";
                                            }

                                            $class = "btn btn-danger";
                                            $title = "Cliquez ici pour  arreter la caisse";
                                            $icon = "fa fa-unlock";
                                        }
                                        ?>

                                  <a href="<?= $href; ?>" class="<?= $class; ?>" data-toggle="tooltip"
                                      title="<?= $title; ?>">
                                      <i class="<?= $icon; ?>"></i>
                                  </a>


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
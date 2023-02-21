  <form method="POST" id="formulaire">
      <div class="input-group">
          <div class="">
              <select class="form-control" name="RefPays" tabindex="1" id="RefPays">
                  <option value="0">Pays</option>
                  <?php foreach ($Pays as $key => $Pays) {   ?>
                      <option value="<?= $Pays['RefPays']; ?>">
                          <?= $Pays['nomPays']; ?></option>
                  <?php }   ?>
              </select>
          </div>
          &nbsp;
          <div class="">
              <select class="form-control" name="RefAgency" tabindex="1" id="RefAgency">
                  <option>Agence</option>
              </select>
          </div>
          &nbsp;
          <div class="">
              <select class="form-control" name="RefCaisse" tabindex="1" id="RefCaisse">
                  <option>Caisse</option>
              </select>
          </div>
          &nbsp;
          <div class="">
              <button type="submit" class="btn btn-primary" data-toggle="tooltip" title="Cliquez ici pour lancer la recherche"><i class="fas fa-search"></i></button>
          </div>
      </div>
  </form>
  </br>
  <?php if ($_SESSION['statut'] == 'ChefCaisse' or $_SESSION['statut'] == 'Caissier') { ?>
      <div class="col-lg-12 col-sm-12 col-xs-12">
          <?php foreach ($Agence as $key => $value) {
                if ($value['SommeDepot'] ==  0) { ?>
                  <div class="alert alert-danger" role="alert">
                      <span class="badge badge-danger"><?= $value['NameAgency']; ?> | Appro</span> Le solde
                      de la reserve est : <?= number_format($value['YesterdayReserve'], 0, '.', '.'); ?> | Merci d'Approvisonner
                      l'Agence
                      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                      </button>
                  </div>
          <?php  }
            } ?>
      </div>
  <?php } ?>
  <div class="row justify-content-center">
      <div class="col-lg-3 col-sm-6 col-xs-12">
          <div class="white-box analytics-info">
              <h3 class="box-title">DEPOT</h3>
              <ul class="list-inline two-part d-flex align-items-center mb-0">
                  <li class="ml-auto"><span class="counter text-danger"><?= number_format($SommeVersementGlobal, 0, '.', '.'); ?></span>
                  </li>
              </ul>
              <span>CAISSE</span>
          </div>
      </div>
      <div class="col-lg-3 col-sm-6 col-xs-12">
          <div class="white-box analytics-info">
              <h3 class="box-title">RETRAIT</h3>
              <ul class="list-inline two-part d-flex align-items-center mb-0">
                  <li class="ml-auto"><span class="counter text-purple"><?= number_format($SommeRetraitGlobal, 0, '.', '.'); ?></span>
                  </li>
              </ul>
              <span>CAISSE</span>
          </div>
      </div>
      <div class="col-lg-3 col-sm-6 col-xs-12">
          <div class="white-box analytics-info">
              <h5 class="box-title">SOLDE ESPECES</h5>
              <ul class="list-inline two-part d-flex align-items-center mb-0">
                  <li class="ml-auto"><span class="counter text-info">
                          <?= number_format($SoldeGlobal, 0, '.', '.'); ?>
                      </span>
                  </li>
              </ul>
              <span>CAISSE</span>
          </div>
      </div>
      <div class="col-lg-3 col-sm-6 col-xs-12">
          <div class="white-box analytics-info">
              <iframe src="https://www.zeitverschiebung.net/clock-widget-iframe-v2?language=fr&size=small&timezone=Africa%2FBamako" width="100%" height="90" frameborder="0" seamless></iframe>
          </div>
      </div>
  </div>
  <?php if ($_SESSION['statut'] == 'ChefCaisse' or $_SESSION['statut'] == 'Caissier' or $_SESSION['statut'] == 'admin') { ?>

      <?php foreach ($links as $key => $name) { ?>
          <a href="<?= $name['url']; ?>" <?php if ($name['target'] == 1) { ?> target="_blank" <?php } ?> class="btn btn-<?= $name['btn']; ?> mt-1"><i class="fa-solid fa-link"></i>
              <?= $name['url_name']; ?></a>
      <?php } ?>
  <?php } ?>
  </br> </br>

  <div class="row">
      <div class="col-md-12 col-lg-12 col-sm-12">
          <div class="white-box">

              <div class="d-md-flex mb-3">
                  <h3 class="box-title mb-0">OPERATIONS DU <?= date('d-m-Y'); ?> </h3>
              </div>
              <div class="table-responsive">
                  <table id="dataTable" class="display nowrap" cellspacing="0" width="100%">
                      <thead>
                          <tr>
                              <th class="border-top-0">RECU</th>
                              <th class="border-top-0">REF</th>
                              <th class="border-top-0">AGENCE</th>
                              <th class="border-top-0">PRODUIT</th>
                              <th class="border-top-0">CAISSE</th>
                              <th class="border-top-0">OPERATION</th>
                              <th class="border-top-0">CLIENT</th>
                              <th class="border-top-0">N°COMPTE</th>
                              <th class="border-top-0">MONTANT</th>
                              <th class="border-top-0">REMARQUE</th>

                          </tr>
                      </thead>
                      <tbody>
                          <?php foreach ($Operation as $key => $value) { ?>
                              <tr class="advance-table-row">
                                  <td><a href="/bordereau/<?= $value['RefOperations']; ?>" target="_blank" class="btn btn-primary" data-toggle="tooltip" title="Cliquez ici pour imprimer le bordereau"><i class="fa fa-print"></i> </td>
                                  <td> <?= $value['RefOperations']; ?></td>
                                  <td> <?= $value['NameAgency']; ?></td>
                                  <td> <?= $value['NameProduit']; ?></td>
                                  <td> <?= $value['NameCaisse']; ?></td>
                                  <td><?= $value['NameType']; ?></td>
                                  <td><?= $value['NameClient']; ?></td>
                                  <td><?= $value['NumCompte']; ?></td>
                                  <td class="counter text-danger">
                                      <?= number_format($value['MontantVersement'], 0, '.', '.'); ?></td>
                                  <td><?= $value['Remarque']; ?></td>
                              </tr>
                          <?php } ?>
                      </tbody>
                  </table>
              </div>
          </div>
      </div>
  </div>
  <a href="/dashboard" target="_blank" class="btn btn-secondary" data-toggle="tooltip" title="Cliquez ici pour voir les stats">Dashboard</a>
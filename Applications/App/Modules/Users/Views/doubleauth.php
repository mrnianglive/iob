  <?php
    require __DIR__ . '/../../../../../Web/vendor/autoload.php';

    use RobThree\Auth\TwoFactorAuth;

    $tfa = new TwoFactorAuth('CAISSE MALI CREANCES');



    ?>

  <div class="row">
      <!-- Column -->
      <div class="col-lg-4 col-xlg-3 col-md-12">
          <div class="white-box">
              <div class="user-bg">
                  <div class="overlay-box">
                      <div class="user-content">
                          <a href="javascript:void(0)"><img src="/images/mlc.png" width="100" height="100"
                                  class="thumb-lg img-circle" alt="img"></a>
                          <h4 class="text-white mt-2"><?= $Info['NomUsers'] . " " . $Info['PrenomUsers']; ?></h4>
                          <h5 class="text-white mt-2"><?= $Info['email']; ?></h5>
                      </div>
                  </div>
              </div>
              <br /></br / <div class="text-center">
              <p>QR Code :</p>
              <img src="<?= $tfa->getQRCodeImageAsDataUri('CAISSE MALI CREANCES', $secret) ?>" alt="test">

          </div>
      </div>
      <div class="col-lg-8 col-xlg-9 col-md-12">
          <div class="card">
              <div class="card-body">
                  <form class="form-horizontal form-material" method="POST" action="">
                      <div class="form-group mb-4">
                          <label for="example-email" class="col-md-12 p-0">Email</label>
                          <div class="col-md-12 border-bottom p-0">
                              <input type="email" value="<?= $Info['email']; ?>" name="email"
                                  class="form-control p-0 border-0" name="example-email" id="example-email">
                          </div>
                      </div>
                      <div class="form-group mb-4">
                          <label class="col-md-12 p-0">Statut</label>
                          <div class="col-md-12 border-bottom p-0">
                              <input type="text" value="<?= $Info['Name']; ?>" class="form-control p-0 border-0">
                          </div>
                      </div>
                      <div class="form-group mb-4">
                          <label class="col-md-12 p-0">Agence</label>
                          <div class="col-md-12 border-bottom p-0">
                              <input type="text" value="<?= $Info['AgenceUsers']; ?>" name="AgenceUsers"
                                  class="form-control p-0 border-0">
                          </div>
                      </div>
                      <button class="btn btn-primary" type="submit">Enregistrer</button>
                  </form>
              </div>
          </div>
      </div>
  </div>

  <div class="modal fade" id="Password" tabindex="-1" role="dialog" aria-labelledby="password">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                          aria-hidden="true">&times;</span></button>
              </div>
              <form role="form" method="post" action="">
                  <div class="modal-body">
                      <input type="password" id="password" class="form-control" name="password"
                          placeholder="Veuillez entrer votre acien mot de passe" required="">
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
                      <button type="submit" class="btn btn-primary">Continuer</button>
                  </div>
              </form>
          </div>
      </div>
  </div>
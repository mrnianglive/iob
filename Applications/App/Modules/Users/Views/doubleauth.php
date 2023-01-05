  <?php
    require __DIR__ . '/../../../../../Web/vendor/autoload.php';

    use RobThree\Auth\TwoFactorAuth;

    $tfa = new TwoFactorAuth();

    $secret = $tfa->createSecret();

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
              <?php if (!$Info['secret']) : ?>
              <p>QR Code :</p>
              <img src="<?= $tfa->getQRCodeImageAsDataUri('CAISSE MLC', $secret) ?>">
              <?php else : ?>
              <p>2FA activée</p>
              <a href="/Users/doubleauth/reset/<?= $Info['RefUsers']; ?>" class="btn btn-danger"><i
                      class="fa fa-lock"></i>
                  Reset 2FA CODE </a>
              <?php endif ?>
          </div>
      </div>
      <?php if (!$Info['secret']) { ?>
      <div class="col-lg-8 col-xlg-9 col-md-12">
          <div class="card">
              <div class="card-body">
                  <form class="form-horizontal form-material" method="POST">
                      <div class="form-group mb-4">
                          <label class="col-md-12 p-0">Secret CODE</label>
                          <div class="col-md-12 border-bottom p-0">
                              <input type="text" name="secret" value="<?= $secret ?>" class="form-control p-0 border-0"
                                  readonly>
                          </div>
                      </div>
                      <input type="hidden" name="RefUsers" value="<?= $Info['RefUsers']; ?>">
                      <button class="btn btn-primary" type="submit"
                          onclick=" return confirm('Assurez vous de faire une capture du QRcode Avant de cliquer sur valider ?');">Valide
                          2FA </button>
                  </form>
              </div>
          </div>
      </div>
      <?php } ?>
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
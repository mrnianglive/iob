<?php if ($user->hasFlash()) { ?>
    <p><?= $user->getFlash(); ?></p>
<?php } ?>
<form method="post">
    <img src="/images/mlc.png" alt="mlc" width="200" height="100%">
    <!-- <div class="text-center social-btn">
        <a href="/authMicrosoft" class="btn btn-warning btn-block"><i class="fa-brands fa-microsoft"></i> Se connecter
            avec
            <b>Microsoft</b></a>
    </div>
    <div class="or-seperator"><i>or</i></div> -->
    <label for="inputEmail" class="sr-only">Login</label>
    <input type="text" id="login" class="form-control mb-1" name="login" placeholder="Login" required autofocus>
    <span id="statut"></span>
    <label for="inputPassword" class="sr-only">Password</label>
    <input type="password" id="inputPassword" class="form-control" name="password" placeholder="Password" required>
    &nbsp;
    <button class="btn btn-lg btn-info btn-block" type="submit" id="register">Connexion</button>

</form>
<p class="mt-5 mb-3 text-muted">&copy; IOB AGENCY |MALI CREANCES Tous les droits sont réservés. <?= date('Y'); ?>
</p>
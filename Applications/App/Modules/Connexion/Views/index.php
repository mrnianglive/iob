<?php if ($user->hasFlash()) { ?>
<p><?= $user->getFlash(); ?></p>
<?php } ?>
<form method="post">
    <img src="/images/mlc.png" alt="mlc" width="200" height="100%">
    <label for="inputEmail" class="sr-only">Login</label>
    <input type="text" id="login" class="form-control mb-1" name="login" placeholder="Login" required autofocus>
    <span id="statut"></span>
    <label for="inputPassword" class="sr-only">Password</label>
    <input type="password" id="inputPassword" class="form-control" name="password" placeholder="Password" required>
    &nbsp;
    <button class="btn btn-lg btn-info btn-block" type="submit" id="register">Connexion</button>
    <p class="mt-5 mb-3 text-muted">&copy; IOB AGENCY |MALI CREANCES <?= date('Y'); ?>
    </p>
</form>
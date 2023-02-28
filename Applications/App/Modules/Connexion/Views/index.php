<?php if ($user->hasFlash()) { ?>
<p><?= $user->getFlash(); ?></p>
<?php } ?>
<form method="post">
    <img src="/images/afc.png" alt="afc" width="200" height="100%">
    <div class="text-center social-btn">
        <a href="#" class="btn btn-primary btn-block"><i class="fa fa-facebook"></i> Sign in with <b>Facebook</b></a>
        <a href="#" class="btn btn-info btn-block"><i class="fa fa-twitter"></i> Sign in with <b>Twitter</b></a>
        <a href="#" class="btn btn-danger btn-block"><i class="fa fa-google"></i> Sign in with <b>Google</b></a>
    </div>
    <div class="or-seperator"><i>or</i></div>

    <label for="inputEmail" class="sr-only">Login</label>
    <input type="text" id="login" class="form-control mb-1" name="login" placeholder="Login" required autofocus>
    <span id="statut"></span>
    <label for="inputPassword" class="sr-only">Password</label>
    <input type="password" id="inputPassword" class="form-control" name="password" placeholder="Password" required>
    <br>
    <button class="bsk-btn bsk-btn-default">
        <object type="image/svg+xml"
            data="https://s3-eu-west-1.amazonaws.com/cdn-testing.web.bas.ac.uk/scratch/bas-style-kit/ms-pictogram/ms-pictogram.svg"
            class="x-icon"></object>
        Sign In</button>
    &nbsp;
    <button class="btn btn-lg btn-primary btn-block" type="submit" id="register">Connexion</button>
    <p class="mt-5 mb-3 text-muted">&copy; AFRIK CREANCES <?= date('Y'); ?> Developed by NIANGALY</p>
</form>
<?php if ($user->hasFlash()) { ?>
<p><?= $user->getFlash(); ?></p>
<?php }  ?>
<!-- <form method="post">
    <div class="form-group">
        <label class="small mb-1" for="inputEmailAddress">Login</label>
        <input class="form-control py-4 " id="login" type="text" name="login" />
        <span id="statut"></span>
    </div>
    <div class="form-group">
        <label class="small mb-1" for="inputPassword">Password</label>
        <input class="form-control py-4" id="inputPassword" type="password" name="password" />
    </div>
    <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
        <button class="btn btn-primary" id="register" type="submit">Login</button>
    </div>
</form> -->


<form method="post">
    <img class="mb-4" src="/images/mlc.png" alt="mlc" width="200" height="200">
    <h1 class="h3 mb-3 font-weight-normal">Connectez Vous</h1>

    <label for="inputEmail" class="sr-only">Login</label>
    <input type="email" id="login" class="form-control" placeholder="Login" required autofocus>
    <span id="statut"></span>
    <label for="inputPassword" class="sr-only">Password</label>
    <input type="password" id="inputPassword" class="form-control" placeholder="Password" required>
    </br>
    <button class="btn btn-lg btn-primary btn-block" type="submit" id="register">Connexion</button>
    <p class="mt-5 mb-3 text-muted">&copy; MALI CREANCES 2021 Develop by NIANGALY</p>
</form>
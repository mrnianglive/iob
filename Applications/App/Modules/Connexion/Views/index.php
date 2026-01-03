<?php if ($user->hasFlash()) { ?>
<p><?= $user->getFlash(); ?></p>
<?php }  ?>
<form method="post">
    <img src="/images/mlc.png" alt="mlc" width="200" height="100%">
    <label for="inputEmail" class="sr-only">Login</label>
    <input type="text" id="login" class="form-control mb-1" name="login" placeholder="Login" required autofocus>
    <span id="statut"></span>
    <label for="inputPassword" class="sr-only">Password</label>
    <div class="input-group">
        <input type="password" id="inputPassword" class="form-control" name="password" placeholder="Password" required>
        <span class="input-group-append">
            <span class="input-group-text" style="cursor:pointer" id="togglePassword">
                <i class="fa fa-eye" id="toggleIcon"></i>
            </span>
        </span>
    </div>
    <script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        var pwd = document.getElementById('inputPassword');
        var icon = document.getElementById('toggleIcon');
        if (pwd.type === 'password') {
            pwd.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            pwd.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
    </script>
    <?= \Library\CSRF::getInput(); ?>
    </br>
    <button class="btn btn-lg btn-primary btn-block" type="submit" id="register">Connexion</button>
    <p class="mt-5 mb-3 text-muted">&copy; IOB AGENCY | MALI CREANCES <?= date('Y'); ?></p>
</form>
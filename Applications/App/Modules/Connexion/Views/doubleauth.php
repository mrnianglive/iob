<form method="post">
    <img src="/images/afc.png" alt="mlc" width="200" height="100%">
    <label for="inputEmail" class="sr-only">Code</label>
    <input type="text" id="tfa_code" class="form-control mb-1" name="tfa_code"
        placeholder="Veuillez saisir le code généré par l'authentificateur" required autofocus>
    <button class="btn btn-lg btn-success btn-block" type="submit">Valider</button>
    <a href="/logout" class="btn btn-lg btn-danger btn-block">Annuler</a>
    <p class="mt-5 mb-3 text-muted">&copy; IOB AGENCY | MALI CREANCES <?= date('Y'); ?></p>
</form>
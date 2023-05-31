<div class="row">
    <div class="col-md-10">
        <div class="white-box">
            <form class="form-horizontal" method="post" action="">
                <div class="form-group">
                    <label for="nom" class="control-label"> Nom</label>
                    <input class="form-control" name="NomUsers" type="text" value="<?= $Info['NomUsers']; ?>" />
                </div>
                <div class="form-group">
                    <label for="nom" class="cpntrol-label"> Prenom</label>
                    <input class="form-control" name="PrenomUsers" type="text" value="<?= $Info['PrenomUsers']; ?>" />
                </div>
                <div class="form-group">
                    <label for="nom" class="control-label"> Email</label>
                    <input class="form-control" name="email" type="email" value="<?= $Info['email']; ?>" />
                </div>
                <div class="form-group">
                    <label for="nom" class="control-label">Login</label>
                    <input class="form-control" name="login" type="text" value="<?= $Info['login']; ?>" />
                </div>
                <div class="form-group">
                    <label for="stataut" class="control-label">Statut</label>
                    <select class="form-control" name="RefStatut" required>
                        <option>Veuillez Choisir</option>
                        <?php foreach ($ListeStatut as $key => $statut) { ?>
                            <option value="<?= $statut['RefStatut']; ?>" <?php if ($statut['RefStatut'] == $Info['RefStatut']) { ?> selected="" <?php } ?>>
                                <?= $statut['Name']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <input class="form-control" name="RefUsers" type="hidden" value="<?= $Info['RefUsers']; ?>" />

                <div class="form-group">
                    <label for="nom" class="control-label">Password</label>
                    <input class="form-control" name="password" type="password" />
                </div>

                <div class="form-group">
                    <label for="stataut" class="control-label">Pays</label>
                    <select class="form-control" name="RefPays" required>
                        <option>Veuillez Choisir</option>
                        <?php foreach ($ListePays as $key => $pays) { ?>
                            <option value="<?= $pays['RefPays']; ?>" <?php if ($pays['RefPays'] == $Info['RefPays']) { ?> selected="" <?php } ?>>
                                <?= $pays['nomPays']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="stataut" class="control-label">Partenaire</label>
                    <select class="form-control" name="RefBanque" required>
                        <option>Veuillez Choisir</option>
                        <?php foreach ($ListeBanque as $key => $banque) { ?>
                            <option value="<?= $banque['RefBanque']; ?>" <?php if ($banque['RefBanque'] == $Info['RefBanque']) { ?> selected="" <?php } ?>>
                                <?= $banque['NameBanque']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <button class="btn btn-primary"><i class="fa fa-edit"> Modifier</i></button>
            </form>
        </div>
    </div>
</div>
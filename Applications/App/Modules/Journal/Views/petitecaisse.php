  <div class="row">
      <div class="col-md-12">
          <form method="POST" id="formulaire">
              <div class="input-group">
                  <div class="col-md-3">Journée du:
                      <input type="date" id="jour" name="jour" value="<?= $day; ?>" class="form-control">
                  </div>
                  <div class=""></br>
                      <button type="submit" class="btn btn-primary" data-toggle="tooltip"
                          title="Cliquer ici pour charger les informations"><i class="fa fa-search"></i></button>
                  </div>
              </div>
          </form><br />
          <div class="white-box">
              <h3 class="box-title">Petite Caisse</h3>
              <div class="table-responsive">
                  <table id="dataTable" class="display nowrap" cellspacing="0" width="100%">
                      <thead>
                          <tr>
                              <th class="border-top-0">Agence</th>
                              <th class="border-top-0">Caisse</th>
                              <th class="border-top-0">Appro Caisse</th>
                              <th class="border-top-0">Appro C2C</th>
                              <th class="border-top-0">Sortie de Fond</th>
                              <th class="border-top-0">Depot</th>
                              <th class="border-top-0">Retrait</th>
                              <?php if ($_SESSION['RefPays'] != 1) { ?>
                              <th class="border-top-0">Frais Timbre</th>
                              <?php } ?>

                              <th class="border-top-0">Solde Caisse</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php foreach ($Agence as $value) { ?>
                          <tr>
                              <td><?= $value['NameAgency']; ?></td>
                              <td>
                                  <ul>
                                      <?php foreach ($value['Afficher'] as $print) { ?>
                                      <li><?= $print['NameCaisse']; ?></li>
                                      <?php } ?>
                                  </ul>
                              </td>
                              <td>
                                  <ul>
                                      <?php foreach ($value['Afficher'] as $afficher) { ?>
                                      <li><?= number_format($afficher['SoldeInitial'], 0, '.', '.'); ?></li>
                                      <?php } ?>
                                  </ul>
                              </td>


                              <td>
                                  <ul>
                                      <?php foreach ($value['Afficher'] as $afficher) { ?>
                                      <li><?= number_format($afficher['TotalAppro'], 0, '.', '.'); ?></li>
                                      <?php } ?>
                                  </ul>
                              </td>
                              <td>
                                  <ul>
                                      <?php foreach ($value['Afficher'] as $afficher) { ?>
                                      <li><?= number_format($afficher['TotalSortieCaisse'], 0, '.', '.'); ?></li>
                                      <?php } ?>
                                  </ul>
                              </td>
                              <td>
                                  <ul>
                                      <?php foreach ($value['Afficher'] as $afficher) { ?>
                                      <li><?= number_format($afficher['TotalVersement'] + $afficher['SoldeRemittanceVersement'], 0, '.', '.'); ?>
                                      </li>
                                      <?php } ?>
                                  </ul>
                              </td>
                              <td>
                                  <ul>
                                      <?php foreach ($value['Afficher'] as $afficher) { ?>
                                      <li><?= number_format($afficher['TotalRetrait'] + $afficher['SoldeRemittanceRetrait'], 0, '.', '.'); ?>
                                      </li>
                                      <?php } ?>
                                  </ul>
                              </td>
                              <?php if ($_SESSION['RefPays'] != 1) { ?>
                              <td>
                                  <ul>
                                      <?php foreach ($value['Afficher'] as $afficher) { ?>
                                      <li><?= number_format($afficher['TotalFraisTimbre'], 0, '.', '.'); ?></li>
                                      <?php } ?>
                                  </ul>
                              </td>
                              <?php } ?>

                              <td>
                                  <ul>
                                      <?php foreach ($value['Afficher'] as $afficher) { ?>
                                      <li><?= number_format($afficher['SoldeDisponible'], 0, '.', '.'); ?></li>
                                      <?php } ?>
                                  </ul>
                              </td>
                          </tr>
                          <?php } ?>
                      </tbody>
                  </table>
              </div>
          </div>
      </div>
      <div class="col-md-12">
          <div class="white-box">
              <h3 class="box-title">Solde Reserve</h3>
              <div class="table-responsive">
                  <table id="dataTable1" class="display nowrap" cellspacing="0" width="100%">
                      <thead>
                          <tr>
                              <th class="border-top-0">Agence</th>
                              <th class="border-top-0">Solde Reserve(J-1)</th>
                              <th class="border-top-0">Solde Reserve</th>
                              <th class="border-top-0">Depot</th>
                              <th class="border-top-0">Retrait</th>
                              <?php if ($_SESSION['RefPays'] != 1) { ?>
                              <th class="border-top-0">Frais Timbre</th>
                              <?php } ?>
                              <th class="border-top-0">Solde Agence</th>
                              <?php if ($_SESSION['statut'] == 'superadmin' or $_SESSION['statut'] == 'admin' or $_SESSION['statut'] == 'ChefCaisse' or $_SESSION['statut'] == 'Caissier') { ?>
                              <th class="border-top-0">Action</th>
                              <?php } ?>

                          </tr>
                      </thead>
                      <tbody>
                          <?php foreach ($Agence as $value) { ?>
                          <tr data-agency-id="<?= $value['RefAgency']; ?>">
                              <td><span class="btn btn-primary" data-toggle="modal"
                                      data-target="#depotModal-<?= $value['RefAgency']; ?>" data-whatever="@mdo"
                                      title="Cliquer pour voir les details">
                                      <?= $value['NameAgency']; ?>
                                  </span> </td>
                              <td class="YesterdayReserve">
                                  <?= number_format($value['YesterdayReserve'], 0, '.', '.'); ?><br>
                                  <small class="LastDate">
                                      <?= $value['LastDate']; ?>
                                  </small>
                              </td>
                              <td class="DayReserve"><?= number_format($value['DayReserve'], 0, '.', '.'); ?></td>
                              <td class="SommeDepotWithRemittance">
                                  <?= number_format($value['SommeDepotWithRemittance'], 0, '.', '.'); ?></td>
                              <td class="SommeSortieWithRemittance">
                                  <?= number_format($value['SommeSortieWithRemittance'], 0, '.', '.'); ?>
                              </td>
                              <?php if ($_SESSION['RefPays'] != 1) { ?>
                              <td class="SommeTimbre"><?= number_format($value['SommeTimbre'], 0, '.', '.'); ?></td>
                              <?php } ?>
                              <td class="ReserveActuelle"><?= number_format($value['ReserveActuelle'], 0, '.', '.'); ?>
                              </td>
                              <?php if ($_SESSION['statut'] == 'superadmin' or  $_SESSION['statut'] == 'admin' or $_SESSION['statut'] == 'ChefCaisse' or $_SESSION['statut'] == 'Caissier') { ?>
                              <td> <?php if (!empty($value['validate'])) { ?><a
                                      <?php if ($_SESSION['statut'] == 'superadmin' or  $_SESSION['statut'] == 'admin') { ?>
                                      href="/Arreter/cancel/<?= $value['validate']['RefCompte']; ?>/<?= $value['RefAgency']; ?>/<?= $day; ?>"
                                      <?php } ?> class="btn btn-success" data-toggle="tooltip"
                                      title="Cliquez ici pour reouvrir l'agence"><i class="fa  fa-lock"></i></a>
                                  <?php } else { ?>
                                  <form method="POST" action="/Arreter/reserve">
                                      <input type="hidden" value="<?= $value['ReserveActuelle']; ?>"
                                          name="ReserveActuelle">
                                      <input type="hidden" value="<?= $day; ?>" name="daycloture">
                                      <input type="hidden" value="<?= $value['RefAgency']; ?>" name="RefAgency">
                                      <button type="submit" class="btn btn-danger" data-toggle="tooltip"
                                          title="Cliquez ici pour fermer les caisses de l'agence"><i
                                              class="fa fa-unlock"></i></button>
                                  </form>
                                  <?php } ?>
                              </td>
                              <?php } ?>
                              <div class='modal fade' id='depotModal-<?= $value['RefAgency']; ?>' tabindex='-1'
                                  role='dialog' aria-labelledby='AddCaisse'>
                                  <div class='modal-dialog' role='document'>
                                      <div class='modal-content'>
                                          <div class='modal-header'>Volume Dépôt - Retrait Par Produit
                                              -<?= $value['NameAgency']; ?>
                                              <button type='button' class='close' data-dismiss='modal'
                                                  aria-label='Close'>
                                                  <span aria-hidden='true'>&times;</span>
                                              </button>
                                          </div>
                                          <div class='modal-body'>
                                              <ul>
                                                  <?php foreach ([$value['SommeDepotProduit'], $value['SommeSortieProduit']] as $index => $products) : ?>
                                                  <h5><?= $index === 0 ? 'Dépôt' : 'Retrait' ?></h5>
                                                  <?php foreach ($products as $product => $total) : ?>
                                                  <li><?= $product ?> :
                                                      <?= is_numeric($total) ? number_format($total, 0, '.', '.') : ($total ?? '0') ?>
                                                  </li>
                                                  <?php endforeach; ?>
                                                  <hr>
                                                  <?php endforeach; ?>
                                              </ul>
                                          </div>
                                          <div class='modal-footer'>
                                              <button type='button' class='btn btn-danger'
                                                  data-dismiss='modal'>Fermer</button>
                                          </div>
                                      </div>
                                  </div>
                              </div>

                          </tr>

                          <?php } ?>
                      </tbody>
                  </table>
              </div>
          </div>
      </div>
  </div>
  <script>
document.addEventListener('DOMContentLoaded', function() {
    const date = document.getElementById('jour').value;
    const agencies = <?php echo json_encode($Agence); ?>;

    agencies.forEach(agency => {
        fetch('/Journal/petite_caisse/data', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `date=${encodeURIComponent(date)}&refAgency=${encodeURIComponent(agency.RefAgency)}`
            })
            .then(response => response.json())
            .then(data => {
                updateAgencyData(agency.RefAgency, data);
            })
            .catch(error => console.error('Error:', error));
    });
});

function updateAgencyData(agencyId, data) {
    const row = document.querySelector(`tr[data-agency-id="${agencyId}"]`);
    if (!row) return;

    // Update YesterdayReserve
    row.querySelector('.YesterdayReserve').textContent = formatNumber(data.YesterdayReserve);
    row.querySelector('.LastDate').innerHTML = data.LastDate;

    // Update DayReserve (using DayReserve from API)
    row.querySelector('.DayReserve').textContent = formatNumber(data.DayReserve);

    // Update SommeDepotWithRemittance
    row.querySelector('.SommeDepotWithRemittance').textContent = formatNumber(data.SommeDepotWithRemittance);

    // Update SommeSortieWithRemittance
    row.querySelector('.SommeSortieWithRemittance').textContent = formatNumber(data.SommeSortieWithRemittance);

    // Update SommeTimbre
    if (row.querySelector('.SommeTimbre')) {
        row.querySelector('.SommeTimbre').textContent = formatNumber(data.SommeTimbre);
    }

    // Update ReserveActuelle
    row.querySelector('.ReserveActuelle').textContent = formatNumber(data.ReserveActuelle);

    // Update modal content
    updateModalContent(agencyId, data);
}

function updateModalContent(agencyId, data) {
    const modal = document.querySelector(`#depotModal-${agencyId}`);
    if (!modal) return;

    const modalBody = modal.querySelector('.modal-body');
    modalBody.innerHTML = '';

    // Add agency summary
    modalBody.innerHTML += `
        <h5>Résumé de l'agence</h5>
        <p>Solde Reserve (J-1): ${formatNumber(data.YesterdayReserve)}</p>
        <p>Solde Reserve: ${formatNumber(data.DayReserve)}</p>
        <p>Total Dépôt: ${formatNumber(data.SommeDepotWithRemittance)}</p>
        <p>Total Retrait: ${formatNumber(data.SommeSortieWithRemittance)}</p>
        <hr>
    `;

    // Add deposit and withdrawal details
    ['Dépôt', 'Retrait'].forEach((type, index) => {
        const productData = index === 0 ? data.SommeDepotProduit : data.SommeSortieProduit;

        modalBody.innerHTML += `<h5>${type} par produit</h5><ul>`;
        for (const [product, amount] of Object.entries(productData)) {
            if (amount !== null) {
                modalBody.innerHTML += `<li>${product}: ${formatNumber(amount)}</li>`;
            }
        }
        modalBody.innerHTML += '</ul><hr>';
    });
}

function formatNumber(number) {
    return new Intl.NumberFormat('fr-FR').format(parseFloat(number) || 0);
}
  </script>
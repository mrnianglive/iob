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
                      <tbody id="agencyTableBody">
                          <?php foreach ($Agence as $value) { ?>
                          <tr id="agency-row-<?= $value['RefAgency']; ?>" class="agency-row"
                              data-agency="<?= $value['RefAgency']; ?>">
                              <td><?= $value['NameAgency']; ?></td>
                              <td class="loading-placeholder">Chargement...</td>
                              <td class="loading-placeholder">Chargement...</td>
                              <td class="loading-placeholder">Chargement...</td>
                              <td class="loading-placeholder">Chargement...</td>
                              <td class="loading-placeholder">Chargement...</td>
                              <td class="loading-placeholder">Chargement...</td>
                              <?php if ($_SESSION['RefPays'] != 1) { ?>
                              <td class="loading-placeholder">Chargement...</td>
                              <?php } ?>
                              <td class="loading-placeholder">Chargement...</td>
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
                      <tbody id="reserveTableBody">
                          <?php foreach ($Agence as $value) { ?>
                          <tr id="reserve-row-<?= $value['RefAgency']; ?>" class="reserve-row"
                              data-agency="<?= $value['RefAgency']; ?>">
                              <td><span class="btn btn-primary" data-toggle="modal"
                                      data-target="#depotModal-<?= $value['RefAgency']; ?>" data-whatever="@mdo"
                                      title="Cliquer pour voir les details">
                                      <?= $value['NameAgency']; ?>
                                  </span></td>
                              <td class="loading-placeholder">Chargement...</td>
                              <td class="loading-placeholder">Chargement...</td>
                              <td class="loading-placeholder">Chargement...</td>
                              <td class="loading-placeholder">Chargement...</td>
                              <?php if ($_SESSION['RefPays'] != 1) { ?>
                              <td class="loading-placeholder">Chargement...</td>
                              <?php } ?>
                              <td class="loading-placeholder">Chargement...</td>
                              <?php if ($_SESSION['statut'] == 'superadmin' or  $_SESSION['statut'] == 'admin' or $_SESSION['statut'] == 'ChefCaisse' or $_SESSION['statut'] == 'Caissier') { ?>
                              <td class="loading-placeholder">Chargement...</td>
                              <?php } ?>
                          </tr>
                          <?php } ?>
                      </tbody>
                  </table>
              </div>
          </div>
      </div>
  </div>

  <style>
.loading-placeholder {
    color: #ccc;
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% {
        opacity: 0.6;
    }

    50% {
        opacity: 1;
    }

    100% {
        opacity: 0.6;
    }
}
  </style>

  <script>
document.addEventListener('DOMContentLoaded', function() {
    const day = document.getElementById('jour').value;
    const agencies = document.querySelectorAll('[data-agency]');

    function formatNumber(number) {
        return new Intl.NumberFormat('fr-FR').format(number);
    }

    function updateAgencyRow(agencyId, data) {
        // Update Petite Caisse table
        const agencyRow = document.getElementById(`agency-row-${agencyId}`);
        if (agencyRow) {
            let html = `
                <td>${data.Afficher.map(caisse => `<li>${caisse.NameCaisse}</li>`).join('')}</td>
                <td>${data.Afficher.map(caisse => `<li>${formatNumber(caisse.SoldeInitial)}</li>`).join('')}</td>
                <td>${data.Afficher.map(caisse => `<li>${formatNumber(caisse.TotalAppro)}</li>`).join('')}</td>
                <td>${data.Afficher.map(caisse => `<li>${formatNumber(caisse.TotalSortieCaisse)}</li>`).join('')}</td>
                <td>${data.Afficher.map(caisse => `<li>${formatNumber(caisse.TotalVersement + caisse.SoldeRemittanceVersement)}</li>`).join('')}</td>
                <td>${data.Afficher.map(caisse => `<li>${formatNumber(caisse.TotalRetrait + caisse.SoldeRemittanceRetrait)}</li>`).join('')}</td>
            `;

            if (window._SESSION_RefPays !== 1) {
                html +=
                    `<td>${data.Afficher.map(caisse => `<li>${formatNumber(caisse.TotalFraisTimbre)}</li>`).join('')}</td>`;
            }

            html +=
                `<td>${data.Afficher.map(caisse => `<li>${formatNumber(caisse.SoldeDisponible)}</li>`).join('')}</td>`;

            agencyRow.innerHTML = html;
        }

        // Update Solde Reserve table
        const reserveRow = document.getElementById(`reserve-row-${agencyId}`);
        if (reserveRow) {
            const cells = reserveRow.getElementsByTagName('td');
            cells[1].innerHTML = `${formatNumber(data.YesterdayReserve)}<br><small>${data.LastDate}</small>`;
            cells[2].innerHTML = formatNumber(data.DayReserve);
            cells[3].innerHTML = formatNumber(data.SommeDepotWithRemittance);
            cells[4].innerHTML = formatNumber(data.SommeSortieWithRemittance);

            let currentCell = 5;
            if (window._SESSION_RefPays !== 1) {
                cells[currentCell].innerHTML = formatNumber(data.SommeTimbre);
                currentCell++;
            }

            cells[currentCell].innerHTML = formatNumber(data.ReserveActuelle);
            currentCell++;

            if (cells[currentCell]) {
                if (data.validate) {
                    cells[currentCell].innerHTML = `
                        <a href="/Arreter/cancel/${data.validate.RefCompte}/${agencyId}/${day}"
                           class="btn btn-success" data-toggle="tooltip"
                           title="Cliquez ici pour reouvrir l'agence">
                            <i class="fa fa-lock"></i>
                        </a>`;
                } else {
                    cells[currentCell].innerHTML = `
                        <form method="POST" action="/Arreter/reserve">
                            <input type="hidden" value="${data.ReserveActuelle}" name="ReserveActuelle">
                            <input type="hidden" value="${day}" name="daycloture">
                            <input type="hidden" value="${agencyId}" name="RefAgency">
                            <button type="submit" class="btn btn-danger" data-toggle="tooltip"
                                    title="Cliquez ici pour fermer les caisses de l'agence">
                                <i class="fa fa-unlock"></i>
                            </button>
                        </form>`;
                }
            }
        }
    }

    // Load data for each agency
    agencies.forEach(agency => {
        const agencyId = agency.dataset.agency;
        fetch(`/Journal/getAgencyData?date=${day}&RefAgency=${agencyId}`)
            .then(response => response.json())
            .then(data => {
                updateAgencyRow(agencyId, data);
            })
            .catch(error => {
                console.error('Error loading agency data:', error);
            });
    });
});
  </script>
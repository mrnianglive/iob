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
                      <tbody id="agencyTableBody">
                          <!-- Data will be populated here by JavaScript -->
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

    function loadAgencyData() {
        const tableBody = document.getElementById('agencyTableBody');
        tableBody.innerHTML = ''; // Clear existing data

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
                    const row = createAgencyRow(agency, data);
                    tableBody.appendChild(row);
                    updateCaisseTable(agency.RefAgency, data.Afficher);
                })
                .catch(error => console.error('Error:', error));
        });
    }

    function createAgencyRow(agency, data) {
        const row = document.createElement('tr');
        row.dataset.agencyId = agency.RefAgency;

        row.innerHTML = `
            <td><span class="btn btn-primary" data-toggle="modal" data-target="#depotModal-${agency.RefAgency}" data-whatever="@mdo" title="Cliquer pour voir les details">
                ${agency.NameAgency}
            </span></td>
            <td class="YesterdayReserve">${formatNumber(data.YesterdayReserve)}<br><small>${data.LastDate}</small></td>
            <td class="DayReserve">${formatNumber(data.DayReserve)}</td>
            <td class="SommeDepotWithRemittance">${formatNumber(data.SommeDepotWithRemittance)}</td>
            <td class="SommeSortieWithRemittance">${formatNumber(data.SommeSortieWithRemittance)}</td>
            ${<?php echo $_SESSION['RefPays'] != 1 ? 'true' : 'false'; ?> ? `<td class="SommeTimbre">${formatNumber(data.SommeTimbre)}</td>` : ''}
            <td class="ReserveActuelle">${formatNumber(data.ReserveActuelle)}</td>
            ${createActionColumn(agency, data)}
        `;

        // Create and append the modal
        const modal = createModal(agency, data);
        document.body.appendChild(modal);

        return row;
    }

    function createActionColumn(agency, data) {
        if (['superadmin', 'admin', 'ChefCaisse', 'Caissier'].includes('<?php echo $_SESSION['statut']; ?>')) {
            if (data.validate) {
                return `
                    <td>
                        <a ${['superadmin', 'admin'].includes('<?php echo $_SESSION['statut']; ?>') ? `href="/Arreter/cancel/${data.validate.RefCompte}/${agency.RefAgency}/${date}"` : ''} 
                           class="btn btn-success" data-toggle="tooltip" title="Cliquez ici pour reouvrir l'agence">
                            <i class="fa fa-lock"></i>
                        </a>
                    </td>
                `;
            } else {
                return `
                    <td>
                        <form method="POST" action="/Arreter/reserve">
                            <input type="hidden" value="${data.ReserveActuelle}" name="ReserveActuelle">
                            <input type="hidden" value="${date}" name="daycloture">
                            <input type="hidden" value="${agency.RefAgency}" name="RefAgency">
                            <button type="submit" class="btn btn-danger" data-toggle="tooltip" title="Cliquez ici pour fermer les caisses de l'agence">
                                <i class="fa fa-unlock"></i>
                            </button>
                        </form>
                    </td>
                `;
            }
        }
        return '';
    }

    function updateCaisseTable(agencyId, caisseData) {
        const caisseTable = document.querySelector(`#caisseTable-${agencyId} tbody`);
        if (!caisseTable) return;

        caisseTable.innerHTML = '';
        caisseData.forEach(caisse => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${caisse.NameCaisse}</td>
                <td>${formatNumber(caisse.SoldeInitial)}</td>
                <td>${formatNumber(caisse.TotalAppro)}</td>
                <td>${formatNumber(caisse.TotalSortieCaisse)}</td>
                <td>${formatNumber(caisse.TotalVersement + caisse.SoldeRemittanceVersement)}</td>
                <td>${formatNumber(caisse.TotalRetrait + caisse.SoldeRemittanceRetrait)}</td>
                ${<?php echo $_SESSION['RefPays'] != 1 ? 'true' : 'false'; ?> ? `<td>${formatNumber(caisse.TotalFraisTimbre)}</td>` : ''}
                <td>${formatNumber(caisse.SoldeDisponible)}</td>
            `;
            caisseTable.appendChild(row);
        });
    }

    function createModal(agency, data) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = `depotModal-${agency.RefAgency}`;
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-labelledby', 'AddCaisse');

        modal.innerHTML = `
            <div class='modal-dialog' role='document'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        Volume Dépôt - Retrait Par Produit - ${agency.NameAgency}
                        <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                            <span aria-hidden='true'>&times;</span>
                        </button>
                    </div>
                    <div class='modal-body'>
                        ${createModalContent(data)}
                    </div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-danger' data-dismiss='modal'>Fermer</button>
                    </div>
                </div>
            </div>
        `;

        return modal;
    }

    function createModalContent(data) {
        let content = `
            <h5>Résumé de l'agence</h5>
            <p>Solde Reserve (J-1): ${formatNumber(data.YesterdayReserve)}</p>
            <p>Solde Reserve: ${formatNumber(data.DayReserve)}</p>
            <p>Total Dépôt: ${formatNumber(data.SommeDepotWithRemittance)}</p>
            <p>Total Retrait: ${formatNumber(data.SommeSortieWithRemittance)}</p>
            <hr>
        `;

        ['Dépôt', 'Retrait'].forEach((type, index) => {
            const productData = index === 0 ? data.SommeDepotProduit : data.SommeSortieProduit;
            content += `<h5>${type} par produit</h5><ul>`;
            for (const [product, amount] of Object.entries(productData)) {
                if (amount !== null) {
                    content += `<li>${product}: ${formatNumber(amount)}</li>`;
                }
            }
            content += '</ul><hr>';
        });

        return content;
    }

    function formatNumber(number) {
        return new Intl.NumberFormat('fr-FR').format(parseFloat(number) || 0);
    }

    // Initial load
    loadAgencyData();

    // Reload data when date changes
    document.getElementById('jour').addEventListener('change', loadAgencyData);
});
  </script>

  <!-- Add this table for each agency -->
  <?php foreach ($Agence as $agency): ?>
  <table id="caisseTable-<?php echo $agency['RefAgency']; ?>" style="display: none;">
      <thead>
          <tr>
              <th>Caisse</th>
              <th>Appro Caisse</th>
              <th>Appro C2C</th>
              <th>Sortie de Fond</th>
              <th>Depot</th>
              <th>Retrait</th>
              <?php if ($_SESSION['RefPays'] != 1): ?>
              <th>Frais Timbre</th>
              <?php endif; ?>
              <th>Solde Caisse</th>
          </tr>
      </thead>
      <tbody>
          <!-- This will be populated by JavaScript -->
      </tbody>
  </table>
  <?php endforeach; ?>
  <div class="row">
      <div class="col-md-12">
          <form method="POST" id="formulaire">
              <div class="input-group">
                  <div class="col-md-3">Journée du:
                      <input type="date" id="jour" name="jour" value="<?= $day; ?>" class="form-control">
                  </div>
                  <div class=""></br>
                      <button type="button" id="loadData" class="btn btn-primary" data-toggle="tooltip"
                          title="Cliquer ici pour charger les informations"><i class="fa fa-search"></i></button>
                  </div>
              </div>
          </form><br />
          <div id="loader" style="display: none;">Chargement en cours...</div>
          <div id="petiteCaisseData"></div>
      </div>
  </div>

  <script>
document.addEventListener('DOMContentLoaded', function() {
    const loadDataBtn = document.getElementById('loadData');
    const loader = document.getElementById('loader');
    const dataContainer = document.getElementById('petiteCaisseData');
    const dateInput = document.getElementById('jour');

    loadDataBtn.addEventListener('click', loadPetiteCaisseData);

    function loadPetiteCaisseData() {
        loader.style.display = 'block';
        dataContainer.innerHTML = '';

        fetch(`/Journal/petite_caisse/data?ajax=1&jour=${dateInput.value}`)
            .then(response => response.json())
            .then(data => {
                loader.style.display = 'none';
                displayPetiteCaisseData(data);
            })
            .catch(error => {
                console.error('Error:', error);
                loader.style.display = 'none';
                dataContainer.innerHTML = 'Une erreur est survenue lors du chargement des données.';
            });
    }

    function displayPetiteCaisseData(data) {
        // Créez ici le HTML pour afficher les données
        // Vous pouvez utiliser une fonction de template ou créer les éléments manuellement
        let html = '<div class="white-box">';
        html += '<h3 class="box-title">Petite Caisse</h3>';
        html += '<div class="table-responsive">';
        html += '<table id="dataTable" class="display nowrap" cellspacing="0" width="100%">';
        // Ajoutez ici les en-têtes et les lignes du tableau en fonction des données
        html += '</table></div></div>';

        dataContainer.innerHTML = html;
    }
});
  </script>
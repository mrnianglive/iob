  <div class="row">
      <div class="col-lg-4 col-md-12 col-sm-12">
          <div class="card">
              <div class="card-heading">
                  Bielletage du Jour
              </div>
              <div class="card-body">
                  <table class="table">
                      <tbody>
                          <tr>
                              <td>10.000</td>
                              <td class="counter text-danger"><?= $Biellet['dixmille']
                                                                ?></td>
                              <td>250</td>
                              <td class="counter text-danger"><?= $Biellet['deuxcentcinq'];
                                                                ?></td>
                          </tr>
                          <tr>
                              <td>5.000</td>
                              <td class="counter text-danger"><?= $Biellet['cinqmille'];
                                                                ?></td>
                              <td>200</td>
                              <td class="counter text-danger"><?= $Biellet['deuxcent'];
                                                                ?></td>
                          </tr>
                          <tr>
                              <td>2.000</td>
                              <td class="counter text-danger"><?= $Biellet['deuxmille'];
                                                                ?></td>
                              <td>100</td>
                              <td class="counter text-danger"><?= $Biellet['cent'];
                                                                ?></td>
                          </tr>
                          <tr>
                              <td>1.000</td>
                              <td class="counter text-danger"><?= $Biellet['mille'];
                                                                ?></td>
                              <td>50</td>
                              <td class="counter text-danger"><?= $Biellet['cinquante'];
                                                                ?></td>
                          </tr>
                          <tr>
                              <td>500</td>
                              <td class="counter text-danger"><?= $Biellet['cinqcent'];
                                                                ?></td>
                              <td>25</td>
                              <td class="counter text-danger"><?= $Biellet['vingtcinq'];
                                                                ?></td>
                          </tr>
                          <tr>
                              <td>10</td>
                              <td class="counter text-danger"><?= $Biellet['dix'];
                                                                ?></td>
                              <td>5</td>
                              <td class="counter text-danger"><?= $Biellet['cinq'];
                                                                ?></td>
                          </tr>
                          <tr>
                              <td style="border: none !important;"></td>
                              <td style="border: none !important;"></td>
                              <td>1</td>
                              <td class="counter text-danger"><?= $Biellet['un'];
                                                                ?></td>
                          </tr>

                      </tbody>
                  </table>
              </div>
          </div>
      </div>
      <div class="col-lg-4 col-md-12 col-sm-12">
          <div class="card">
              <div class="card-heading">
                  Solde du Jour <span class="badge badge-danger">New</span>
              </div>
              <div class="card-body">
                  <table class="table">
                      <tbody>
                          <tr>
                              <td>Depot</td>
                              <td><span class="counter text-danger">
                                      <?= number_format($SommeVersement ?? 0, 0, '.', '.');
                                        ?>
                                  </span>
                              </td>
                          </tr>
                          <tr>
                              <td>Retrait</td>
                              <td><span class="counter text-danger">
                                      <?= number_format($SommeRetrait ?? 0, 0, '.', '.');
                                        ?>
                                  </span>
                              </td>
                          </tr>
                          <tr>
                              <td>Solde Especes</td>
                              <td><span class="counter text-danger">
                                      <?= number_format($Solde ?? 0, 0, '.', ',');
                                        ?>
                                  </span>
                              </td>
                          </tr>
                      </tbody>

                      <tbody>
                          <tr>
                              <td>REMITTANCE|Depot </td>
                              <td><span class="counter text-danger">
                                      <?= number_format($SommeRemittanceDepot ?? 0, 0, '.', '.');
                                        ?>
                                  </span>
                              </td>
                          </tr>
                          <tr>
                              <td>REMITTANCE|Retrait</td>
                              <td><span class="counter text-danger">
                                      <?= number_format($SommeRemittanceRetrait ?? 0, 0, '.', '.');
                                        ?>
                                  </span>
                              </td>
                          </tr>
                          <tr>
                              <td> REMITTANCE|Solde </td>
                              <td><span class="counter text-danger">
                                      <?= number_format($SoldeRemittance ?? 0, 0, '.', '.');
                                        ?>
                                  </span>
                              </td>
                          </tr>
                      </tbody>
                  </table>
              </div>
          </div>
      </div>
      <div class="col-lg-4 col-md-12 col-sm-12">
          <div class="card">
              <div class="card-heading">
                  Info Caisse
              </div>
              <div class="card-body">
                  <table class="table">
                      <tbody>
                          <tr>
                              <td>Lundi</td>
                              <td>Versement</td>
                              <td>Retrait</td>
                          </tr>
                          <tr>
                              <td></td>
                              <td class="counter text-danger">
                                  <?= number_format($DailyVersement['LundiVersement'] ?? 0, 0, '.', ',');
                                    ?></td>
                              <td class="counter text-danger">
                                  <?= number_format($DailyVersement['LundiRetrait'] ?? 0, 0, '.', ',');
                                    ?></td>
                          </tr>
                          <tr>
                              <td>Mardi</td>
                              <td>Versement</td>
                              <td>Retrait
                              </td>
                          </tr>
                          <tr>
                              <td></td>
                              <td class="counter text-danger">
                                  <?= number_format($DailyVersement['MardiVersement'] ?? 0, 0, '.', ',');
                                    ?></td>
                              <td class="counter text-danger">
                                  <?= number_format($DailyVersement['MardiRetrait'] ?? 0, 0, '.', ',');
                                    ?></td>
                          </tr>
                          <tr>
                              <td>Mercredi</td>
                              <td>Versement</td>
                              <td>Retrait
                              </td>
                          </tr>
                          <tr>
                              <td></td>
                              <td class="counter text-danger">
                                  <?= number_format($DailyVersement['MercrediVersement'] ?? 0, 0, '.', ',');
                                    ?></td>
                              <td class="counter text-danger">
                                  <?= number_format($DailyVersement['MercrediRetrait'] ?? 0, 0, '.', ',');
                                    ?></td>
                          </tr>
                          <tr>
                              <td>Jeudi</td>
                              <td>Versement</td>
                              <td>Retrait
                              </td>
                          </tr>
                          <tr>
                              <td></td>
                              <td class="counter text-danger">
                                  <?= number_format($DailyVersement['JeudiVersement'] ?? 0, 0, '.', ',');
                                    ?></td>
                              <td class="counter text-danger">
                                  <?= number_format($DailyVersement['JeudiRetrait'] ?? 0, 0, '.', ',');
                                    ?></td>
                          </tr>
                          <tr>
                              <td>Vendredi</td>
                              <td>Versement</td>
                              <td>Retrait
                              </td>
                          </tr>
                          <tr>
                              <td></td>
                              <td class="counter text-danger">
                                  <?= number_format($DailyVersement['VendrediVersement'] ?? 0, 0, '.', ',');
                                    ?></td>
                              <td class="counter text-danger">
                                  <?= number_format($DailyVersement['VendrediRetrait'] ?? 0, 0, '.', ',');
                                    ?></td>
                          </tr>
                          <tr>
                              <td>Samedi</td>
                              <td>Versement</td>
                              <td>Retrait
                              </td>
                          </tr>
                          <tr>
                              <td></td>
                              <td class="counter text-danger">
                                  <?= number_format($DailyVersement['SamediVersement'] ?? 0, 0, '.', ',');
                                    ?></td>
                              <td class="counter text-danger">
                                  <?= number_format($DailyVersement['SamediRetrait'] ?? 0, 0, '.', ',');
                                    ?></td>
                          </tr>
                      </tbody>
                  </table>
              </div>
          </div>
      </div>
  </div>
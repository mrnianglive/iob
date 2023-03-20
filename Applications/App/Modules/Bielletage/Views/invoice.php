     <div class="wrapper wrapper-content" style="margin: 0px 32px;padding: 0px !important;">
         <div style="padding: 0px !important;">
             <div class="row"
                 style="border: 1px solid grey;border-radius: 4px;padding: 4px; background-color: #efefef;">
                 <div style="width: 10%; display: inline-block; vertical-align: top;">
                     <?php if ($GetInvoice['RefPays'] == 1) { ?>
                     <img src="/bordereau/mlc.jpg" alt="Logo" style="height: 40px; width: 100%;">
                     <img src="/bordereau/ecobank.jpg" alt="Logo" style="height: 40px; width: 100%;">
                     <?php } else { ?>
                     <img src="/images/afc.png" alt="Logo" style="height: 80px; width: 250%;">
                     <?php } ?>
                 </div>
                 <div style="text-align: center; width: 88%; display: inline-block;">
                     <p>
                         <?php if ($GetInvoice['RefPays'] == 1) { ?>
                         MALI CREANCES SA - Intermediare en Opérations de Banque et Recouvrement
                         <?php } else { ?>
                         AFRIK CREANCES
                         <?php } ?>
                         <img style="float: right; margin-right: -15px;"
                             src="/qr-code-generator.php?text=<?= $GetInvoice['uniqid'] ?: $GetInvoice['RefOperations'] . '' . date('dmY', strtotime($GetInvoice['Insert_Time'])); ?>"
                             width="80" height="80" alt="Logo">
                     </p>
                     <h2>
                         <?php
                            $type = '';
                            switch ($GetInvoice['RefType']) {
                                case 1:
                                    $type = 'VERSEMENT';
                                    break;
                                case 2:
                                    $type = 'RETRAIT';
                                    break;
                                case 3:
                                    $type = 'APPRO CAISSE';
                                    break;
                                case 4:
                                    $type = 'SORTIE DE FOND';
                                    break;
                            }
                            echo $type . ' ESPECES';
                            ?>
                     </h2>
                     <h3>BANQUE</h3>
                     <?php if ($getResetStatus == true) { ?>
                     <h3 style="color:#c62828;">Opération Annulée</h3>
                     <?php } ?>
                 </div>
             </div>

             <br>
             <div class="row">
                 <div style="width: 48%;display: inline-block;vertical-align: top;">
                     <p>AGENCE : <?= $GetInvoice['NameAgency']; ?></p>
                     <p>CONTACT : <?= $GetInvoice['TelAgence']; ?></p>
                     <p>REFERENCE :
                         <?= $GetInvoice['RefOperations'] . "" . $GetInvoice['NameAgency'] . "" . date('d-m-Y', strtotime($GetInvoice['Insert_Time'])); ?>
                     </p>
                     <p>DATE : <?= date('d-M-Y', strtotime($GetInvoice['Insert_Time'])); ?> <?= gmdate("H:i:s"); ?></p>
                     <p>MOTIF : <?= $GetInvoice['Remarque']; ?></p>
                     <p>N° DU COMPTE <?php if ($GetInvoice['RefType'] == 1) { ?>
                         CREDITE<?php } elseif ($GetInvoice['RefType'] == 2) { ?> DEBITE <?php } ?> :
                         <?= $GetInvoice['NumCompte']; ?></p>
                     <p class="text-uppercase">TITULAIRE : <?= $GetInvoice['NameClient']; ?></p>
                     <p>MONTANT<?php if ($GetInvoice['RefType'] == 1) { ?>
                         VERSE<?php } elseif ($GetInvoice['RefType'] == 2) { ?> RETIRE <?php } ?> :
                         <?= number_format($GetInvoice['MontantVersement'] + $GetInvoice['fraisTimbre'], 0, ".", ",") . " XOF"; ?>
                     </p>
                     <p>FRAIS TIMBRE: <?= $GetInvoice['fraisTimbre']; ?> XOF</p>
                     <p>MONTANT<?php if ($GetInvoice['RefType'] == 1) { ?>
                         CREDITE<?php } elseif ($GetInvoice['RefType'] == 2) { ?> DEBITE <?php } ?> :
                         <?= $numberToLetter; ?> (<?= number_format($GetInvoice['MontantVersement'], 0, ".", ","); ?>)
                         XOF
                     </p>

                     <p>DATE DE VALEUR : <?= date('d-M-Y', strtotime($GetInvoice['Insert_Time'])); ?></p>
                     <p class="text-uppercase">REMARQUES : <?= $GetInvoice['Remarque']; ?></p>
                     <p class="text-uppercase">CAISSIER(E) : <?= $GetInvoice['login']; ?></p>
                 </div>
                 <div style="width: 48%;display: inline-block;vertical-align: top;">
                     <div style="border: 1px solid grey;border-radius: 4px;padding: 4px 8px;margin-bottom: 10px;">
                         <p style="text-align: center;">
                             <b><u><?php if ($GetInvoice['RefType'] == 1) { ?>DEPOSANT<?php } elseif ($GetInvoice['RefType'] == 2) { ?>AUTEUR
                                     DU RETRAIT<?php } ?></u></b>
                         </p>
                         <p class="text-uppercase">NOM & PRENOM : <?= $GetInvoice['NameDeposant']; ?></p>
                         <p class="text-uppercase">TEL : <?= $GetInvoice['TelDeposant']; ?></p>
                         <p class="text-uppercase">SIGNATURE</p>
                         <br>
                     </div>
                     <table class="table table-bordered" style="border: none !important;">
                         <tbody style="<?php if ($getResetStatus == true) { ?>color:#c62828;<?php } ?>">
                             <tr>
                                 <td>10.000</td>
                                 <td><?= $GetInvoice['a2']; ?></td>
                                 <td>250</td>
                                 <td><?= $GetInvoice['f2']; ?></td>
                             </tr>
                             <tr>
                                 <td>5.000</td>
                                 <td><?= $GetInvoice['b2']; ?></td>
                                 <td>200</td>
                                 <td><?= $GetInvoice['g2']; ?></td>
                             </tr>
                             <tr>
                                 <td>2.000</td>
                                 <td><?= $GetInvoice['c2']; ?></td>
                                 <td>100</td>
                                 <td><?= $GetInvoice['h2']; ?></td>
                             </tr>
                             <tr>
                                 <td>1.000</td>
                                 <td><?= $GetInvoice['d2']; ?></td>
                                 <td>50</td>
                                 <td><?= $GetInvoice['i2']; ?></td>
                             </tr>
                             <tr>
                                 <td>500</td>
                                 <td><?= $GetInvoice['e2']; ?></td>
                                 <td>25</td>
                                 <td><?= $GetInvoice['j2']; ?></td>
                             </tr>
                             <tr>
                                 <td>500p</td>
                                 <td></td>
                                 <td>10</td>
                                 <td><?= $GetInvoice['k2']; ?></td>

                             </tr>
                             <tr>
                                 <td style="border: none !important;"></td>
                                 <td style="border: none !important;"></td>
                                 <td>5</td>
                                 <td><?= $GetInvoice['l2']; ?></td>

                             </tr>
                             <tr>
                                 <td style="border: none !important;"></td>
                                 <td style="border: none !important;"></td>
                                 <td>1</td>
                                 <td><?= $GetInvoice['m2']; ?></td>
                             </tr>
                         </tbody>
                     </table>
                 </div>
             </div>
         </div>
         <hr>
         <div style="padding: 0px !important;">
             <div class="row"
                 style="border: 1px solid grey;border-radius: 4px;padding: 4px; background-color: #efefef;">
                 <div style="width: 10%; display: inline-block; vertical-align: top;">
                     <?php if ($GetInvoice['RefPays'] == 1) { ?>
                     <img src="/bordereau/mlc.jpg" alt="Logo" style="height: 40px; width: 100%;">
                     <img src="/bordereau/ecobank.jpg" alt="Logo" style="height: 40px; width: 100%;">
                     <?php } else { ?>
                     <img src="/images/afc.png" alt="Logo" style="height: 80px; width: 250%;">
                     <?php } ?>
                 </div>
                 <div style="text-align: center; width: 88%; display: inline-block;">
                     <p>
                         <?php if ($GetInvoice['RefPays'] == 1) { ?>
                         MALI CREANCES SA - Intermediare en Opérations de Banque et Recouvrement
                         <?php } else { ?>
                         AFRIK CREANCES
                         <?php } ?>
                         <img style="float: right; margin-right: -15px;"
                             src="/qr-code-generator.php?text=<?= $GetInvoice['uniqid'] ?: $GetInvoice['RefOperations'] . '' . date('dmY', strtotime($GetInvoice['Insert_Time'])); ?>"
                             width="80" height="80" alt="Logo">
                     </p>
                     <h2>
                         <?php
                            $type = '';
                            switch ($GetInvoice['RefType']) {
                                case 1:
                                    $type = 'VERSEMENT';
                                    break;
                                case 2:
                                    $type = 'RETRAIT';
                                    break;
                                case 3:
                                    $type = 'APPRO CAISSE';
                                    break;
                                case 4:
                                    $type = 'SORTIE DE FOND';
                                    break;
                            }
                            echo $type . ' ESPECES';
                            ?>
                     </h2>
                     <h3>CLIENT</h3>
                     <?php if ($getResetStatus == true) { ?>
                     <h3 style="color:#c62828;">Opération Annulée</h3>
                     <?php } ?>
                 </div>
             </div>
             <br>
             <div class="row">
                 <div style="width: 48%;display: inline-block;vertical-align: top;">
                     <p>AGENCE : <?= $GetInvoice['NameAgency']; ?></p>
                     <p>CONTACT : <?= $GetInvoice['TelAgence']; ?></p>
                     <p>REFERENCE :
                         <?= $GetInvoice['RefOperations'] . "" . $GetInvoice['NameAgency'] . "" . date('d-m-Y', strtotime($GetInvoice['Insert_Time'])); ?>
                     </p>
                     <p>DATE : <?= date('d-M-Y', strtotime($GetInvoice['Insert_Time'])); ?> <?= gmdate("H:i:s"); ?></p>
                     <p>MOTIF : <?= $GetInvoice['Remarque']; ?></p>
                     <p>N° DU COMPTE <?php if ($GetInvoice['RefType'] == 1) { ?>
                         CREDITE<?php } elseif ($GetInvoice['RefType'] == 2) { ?> DEBITE <?php } ?> :
                         <?= $GetInvoice['NumCompte']; ?></p>
                     <p class="text-uppercase">TITULAIRE : <?= $GetInvoice['NameClient']; ?></p>
                     <p>MONTANT<?php if ($GetInvoice['RefType'] == 1) { ?>
                         VERSE<?php } elseif ($GetInvoice['RefType'] == 2) { ?> RETIRE <?php } ?> :
                         <?= number_format($GetInvoice['MontantVersement'] + $GetInvoice['fraisTimbre'], 0, ".", ",") . " XOF"; ?>
                     </p>
                     <p>FRAIS TIMBRE: <?= $GetInvoice['fraisTimbre']; ?> XOF
                     </p>
                     <p>MONTANT<?php if ($GetInvoice['RefType'] == 1) { ?>
                         CREDITE<?php } elseif ($GetInvoice['RefType'] == 2) { ?> DEBITE <?php } ?> :

                         <?= $numberToLetter; ?> (<?= number_format($GetInvoice['MontantVersement'], 0, ".", ","); ?>)
                         XOF
                     </p>

                     <p>DATE DE VALEUR : <?= date('d-M-Y', strtotime($GetInvoice['Insert_Time'])); ?></p>
                     <p class="text-uppercase">REMARQUES : <?= $GetInvoice['Remarque']; ?></p>
                     <p class="text-uppercase">CAISSIER(E) : <?= $GetInvoice['login']; ?></p>
                 </div>
                 <div style="width: 48%;display: inline-block;vertical-align: top;">
                     <div style="border: 1px solid grey;border-radius: 4px;padding: 4px 8px;margin-bottom: 10px;">
                         <p style="text-align: center;">
                             <b><u><?php if ($GetInvoice['RefType'] == 1) { ?>DEPOSANT<?php } elseif ($GetInvoice['RefType'] == 2) { ?>AUTEUR
                                     DU RETRAIT<?php } ?></u></b>
                         </p>
                         <p class="text-uppercase">NOM & PRENOM : <?= $GetInvoice['NameDeposant']; ?></p>
                         <p class="text-uppercase">TEL : <?= $GetInvoice['TelDeposant']; ?></p>
                         <p class="text-uppercase">SIGNATURE</p>
                         <br>
                     </div>
                     <table class="table table-bordered" style="border: none !important;">
                         <tbody style="<?php if ($getResetStatus == true) { ?>color:#c62828;<?php } ?>">
                             <tr>
                                 <td>10.000</td>
                                 <td><?= $GetInvoice['a2']; ?></td>
                                 <td>250</td>
                                 <td><?= $GetInvoice['f2']; ?></td>
                             </tr>
                             <tr>
                                 <td>5.000</td>
                                 <td><?= $GetInvoice['b2']; ?></td>
                                 <td>200</td>
                                 <td><?= $GetInvoice['g2']; ?></td>
                             </tr>
                             <tr>
                                 <td>2.000</td>
                                 <td><?= $GetInvoice['c2']; ?></td>
                                 <td>100</td>
                                 <td><?= $GetInvoice['h2']; ?></td>
                             </tr>
                             <tr>
                                 <td>1.000</td>
                                 <td><?= $GetInvoice['d2']; ?></td>
                                 <td>50</td>
                                 <td><?= $GetInvoice['i2']; ?></td>
                             </tr>
                             <tr>
                                 <td>500</td>
                                 <td><?= $GetInvoice['e2']; ?></td>
                                 <td>25</td>
                                 <td><?= $GetInvoice['j2']; ?></td>
                             </tr>
                             <tr>
                                 <td>500p</td>
                                 <td></td>
                                 <td>10</td>
                                 <td><?= $GetInvoice['k2']; ?></td>
                             </tr>
                             <tr>
                                 <td style="border: none !important;"></td>
                                 <td style="border: none !important;"></td>
                                 <td>5</td>
                                 <td><?= $GetInvoice['l2']; ?></td>
                             </tr>
                             <tr>
                                 <td style="border: none !important;"></td>
                                 <td style="border: none !important;"></td>
                                 <td>1</td>
                                 <td><?= $GetInvoice['m2']; ?></td>
                             </tr>
                         </tbody>
                     </table>
                 </div>
             </div>
         </div>
         <h5 style="text-align:right;">
             <?php if ($GetInvoice['RefPays'] == 1) { ?>MALI CREANCES SA - Intermediare en Opérations de Banque et
             Recouvrement |www.malicreances-sa.com<?php } else { ?>AFRIK CREANCES - Intermédiation en opérations
             bancaires et non bancaires |www.afrikcreances.com<?php } ?>
         </h5>
     </div>
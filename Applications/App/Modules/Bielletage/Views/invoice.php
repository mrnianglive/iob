                         <?php
// Déterminer le type d'opération et le badge correspondant
$refType = $GetInvoice['RefType'] ?? 0;
                            $type = '';
$badgeClass = '';

switch ($refType) {
                                case 1:
                                    $type = 'VERSEMENT';
        $badgeClass = 'versement';
                                    break;
                                case 2:
                                    $type = 'RETRAIT';
        $badgeClass = 'retrait';
                                    break;
                                case 3:
                                    $type = 'APPRO CAISSE';
        $badgeClass = 'appro';
                                    break;
                                case 4:
                                    $type = 'SORTIE DE FOND';
        $badgeClass = 'sortie';
                                    break;
                            }

if ($getResetStatus == true) {
    $badgeClass = 'cancelled';
}

// Formatage des dates
$insertTime = $GetInvoice['Insert_Time'] ?? 'now';
$dateFormatted = date('d-M-Y', strtotime($insertTime));
$timeFormatted = gmdate("H:i:s");
$dateValue = date('d-M-Y', strtotime($insertTime));

// Référence complète
$reference = ($GetInvoice['RefOperations'] ?? '') . '-' . ($GetInvoice['NameAgency'] ?? '') . '-' . date('d-m-Y', strtotime($insertTime));

// QR Code - Utiliser l'API externe directement
$qrData = $GetInvoice['uniqid'] ?? (($GetInvoice['RefOperations'] ?? '') . date('dmY', strtotime($insertTime)));
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($qrData);

// Logo selon le pays
$logoPath = (($GetInvoice['RefPays'] ?? 1) == 1) ? '/images/mlc.png' : '/images/afc.png';

// Montant formaté
$montantTotal = ($GetInvoice['MontantVersement'] ?? 0) + ($GetInvoice['fraisTimbre'] ?? 0);
$montantBase = $GetInvoice['MontantVersement'] ?? 0;
$fraisTimbre = $GetInvoice['fraisTimbre'] ?? 0;
?>

                         <!-- ==================== EXEMPLAIRE BANQUE ==================== -->
                         <div class="invoice-wrapper">
                             <!-- Filigrane de sécurité -->
                             <div class="invoice-watermark <?= $getResetStatus ? 'cancelled' : ''; ?>">
                                 <?= $getResetStatus ? 'ANNULEE' : 'EXEMPLAIRE BANQUE'; ?>
                             </div>

                             <!-- En-tête -->
                             <div class="invoice-header">
                                 <img src="<?= $logoPath; ?>" alt="Logo MLC" class="invoice-logo">
                                 <div class="invoice-header-center">
                                     <?php if (!empty($GetInvoice['NameBanque'])): ?>
                                     <div class="invoice-partner">PARTENAIRE <?= $GetInvoice['NameBanque']; ?></div>
                                     <?php endif; ?>
                                     <div class="invoice-title <?= $badgeClass; ?>"><?= $type; ?> ESPECES</div>
                                     <div class="invoice-copy-type">BANQUE</div>
                                 </div>
                                 <img src="<?= $qrUrl; ?>" alt="QR Code" class="invoice-qr">
                             </div>

                             <!-- Informations de l'opération -->
                             <div class="invoice-info-grid">
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">AGENCE</div>
                                     <div class="invoice-info-value"><?= $GetInvoice['NameAgency'] ?? ''; ?></div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">CONTACT</div>
                                     <div class="invoice-info-value"><?= $GetInvoice['TelAgence'] ?? ''; ?></div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">REFERENCE</div>
                                     <div class="invoice-info-value"><?= $reference; ?></div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">DATE</div>
                                     <div class="invoice-info-value"><?= $dateFormatted; ?> <?= $timeFormatted; ?></div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">MOTIF</div>
                                     <div class="invoice-info-value"><?= $GetInvoice['Remarques'] ?? ''; ?></div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">N° DU COMPTE
                                         <?php if ($refType == 1): ?>CREDITE<?php elseif ($refType == 2): ?>DEBITE<?php endif; ?>
                                     </div>
                                     <div class="invoice-info-value"><?= $GetInvoice['NumCompte'] ?? ''; ?></div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">TITULAIRE</div>
                                     <div class="invoice-info-value"><?= $GetInvoice['NameClient'] ?? ''; ?></div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">MONTANT
                                         <?php if ($refType == 1): ?>VERSE<?php elseif ($refType == 2): ?>RETIRE<?php endif; ?>
                                     </div>
                                     <div class="invoice-info-value"><?= number_format($montantTotal, 0, ",", "."); ?>
                                         XOF</div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">FRAIS TIMBRE</div>
                                     <div class="invoice-info-value"><?= number_format($fraisTimbre, 0, ",", "."); ?>
                                         XOF</div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">MONTANT CREDITE</div>
                                     <div class="invoice-info-value"><?= $numberToLetter ?? ''; ?>
                                         (<?= number_format($montantBase, 0, ",", "."); ?>) XOF</div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">DATE DE VALEUR</div>
                                     <div class="invoice-info-value"><?= $dateValue; ?></div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">REMARQUES</div>
                                     <div class="invoice-info-value"><?= $GetInvoice['Remarques'] ?? ''; ?></div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">CAISSIER(E)</div>
                                     <div class="invoice-info-value">
                                         <?= ($GetInvoice['PrenomUsers'] ?? '') . ' ' . ($GetInvoice['NomUsers'] ?? ''); ?>
                                     </div>
                                 </div>
                             </div>

                             <!-- Billetage et Signature -->
                             <div class="invoice-billetage-section">
                                 <!-- Tableau Billetage -->
                                 <div>
                                     <table class="invoice-billetage-table">
                                         <thead>
                                             <tr>
                                                 <th>Coupure</th>
                                                 <th>Qté</th>
                                                 <th>Coupure</th>
                                                 <th>Qté</th>
                                             </tr>
                                         </thead>
                                         <tbody <?= $getResetStatus ? 'style="color:#EF4444;"' : ''; ?>>
                                             <tr>
                                                 <td>10.000</td>
                                                 <td><?= !empty($GetInvoice['a2']) ? intval($GetInvoice['a2']) : ''; ?>
                                                 </td>
                                                 <td>250</td>
                                                 <td><?= !empty($GetInvoice['f2']) ? intval($GetInvoice['f2']) : ''; ?>
                                                 </td>
                                             </tr>
                                             <tr>
                                                 <td>5.000</td>
                                                 <td><?= !empty($GetInvoice['b2']) ? intval($GetInvoice['b2']) : ''; ?>
                                                 </td>
                                                 <td>200</td>
                                                 <td><?= !empty($GetInvoice['g2']) ? intval($GetInvoice['g2']) : ''; ?>
                                                 </td>
                                             </tr>
                                             <tr>
                                                 <td>2.000</td>
                                                 <td><?= !empty($GetInvoice['c2']) ? intval($GetInvoice['c2']) : ''; ?>
                                                 </td>
                                                 <td>100</td>
                                                 <td><?= !empty($GetInvoice['h2']) ? intval($GetInvoice['h2']) : ''; ?>
                                                 </td>
                                             </tr>
                                             <tr>
                                                 <td>1.000</td>
                                                 <td><?= !empty($GetInvoice['d2']) ? intval($GetInvoice['d2']) : ''; ?>
                                                 </td>
                                                 <td>50</td>
                                                 <td><?= !empty($GetInvoice['i2']) ? intval($GetInvoice['i2']) : ''; ?>
                                                 </td>
                                             </tr>
                                             <tr>
                                                 <td>500</td>
                                                 <td><?= !empty($GetInvoice['e2']) ? intval($GetInvoice['e2']) : ''; ?>
                                                 </td>
                                                 <td>25</td>
                                                 <td><?= !empty($GetInvoice['j2']) ? intval($GetInvoice['j2']) : ''; ?>
                                                 </td>
                                             </tr>
                                             <tr>
                                                 <td></td>
                                                 <td></td>
                                                 <td>10</td>
                                                 <td><?= !empty($GetInvoice['k2']) ? intval($GetInvoice['k2']) : ''; ?>
                                                 </td>
                                             </tr>
                                             <tr>
                                                 <td></td>
                                                 <td></td>
                                                 <td>5</td>
                                                 <td><?= !empty($GetInvoice['l2']) ? intval($GetInvoice['l2']) : ''; ?>
                                                 </td>
                                             </tr>
                                             <tr>
                                                 <td></td>
                                                 <td></td>
                                                 <td>1</td>
                                                 <td><?= !empty($GetInvoice['m2']) ? intval($GetInvoice['m2']) : ''; ?>
                                                 </td>
                                             </tr>
                                         </tbody>
                                     </table>
                                 </div>

                                 <!-- Zone Signature -->
                                 <div class="invoice-signature-box">
                                     <div class="invoice-signature-title">
                                         <?php if ($refType == 1): ?>DEPOSANT<?php elseif ($refType == 2): ?>AUTEUR DU
                                         RETRAIT<?php endif; ?></div>
                                     <div class="invoice-signature-field">
                                         <div class="invoice-signature-label">NOM & PRENOM</div>
                                         <div class="invoice-signature-value"><?= $GetInvoice['NameDeposant'] ?? ''; ?>
                                         </div>
                                     </div>
                                     <div class="invoice-signature-field">
                                         <div class="invoice-signature-label">TEL</div>
                                         <div class="invoice-signature-value"><?= $GetInvoice['TelDeposant'] ?? ''; ?>
                                         </div>
                                     </div>
                                     <div class="invoice-signature-line">SIGNATURE</div>
                                 </div>
                             </div>
                         </div>

                         <!-- Séparateur pour découpe -->
                         <hr class="invoice-divider">

                         <!-- ==================== EXEMPLAIRE CLIENT ==================== -->
                         <div class="invoice-wrapper">
                             <!-- Filigrane de sécurité -->
                             <div class="invoice-watermark <?= $getResetStatus ? 'cancelled' : ''; ?>">
                                 <?= $getResetStatus ? 'ANNULEE' : 'EXEMPLAIRE CLIENT'; ?>
                             </div>

                             <!-- En-tête -->
                             <div class="invoice-header">
                                 <img src="<?= $logoPath; ?>" alt="Logo MLC" class="invoice-logo">
                                 <div class="invoice-header-center">
                                     <?php if (!empty($GetInvoice['NameBanque'])): ?>
                                     <div class="invoice-partner">PARTENAIRE <?= $GetInvoice['NameBanque']; ?></div>
                                     <?php endif; ?>
                                     <div class="invoice-title <?= $badgeClass; ?>"><?= $type; ?> ESPECES</div>
                                     <div class="invoice-copy-type">CLIENT</div>
                                 </div>
                                 <img src="<?= $qrUrl; ?>" alt="QR Code" class="invoice-qr">
                             </div>

                             <!-- Informations de l'opération -->
                             <div class="invoice-info-grid">
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">AGENCE</div>
                                     <div class="invoice-info-value"><?= $GetInvoice['NameAgency'] ?? ''; ?></div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">CONTACT</div>
                                     <div class="invoice-info-value"><?= $GetInvoice['TelAgence'] ?? ''; ?></div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">REFERENCE</div>
                                     <div class="invoice-info-value"><?= $reference; ?></div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">DATE</div>
                                     <div class="invoice-info-value"><?= $dateFormatted; ?> <?= $timeFormatted; ?></div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">MOTIF</div>
                                     <div class="invoice-info-value"><?= $GetInvoice['Remarques'] ?? ''; ?></div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">N° DU COMPTE
                                         <?php if ($refType == 1): ?>CREDITE<?php elseif ($refType == 2): ?>DEBITE<?php endif; ?>
                                     </div>
                                     <div class="invoice-info-value"><?= $GetInvoice['NumCompte'] ?? ''; ?></div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">TITULAIRE</div>
                                     <div class="invoice-info-value"><?= $GetInvoice['NameClient'] ?? ''; ?></div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">MONTANT
                                         <?php if ($refType == 1): ?>VERSE<?php elseif ($refType == 2): ?>RETIRE<?php endif; ?>
                                     </div>
                                     <div class="invoice-info-value"><?= number_format($montantTotal, 0, ",", "."); ?>
                                         XOF</div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">FRAIS TIMBRE</div>
                                     <div class="invoice-info-value"><?= number_format($fraisTimbre, 0, ",", "."); ?>
                                         XOF</div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">MONTANT CREDITE</div>
                                     <div class="invoice-info-value"><?= $numberToLetter ?? ''; ?>
                                         (<?= number_format($montantBase, 0, ",", "."); ?>) XOF</div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">DATE DE VALEUR</div>
                                     <div class="invoice-info-value"><?= $dateValue; ?></div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">REMARQUES</div>
                                     <div class="invoice-info-value"><?= $GetInvoice['Remarques'] ?? ''; ?></div>
                                 </div>
                                 <div class="invoice-info-item">
                                     <div class="invoice-info-label">CAISSIER(E)</div>
                                     <div class="invoice-info-value">
                                         <?= ($GetInvoice['PrenomUsers'] ?? '') . ' ' . ($GetInvoice['NomUsers'] ?? ''); ?>
                                     </div>
                                 </div>
                             </div>

                             <!-- Billetage et Signature -->
                             <div class="invoice-billetage-section">
                                 <!-- Tableau Billetage -->
                                 <div>
                                     <table class="invoice-billetage-table">
                                         <thead>
                                             <tr>
                                                 <th>Coupure</th>
                                                 <th>Qté</th>
                                                 <th>Coupure</th>
                                                 <th>Qté</th>
                                             </tr>
                                         </thead>
                                         <tbody <?= $getResetStatus ? 'style="color:#EF4444;"' : ''; ?>>
                                             <tr>
                                                 <td>10.000</td>
                                                 <td><?= !empty($GetInvoice['a2']) ? intval($GetInvoice['a2']) : ''; ?>
                                                 </td>
                                                 <td>250</td>
                                                 <td><?= !empty($GetInvoice['f2']) ? intval($GetInvoice['f2']) : ''; ?>
                                                 </td>
                                             </tr>
                                             <tr>
                                                 <td>5.000</td>
                                                 <td><?= !empty($GetInvoice['b2']) ? intval($GetInvoice['b2']) : ''; ?>
                                                 </td>
                                                 <td>200</td>
                                                 <td><?= !empty($GetInvoice['g2']) ? intval($GetInvoice['g2']) : ''; ?>
                                                 </td>
                                             </tr>
                                             <tr>
                                                 <td>2.000</td>
                                                 <td><?= !empty($GetInvoice['c2']) ? intval($GetInvoice['c2']) : ''; ?>
                                                 </td>
                                                 <td>100</td>
                                                 <td><?= !empty($GetInvoice['h2']) ? intval($GetInvoice['h2']) : ''; ?>
                                                 </td>
                                             </tr>
                                             <tr>
                                                 <td>1.000</td>
                                                 <td><?= !empty($GetInvoice['d2']) ? intval($GetInvoice['d2']) : ''; ?>
                                                 </td>
                                                 <td>50</td>
                                                 <td><?= !empty($GetInvoice['i2']) ? intval($GetInvoice['i2']) : ''; ?>
                                                 </td>
                                             </tr>
                                             <tr>
                                                 <td>500</td>
                                                 <td><?= !empty($GetInvoice['e2']) ? intval($GetInvoice['e2']) : ''; ?>
                                                 </td>
                                                 <td>25</td>
                                                 <td><?= !empty($GetInvoice['j2']) ? intval($GetInvoice['j2']) : ''; ?>
                                                 </td>
                                             </tr>
                                             <tr>
                                                 <td></td>
                                                 <td></td>
                                                 <td>10</td>
                                                 <td><?= !empty($GetInvoice['k2']) ? intval($GetInvoice['k2']) : ''; ?>
                                                 </td>
                                             </tr>
                                             <tr>
                                                 <td></td>
                                                 <td></td>
                                                 <td>5</td>
                                                 <td><?= !empty($GetInvoice['l2']) ? intval($GetInvoice['l2']) : ''; ?>
                                                 </td>
                                             </tr>
                                             <tr>
                                                 <td></td>
                                                 <td></td>
                                                 <td>1</td>
                                                 <td><?= !empty($GetInvoice['m2']) ? intval($GetInvoice['m2']) : ''; ?>
                                                 </td>
                                             </tr>
                                         </tbody>
                                     </table>
                                 </div>

                                 <!-- Zone Signature -->
                                 <div class="invoice-signature-box">
                                     <div class="invoice-signature-title">
                                         <?php if ($refType == 1): ?>DEPOSANT<?php elseif ($refType == 2): ?>AUTEUR DU
                                         RETRAIT<?php endif; ?></div>
                                     <div class="invoice-signature-field">
                                         <div class="invoice-signature-label">NOM & PRENOM</div>
                                         <div class="invoice-signature-value"><?= $GetInvoice['NameDeposant'] ?? ''; ?>
                                         </div>
                                     </div>
                                     <div class="invoice-signature-field">
                                         <div class="invoice-signature-label">TEL</div>
                                         <div class="invoice-signature-value"><?= $GetInvoice['TelDeposant'] ?? ''; ?>
                                         </div>
                                     </div>
                                     <div class="invoice-signature-line">SIGNATURE</div>
                                 </div>
                             </div>

                             <!-- Pied de page -->
                             <div class="invoice-footer">
                                 <div class="invoice-footer-company">À très bientôt !</div>
                                 <div>MALI CREANCES - Intermédiation en opérations bancaires et recouvrement</div>
                                 <div><a href="https://www.malicreances-sa.com"
                                         class="invoice-footer-website">www.malicreances-sa.com</a></div>
                             </div>
                         </div>
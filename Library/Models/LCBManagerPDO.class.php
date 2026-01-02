<?php

namespace Library\Models;

class LCBManagerPDO extends LCBManager
{
    // Codes d'alertes
    const AML_TRANSACTION_ELEVEE = 'AML-001';
    const AML_CUMUL_JOURNALIER = 'AML-002';
    const AML_CUMUL_HEBDO = 'AML-003';
    const AML_FRACTIONNEMENT = 'AML-004';
    const AML_DEPOT_RETRAIT_RAPIDE = 'AML-005';
    const AML_MULTI_AGENCE = 'AML-006';

    /**
     * Analyse une transaction et genere les alertes necessaires
     * A appeler apres chaque Add() dans BielletageManagerPDO
     */
    public function analyzeTransaction($refOperation)
    {
        // Recuperer les details de l'operation
        $stmt = $this->dao->prepare("
            SELECT o.*, c.RefAgency, a.NameAgency, ca.NameCaisse
            FROM TbleOperations o
            INNER JOIN TbleCaisse ca ON ca.RefCaisse = o.RefCaisse
            INNER JOIN TbleAgency a ON a.RefAgency = ca.RefAgency
            LEFT JOIN TbleClients c ON c.NumCompte = o.NumCompte
            WHERE o.RefOperations = :refOp
        ");
        $stmt->bindValue(':refOp', $refOperation, \PDO::PARAM_INT);
        $stmt->execute();
        $operation = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$operation) return;
        
        $alertes = [];
        
        // 1. Verifier si le montant depasse le seuil de declaration
        $seuilDeclaration = $this->getSeuil('LCB_SEUIL_DECLARATION');
        if ($operation['MontantVersement'] >= $seuilDeclaration) {
            $alertes[] = $this->createAlerte(self::AML_TRANSACTION_ELEVEE, [
                'NumCompte' => $operation['NumCompte'],
                'RefOperations' => $refOperation,
                'RefAgency' => $operation['RefAgency'],
                'RefCaisse' => $operation['RefCaisse'],
                'Montant' => $operation['MontantVersement'],
                'Severite' => 'CRITIQUE',
                'Description' => sprintf(
                    "Transaction de %s FCFA (>= seuil CENTIF %s FCFA). Client: %s, Compte: %s, Agence: %s",
                    number_format($operation['MontantVersement'], 0, ',', ' '),
                    number_format($seuilDeclaration, 0, ',', ' '),
                    $operation['NameClient'],
                    $operation['NumCompte'],
                    $operation['NameAgency']
                )
            ]);
        }
        
        // 2. Verifier le cumul journalier
        $cumulJour = $this->getCumulClient($operation['NumCompte'], 'JOUR');
        $seuilCumulJour = $this->getSeuil('LCB_CUMUL_JOURNALIER');
        if ($cumulJour['Total'] >= $seuilCumulJour) {
            $alertes[] = $this->createAlerte(self::AML_CUMUL_JOURNALIER, [
                'NumCompte' => $operation['NumCompte'],
                'RefOperations' => $refOperation,
                'RefAgency' => $operation['RefAgency'],
                'MontantCumul' => $cumulJour['Total'],
                'PeriodeCumul' => 'JOUR',
                'Severite' => 'HAUTE',
                'Description' => sprintf(
                    "Cumul journalier de %s FCFA (>= seuil %s FCFA) pour le client %s (compte: %s). %d operations aujourd'hui.",
                    number_format($cumulJour['Total'], 0, ',', ' '),
                    number_format($seuilCumulJour, 0, ',', ' '),
                    $operation['NameClient'],
                    $operation['NumCompte'],
                    $cumulJour['NbOps']
                )
            ]);
        }
        
        // 3. Verifier le cumul hebdomadaire
        $cumulSemaine = $this->getCumulClient($operation['NumCompte'], 'SEMAINE');
        $seuilCumulSemaine = $this->getSeuil('LCB_CUMUL_HEBDO');
        if ($cumulSemaine['Total'] >= $seuilCumulSemaine) {
            $alertes[] = $this->createAlerte(self::AML_CUMUL_HEBDO, [
                'NumCompte' => $operation['NumCompte'],
                'RefOperations' => $refOperation,
                'RefAgency' => $operation['RefAgency'],
                'MontantCumul' => $cumulSemaine['Total'],
                'PeriodeCumul' => 'SEMAINE',
                'Severite' => 'HAUTE',
                'Description' => sprintf(
                    "Cumul hebdomadaire de %s FCFA pour le client %s. %d operations cette semaine.",
                    number_format($cumulSemaine['Total'], 0, ',', ' '),
                    $operation['NameClient'],
                    $cumulSemaine['NbOps']
                )
            ]);
        }
        
        // 4. Detecter le fractionnement
        if ($this->detectFractionnement($operation['NumCompte'], date('Y-m-d'))) {
            $alertes[] = $this->createAlerte(self::AML_FRACTIONNEMENT, [
                'NumCompte' => $operation['NumCompte'],
                'RefOperations' => $refOperation,
                'RefAgency' => $operation['RefAgency'],
                'Severite' => 'CRITIQUE',
                'Description' => sprintf(
                    "Suspicion de fractionnement: plusieurs operations proches du seuil de declaration pour le client %s (compte: %s).",
                    $operation['NameClient'],
                    $operation['NumCompte']
                )
            ]);
        }
        
        // 5. Detecter depot suivi de retrait rapide
        if ($this->detectDepotRetraitRapide($operation['NumCompte'])) {
            $alertes[] = $this->createAlerte(self::AML_DEPOT_RETRAIT_RAPIDE, [
                'NumCompte' => $operation['NumCompte'],
                'RefOperations' => $refOperation,
                'RefAgency' => $operation['RefAgency'],
                'Severite' => 'HAUTE',
                'Description' => sprintf(
                    "Depot suivi de retrait dans les 24h pour le client %s (compte: %s). Pattern suspect.",
                    $operation['NameClient'],
                    $operation['NumCompte']
                )
            ]);
        }
        
        // 6. Detecter utilisation multi-agence le meme jour
        if ($this->detectMultiAgence($operation['NumCompte'], date('Y-m-d'))) {
            $alertes[] = $this->createAlerte(self::AML_MULTI_AGENCE, [
                'NumCompte' => $operation['NumCompte'],
                'RefOperations' => $refOperation,
                'RefAgency' => $operation['RefAgency'],
                'Severite' => 'MOYENNE',
                'Description' => sprintf(
                    "Client %s utilise plusieurs agences le meme jour (compte: %s).",
                    $operation['NameClient'],
                    $operation['NumCompte']
                )
            ]);
        }
        
        // Mettre le client sous surveillance si alerte critique
        foreach ($alertes as $alerte) {
            if ($alerte && in_array($alerte['Severite'] ?? '', ['CRITIQUE', 'HAUTE'])) {
                $this->mettreClientSousSurveillance($operation['NumCompte'], $alerte['CodeAlerte']);
            }
        }
        
        return $alertes;
    }

    /**
     * Calcule le cumul des operations d'un client sur une periode
     */
    public function getCumulClient($numCompte, $periode = 'JOUR')
    {
        switch ($periode) {
            case 'JOUR':
                $dateCondition = "DATE(Approve2_Time) = CURDATE()";
                break;
            case 'SEMAINE':
                $dateCondition = "Approve2_Time >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case 'MOIS':
                $dateCondition = "Approve2_Time >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
            default:
                $dateCondition = "DATE(Approve2_Time) = CURDATE()";
        }
        
        $stmt = $this->dao->prepare("
            SELECT 
                COUNT(*) AS NbOps,
                COALESCE(SUM(MontantVersement), 0) AS Total,
                COALESCE(SUM(CASE WHEN RefType = 1 THEN MontantVersement ELSE 0 END), 0) AS TotalDepot,
                COALESCE(SUM(CASE WHEN RefType = 2 THEN MontantVersement ELSE 0 END), 0) AS TotalRetrait,
                MAX(MontantVersement) AS MaxOp
            FROM TbleOperations
            WHERE NumCompte = :numCompte
                AND Approve2_Id IS NOT NULL
                AND Reset_Id IS NULL
                AND RefType IN (1, 2)
                AND $dateCondition
        ");
        $stmt->bindValue(':numCompte', $numCompte, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Detecte le fractionnement (plusieurs operations proches du seuil)
     */
    public function detectFractionnement($numCompte, $date)
    {
        $seuilDeclaration = $this->getSeuil('LCB_SEUIL_DECLARATION');
        $margeFractionnement = $this->getSeuil('LCB_MARGE_FRACTIONNEMENT');
        $seuilBas = $seuilDeclaration - $margeFractionnement;
        
        $stmt = $this->dao->prepare("
            SELECT COUNT(*) AS NbOps, SUM(MontantVersement) AS Total
            FROM TbleOperations
            WHERE NumCompte = :numCompte
                AND Approve2_Id IS NOT NULL
                AND Reset_Id IS NULL
                AND RefType IN (1, 2)
                AND MontantVersement BETWEEN :seuilBas AND :seuilHaut
                AND DATE(Approve2_Time) = :date
        ");
        $stmt->bindValue(':numCompte', $numCompte, \PDO::PARAM_STR);
        $stmt->bindValue(':seuilBas', $seuilBas);
        $stmt->bindValue(':seuilHaut', $seuilDeclaration - 1);
        $stmt->bindValue(':date', $date, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Suspicion si 2+ operations proches du seuil OU si le cumul depasse le seuil
        return ($result['NbOps'] >= 2) || ($result['Total'] >= $seuilDeclaration);
    }

    /**
     * Detecte un depot suivi d'un retrait rapide (dans les 24h)
     */
    public function detectDepotRetraitRapide($numCompte)
    {
        $delai = $this->getSeuil('LCB_DELAI_DEPOT_RETRAIT');
        $seuilSurveillance = $this->getSeuil('LCB_SEUIL_SURVEILLANCE');
        
        $stmt = $this->dao->prepare("
            SELECT d.RefOperations AS DepotRef, r.RefOperations AS RetraitRef,
                   d.MontantVersement AS MontantDepot, r.MontantVersement AS MontantRetrait,
                   TIMESTAMPDIFF(HOUR, d.Approve2_Time, r.Approve2_Time) AS DelaiHeures
            FROM TbleOperations d
            INNER JOIN TbleOperations r ON r.NumCompte = d.NumCompte
                AND r.RefType = 2
                AND r.Approve2_Time > d.Approve2_Time
                AND r.Approve2_Time <= DATE_ADD(d.Approve2_Time, INTERVAL :delai HOUR)
                AND r.Approve2_Id IS NOT NULL
                AND r.Reset_Id IS NULL
            WHERE d.NumCompte = :numCompte
                AND d.RefType = 1
                AND d.MontantVersement >= :seuil
                AND d.Approve2_Id IS NOT NULL
                AND d.Reset_Id IS NULL
                AND d.Approve2_Time >= DATE_SUB(NOW(), INTERVAL 2 DAY)
            LIMIT 1
        ");
        $stmt->bindValue(':numCompte', $numCompte, \PDO::PARAM_STR);
        $stmt->bindValue(':delai', $delai, \PDO::PARAM_INT);
        $stmt->bindValue(':seuil', $seuilSurveillance);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Detecte l'utilisation de plusieurs agences le meme jour
     */
    public function detectMultiAgence($numCompte, $date)
    {
        $stmt = $this->dao->prepare("
            SELECT COUNT(DISTINCT c.RefAgency) AS NbAgences
            FROM TbleOperations o
            INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
            WHERE o.NumCompte = :numCompte
                AND o.Approve2_Id IS NOT NULL
                AND o.Reset_Id IS NULL
                AND DATE(o.Approve2_Time) = :date
        ");
        $stmt->bindValue(':numCompte', $numCompte, \PDO::PARAM_STR);
        $stmt->bindValue(':date', $date, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result['NbAgences'] > 1;
    }

    /**
     * Cree une alerte LCB
     */
    public function createAlerte($codeAlerte, $data)
    {
        // Verifier si une alerte similaire existe deja aujourd'hui
        $stmt = $this->dao->prepare("
            SELECT RefAlerteLCB FROM TbleAlertesLCB 
            WHERE CodeAlerte = :code 
                AND NumCompte = :numCompte 
                AND DATE(DateCreation) = CURDATE()
                AND Statut IN ('NOUVELLE', 'EN_COURS')
        ");
        $stmt->bindValue(':code', $codeAlerte, \PDO::PARAM_STR);
        $stmt->bindValue(':numCompte', $data['NumCompte'] ?? '', \PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return null; // Alerte deja existante
        }
        
        $sql = "INSERT INTO TbleAlertesLCB 
            (CodeAlerte, Severite, NumCompte, RefOperations, RefAgency, RefCaisse, 
             Montant, MontantCumul, PeriodeCumul, Description)
            VALUES 
            (:code, :severite, :numCompte, :refOp, :refAgency, :refCaisse,
             :montant, :montantCumul, :periodeCumul, :description)";
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':code', $codeAlerte, \PDO::PARAM_STR);
        $stmt->bindValue(':severite', $data['Severite'] ?? 'MOYENNE', \PDO::PARAM_STR);
        $stmt->bindValue(':numCompte', $data['NumCompte'] ?? null, \PDO::PARAM_STR);
        $stmt->bindValue(':refOp', $data['RefOperations'] ?? null, \PDO::PARAM_INT);
        $stmt->bindValue(':refAgency', $data['RefAgency'] ?? null, \PDO::PARAM_INT);
        $stmt->bindValue(':refCaisse', $data['RefCaisse'] ?? null, \PDO::PARAM_INT);
        $stmt->bindValue(':montant', $data['Montant'] ?? null);
        $stmt->bindValue(':montantCumul', $data['MontantCumul'] ?? null);
        $stmt->bindValue(':periodeCumul', $data['PeriodeCumul'] ?? null, \PDO::PARAM_STR);
        $stmt->bindValue(':description', $data['Description'] ?? '', \PDO::PARAM_STR);
        $stmt->execute();
        
        return array_merge($data, ['CodeAlerte' => $codeAlerte, 'RefAlerteLCB' => $this->dao->lastInsertId()]);
    }

    /**
     * Recupere les alertes
     */
    public function getAlertes($statut = null, $limit = 50)
    {
        $statutCondition = $statut ? "AND al.Statut = :statut" : "";
        
        $sql = "SELECT 
            al.*,
            a.NameAgency,
            c.NomClient,
            CONCAT(u.PrenomUsers, ' ', u.NomUsers) AS TraitePar
        FROM TbleAlertesLCB al
        LEFT JOIN TbleAgency a ON a.RefAgency = al.RefAgency
        LEFT JOIN TbleClients c ON c.NumCompte = al.NumCompte
        LEFT JOIN TbleUsers u ON u.RefUsers = al.RefUsersTraitement
        WHERE 1=1 $statutCondition
        ORDER BY 
            FIELD(al.Severite, 'CRITIQUE', 'HAUTE', 'MOYENNE', 'INFO'),
            al.DateCreation DESC
        LIMIT :limit";
        
        $stmt = $this->dao->prepare($sql);
        if ($statut) {
            $stmt->bindValue(':statut', $statut, \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Recupere une alerte specifique
     */
    public function getAlerte($refAlerte)
    {
        $stmt = $this->dao->prepare("
            SELECT 
                al.*,
                a.NameAgency,
                c.NomClient,
                c.Segment,
                c.NiveauRisque,
                CONCAT(u.PrenomUsers, ' ', u.NomUsers) AS TraitePar
            FROM TbleAlertesLCB al
            LEFT JOIN TbleAgency a ON a.RefAgency = al.RefAgency
            LEFT JOIN TbleClients c ON c.NumCompte = al.NumCompte
            LEFT JOIN TbleUsers u ON u.RefUsers = al.RefUsersTraitement
            WHERE al.RefAlerteLCB = :refAlerte
        ");
        $stmt->bindValue(':refAlerte', $refAlerte, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Traite une alerte
     */
    public function traiterAlerte($refAlerte, $statut, $commentaire)
    {
        $stmt = $this->dao->prepare("
            UPDATE TbleAlertesLCB SET
                Statut = :statut,
                RefUsersTraitement = :refUsers,
                DateTraitement = NOW(),
                ActionPrise = :commentaire
            WHERE RefAlerteLCB = :refAlerte
        ");
        $stmt->bindValue(':statut', $statut, \PDO::PARAM_STR);
        $stmt->bindValue(':refUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $stmt->bindValue(':commentaire', $commentaire, \PDO::PARAM_STR);
        $stmt->bindValue(':refAlerte', $refAlerte, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Statistiques pour dashboard LCB
     */
    public function getDashboardStats()
    {
        $stmt = $this->dao->query("
            SELECT 
                COUNT(*) AS TotalAlertes,
                SUM(CASE WHEN Statut = 'NOUVELLE' THEN 1 ELSE 0 END) AS Nouvelles,
                SUM(CASE WHEN Statut = 'EN_COURS' THEN 1 ELSE 0 END) AS EnCours,
                SUM(CASE WHEN Statut = 'TRAITEE' THEN 1 ELSE 0 END) AS Traitees,
                SUM(CASE WHEN Statut = 'DECLAREE_CENTIF' THEN 1 ELSE 0 END) AS Declarees,
                SUM(CASE WHEN Severite = 'CRITIQUE' AND Statut = 'NOUVELLE' THEN 1 ELSE 0 END) AS CritiquesNonTraitees,
                SUM(CASE WHEN Severite = 'HAUTE' AND Statut = 'NOUVELLE' THEN 1 ELSE 0 END) AS HautesNonTraitees,
                SUM(CASE WHEN DATE(DateCreation) = CURDATE() THEN 1 ELSE 0 END) AS AlertesAujourdhui,
                SUM(CASE WHEN DateCreation >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS AlertesSemaine,
                SUM(CASE WHEN DateCreation >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS AlertesMois
            FROM TbleAlertesLCB
        ");
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Liste des clients sous surveillance
     */
    public function getClientsSurveilles()
    {
        $stmt = $this->dao->query("
            SELECT 
                c.*,
                a.NameAgency,
                (SELECT COUNT(*) FROM TbleAlertesLCB WHERE NumCompte = c.NumCompte) AS NbAlertes,
                (SELECT MAX(DateCreation) FROM TbleAlertesLCB WHERE NumCompte = c.NumCompte) AS DerniereAlerte
            FROM TbleClients c
            LEFT JOIN TbleAgency a ON a.RefAgency = c.RefAgencyPrincipale
            WHERE c.EstSurveille = 1
            ORDER BY c.NiveauRisque DESC, NbAlertes DESC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Met un client sous surveillance
     */
    public function mettreClientSousSurveillance($numCompte, $motif)
    {
        $stmt = $this->dao->prepare("
            UPDATE TbleClients SET 
                EstSurveille = 1,
                NiveauRisque = CASE 
                    WHEN NiveauRisque = 'FAIBLE' THEN 'MOYEN'
                    WHEN NiveauRisque = 'MOYEN' THEN 'ELEVE'
                    ELSE NiveauRisque
                END,
                MotifSurveillance = CONCAT(COALESCE(MotifSurveillance, ''), ' | ', :motif, ' (', NOW(), ')')
            WHERE NumCompte = :numCompte
        ");
        $stmt->bindValue(':motif', $motif, \PDO::PARAM_STR);
        $stmt->bindValue(':numCompte', $numCompte, \PDO::PARAM_STR);
        return $stmt->execute();
    }

    /**
     * Recupere un seuil
     */
    public function getSeuil($code)
    {
        $stmt = $this->dao->prepare("SELECT Valeur FROM TbleSeuilsLCB WHERE CodeSeuil = :code AND Actif = 1");
        $stmt->bindValue(':code', $code, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Valeurs par defaut
        $defaults = [
            'LCB_SEUIL_DECLARATION' => 15000000,
            'LCB_SEUIL_SURVEILLANCE' => 5000000,
            'LCB_CUMUL_JOURNALIER' => 15000000,
            'LCB_CUMUL_HEBDO' => 25000000,
            'LCB_MARGE_FRACTIONNEMENT' => 500000,
            'LCB_DELAI_DEPOT_RETRAIT' => 24
        ];
        
        return $result ? floatval($result['Valeur']) : ($defaults[$code] ?? 0);
    }

    /**
     * Met a jour un seuil
     */
    public function updateSeuil($code, $valeur)
    {
        $stmt = $this->dao->prepare("
            UPDATE TbleSeuilsLCB SET 
                Valeur = :valeur,
                RefUsersModification = :refUsers
            WHERE CodeSeuil = :code
        ");
        $stmt->bindValue(':valeur', $valeur);
        $stmt->bindValue(':refUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $stmt->bindValue(':code', $code, \PDO::PARAM_STR);
        return $stmt->execute();
    }

    /**
     * Recupere tous les seuils
     */
    public function getAllSeuils()
    {
        $stmt = $this->dao->query("SELECT * FROM TbleSeuilsLCB ORDER BY CodeSeuil");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
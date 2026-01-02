<?php

namespace Library\Models;

class ClientManagerPDO extends ClientManager
{
    /**
     * Recupere un client par son numero de compte
     */
    public function getClient($numCompte)
    {
        $stmt = $this->dao->prepare("
            SELECT c.*, a.NameAgency 
            FROM TbleClients c
            LEFT JOIN TbleAgency a ON a.RefAgency = c.RefAgencyPrincipale
            WHERE c.NumCompte = :numCompte
        ");
        $stmt->bindValue(':numCompte', $numCompte, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Cree ou met a jour un profil client apres une operation
     */
    public function createOrUpdateClient($data)
    {
        $numCompte = $data['NumCompte'];
        $nomClient = $data['NameClient'] ?? null;
        $telClient = $data['TelDeposant'] ?? null;
        $refAgency = $data['RefAgency'] ?? null;
        $montant = floatval($data['MontantVersement'] ?? 0);
        $refType = intval($data['RefType'] ?? 0);
        
        // Verifier si le client existe
        $client = $this->getClient($numCompte);
        
        if ($client) {
            // Mise a jour
            $sql = "UPDATE TbleClients SET 
                NomClient = COALESCE(:nomClient, NomClient),
                TelClient = COALESCE(:telClient, TelClient),
                DateDerniereOp = CURDATE(),
                NbTotalOperations = NbTotalOperations + 1,
                VolumeTotalDepot = VolumeTotalDepot + :volumeDepot,
                VolumeTotalRetrait = VolumeTotalRetrait + :volumeRetrait,
                MontantMoyenOperation = (VolumeTotalDepot + VolumeTotalRetrait + :montant) / (NbTotalOperations + 1)
            WHERE NumCompte = :numCompte";
            
            $stmt = $this->dao->prepare($sql);
            $stmt->bindValue(':nomClient', $nomClient, \PDO::PARAM_STR);
            $stmt->bindValue(':telClient', $telClient, \PDO::PARAM_STR);
            $stmt->bindValue(':volumeDepot', ($refType == 1) ? $montant : 0);
            $stmt->bindValue(':volumeRetrait', ($refType == 2) ? $montant : 0);
            $stmt->bindValue(':montant', $montant);
            $stmt->bindValue(':numCompte', $numCompte, \PDO::PARAM_STR);
            $stmt->execute();
        } else {
            // Creation
            $sql = "INSERT INTO TbleClients 
                (NumCompte, NomClient, TelClient, RefAgencyPrincipale, 
                 DatePremiereOp, DateDerniereOp, NbTotalOperations,
                 VolumeTotalDepot, VolumeTotalRetrait, MontantMoyenOperation, Segment)
                VALUES 
                (:numCompte, :nomClient, :telClient, :refAgency,
                 CURDATE(), CURDATE(), 1,
                 :volumeDepot, :volumeRetrait, :montant, 'NOUVEAU')";
            
            $stmt = $this->dao->prepare($sql);
            $stmt->bindValue(':numCompte', $numCompte, \PDO::PARAM_STR);
            $stmt->bindValue(':nomClient', $nomClient, \PDO::PARAM_STR);
            $stmt->bindValue(':telClient', $telClient, \PDO::PARAM_STR);
            $stmt->bindValue(':refAgency', $refAgency, \PDO::PARAM_INT);
            $stmt->bindValue(':volumeDepot', ($refType == 1) ? $montant : 0);
            $stmt->bindValue(':volumeRetrait', ($refType == 2) ? $montant : 0);
            $stmt->bindValue(':montant', $montant);
            $stmt->execute();
        }
        
        // Mettre a jour les stats mensuelles
        $this->updateMonthlyStats($numCompte, $refAgency, $montant, $refType);
        
        // Recalculer le segment
        $this->recalculerSegment($numCompte);
        
        return $numCompte;
    }

    /**
     * Met a jour les statistiques mensuelles d'un client
     */
    public function updateMonthlyStats($numCompte, $refAgency, $montant = 0, $refType = 0)
    {
        $anneeMois = date('Y-m');
        
        $sql = "INSERT INTO TbleClientStats 
            (NumCompte, AnneeMois, RefAgency, NbOperations, NbDepots, NbRetraits, 
             VolumeDepot, VolumeRetrait, VolumeNet, MaxOperation)
            VALUES 
            (:numCompte, :anneeMois, :refAgency, 1, 
             :nbDepots, :nbRetraits, :volumeDepot, :volumeRetrait, 
             :volumeNet, :montant)
            ON DUPLICATE KEY UPDATE
                NbOperations = NbOperations + 1,
                NbDepots = NbDepots + :nbDepots2,
                NbRetraits = NbRetraits + :nbRetraits2,
                VolumeDepot = VolumeDepot + :volumeDepot2,
                VolumeRetrait = VolumeRetrait + :volumeRetrait2,
                VolumeNet = VolumeDepot - VolumeRetrait,
                MaxOperation = GREATEST(MaxOperation, :montant2)";
        
        $isDepot = ($refType == 1) ? 1 : 0;
        $isRetrait = ($refType == 2) ? 1 : 0;
        $volumeDepot = ($refType == 1) ? $montant : 0;
        $volumeRetrait = ($refType == 2) ? $montant : 0;
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':numCompte', $numCompte, \PDO::PARAM_STR);
        $stmt->bindValue(':anneeMois', $anneeMois, \PDO::PARAM_STR);
        $stmt->bindValue(':refAgency', $refAgency, \PDO::PARAM_INT);
        $stmt->bindValue(':nbDepots', $isDepot, \PDO::PARAM_INT);
        $stmt->bindValue(':nbRetraits', $isRetrait, \PDO::PARAM_INT);
        $stmt->bindValue(':volumeDepot', $volumeDepot);
        $stmt->bindValue(':volumeRetrait', $volumeRetrait);
        $stmt->bindValue(':volumeNet', $volumeDepot - $volumeRetrait);
        $stmt->bindValue(':montant', $montant);
        $stmt->bindValue(':nbDepots2', $isDepot, \PDO::PARAM_INT);
        $stmt->bindValue(':nbRetraits2', $isRetrait, \PDO::PARAM_INT);
        $stmt->bindValue(':volumeDepot2', $volumeDepot);
        $stmt->bindValue(':volumeRetrait2', $volumeRetrait);
        $stmt->bindValue(':montant2', $montant);
        $stmt->execute();
    }

    /**
     * Recupere les stats mensuelles d'un client
     */
    public function getClientStats($numCompte, $nbMois = 12)
    {
        $stmt = $this->dao->prepare("
            SELECT * FROM TbleClientStats 
            WHERE NumCompte = :numCompte 
            ORDER BY AnneeMois DESC 
            LIMIT :nbMois
        ");
        $stmt->bindValue(':numCompte', $numCompte, \PDO::PARAM_STR);
        $stmt->bindValue(':nbMois', $nbMois, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Recalcule les stats globales d'un client depuis l'historique
     */
    public function updateClientStats($numCompte)
    {
        $sql = "UPDATE TbleClients c SET
            NbTotalOperations = (
                SELECT COUNT(*) FROM TbleOperations 
                WHERE NumCompte = c.NumCompte AND Approve2_Id IS NOT NULL AND Reset_Id IS NULL AND RefType IN (1,2)
            ),
            VolumeTotalDepot = (
                SELECT COALESCE(SUM(MontantVersement), 0) FROM TbleOperations 
                WHERE NumCompte = c.NumCompte AND Approve2_Id IS NOT NULL AND Reset_Id IS NULL AND RefType = 1
            ),
            VolumeTotalRetrait = (
                SELECT COALESCE(SUM(MontantVersement), 0) FROM TbleOperations 
                WHERE NumCompte = c.NumCompte AND Approve2_Id IS NOT NULL AND Reset_Id IS NULL AND RefType = 2
            ),
            DatePremiereOp = (
                SELECT MIN(DATE(Approve2_Time)) FROM TbleOperations 
                WHERE NumCompte = c.NumCompte AND Approve2_Id IS NOT NULL AND Reset_Id IS NULL
            ),
            DateDerniereOp = (
                SELECT MAX(DATE(Approve2_Time)) FROM TbleOperations 
                WHERE NumCompte = c.NumCompte AND Approve2_Id IS NOT NULL AND Reset_Id IS NULL
            )
            WHERE c.NumCompte = :numCompte";
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':numCompte', $numCompte, \PDO::PARAM_STR);
        $stmt->execute();
    }

    /**
     * Top clients par volume
     */
    public function getTopClients($refAgency = null, $limit = 10, $periode = 'mois')
    {
        $dateCondition = "";
        if ($periode == 'mois') {
            $dateCondition = "AND DATE(o.Approve2_Time) >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
        } elseif ($periode == 'semaine') {
            $dateCondition = "AND DATE(o.Approve2_Time) >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
        }
        
        $agencyCondition = $refAgency ? "AND ca.RefAgency = :refAgency" : "";
        
        $sql = "SELECT 
            c.*,
            a.NameAgency,
            COALESCE(SUM(o.MontantVersement), 0) AS VolumePeriode,
            COUNT(o.RefOperations) AS NbOpsPeriode
        FROM TbleClients c
        LEFT JOIN TbleAgency a ON a.RefAgency = c.RefAgencyPrincipale
        LEFT JOIN TbleOperations o ON o.NumCompte = c.NumCompte 
            AND o.Approve2_Id IS NOT NULL AND o.Reset_Id IS NULL
            AND o.RefType IN (1, 2)
            $dateCondition
        LEFT JOIN TbleCaisse ca ON ca.RefCaisse = o.RefCaisse
        WHERE 1=1 $agencyCondition
        GROUP BY c.RefClient
        ORDER BY VolumePeriode DESC
        LIMIT :limit";
        
        $stmt = $this->dao->prepare($sql);
        if ($refAgency) {
            $stmt->bindValue(':refAgency', $refAgency, \PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Clients inactifs (n'ont pas fait d'operation depuis X jours)
     */
    public function getClientsInactifs($refAgency = null, $jours = 30)
    {
        $agencyCondition = $refAgency ? "AND c.RefAgencyPrincipale = :refAgency" : "";
        
        $sql = "SELECT 
            c.*,
            a.NameAgency,
            DATEDIFF(CURDATE(), c.DateDerniereOp) AS JoursInactif,
            (c.VolumeTotalDepot + c.VolumeTotalRetrait) AS VolumeTotal
        FROM TbleClients c
        LEFT JOIN TbleAgency a ON a.RefAgency = c.RefAgencyPrincipale
        WHERE c.DateDerniereOp < DATE_SUB(CURDATE(), INTERVAL :jours DAY)
            AND c.DateDerniereOp IS NOT NULL
            $agencyCondition
        ORDER BY VolumeTotal DESC
        LIMIT 100";
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':jours', $jours, \PDO::PARAM_INT);
        if ($refAgency) {
            $stmt->bindValue(':refAgency', $refAgency, \PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Nouveaux clients (premiere operation dans les X derniers jours)
     */
    public function getNouveauxClients($refAgency = null, $jours = 30)
    {
        $agencyCondition = $refAgency ? "AND c.RefAgencyPrincipale = :refAgency" : "";
        
        $sql = "SELECT 
            c.*,
            a.NameAgency,
            (c.VolumeTotalDepot + c.VolumeTotalRetrait) AS VolumeTotal
        FROM TbleClients c
        LEFT JOIN TbleAgency a ON a.RefAgency = c.RefAgencyPrincipale
        WHERE c.DatePremiereOp >= DATE_SUB(CURDATE(), INTERVAL :jours DAY)
            $agencyCondition
        ORDER BY c.DatePremiereOp DESC
        LIMIT 50";
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':jours', $jours, \PDO::PARAM_INT);
        if ($refAgency) {
            $stmt->bindValue(':refAgency', $refAgency, \PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Clients par segment
     */
    public function getClientsBySegment($segment, $refAgency = null)
    {
        $agencyCondition = $refAgency ? "AND c.RefAgencyPrincipale = :refAgency" : "";
        
        $sql = "SELECT 
            c.*,
            a.NameAgency,
            (c.VolumeTotalDepot + c.VolumeTotalRetrait) AS VolumeTotal
        FROM TbleClients c
        LEFT JOIN TbleAgency a ON a.RefAgency = c.RefAgencyPrincipale
        WHERE c.Segment = :segment
            $agencyCondition
        ORDER BY VolumeTotal DESC";
        
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':segment', $segment, \PDO::PARAM_STR);
        if ($refAgency) {
            $stmt->bindValue(':refAgency', $refAgency, \PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Statistiques pour dashboard CRM
     */
    public function getDashboardStats($refAgency = null)
    {
        $agencyCondition = $refAgency ? "WHERE c.RefAgencyPrincipale = :refAgency" : "";
        
        $sql = "SELECT 
            COUNT(*) AS TotalClients,
            SUM(CASE WHEN c.Segment = 'VIP' THEN 1 ELSE 0 END) AS NbVIP,
            SUM(CASE WHEN c.Segment = 'REGULIER' THEN 1 ELSE 0 END) AS NbRegulier,
            SUM(CASE WHEN c.Segment = 'OCCASIONNEL' THEN 1 ELSE 0 END) AS NbOccasionnel,
            SUM(CASE WHEN c.Segment = 'DORMANT' THEN 1 ELSE 0 END) AS NbDormant,
            SUM(CASE WHEN c.Segment = 'PERDU' THEN 1 ELSE 0 END) AS NbPerdu,
            SUM(CASE WHEN c.Segment = 'NOUVEAU' THEN 1 ELSE 0 END) AS NbNouveau,
            SUM(CASE WHEN c.DateDerniereOp >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS NbActifs,
            SUM(CASE WHEN c.DateDerniereOp < DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS NbInactifs,
            SUM(c.VolumeTotalDepot) AS TotalVolumeDepot,
            SUM(c.VolumeTotalRetrait) AS TotalVolumeRetrait,
            SUM(c.NbTotalOperations) AS TotalOperations
        FROM TbleClients c
        $agencyCondition";
        
        $stmt = $this->dao->prepare($sql);
        if ($refAgency) {
            $stmt->bindValue(':refAgency', $refAgency, \PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Recalcule le segment d'un client
     */
    public function recalculerSegment($numCompte)
    {
        // Recuperer les seuils
        $seuilVipVolume = $this->getSeuil('CRM_SEUIL_VIP_VOLUME', 20000000);
        $seuilVipNbOps = $this->getSeuil('CRM_SEUIL_VIP_NB_OPS', 50);
        $joursDormant = $this->getSeuil('CRM_JOURS_DORMANT', 30);
        $joursPerdu = $this->getSeuil('CRM_JOURS_PERDU', 90);
        
        // Calculer le volume du mois en cours
        $stmt = $this->dao->prepare("
            SELECT 
                COALESCE(SUM(VolumeDepot + VolumeRetrait), 0) AS VolumeMois,
                COALESCE(SUM(NbOperations), 0) AS NbOpsMois
            FROM TbleClientStats 
            WHERE NumCompte = :numCompte AND AnneeMois = :anneeMois
        ");
        $stmt->bindValue(':numCompte', $numCompte, \PDO::PARAM_STR);
        $stmt->bindValue(':anneeMois', date('Y-m'), \PDO::PARAM_STR);
        $stmt->execute();
        $statsMois = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Recuperer le client
        $client = $this->getClient($numCompte);
        if (!$client) return;
        
        $joursInactif = $client['DateDerniereOp'] 
            ? (strtotime('now') - strtotime($client['DateDerniereOp'])) / 86400 
            : 999;
        
        // Determiner le segment
        $segment = 'OCCASIONNEL';
        
        if ($joursInactif >= $joursPerdu) {
            $segment = 'PERDU';
        } elseif ($joursInactif >= $joursDormant) {
            $segment = 'DORMANT';
        } elseif ($statsMois['VolumeMois'] >= $seuilVipVolume || $statsMois['NbOpsMois'] >= $seuilVipNbOps) {
            $segment = 'VIP';
        } elseif ($statsMois['NbOpsMois'] >= 4) {
            $segment = 'REGULIER';
        } elseif ($client['NbTotalOperations'] <= 2 && $joursInactif <= 30) {
            $segment = 'NOUVEAU';
        }
        
        // Mettre a jour
        $stmtUpdate = $this->dao->prepare("UPDATE TbleClients SET Segment = :segment WHERE NumCompte = :numCompte");
        $stmtUpdate->bindValue(':segment', $segment, \PDO::PARAM_STR);
        $stmtUpdate->bindValue(':numCompte', $numCompte, \PDO::PARAM_STR);
        $stmtUpdate->execute();
    }

    /**
     * Recalcule tous les segments (a executer periodiquement)
     */
    public function recalculerTousSegments()
    {
        $stmt = $this->dao->query("SELECT NumCompte FROM TbleClients");
        $clients = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        
        foreach ($clients as $numCompte) {
            $this->recalculerSegment($numCompte);
        }
        
        return count($clients);
    }

    /**
     * Recherche de clients
     */
    public function rechercherClients($terme, $refAgency = null)
    {
        $agencyCondition = $refAgency ? "AND c.RefAgencyPrincipale = :refAgency" : "";
        
        $sql = "SELECT 
            c.*,
            a.NameAgency,
            (c.VolumeTotalDepot + c.VolumeTotalRetrait) AS VolumeTotal
        FROM TbleClients c
        LEFT JOIN TbleAgency a ON a.RefAgency = c.RefAgencyPrincipale
        WHERE (c.NumCompte LIKE :terme OR c.NomClient LIKE :terme2 OR c.TelClient LIKE :terme3)
            $agencyCondition
        ORDER BY VolumeTotal DESC
        LIMIT 50";
        
        $termeLike = '%' . $terme . '%';
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':terme', $termeLike, \PDO::PARAM_STR);
        $stmt->bindValue(':terme2', $termeLike, \PDO::PARAM_STR);
        $stmt->bindValue(':terme3', $termeLike, \PDO::PARAM_STR);
        if ($refAgency) {
            $stmt->bindValue(':refAgency', $refAgency, \PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Recupere les dernieres operations d'un client
     */
    public function getClientOperations($numCompte, $limit = 20)
    {
        $stmt = $this->dao->prepare("
            SELECT 
                o.*,
                t.NameType,
                c.NameCaisse,
                a.NameAgency,
                CONCAT(u.PrenomUsers, ' ', u.NomUsers) AS Caissier
            FROM TbleOperations o
            INNER JOIN TbleType t ON t.RefType = o.RefType
            INNER JOIN TbleCaisse c ON c.RefCaisse = o.RefCaisse
            INNER JOIN TbleAgency a ON a.RefAgency = c.RefAgency
            INNER JOIN TbleUsers u ON u.RefUsers = o.Insert_Id
            WHERE o.NumCompte = :numCompte
                AND o.Approve2_Id IS NOT NULL
                AND o.Reset_Id IS NULL
            ORDER BY o.Approve2_Time DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':numCompte', $numCompte, \PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Recupere un seuil depuis la table de configuration
     */
    private function getSeuil($code, $defaut = 0)
    {
        $stmt = $this->dao->prepare("SELECT Valeur FROM TbleSeuilsLCB WHERE CodeSeuil = :code AND Actif = 1");
        $stmt->bindValue(':code', $code, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? floatval($result['Valeur']) : $defaut;
    }
}
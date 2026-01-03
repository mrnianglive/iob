<?php

namespace Library\Models;

use \Library\Entities\Caisse;

class CaisseManagerPDO extends CaisseManager
{
    public function UserCaisse()
    {
        $requete = $this->dao->prepare("SELECT * FROM TbleCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleCaisse.RefCaisse WHERE TbleChmod.RefUsers=:RefUsers");
        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->execute();
        $data = $requete->fetchAll();
        return $data;
    }


    public function UserCaisseAgence($Caisse)
    {
    }

    public function GetSolde($Caisse)
    {
        if (!empty($Caisse)) {
            $requete = $this->dao->prepare("SELECT  *  FROM tblecompte WHERE  RefCaisse=:RefCaisse");
            $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
            $requete->execute();
            $data = $requete->fetch();
        } else {
            $requete = $this->dao->prepare("SELECT  *  FROM tblecompte INNER JOIN TbleChmod ON TbleChmod.RefCaisse=tblecompte.RefCaisse WHERE TbleChmod.RefUsers=:RefUsers");
            $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requete->execute();
            $data = $requete->fetch();
        }

        return $data;
    }
    public function GetOperations($Caisse)
    {
        if (!empty($Caisse)) {
            $requete = $this->dao->prepare("SELECT  *  FROM TbleOperations INNER JOIN TbleType ON TbleType.RefType=TbleOperations.RefType WHERE  RefCaisse=:RefCaisse");
            $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
            $requete->execute();
            $data = $requete->fetchAll();
        } else {
            $requete = $this->dao->prepare("SELECT  *  FROM TbleOperations INNER JOIN TbleType ON TbleType.RefType=TbleOperations.RefType INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse WHERE  TbleChmod.RefUsers=:RefUsers");
            $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requete->execute();
            $data = $requete->fetchAll();
        }
        return $data;
    }
    public function Versement($Caisse)
    {
        if (!empty($Caisse)) {
            $requete = $this->dao->prepare("SELECT SUM(MontantVersement) AS Versement FROM TbleOperations  WHERE RefType='1' AND  RefCaisse=:RefCaisse");
            $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
            $requete->execute();
            $data = $requete->fetch();
        } else {
            $requete = $this->dao->prepare("SELECT SUM(MontantVersement) AS Versement FROM TbleOperations INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse  WHERE RefType='1' AND  TbleChmod.RefUsers=:RefUsers");
            $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requete->execute();
            $data = $requete->fetch();
        }
        return $data['Versement'];
    }

    public function Retrait($Caisse)
    {
        if (!empty($Caisse)) {
            $requete = $this->dao->prepare("SELECT SUM(MontantVersement) AS Retrait FROM TbleOperations  WHERE RefType='2' AND  RefCaisse=:RefCaisse");
            $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
            $requete->execute();
            $data = $requete->fetch();
        } else {
            $requete = $this->dao->prepare("SELECT SUM(MontantVersement) AS Retrait FROM TbleOperations INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse  WHERE RefType='2' AND  TbleChmod.RefUsers=:RefUsers AND Insert_Time >= :todayDebut AND Insert_Time <= :todayFin");
            $requete->bindValue(':todayDebut', date('Y-m-d') . ' 00:00:00', \PDO::PARAM_STR);
            $requete->bindValue(':todayFin', date('Y-m-d') . ' 23:59:59', \PDO::PARAM_STR);
            $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requete->execute();
            $data = $requete->fetch();
        }
        return $data['Retrait'];
    }
    public function Appro($Caisse)
    {
        if (!empty($Caisse)) {
            $requete = $this->dao->prepare("SELECT SUM(MontantAppro) AS Appro FROM TbleAppro  WHERE   RefCaisse=:RefCaisse");
            $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
            $requete->execute();
            $data = $requete->fetch();
        } else {
            $requete = $this->dao->prepare("SELECT SUM(MontantAppro) AS Appro FROM TbleAppro INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleAppro.RefCaisse  WHERE   TbleChmod.RefUsers=:RefUsers ");
            $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requete->execute();
            $data = $requete->fetch();
        }
        return $data['Appro'];
    }
    public function Transfert($Caisse)
    {
        if (!empty($Caisse)) {
            $requete = $this->dao->prepare("SELECT SUM(MontantTransfert) AS Transfert FROM tbletransfert  WHERE   RefCaisse=:RefCaisse");
            $requete->bindValue(':RefCaisse', $Caisse, \PDO::PARAM_INT);
            $requete->execute();
            $data = $requete->fetch();
        } else {
            $requete = $this->dao->prepare("SELECT SUM(MontantTransfert) AS Transfert FROM tbletransfert INNER JOIN TbleChmod ON TbleChmod.RefCaisse=tbletransfert.RefCaisse  WHERE   TbleChmod.RefUsers=:RefUsers ");
            $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requete->execute();
            $data = $requete->fetch();
        }
        return $data['Transfert'];
    }
    public function ListeFond($Agence = NULL, $Debut = NULL, $Fin = NULL)
    {
        if ($Agence != NULL && $Debut != NULL && $Fin != NULL) {
            $requete = $this->dao->prepare("SELECT * FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse WHERE  TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND  TbleChmod.RefUsers=:RefUsers AND TbleOperations.RefType=4 AND  TbleOperations.Approve2_Time >= :Debut AND TbleOperations.Approve2_Time <= :Fin  AND TbleAgency.RefAgency=:Agence ");
            $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
            $requete->bindValue(':Agence', $Agence, \PDO::PARAM_INT);
            $requete->bindValue(':Debut', $Debut . ' 00:00:00', \PDO::PARAM_STR);
            $requete->bindValue(':Fin', $Fin . ' 23:59:59', \PDO::PARAM_STR);
        } else {
            $requete = $this->dao->prepare("SELECT * FROM TbleOperations INNER JOIN TbleCaisse ON TbleCaisse.RefCaisse=TbleOperations.RefCaisse INNER JOIN TbleAgency ON TbleAgency.RefAgency=TbleCaisse.RefAgency INNER JOIN TbleChmod ON TbleChmod.RefCaisse=TbleOperations.RefCaisse WHERE  TbleOperations.Approve2_Id IS NOT NULL AND TbleOperations.Reset_Id IS NULL AND  TbleChmod.RefUsers=:RefUsers AND TbleOperations.RefType=4 AND  TbleOperations.Approve2_Time >= :todayDebut AND TbleOperations.Approve2_Time <= :todayFin");
            $requete->bindValue(':todayDebut', date('Y-m-d') . ' 00:00:00', \PDO::PARAM_STR);
            $requete->bindValue(':todayFin', date('Y-m-d') . ' 23:59:59', \PDO::PARAM_STR);
            $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        }
        $requete->execute();
        $data = $requete->fetchAll();
        return $data;
    }

    public function AddTransfert()
    //Transfert Olde Code NOW WITH BIELLETAGE
    {
        $requete = $this->dao->prepare("INSERT INTO tbletransfert(RefCaisse,MontantTransfert,RefUsers) VALUES(:RefCaisse,:MontantTransfert,:RefUsers)");
        $requete->bindValue(':RefCaisse', $_POST['RefCaisse'], \PDO::PARAM_INT);
        $requete->bindValue(':MontantTransfert', $_POST['MontantTransfert'], \PDO::PARAM_INT);
        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->execute();
    }

    public function ListeAppro($Annee = NULL)
    {
        // Par défaut, année courante si non spécifié
        if ($Annee === NULL) {
            $Annee = date('Y');
        }
        
        // Requête optimisée:
        // - Utilise des plages de dates au lieu de YEAR() pour profiter des index
        // - Sélectionne uniquement les colonnes nécessaires
        // - Ordonne par date décroissante pour afficher les plus récents en premier
        // - Limite à 500 résultats pour éviter les temps de chargement excessifs
        $debutAnnee = $Annee . '-01-01 00:00:00';
        $finAnnee = $Annee . '-12-31 23:59:59';
        
        $sql = "SELECT 
                    op.RefOperations,
                    op.MontantVersement,
                    op.Approve2_Time,
                    op.RefCaisse,
                    c.NameCaisse,
                    a.NameAgency
                FROM TbleOperations op
                INNER JOIN TbleCaisse c ON c.RefCaisse = op.RefCaisse
                INNER JOIN TbleAgency a ON a.RefAgency = c.RefAgency
                INNER JOIN TbleChmod ch ON ch.RefCaisse = op.RefCaisse
                WHERE op.Approve2_Id IS NOT NULL 
                    AND op.Reset_Id IS NULL 
                    AND ch.RefUsers = :RefUsers 
                    AND op.RefType = 3 
                    AND op.Approve2_Time >= :debutAnnee
                    AND op.Approve2_Time <= :finAnnee
                ORDER BY op.Approve2_Time DESC
                LIMIT 500";
        
        $requete = $this->dao->prepare($sql);
        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->bindValue(':debutAnnee', $debutAnnee, \PDO::PARAM_STR);
        $requete->bindValue(':finAnnee', $finAnnee, \PDO::PARAM_STR);
        $requete->execute();
        $data = $requete->fetchAll();
        return $data;
    }

    public function AddAppro()
    //Old APPRO COde NOW WITH BIELLETAGE, THIS CODE IS FOR OMNI APPRO 
    {
        $requete = $this->dao->prepare("INSERT INTO TbleAppro(RefCaisse,MontantAppro,RefUsers) VALUES(:RefCaisse,:MontantAppro,:RefUsers)");
        $requete->bindValue(':RefCaisse', $_POST['RefCaisse'], \PDO::PARAM_INT);
        $requete->bindValue(':MontantAppro', $_POST['MontantAppro'], \PDO::PARAM_INT);
        $requete->bindValue(':RefUsers', $_SESSION['RefUsers'], \PDO::PARAM_INT);
        $requete->execute();
    }
    public function DeleteTransfert($Transfert)
    //Olde Transfert
    {
        $requete = $this->dao->prepare("DELETE FROM tbletransfert WHERE RefTransfert=:RefTransfert");
        $requete->bindValue(':RefTransfert', $Transfert, \PDO::PARAM_INT);
        $requete->execute();
    }
    public function DeleteAppro($Appro)
    //OMNI APPRO DELETE
    {
        $requete = $this->dao->prepare("DELETE FROM TbleAppro WHERE RefAppro=:RefAppro");
        $requete->bindValue(':RefAppro', $Appro, \PDO::PARAM_INT);
        $requete->execute();
    }

    public function GetOperationsWithTotals($refCaisse, $date = null)
    {
        $date = $date ?: date('Y-m-d');

        $sql = "
            SELECT
                o.RefOperations,
                o.NumCompte,
                o.NameClient,
                o.MontantVersement,
                o.RefType,
                t.NameType,
                o.Insert_Time,
                o.Approve2_Time
            FROM TbleOperations o
            INNER JOIN TbleType t ON t.RefType = o.RefType
            WHERE o.RefCaisse = :refCaisse
              AND o.Approve2_Id IS NOT NULL
              AND o.Reset_Id IS NULL
              AND o.Approve2_Time >= :dateDebut
              AND o.Approve2_Time <= :dateFin
            ORDER BY o.Approve2_Time DESC";

        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':refCaisse', $refCaisse, \PDO::PARAM_INT);
        $stmt->bindValue(':dateDebut', $date . ' 00:00:00', \PDO::PARAM_STR);
        $stmt->bindValue(':dateFin', $date . ' 23:59:59', \PDO::PARAM_STR);
        $stmt->execute();

        $operations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($operations)) {
            return ['operations' => [], 'totaux' => ['TotalVersement' => 0, 'TotalRetrait' => 0, 'NbOperations' => 0]];
        }

        $totaux = [
            'TotalVersement' => 0,
            'TotalRetrait' => 0,
            'NbOperations' => count($operations)
        ];

        foreach ($operations as $op) {
            if ($op['RefType'] == 1) {
                $totaux['TotalVersement'] += $op['MontantVersement'];
            } elseif ($op['RefType'] == 2) {
                $totaux['TotalRetrait'] += $op['MontantVersement'];
            }
        }

        return ['operations' => $operations, 'totaux' => $totaux];
    }
}

<?php

namespace Applications\App\Modules\LCB;

class LCBController extends \Library\BackController
{
    /**
     * Roles autorises pour le module LCB
     */
    private $allowedRoles = ['admin', 'superadmin', 'Controleur', 'Control', 'Niveau1'];

    /**
     * Verifie l'acces au module LCB
     */
    private function checkAccess()
    {
        if (!in_array($_SESSION['statut'], $this->allowedRoles)) {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Accès réservé aux administrateurs et contrôleurs.',
                'number' => 2
            ];
            $this->app()->httpResponse()->redirect('/');
            return false;
        }
        return true;
    }

    /**
     * Dashboard LCB principal
     */
    public function executeIndex(\Library\HTTPRequest $request)
    {
        if (!$this->checkAccess()) return;
        
        $this->page->addVar("titles", "LCB-FT Anti-Blanchiment");
        
        // Statistiques
        $stats = $this->managers->getManagerOf('LCB')->getDashboardStats();
        $this->page->addVar('Stats', $stats);
        
        // Alertes critiques non traitees
        $alertesCritiques = $this->managers->getManagerOf('LCB')->getAlertes('NOUVELLE', 20);
        $this->page->addVar('AlertesCritiques', $alertesCritiques);
        
        // Clients sous surveillance
        $surveilles = $this->managers->getManagerOf('LCB')->getClientsSurveilles();
        $this->page->addVar('ClientsSurveilles', $surveilles);
        
        // Seuils actuels
        $seuils = $this->managers->getManagerOf('LCB')->getAllSeuils();
        $this->page->addVar('Seuils', $seuils);
    }

    /**
     * Liste toutes les alertes
     */
    public function executeAlertes(\Library\HTTPRequest $request)
    {
        if (!$this->checkAccess()) return;
        
        $this->page->addVar("titles", "Alertes LCB-FT");
        
        $statut = $request->getData('statut') ?: null;
        $alertes = $this->managers->getManagerOf('LCB')->getAlertes($statut, 100);
        
        $this->page->addVar('Alertes', $alertes);
        $this->page->addVar('StatutFiltre', $statut);
    }

    /**
     * Detail d'une alerte
     */
    public function executeDetail(\Library\HTTPRequest $request)
    {
        if (!$this->checkAccess()) return;
        
        $this->page->addVar("titles", "Détail Alerte LCB");
        
        $refAlerte = $request->getData('id');
        $alerte = $this->managers->getManagerOf('LCB')->getAlerte($refAlerte);
        
        if (!$alerte) {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Alerte non trouvée.',
                'number' => 2
            ];
            $this->app()->httpResponse()->redirect('/lcb/alertes');
            return;
        }
        
        $this->page->addVar('Alerte', $alerte);
        
        // Historique du client si disponible
        if ($alerte['NumCompte']) {
            $client = $this->managers->getManagerOf('Client')->getClient($alerte['NumCompte']);
            $this->page->addVar('Client', $client);
            
            $operations = $this->managers->getManagerOf('Client')->getClientOperations($alerte['NumCompte'], 10);
            $this->page->addVar('Operations', $operations);
        }
    }

    /**
     * Traiter une alerte
     */
    public function executeTraiter(\Library\HTTPRequest $request)
    {
        if (!$this->checkAccess()) return;
        
        $refAlerte = $request->getData('id');
        $statut = $request->postData('statut');
        $commentaire = $request->postData('commentaire');
        
        if ($statut && $commentaire) {
            $this->managers->getManagerOf('LCB')->traiterAlerte($refAlerte, $statut, $commentaire);
            
            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'Alerte traitée avec succès.',
                'number' => 1
            ];
        }
        
        $this->app()->httpResponse()->redirect('/lcb/alertes');
    }

    /**
     * Gestion des seuils
     */
    public function executeSeuils(\Library\HTTPRequest $request)
    {
        if ($_SESSION['statut'] !== 'admin') {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Action réservée aux administrateurs.',
                'number' => 2
            ];
            $this->app()->httpResponse()->redirect('/lcb/index');
            return;
        }
        
        $this->page->addVar("titles", "Configuration Seuils LCB");
        
        // Mise a jour si POST
        if ($request->postData('code')) {
            $code = $request->postData('code');
            $valeur = floatval($request->postData('valeur'));
            
            $this->managers->getManagerOf('LCB')->updateSeuil($code, $valeur);
            
            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'Seuil mis à jour.',
                'number' => 1
            ];
        }
        
        $seuils = $this->managers->getManagerOf('LCB')->getAllSeuils();
        $this->page->addVar('Seuils', $seuils);
    }

    /**
     * Clients sous surveillance
     */
    public function executeSurveilles(\Library\HTTPRequest $request)
    {
        if (!$this->checkAccess()) return;
        
        $this->page->addVar("titles", "Clients Surveillés");
        
        $surveilles = $this->managers->getManagerOf('LCB')->getClientsSurveilles();
        $this->page->addVar('Clients', $surveilles);
    }
}


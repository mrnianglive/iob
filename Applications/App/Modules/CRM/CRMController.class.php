<?php

namespace Applications\App\Modules\CRM;

class CRMController extends \Library\BackController
{
    /**
     * Dashboard CRM principal
     */
    public function executeIndex(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "CRM Clients");
        
        // Verifier les droits
        $allowedRoles = ['ChefCaisse', 'Head', 'admin', 'superadmin', 'Niveau1', 'Control'];
        if (!in_array($_SESSION['statut'], $allowedRoles)) {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Vous n\'avez pas accès au module CRM.',
                'number' => 2
            ];
            $this->app()->httpResponse()->redirect('/');
            return;
        }
        
        // Determiner l'agence (admin voit tout, autres voient leur agence)
        $refAgency = null;
        if (!in_array($_SESSION['statut'], ['admin', 'superadmin', 'Niveau1', 'Control'])) {
            $refAgency = $this->getUserAgency();
        }
        
        // Statistiques dashboard
        $stats = $this->managers->getManagerOf('Client')->getDashboardStats($refAgency);
        $this->page->addVar('Stats', $stats);
        
        // Top 10 clients du mois
        $topClients = $this->managers->getManagerOf('Client')->getTopClients($refAgency, 10, 'mois');
        $this->page->addVar('TopClients', $topClients);
        
        // Clients inactifs (a relancer)
        $inactifs = $this->managers->getManagerOf('Client')->getClientsInactifs($refAgency, 30);
        $this->page->addVar('ClientsInactifs', array_slice($inactifs, 0, 10));
        
        // Nouveaux clients du mois
        $nouveaux = $this->managers->getManagerOf('Client')->getNouveauxClients($refAgency, 30);
        $this->page->addVar('NouveauxClients', array_slice($nouveaux, 0, 10));
        
        // Liste des agences pour filtre
        if (in_array($_SESSION['statut'], ['admin', 'superadmin'])) {
            $agences = $this->managers->getManagerOf('Pannel')->ListeAgence();
            $this->page->addVar('Agences', $agences);
        }
    }

    /**
     * Fiche client detaillee
     */
    public function executeClient(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Fiche Client");
        
        $numCompte = $request->getData('id');
        
        // Recuperer le client
        $client = $this->managers->getManagerOf('Client')->getClient($numCompte);
        if (!$client) {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Client non trouvé.',
                'number' => 2
            ];
            $this->app()->httpResponse()->redirect('/crm/index');
            return;
        }
        $this->page->addVar('Client', $client);
        
        // Statistiques mensuelles (12 derniers mois)
        $statsMensuelles = $this->managers->getManagerOf('Client')->getClientStats($numCompte, 12);
        $this->page->addVar('StatsMensuelles', array_reverse($statsMensuelles));
        
        // Dernieres operations
        $operations = $this->managers->getManagerOf('Client')->getClientOperations($numCompte, 20);
        $this->page->addVar('Operations', $operations);
        
        // Alertes LCB liees a ce client
        if (in_array($_SESSION['statut'], ['admin', 'superadmin', 'Controleur'])) {
            $alertes = $this->managers->getManagerOf('LCB')->getAlertes();
            $alertesClient = array_filter($alertes, function($a) use ($numCompte) {
                return $a['NumCompte'] == $numCompte;
            });
            $this->page->addVar('AlertesLCB', array_values($alertesClient));
        }
    }

    /**
     * Liste des clients inactifs
     */
    public function executeInactifs(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Clients Inactifs");
        
        $jours = $request->getData('jours') ?: 30;
        $refAgency = null;
        if (!in_array($_SESSION['statut'], ['admin', 'superadmin'])) {
            $refAgency = $this->getUserAgency();
        }
        
        $inactifs = $this->managers->getManagerOf('Client')->getClientsInactifs($refAgency, $jours);
        $this->page->addVar('Clients', $inactifs);
        $this->page->addVar('Jours', $jours);
    }

    /**
     * Top clients
     */
    public function executeTop(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Top Clients");
        
        $periode = $request->getData('periode') ?: 'mois';
        $refAgency = null;
        if (!in_array($_SESSION['statut'], ['admin', 'superadmin'])) {
            $refAgency = $this->getUserAgency();
        }
        
        $topClients = $this->managers->getManagerOf('Client')->getTopClients($refAgency, 50, $periode);
        $this->page->addVar('Clients', $topClients);
        $this->page->addVar('Periode', $periode);
    }

    /**
     * Recherche de clients
     */
    public function executeRecherche(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Recherche Client");
        
        $terme = $request->postData('terme') ?: $request->getData('terme');
        $refAgency = null;
        if (!in_array($_SESSION['statut'], ['admin', 'superadmin'])) {
            $refAgency = $this->getUserAgency();
        }
        
        $resultats = [];
        if ($terme) {
            $resultats = $this->managers->getManagerOf('Client')->rechercherClients($terme, $refAgency);
        }
        
        $this->page->addVar('Resultats', $resultats);
        $this->page->addVar('Terme', $terme);
    }

    /**
     * Clients par segment
     */
    public function executeSegment(\Library\HTTPRequest $request)
    {
        $this->page->addVar("titles", "Clients par Segment");
        
        $segment = strtoupper($request->getData('segment') ?: 'VIP');
        $refAgency = null;
        if (!in_array($_SESSION['statut'], ['admin', 'superadmin'])) {
            $refAgency = $this->getUserAgency();
        }
        
        $clients = $this->managers->getManagerOf('Client')->getClientsBySegment($segment, $refAgency);
        $this->page->addVar('Clients', $clients);
        $this->page->addVar('Segment', $segment);
    }

    /**
     * Recalculer tous les segments (admin only)
     */
    public function executeRecalculer(\Library\HTTPRequest $request)
    {
        if ($_SESSION['statut'] !== 'admin') {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Action réservée aux administrateurs.',
                'number' => 2
            ];
            $this->app()->httpResponse()->redirect('/crm/index');
            return;
        }
        
        $count = $this->managers->getManagerOf('Client')->recalculerTousSegments();
        
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => "Segmentation recalculée pour $count clients.",
            'number' => 1
        ];
        $this->app()->httpResponse()->redirect('/crm/index');
    }

    /**
     * Recupere l'agence de l'utilisateur courant
     */
    private function getUserAgency()
    {
        $chmod = $this->managers->getManagerOf('Bielletage')->ChomdUser();
        if (!empty($chmod)) {
            $refCaisse = $chmod[0]['RefCaisse'];
            return $this->managers->getManagerOf('Bielletage')->GetAgencyFromCaisse($refCaisse);
        }
        return null;
    }
}
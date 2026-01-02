<?php

namespace Library\Models;

abstract class ClientManager extends \Library\Manager
{
    // CRUD Clients
    abstract public function getClient($numCompte);
    abstract public function createOrUpdateClient($data);
    abstract public function updateClientStats($numCompte);
    
    // Statistiques
    abstract public function getClientStats($numCompte, $nbMois = 12);
    abstract public function updateMonthlyStats($numCompte, $refAgency);
    
    // CRM - Listes
    abstract public function getTopClients($refAgency = null, $limit = 10);
    abstract public function getClientsInactifs($refAgency = null, $jours = 30);
    abstract public function getNouveauxClients($refAgency = null, $jours = 30);
    abstract public function getClientsBySegment($segment, $refAgency = null);
    
    // CRM - Dashboard
    abstract public function getDashboardStats($refAgency = null);
    
    // Segmentation
    abstract public function recalculerSegment($numCompte);
    abstract public function recalculerTousSegments();
    
    // Recherche
    abstract public function rechercherClients($terme, $refAgency = null);
}
<?php

namespace Library\Models;

abstract class LCBManager extends \Library\Manager
{
    // Analyse
    abstract public function analyzeTransaction($refOperation);
    abstract public function getCumulClient($numCompte, $periode);
    abstract public function detectFractionnement($numCompte, $date);
    abstract public function detectDepotRetraitRapide($numCompte);
    
    // Alertes
    abstract public function createAlerte($codeAlerte, $data);
    abstract public function getAlertes($statut = null, $limit = 50);
    abstract public function getAlerte($refAlerte);
    abstract public function traiterAlerte($refAlerte, $action, $commentaire);
    
    // Dashboard
    abstract public function getDashboardStats();
    abstract public function getClientsSurveilles();
    
    // Seuils
    abstract public function getSeuil($code);
    abstract public function updateSeuil($code, $valeur);
}
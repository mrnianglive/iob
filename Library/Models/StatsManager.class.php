<?php

namespace Library\Models;

class StatsManager extends \Library\Manager
{
    protected function add(\Library\Entity $entity)
    {
        
    }

    protected function count($where = "")
    {
        
    }

    protected function delete($id)
    {
        
    }

    protected function getList($debut = -1, $limite = -1, $where = "")
    {
        
    }

    protected function modify(\Library\Entity $entity)
    {
        
    }

    protected function get($id)
    {
        
    }

    public function incrementOperationStats($refCaisse, $refType, $montant)
    {
        
    }

    public function incrementRemittanceStats($refCaisse, $refProduit, $refType, $montant)
    {
        
    }

    public function getCaisseStatsForDate($date, $refCaisse)
    {
        
    }

    public function getJournalStatsForDate($date, $refUsers = null)
    {
        
    }

    public function getPetiteCaisseStats($date)
    {
        
    }

    public function getAllStatsForDate($date)
    {
        
    }
}

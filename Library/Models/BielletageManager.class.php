<?php

namespace Library\Models;

use \Library\Entities\Bielletage;

abstract class BielletageManager extends \Library\Manager
{
    abstract protected function CheckOuverture($data);
    abstract protected function ChomdUser();
    abstract protected function GetInvoice($id);
    abstract protected function GetAgency($Caisse);
    abstract protected function CheckAfterRapport($Caisse);
    abstract protected function Add();
    abstract protected function SommeVersementCaisse($Date);
    abstract protected function SommeRetraitCaisse($Date);
    abstract protected function getCurrentWeek();
    abstract  protected function daysofweek();
    abstract protected function DailyVersement();
    abstract protected function SommeRetraitStatistique($Date);
    abstract protected function SommeVersementStatistique($Date);
    abstract protected function YesterdaySolde($Caisse = NULL);
}

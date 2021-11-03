<?php

namespace Library\Models;

use \Library\Entities\Arreter;

abstract class ArreterManager extends \Library\Manager
{
    abstract protected function GetListeCaisse();
    // abstract protected function CloseCaisse($Caisse);
    abstract protected function SommeVersementCaisse($Caisse);
    abstract protected function SommeRetraitCaisse($Caisse);
    abstract protected function GetRapports($Caisse);
    abstract protected function GetDailyBielletage($Date);
    abstract protected function Versement($Date);
    abstract protected function Retrait($Date);
    abstract protected function StopCaisse($RefCaisse, $Solde, $date);
    abstract protected function CheckDailyClose($Caisse);
    abstract protected function ListeSolde($Caisse);
    abstract protected function DeleteSolde($RefSolde);
}
<?php

namespace Library\Models;

use \Library\Entities\Caisse;

abstract class CaisseManager extends \Library\Manager
{
    abstract protected function UserCaisse();
    abstract protected function GetSolde($Caisse);
    abstract protected function GetOperations($Caisse);
    abstract protected function Versement($Caisse);
    abstract protected function Retrait($Caisse);
    abstract protected function  Appro($Caisse);
    abstract protected function  Transfert($Caisse);
    abstract protected function AddTransfert();
    abstract protected function ListeAppro();
    abstract protected function AddAppro();
    abstract protected function DeleteTransfert($Transfert);
    abstract protected function DeleteAppro($Appro);
}

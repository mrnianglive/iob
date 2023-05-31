<?php

namespace Library\Models;

use \Library\Entities\Bielletage;

abstract class BielletageManager extends \Library\Manager
{

    abstract protected function GetInvoice($id);
}

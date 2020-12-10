<?php

namespace App\Exceptions;

use Exception;

class AjaxException extends Exception
{
    public function __construct()
    {
        parent::__construct();
    }
}

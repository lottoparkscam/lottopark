<?php

namespace Exceptions\Ggr;

use Exception;

class GgrIsNegativeException extends Exception
{
    public function __construct()
    {
        parent::__construct('Ggr is negative.');
    }
}

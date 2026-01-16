<?php

namespace Exceptions\Ggr;

use Exception;

class SalesTicketSumIsNegativeException extends Exception
{
    public function __construct()
    {
        parent::__construct('Sales ticket sum is negative.');
    }
}

<?php

namespace Exceptions\Ggr;

use Exception;

class WinTicketPrizeSumIsNegativeException extends Exception
{
    public function __construct()
    {
        parent::__construct('Win tickets sum is negative.');
    }
}

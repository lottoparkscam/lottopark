<?php

namespace Exceptions\Ggr;

use Exception;

class WhitelabelMarginIsNegativeException extends Exception
{
    public function __construct()
    {
        parent::__construct('Whitelabel margin is negative.');
    }
}

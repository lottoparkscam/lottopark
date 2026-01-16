<?php

namespace Exceptions;

use Exception;

class PrimeadsSecureUrlParameterNotExistsException extends Exception
{
    public function __construct()
    {
        parent::__construct('Secure parameter in postback to primeads is required. Check database whitelabel_plugin.options does it contain json with "secureUrlParameter".');
    }
}

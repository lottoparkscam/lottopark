<?php

namespace Validators\Auth;

use Validators\Validator;
use Validators\Rules\LoginHash;

class AutoLoginValidator extends Validator
{
    protected static string $method = Validator::GET;

    protected function buildValidation(...$args): void
    {
        $this->addFieldRule(LoginHash::build('login.hash'));
    }
}

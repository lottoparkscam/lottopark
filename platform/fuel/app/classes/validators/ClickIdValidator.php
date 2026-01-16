<?php

namespace Validators;

use Validators\Rules\ClickId;

class ClickIdValidator extends Validator
{
    protected static string $method = Validator::GET;

    protected function buildValidation(...$args): void {
        $token = ClickId::build('clickID');
        $this->addFieldRule($token);
    }
}
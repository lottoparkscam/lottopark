<?php

namespace Validators\Rules;

use Helpers\TypeHelper;

class Login extends Rule
{
    protected string $type = TypeHelper::STRING;

    public function applyRules(): void
    {
        $this->field
        ->add_rule('trim')
        ->add_rule('stripslashes')
        ->add_rule('required')
        ->add_rule('min_length', 3)
        ->add_rule('max_length', 100)
        ->add_rule('valid_string', ['alpha', 'numeric', 'dashes', 'at']);
    }
}

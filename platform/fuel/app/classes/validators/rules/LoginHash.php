<?php

namespace Validators\Rules;

use Helpers\TypeHelper;

class LoginHash extends Rule
{
    protected string $type = TypeHelper::STRING;

    public function applyRules(): void
    {
        $this->field
            ->add_rule('required')
            ->add_rule('valid_string', ['alpha', 'numeric', 'backwardslashes', '+'])
            ->add_rule('exact_length', 64); // length taken from db
    }
}

<?php

namespace Validators\Rules;

use Helpers\TypeHelper;

class Currency extends Rule
{
    protected string $type = TypeHelper::STRING;

    public function applyRules(): void
    {
        $this->field
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('valid_string', ['alpha'])
            ->add_rule('exact_length', 3);
    }
}

<?php

namespace Validators\Rules;

use Helpers\TypeHelper;

class Url extends Rule
{
    protected string $type = TypeHelper::STRING;

    public function applyRules(): void
    {
        $this->field
            ->add_rule('trim')
            ->add_rule('valid_url');
    }
}

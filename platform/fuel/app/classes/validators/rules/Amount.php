<?php

namespace Validators\Rules;

use Helpers\TypeHelper;

class Amount extends Rule
{
    protected string $type = TypeHelper::FLOAT;

    public function applyRules(): void
    {
        $this->field
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('valid_string', ['numeric', 'dots'])
            ->set_error_message('valid_string', 'Wrong balance amount');
    }
}

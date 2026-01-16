<?php

namespace Validators\Rules;

use Helpers\TypeHelper;

class Password extends Rule
{
    protected string $type = TypeHelper::STRING;

    public function applyRules(): void
    {
        $this->field
        ->add_rule('trim')
        ->add_rule('stripslashes')
        ->add_rule('required')
        ->add_rule('min_length', 6)
        ->set_error_message('required', 'Field ' . $this->label . ' is required');
    }
}

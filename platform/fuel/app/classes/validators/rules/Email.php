<?php

namespace Validators\Rules;

use Helpers\TypeHelper;

class Email extends Rule
{
    protected string $type = TypeHelper::STRING;

    public function applyRules(): void
    {
        $this->field
        ->add_rule('trim')
        ->add_rule('stripslashes')
        ->add_rule('required')
        ->add_rule('valid_email')
        ->set_error_message('required', 'Field ' . $this->label . ' is required');
    }
}

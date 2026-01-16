<?php

namespace Validators\Rules;

use Helpers\TypeHelper;

class PromoCode extends Rule
{
    protected string $type = TypeHelper::STRING;

    public function applyRules(): void
    {
        $this->field
        ->add_rule('trim')
        ->add_rule('stripslashes')
        ->add_rule('min_length', 3)
        ->add_rule('max_length', 20)
        ->add_rule('required')
        ->set_error_message('required', 'Field ' . $this->label . ' is required');
    }
}

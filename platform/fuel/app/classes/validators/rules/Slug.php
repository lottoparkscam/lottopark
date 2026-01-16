<?php

namespace Validators\Rules;

use Helpers\TypeHelper;

class Slug extends Rule
{
    protected string $type = TypeHelper::STRING;

    public function applyRules(): void
    {
        $this->field
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes'])
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->set_error_message('required', 'Field ' . $this->label . ' is required');
    }
}

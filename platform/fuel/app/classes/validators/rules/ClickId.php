<?php

namespace Validators\Rules;

class ClickId extends Rule
{
    protected string $type = 'string';

    public function applyRules(): void
    {
        $filters =  ['alpha', 'numeric', 'dashes'];
        $this->field
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('stripslashes')
            ->add_rule('valid_string', $filters)
            ->set_error_message('required', 'Field ' . $this->label . ' is required');
    }
}
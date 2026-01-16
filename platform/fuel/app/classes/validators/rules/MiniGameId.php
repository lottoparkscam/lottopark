<?php

namespace Validators\Rules;

use Helpers\TypeHelper;

class MiniGameId extends Rule
{
    protected string $type = TypeHelper::INTEGER;

    public function applyRules(): void
    {
        $this->field
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('valid_string', ['numeric'])
            ->set_error_message('required', 'Field ' . $this->label . ' is required');
    }
}

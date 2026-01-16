<?php

namespace Validators\Rules;

use Helpers\TypeHelper;

class Timezone extends Rule
{
    protected string $type = TypeHelper::STRING;

    public function applyRules(): void
    {
        $this->field
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes', 'forwardslashes'])
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->set_error_message('required', 'Field ' . $this->label . ' is required');
    }
}

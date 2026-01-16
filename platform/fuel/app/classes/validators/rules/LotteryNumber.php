<?php

namespace Validators\Rules;

use Helpers\TypeHelper;

class LotteryNumber extends Rule
{
    protected string $type = TypeHelper::INTEGER;

    public function applyRules(): void
    {
        $this->field
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('valid_string', ['numeric'])
            ->add_rule('numeric_min', 1)
            ->add_rule('numeric_max', 9999999)
            ->set_error_message('required', 'Field ' . $this->label . ' is required');
    }
}

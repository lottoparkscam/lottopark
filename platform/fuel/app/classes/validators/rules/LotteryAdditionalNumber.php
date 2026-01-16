<?php

namespace Validators\Rules;

use Helpers\TypeHelper;

class LotteryAdditionalNumber extends Rule
{
    protected string $type = TypeHelper::INTEGER;

    public function applyRules(): void
    {
        $this->field
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('valid_string', ['numeric'])
            ->add_rule('numeric_min', 0)
            ->add_rule('numeric_max', 9)
            ->set_error_message('required', 'Field ' . $this->label . ' is required');
    }
}

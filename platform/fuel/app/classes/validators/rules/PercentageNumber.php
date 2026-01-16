<?php

namespace Validators\Rules;

class PercentageNumber extends Rule
{
    protected string $type = 'float';

    public function applyRules(): void
    {
        $this->field
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('valid_string', ['numeric', 'dots'])
            ->add_rule('numeric_min', 1)
            ->add_rule('numeric_max', 100);
    }
}

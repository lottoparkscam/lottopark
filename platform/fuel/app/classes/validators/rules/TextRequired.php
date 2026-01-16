<?php

namespace Validators\Rules;

class TextRequired extends Text
{
    public function applyRules(): void
    {
        parent::applyRules();

        $this->field
            ->add_rule('required');
    }
}

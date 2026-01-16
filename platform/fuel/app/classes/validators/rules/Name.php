<?php

namespace Validators\Rules;

use Helpers\TypeHelper;

/**
 * This is universal rule for validating name and/or surname
 * It accepts asian and accented characters
 */
class Name extends Rule
{
    protected string $type = TypeHelper::STRING;

    public function applyRules(): void
    {
        $this->field
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'singlequotes', 'utf8'])
            ->set_error_message('required', 'Field ' . $this->label . ' is required');
    }
}

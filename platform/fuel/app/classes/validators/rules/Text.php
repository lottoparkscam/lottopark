<?php

namespace Validators\Rules;

/**
 * Universal rule for strings
 * This rule does not have `required` rule, you have to pass it on your own in validator
 */
class Text extends Rule
{
    protected string $type = 'string';

    public function applyRules(): void
    {
        $this->field
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('valid_string', ['numeric', 'alpha', 'specials', 'dashes', 'spaces', 'singlequotes', 'utf8'])
            ->set_error_message('valid_string', sprintf(
                _('valid_string'),
                [':label', _($this->label)]
            ));
    }
}

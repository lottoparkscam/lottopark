<?php

namespace Validators\Rules;

use Helpers\TypeHelper;

/**
 * Example of prefixed token: LPX123456
 * LP = LottoPark (whitelabel)
 * X = D (deposit) or P (purchase) or U (user)
 */
class PrefixedToken extends Rule
{
    protected string $type = TypeHelper::STRING;

    public function applyRules(): void
    {
        $this->field
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('match_pattern', '/^[a-zA-Z]{3}\d+$/')
            ->set_error_message('required', 'Field ' . $this->label . ' is required');
    }
}

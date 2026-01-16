<?php

namespace Validators\Rules;

use Helpers\TypeHelper;

class LoginUnique extends Login
{
    protected string $type = TypeHelper::STRING;

    public function applyRules(): void
    {
        parent::applyRules();
        $this->field
        ->add_rule('isUniqueInDb', ['column' => 'whitelabel_user.login'])
        ->set_error_message('isUniqueInDb', 'Not unique user');
    }
}

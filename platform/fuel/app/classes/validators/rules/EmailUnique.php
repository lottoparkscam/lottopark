<?php

namespace Validators\Rules;

use Helpers\TypeHelper;

class EmailUnique extends Email
{
    protected string $type = TypeHelper::STRING;

    public function applyRules(): void
    {
        parent::applyRules();
        $this->field
        ->add_rule('isUniqueInDb', ['column' => 'whitelabel_user.email'])
        ->set_error_message('isUniqueInDb', 'Not unique user');
    }
}

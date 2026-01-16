<?php

namespace Validators\Rules;

use Helpers\TypeHelper;

class Checkbox extends Rule
{
    protected string $type = TypeHelper::BOOLEAN;

    public function applyRules()
    {
        $this->field
            ->add_rule('match_collection', [0, 1])
            ->set_error_message('match_collection', 'Field ' . $this->label . ' has wrong value.');
    }
}

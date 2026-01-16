<?php

/**
 * @deprecated - use new fixtures instead
* Test Factory Currency.
* @author Marcin Klimek <marcin.klimek at gg.international>
* Date: 2019-07-06
* Time: 20:25:02
*/
final class Test_Factory_Currency extends Test_Factory_Base
{
    protected function values(array &$values): array
    {
        return [
            'code' => parent::random_string_uppercase(3, 'alpha'),
            'rate' => parent::random_decimal(9999, 9999),
        ];
    }
}

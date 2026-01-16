<?php

/**
 * @deprecated - use new fixtures instead
 * Test Factory Lottery Source.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-07-16
 * Time: 18:21:09
 */
final class Test_Factory_Lottery_Source extends Test_Factory_Base
{

    protected function values(array &$values): array
    {
        return [
            'name' => parent::random_string(45, 'alpha'),
            'website' => parent::random_string(45, 'alpha'),
        ];
    }
}

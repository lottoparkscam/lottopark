<?php

/**
 * @deprecated - use new fixtures instead
* Test Factory Lottery Prize Data.
* @author Marcin Klimek <marcin.klimek at gg.international>
* Date: 2019-09-20
* Time: 16:25:02
*/
final class Test_Factory_Lottery_Prize_Data extends Test_Factory_Base
{
    protected function values(array &$values): array
    {
        return [
            'winners' => 0,
            'prizes' => 0,
        ];
    }
}

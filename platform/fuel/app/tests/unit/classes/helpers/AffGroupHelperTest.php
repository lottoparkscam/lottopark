<?php

namespace Tests\Unit\Classes\Helpers;

use Helpers\AffGroupHelper;
use Test_Unit;

class AffGroupHelperTest extends Test_Unit
{
    /** @test */
    public function prepareCasinoGroups_shouldReturnCorrectGenerator()
    {
        $fakeCasinoGroups = [
            [
                'id' => 7,
                'whitelabel_id' => 1,
                'name' => 'testing group',
                'commission_percentage_value_for_tier_1' => 5.0,
                'commission_percentage_value_for_tier_2' => 1.0,
            ]
        ];

        $preparedCasinoGroups = AffGroupHelper::prepareCasinoGroups($fakeCasinoGroups);

        $this->assertInstanceOf('Generator', $preparedCasinoGroups);
        $this->assertSame(7, $preparedCasinoGroups->current()['id']);
    }
}

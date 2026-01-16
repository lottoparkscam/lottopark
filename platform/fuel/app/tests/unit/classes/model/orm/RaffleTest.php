<?php

namespace Tests\Unit\Classes\Model\Orm;

use Models\Raffle;
use Test_Unit;

final class RaffleTest extends Test_Unit
{
    /** @test */
    public function get_NoValuesSet_ReturnsDefaults(): void
    {
        $raffle = new Raffle();
        $this->assertTrue($raffle->is_sell_enabled);
        $this->assertFalse($raffle->is_sell_limitation_enabled);
        $this->assertSame([], $raffle->sell_open_dates);
    }

    /** @test */
    public function computedPropIsSellTemporaryDisabled_VariousValues_ReturnsBool(): void
    {
        $raffle = new Raffle();
        $raffle->is_sell_enabled = false;
        $raffle->is_sell_limitation_enabled = true;
        $this->assertTrue($raffle->is_sell_temporary_disabled);

        $raffle->is_sell_limitation_enabled = false;
        $this->assertFalse($raffle->is_sell_temporary_disabled);

        $raffle->is_sell_limitation_enabled = true;
        $raffle->is_sell_enabled = true;
        $this->assertFalse($raffle->is_sell_temporary_disabled);
    }
}

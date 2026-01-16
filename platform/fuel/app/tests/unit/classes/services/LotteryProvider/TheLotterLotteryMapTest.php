<?php

namespace unit\classes\services\LotteryProvider;

use Services\LotteryProvider\TheLotterLotteryMap;
use Test_Unit;

class TheLotterLotteryMapTest extends Test_Unit
{
    /** @test */
    public function getLotteryIdBySlugExistingSlug(): void
    {
        $slug = 'powerball';
        $expectedId = 25;

        $result = TheLotterLotteryMap::getLotteryIdBySlug($slug);

        $this->assertEquals($expectedId, $result);
    }

    /** @test */
    public function getLotteryIdBySlugNonExistingSlug(): void
    {
        $slug = 'none';

        $result = TheLotterLotteryMap::getLotteryIdBySlug($slug);

        $this->assertNull($result);
    }
}

<?php

use Fuel\Core\Cache;
use Helpers\ArrayHelper;

final class CacheTest extends Test_Feature
{
    /** @test */
    public function getLotteriesOrderById_OldCacheWithoutArguments_LotteriesStoredInCache(): void
    {
        $lotteries = Model_Lottery::get_lotteries_order_by_id();

        $this->assertEquals($lotteries, Lotto_Helper::get_cache('model_lottery.lotteriesorderbyid'));
    }

    /** @test */
    public function getLotteriesForWhitelabel_OldCacheWithWhitelabelIdArgument_ValidArgumentsAndDeleteCacheItemWorks(): void
    {
        $lotteriesForWhitelabel1 = Model_Lottery::get_lotteries_for_whitelabel(['id' => 1]);
        $lotteriesForWhitelabel2 = Model_Lottery::get_lotteries_for_whitelabel(['id' => 2]);

        $this->assertArrayHasKey('__by_slug', $lotteriesForWhitelabel1);
        $this->assertNotEquals($lotteriesForWhitelabel1, $lotteriesForWhitelabel2);

        $removeExtraDataByKeyThatDoesNotSaveIntoCache = ['__by_slug', '__by_id', '__sort_lastdate', '__sort_nextdate', 'supports_ticket_multipliers'];
        ArrayHelper::removeArrayAndArraySubItemsByKeys(
            $lotteriesForWhitelabel1,
            $removeExtraDataByKeyThatDoesNotSaveIntoCache,
        );

        ArrayHelper::removeArrayAndArraySubItemsByKeys(
            $lotteriesForWhitelabel2,
            $removeExtraDataByKeyThatDoesNotSaveIntoCache,
        );


        $cachedLotteriesForWhitelabel1 = Lotto_Helper::get_cache('model_lottery.lotteriesforwl.1');
        $cachedLotteriesForWhitelabel2 = Lotto_Helper::get_cache('model_lottery.lotteriesforwl')[2];

        // Cached results are saved as array in form of Whitelabel that contains Lotteries
        $this->assertEquals($lotteriesForWhitelabel1, $cachedLotteriesForWhitelabel1);
        $this->assertEquals($lotteriesForWhitelabel2, $cachedLotteriesForWhitelabel2);

        // Deleting cache deletes entire segment, not specific array with lotteries for whitelabel
        Lotto_Helper::clear_cache_item('model_lottery.lotteriesforwl');
        $this->expectException('CacheNotFoundException');
        $retrieveCacheAfterDelete = Lotto_Helper::get_cache('model_lottery.lotteriesforwl');
        $this->assertEmpty($retrieveCacheAfterDelete, 'Cache is not deleted');
    }

    /** @test */
    public function getLotteriesForWhitelabel_EmptyWhitelabelParameter_DoesNotCrashWithCorruptedParameter(): void
    {
        // Passing empty array corrupted what was saved into cache
        $lotteriesForWhitelabel = Model_Lottery::get_lotteries_for_whitelabel([]);
        $this->assertEmpty($lotteriesForWhitelabel);
    }

    /** @test */
    public function getLotteriesForWhitelabel_OldCache_DeletesBySpecificKey(): void
    {
        $lotteriesForWhitelabel1 = Model_Lottery::get_lotteries_for_whitelabel(['id' => 1]);
        Lotto_Helper::get_cache('model_lottery.lotteriesforwl.1');
        Lotto_Helper::clear_cache_item('model_lottery.lotteriesforwl.1');
        $this->expectException('CacheNotFoundException');
        Lotto_Helper::get_cache('model_lottery.lotteriesforwl.1');
    }

    /** @test */
    public function setGetDelete_UsesNewWrapperForCache_SetGetAndDeleteCacheCorrectly(): void
    {
        $valueToStore = 'value';

        Cache::set('model_lottery.get_lotteries_order_by_id.287_8760', $valueToStore);
        $this->assertEquals($valueToStore, Cache::get('model_lottery.get_lotteries_order_by_id.287_8760'));

        Cache::delete('model_lottery.get_lotteries_order_by_id.287_8760');
        $this->expectException('CacheNotFoundException');
        $this->assertEmpty(Cache::get('model_lottery.get_lotteries_order_by_id.287_8760'), 'Cache did not clear by specific key');
    }

    /** @test */
    public function setGetDelete_UsesNewWrapperForCache_DoesNotDeleteCacheBySegment(): void
    {
        $valueToStore = 'value';

        Cache::set('model_lottery.get_lotteries_order_by_id.287_8760', $valueToStore);
        $this->assertEquals($valueToStore, Cache::get('model_lottery.get_lotteries_order_by_id.287_8760'));

        Cache::delete('model_lottery.get_lotteries_order_by_id');
        $this->assertNotEmpty(Cache::get('model_lottery.get_lotteries_order_by_id.287_8760'), 'Cache shouldn\'t clear by segment');
    }
}

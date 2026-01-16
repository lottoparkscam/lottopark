<?php

namespace Feature\Dependencies;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractWordpressSeeder;
use Fuel\Tasks\Seeders\Wordpress\GGWorldPageRaffle;
use Fuel\Tasks\Seeders\Wordpress\GGWorldRaffleAddTranslation;
use Fuel\Tasks\Seeders\Wordpress\KenoLotteryAddTranslation;
use Fuel\Tasks\Seeders\Wordpress\KenoPageLottery;
use Fuel\Tasks\Seeders\Wordpress\ParentLotteriesAddTranslation;
use Fuel\Tasks\Seeders\Wordpress\ParentPageLottery;
use Fuel\Tasks\Seeders\Wordpress\ParentPageRaffle;
use Fuel\Tasks\Seeders\Wordpress\ParentRaffleAddTranslation;
use Test_Feature;

final class WpSeedersTest extends Test_Feature
{
    /**
     * @test
     * @return array<AbstractWordpressSeeder>
     */
    public function get_WpSeedersLists_ShouldReturnSeedersInExpectedOrder(): array
    {
        // Given entry name stores in container-config
        $entry = 'wpseeders';

        // And expected order
        $expectedOrder = [
            ParentPageLottery::class,
            ParentLotteriesAddTranslation::class,
            KenoPageLottery::class,
            KenoLotteryAddTranslation::class,
            ParentPageRaffle::class,
            ParentRaffleAddTranslation::class,
            GGWorldPageRaffle::class,
            GGWorldRaffleAddTranslation::class,
        ];

        // When fetched
        $entries = $this->container->get($entry);
        $entriesNames = array_map(fn (object $o) => get_class($o), $entries);

        // Focus only on provided seeders in expectedOrder; the order has only impact on those seeders
        $countExpectedSeeders = count($expectedOrder);
        $entriesNames = array_slice($entriesNames, 0, $countExpectedSeeders, true);

        // Then
        $this->assertSame($expectedOrder, $entriesNames);

        return $entries;
    }

    /**
     * @depends get_WpSeedersLists_ShouldReturnSeedersInExpectedOrder
     * @test
     * @param array<AbstractWordpressSeeder> $entries
     */
    public function get_WpSeedersLists_EachEntryShouldBeWpSeederInstance(array $entries): void
    {
        // Given expected instance
        $expected = AbstractWordpressSeeder::class;

        // Then each instance should be
        foreach ($entries as $entry) {
            $this->assertInstanceOf($expected, $entry);
        }
    }
}

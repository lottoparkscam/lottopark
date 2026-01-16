<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Container;
use Stwarog\FuelFixtures\Fuel\Factory;
use Tests\Fixtures\Utils\DupesPrevention\FeatureToggle;
use Tests\Fixtures\Utils\DupesPrevention\InteractsWithDupesFeatureToggle;

abstract class AbstractFixture extends Factory implements InteractsWithDupesFeatureToggle
{
    public function disallowDupes(): void
    {
        Container::get(FeatureToggle::class)->disallowDupes();
    }

    public function allowDupes(): void
    {
        Container::get(FeatureToggle::class)->allowDupes();
    }
}

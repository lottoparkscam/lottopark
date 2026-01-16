<?php

namespace Tests\Fixtures\Utils\DupesPrevention;

interface InteractsWithDupesFeatureToggle
{
    public function disallowDupes(): void;

    public function allowDupes(): void;
}

<?php

declare(strict_types=1);

namespace Tests\Fixtures\Utils\DupesPrevention;

final class FeatureToggle implements InteractsWithDupesFeatureToggle
{
    private bool $enabled;

    public function __construct(bool $enabled = true)
    {
        $this->enabled = $enabled;
    }

    public function isDupesPreventionDisabled(): bool
    {
        return !$this->enabled;
    }

    public function disallowDupes(): void
    {
        $this->enabled = true;
    }

    public function allowDupes(): void
    {
        $this->enabled = false;
    }
}

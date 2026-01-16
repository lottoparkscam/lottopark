<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Models\Whitelabel;
use Models\WhitelabelPlugin;

class WhitelabelPluginFixture extends AbstractFixture
{
    public function getDefaults(): array
    {
        return [
            'plugin' => $this->faker->randomElement(['primeads', 'mautic-api']),
            'is_enabled' => 1,
            'options' => null
        ];
    }

    public static function getClass(): string
    {
        return WhitelabelPlugin::class;
    }

    public function withWhitelabel(Whitelabel $whitelabel): callable
    {
        $this->with(function (WhitelabelPlugin $whitelabelPlugin) use ($whitelabel) {
            $whitelabelPlugin->whitelabel = $whitelabel;
        });

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Models\WhitelabelPlugin;
use Models\WhitelabelPluginUser;
use Models\WhitelabelUser;
use Carbon\Carbon;

class WhitelabelPluginUserFixture extends AbstractFixture
{
    public function getDefaults(): array
    {
        return [
            'is_active' => 1,
            'created_at' => Carbon::today(),
            'updated_at' => Carbon::now()->addHours(1),
        ];
    }

    public static function getClass(): string
    {
        return WhitelabelPluginUser::class;
    }

    public function withWhitelabelUser(WhitelabelUser $whitelabelUser): callable
    {
        $this->with(function (WhitelabelPluginUser $whitelabelPluginUser) use ($whitelabelUser) {
            $whitelabelPluginUser->whitelabelUser = $whitelabelUser;
        });

        return $this;
    }

    public function withWhitelabelPlugin(WhitelabelPlugin $whitelabelPlugin): callable
    {
        $this->with(function (WhitelabelPluginUser $whitelabelPluginUser) use ($whitelabelPlugin) {
            $whitelabelPluginUser->whitelabelPlugin = $whitelabelPlugin;
        });

        return $this;
    }
}

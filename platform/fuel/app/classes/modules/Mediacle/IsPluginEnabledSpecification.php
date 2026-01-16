<?php

namespace Modules\Mediacle;

use Model_Whitelabel_Plugin;

class IsPluginEnabledSpecification
{
    public function isSatisfiedBy(int $whitelabelId, string $pluginName): bool
    {
        return Model_Whitelabel_Plugin::count([], true, [
            ['whitelabel_id', '=', $whitelabelId],
            ['plugin', '=', $pluginName],
            ['is_enabled', '=', 1],
        ]) > 0;
    }
}

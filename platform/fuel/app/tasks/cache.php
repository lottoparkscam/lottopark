<?php

namespace Fuel\Tasks;

use Fuel\Core\Cache as FuelCache;
use Fuel\Core\Cli;
use Helpers_Cache;

final class Cache
{
    /**
     * Reset cache.
     * NOTE: it will reset everything.
     *
     * @return void
     */
    public function reset(): void
    {
        FuelCache::delete_all();
        echo "Cache reset\r\n";
    }

    /**
     * Reset cache for table.
     * You can pass --use-all option to use delete_all instead of delete.
     *
     * @param string $table
     * @return void
     */
    public function reset_table(string $table): void
    {
        $identifier = Helpers_Cache::reset_table($table, Cli::option('use-all', false));
        echo "Cache reset for $identifier\r\n";
    }

    /**
     * Reset cache for model.
     * You can pass --use-all option to use delete_all instead of delete.
     *
     * @param string $model model class
     * @return void
     */
    public function reset_model(string $model): void
    {
        $identifier = Helpers_Cache::reset_model($model, Cli::option('use-all', false));
        echo "Cache reset for $identifier\r\n";
    }
}

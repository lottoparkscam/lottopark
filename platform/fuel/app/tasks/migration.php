<?php

namespace Fuel\Tasks;

use Fuel\Core\Cli;
use Fuel\Core\Database_PDO_Cached;
use Fuel\Core\DB;
use Helpers_Cli;
use Oil\Refine;
use Task_Cli;

/**
 * Migrate task.
 */
final class Migration extends Task_Cli
{

    public function __construct()
    {
        $this->disableOnProduction();
    }

    private function execute_migration_logic(\Closure $drop_migrations): void // TODO: {Vordis 2020-04-24 15:06:35} could use better name
    {
        set_time_limit($_ENV['MAX_MIGRATION_TIME'] ?? 60);

        if (Cli::option('drop-cache', false)) {
            Helpers_Cli::execute_or_fail_with_print_output('php8.0 oil refine cache:reset');
        }

        echo "\r\nDown with migrations:\r\n";
        $drop_migrations();
        echo "\r\nUp with migrations:\r\n";
        \Helper_Migration::migrate(); // same as `php oil refine migrate --catchup` but with extended time limit

        $dont_seed = !Cli::option('seed', false);
        if ($dont_seed) {
            return;
        }
        echo "\r\nSeeding:\r\n";
        Helpers_Cli::execute_or_fail_with_print_output('php8.0 oil refine seed');
        if (Cli::option('update-lotteries', false)) {
            echo "\r\nUpdate lotteries:\r\n";
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            Refine::run("lottery:update_draw_data");
            echo "\r\nLotteries updated.\r\n";
        }
    }

    /**
     * Migrate down and then up (fresh instance of the application)
     * Attach --seed to launch seeders after migration.
     * Attach --drop-cache to drop cache before migration.
     * Attach --update-lotteries to update lotteries after seeding.
     * E.g. To drop cache, run down and up migration and seed database call `php oil r migrate:rollback --seed --drop-cache`
     */
    public function rollback(): void
    {
        $this->execute_migration_logic(function (): void {
            Helpers_Cli::execute_or_fail_with_print_output('php8.0 oil refine migrate:down -v=0');
        });
    }

    /**
     * Migrate down and then up (fresh instance of the application)
     * Attach --seed to launch seeders after migration.
     * Attach --drop-cache to drop cache before migration.
     * Attach --update-lotteries to update lotteries after seeding.
     * E.g. To drop cache, run down and up migration and seed database call `php oil r migrate:fresh --seed --drop-cache`
     */
    public function fresh(): void
    {
        $this->execute_migration_logic($this->wipe_closure());
    }

    /**
     * Wipe the database completely
     */
    public function wipe(): void
    {
        $this->wipe_closure()();
    }

    private function wipe_closure(): \Closure
    {
        return function (): void {
            $platformDBName = $_ENV["DB_PLATFORM_NAME"] ?? "platform";
            DB::query('SET FOREIGN_KEY_CHECKS=0;')
                ->execute();
            $tables_result = DB::query("SELECT TABLE_NAME
                FROM information_schema.tables
                WHERE table_schema = '{$platformDBName}';")
                ->execute();
            /** @var Database_PDO_Cached $tables_result */
            foreach ($tables_result as $table) {
                DB::query("DROP TABLE IF EXISTS {$table['TABLE_NAME']};")->execute();
            }
            DB::query('SET FOREIGN_KEY_CHECKS=1;')
                ->execute();
            echo "Database wipe successful.";
        };
    }
}

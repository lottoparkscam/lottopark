<?php

namespace Fuel\Tasks\Seeders;

use Fuel\Core\Fuel;
use Fuel\Core\DB;
use ReflectionClass;

abstract class Seeder
{
    /**
     * Seeding logic.
     * @return void
     * @throws \Exception
     */
    public function execute(): void
    {
        // load rows and columns
        switch (Fuel::$env) {
            case Fuel::DEVELOPMENT:
                $columns = $this->columnsDevelopment();
                $rows = $this->rowsDevelopment();
                break;
            case Fuel::STAGING:
                $columns = $this->columnsStaging();
                $rows =  $this->rowsStaging();
                break;
            default:
            case Fuel::PRODUCTION:
                $columns =  $this->columnsProduction();
                $rows =  $this->rowsProduction();
                break;
        }

        // allow for null or empty array to signal that specified env should not seed
        if (empty($rows)) {
            return;
        }
        // build and execute queries (one per table)
        foreach ($columns as $table => $columns) {
            DB::insert($table, $columns)
                ->values($rows[$table])
                ->execute();
        }

        $this->run_post_execute_callbacks();
    }

    /**
     * Automatically executes any method that ends with _post_execute_callback
     * after Seeder::execute() method
     */
    protected function run_post_execute_callbacks(): void
    {
        $seeder_class = new ReflectionClass($this);
        $methods = $seeder_class->getMethods();
        $postfix_search = "_post_execute_callback";
        foreach ($methods as $method) {
            if (substr($method->name, -strlen($postfix_search)) === $postfix_search) {
                call_user_func([$this, $method->name]);
            }
        }
    }

    /**
     * Check if Docker is used
     * @return bool
     */
    protected function is_docker(): bool
    {
        if (getenv("IS_DOCKER")) {
            return true;
        }
        return false;
    }

    /**
     * Define columns used by seeder.
     * NOTE: can be for many tables.
     *
     * @return array format 'table' => [col1...coln]
     */
    protected function columnsDevelopment(): array
    {
        return $this->columnsStaging();
    }

    /**
     * Define rows used by seeder.
     * NOTE: can be for many tables.
     *
     * @return array format 'table' => [row1[val1...valn]...rown[val1...valn]]
     */
    protected function rowsDevelopment(): array
    {
        return $this->rowsStaging();
    }

    /**
     * Define columns used by seeder.
     * NOTE: can be for many tables.
     * NOTE: this will be used as default columns for local and production if you don't override them.
     *
     * @return array format 'table' => [col1...coln]
     */
    abstract protected function columnsStaging(): array;

    /**
     * Define rows used by seeder.
     * NOTE: can be for many tables.
     * NOTE: this will be used as default rows for local and production if you don't override them.
     *
     * @return array format 'table' => [row1[val1...valn]...rown[val1...valn]]
     */
    abstract protected function rowsStaging(): array;

    /**
     * Define columns used by seeder.
     * NOTE: can be for many tables.
     *
     * @return array format 'table' => [col1...coln]
     */
    protected function columnsProduction(): array
    {
        return $this->columnsStaging();
    }

    /**
     * Define rows used by seeder.
     * NOTE: can be for many tables.
     *
     * @return array format 'table' => [row1[val1...valn]...rown[val1...valn]]
     */
    protected function rowsProduction(): array
    {
        return $this->rowsStaging();
    }
}

<?php


namespace Fuel\Tasks\Seeders;


use Fuel\Core\DB;
use Fuel\Core\Fuel;

abstract class Reseeder extends Seeder
{
    /**
     * Seeding logic.
     *
     * @return void
     * @throws \Exception
     */
    public function execute(): void
    {
        // load rows and columns
        switch (Fuel::$env) {
            case Fuel::DEVELOPMENT:
                $rows = $this->rowsDevelopment();
                break;
            case Fuel::STAGING:
                $rows = $this->rowsStaging();
                break;
            default:
            case Fuel::PRODUCTION:
                $rows = $this->rowsProduction();
                break;
        }

        // allow for null or empty array to signal that specified env should not seed
        if (empty($rows)) {
            return;
        }
        // build and execute queries (one per table)
        foreach ($rows as $table => $records) {
            foreach ($records as $record) {
                try {
                    DB::update($table)
                        ->where($record['where'])
                        ->set($record['set'])
                        ->execute();
                } catch (\Throwable $e) {
                    \Helpers_Cli::warning('Error executing update query. ' . $e->getMessage());
                }
            }
        }
    }

    protected function columnsStaging(): array
    {
        return [];
    }
}
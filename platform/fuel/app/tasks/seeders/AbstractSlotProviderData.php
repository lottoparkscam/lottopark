<?php

namespace Fuel\Tasks\Seeders;

use Fuel\Core\DB;

abstract class AbstractSlotProviderData extends Seeder
{
    use \Without_Foreign_Key_Checks;

    /**
     *  @var array{slot_provider: int} $newRecordsIds
     *  example: ['slot_provider' => 1]
     */
    private array $newRecordsIds;

    public function __construct()
    {
        $tables = array_keys($this->columnsStaging());
        /** we don't need any id from this table so one query less with this unset */
        unset($tables['slot_whitelist_ip']);

        foreach ($tables as $table) {
            $lastId = DB::query("SELECT max(id) as lastId FROM $table ")->execute()[0]['lastId'];
            $this->newRecordsIds[$table] = $lastId + 1;
        }
    }

    protected function columnsStaging(): array
    {
        return [
            "slot_provider" => ["id", "slug", "api_url", "init_game_path", "init_demo_game_path", "api_credentials", "game_list_path"],
            "slot_whitelist_ip" => ["slot_provider_id", "ip"]
        ];
    }

    protected function rowsStaging(): array
    {
        $dataToInsert = [
            "slot_provider" => [
                $this->newRecordsIds['slot_provider'],
                static::SLOT_PROVIDER_SLUG,
                static::SLOT_PROVIDER_API_URL,
                static::SLOT_PROVIDER_INIT_GAME_PATH,
                static::SLOT_PROVIDER_INIT_DEMO_GAME_PATH,
                json_encode(static::SLOT_PROVIDER_API_CREDENTIALS),
                static::SLOT_PROVIDER_GAME_LIST_PATH
            ],
            "slot_whitelist_ip" => []
        ];

        foreach (static::WHITELIST_IP as $ip) {
            array_push($dataToInsert['slot_whitelist_ip'], [
                $this->newRecordsIds['slot_provider'], $ip
            ]);
        }

        return $dataToInsert;
    }

    protected function rowsProduction(): array
    {
        $dataToInsert = [
            "slot_provider" => [
                $this->newRecordsIds['slot_provider'],
                static::SLOT_PROVIDER_SLUG,
                "CORRECT_ME",
                static::SLOT_PROVIDER_INIT_GAME_PATH,
                static::SLOT_PROVIDER_INIT_DEMO_GAME_PATH,
                json_encode(['lottopark_merchant_id' => 'CORRECT_ME', 'lottopark_merchant_key' => 'CORRECT_ME']),
                static::SLOT_PROVIDER_GAME_LIST_PATH
            ],
            "slot_whitelist_ip" => []
        ];

        foreach (static::PROD_WHITELIST_IP as $ip) {
            array_push($dataToInsert['slot_whitelist_ip'], [
                $this->newRecordsIds['slot_provider'], $ip
            ]);
        }

        return $dataToInsert;
    }

    protected function columnsProduction(): array
    {
        return $this->columnsStaging();
    }

    protected function rowsDevelopment(): array
    {
        return $this->rowsStaging();
    }

    protected function columnsDevelopment(): array
    {
        return $this->columnsStaging();
    }
}

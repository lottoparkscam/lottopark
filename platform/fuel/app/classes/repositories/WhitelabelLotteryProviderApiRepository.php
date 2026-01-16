<?php

namespace Repositories;

use Composer\Installers\PantheonInstaller;
use Models\WhitelabelLotteryProviderApi;
use Throwable;
use Helpers_Time;
use Fuel\Core\Cache;
use Fuel\Core\CacheNotFoundException;
use Repositories\Orm\AbstractRepository;

class WhitelabelLotteryProviderApiRepository extends AbstractRepository
{
    public const CACHE_KEY = 'whitelabel_lottery_provider_api_';

    public function __construct(WhitelabelLotteryProviderApi $model)
    {
        parent::__construct($model);
    }

    /** @throws Throwable on database error */
    public function getApiDetailsByWhitelabelId(int $whitelabelId): array
    {
        try {
            $apiSettings = Cache::get(WhitelabelLotteryProviderApiRepository::CACHE_KEY . 'apiDetails_' . $whitelabelId);
        } catch (CacheNotFoundException $exception) {
            /** @var mixed $results */
            $results = $this->db->selectArray([
                'id',
                'api_key',
                'api_secret',
                'is_enabled',
                'scan_confirm_url',
            ])
                ->from($this->model::get_table_name())
                ->where('whitelabel_id', $whitelabelId)
                ->execute();
            $apiSettings = $results->as_array()[0];
            Cache::set(WhitelabelLotteryProviderApiRepository::CACHE_KEY . 'apiDetails_' . $whitelabelId, $apiSettings, Helpers_Time::DAY_IN_SECONDS);
        }

        return $apiSettings;
    }

    /** @throws Throwable on database error */
    public function getWhitelabelIdsWithApiEnabled(): array
    {
        try {
            $whitelabelIds = Cache::get(WhitelabelLotteryProviderApiRepository::CACHE_KEY . 'whitelabelIds');
        } catch (CacheNotFoundException $exception) {
            /** @var mixed $results */
            $results = $this->db->selectArray([
                'whitelabel_id',
            ])
                ->from($this->model::get_table_name())
                ->where('is_enabled', true);

            $results = $results->execute()->as_array();

            $whitelabelIds = [];
            foreach ($results as $api) {
                $whitelabelIds[] = (int)$api['whitelabel_id'];
            }

            Cache::set(WhitelabelLotteryProviderApiRepository::CACHE_KEY . 'whitelabelIds', $whitelabelIds, Helpers_Time::DAY_IN_SECONDS);
        }

        return $whitelabelIds;
    }
}

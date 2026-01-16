<?php

namespace Repositories;

use Exception;
use Fuel\Core\Cache;
use Fuel\Core\CacheNotFoundException;
use Helpers_Time;
use Models\MiniGame;
use Repositories\Orm\AbstractRepository;
use Services\Logs\FileLoggerService;

/**
 * @method findOneBySlug($miniGameSlug)
 */
class MiniGameRepository extends AbstractRepository
{
    private FileLoggerService $fileLoggerService;

    public function __construct(
        MiniGame $model,
        FileLoggerService $fileLoggerService,
    ) {
        parent::__construct($model);
        $this->fileLoggerService = $fileLoggerService;
    }

    public function getAllEnabledGamesBasicInfoById(): array
    {
        $cacheKey = 'MiniGameRepository.getAllEnabledGamesBasicInfoById';

        try {
            try {
                $results = Cache::get($cacheKey);
            } catch (CacheNotFoundException $e) {
                /** @var mixed $query */
                $query = $this->db->selectArray([
                        'id',
                        'slug',
                        'name',
                        'is_enabled',
                        'is_deleted'
                    ])
                    ->from($this->model::get_table_name())
                    ->where('is_enabled', true)
                    ->and_where('is_deleted', false);

                $results = $query->execute();
                $results = $results->as_array();
            
                Cache::set($cacheKey, $results, Helpers_Time::DAY_IN_SECONDS);
            }

            return $results ?? [];
        } catch (Exception $e) {
            $this->fileLoggerService->error('Error fetching MiniGames from database: ' . $e->getMessage());
            return [];
        }
    }

    public function getAvailableBetsByGameId(): array
    {
        $cacheKey = 'MiniGameRepository.getAvailableBetsByGameId';

        try {
            try {
                $results = Cache::get($cacheKey);
            } catch (CacheNotFoundException $e) {
                /** @var mixed $query */
                $query = $this->db->selectArray([
                    'id',
                    'available_bets'
                ])
                    ->from($this->model::get_table_name())
                    ->where('is_enabled', true)
                    ->and_where('is_deleted', false);

                $results = $query->execute();
                $results = $results->as_array();

                Cache::set($cacheKey, $results, Helpers_Time::DAY_IN_SECONDS);
            }

            $betsByGameId = [];
            foreach ($results as $result) {
                $betsByGameId[$result['id']] = $result['available_bets'];
            }

            return $betsByGameId;
        } catch (Exception $e) {
            $this->fileLoggerService->error('Error fetching available bets from database: ' . $e->getMessage());
            return [];
        }
    }
}

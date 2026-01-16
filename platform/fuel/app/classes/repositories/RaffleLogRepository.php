<?php

namespace Repositories;

use Models\RaffleLog;
use Repositories\Orm\AbstractRepository;

/** @method deleteRecordsOlderThanXDays(int $days, string $dateColumn = 'date'): void */
class RaffleLogRepository extends AbstractRepository
{
    public function __construct(RaffleLog $model)
    {
        parent::__construct($model);
    }
}

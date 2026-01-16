<?php

namespace Repositories;

use Repositories\Orm\AbstractRepository;
use Models\ImvalapLog;

/** 
 * @method deleteRecordsOlderThanXDays(int $days, string $dateColumn = 'date'): void 
 * @deprecated
 */
class ImvalapLogRepository extends AbstractRepository
{
    public function __construct(ImvalapLog $model)
    {
        parent::__construct($model);
    }
}

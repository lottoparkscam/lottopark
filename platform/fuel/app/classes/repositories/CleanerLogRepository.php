<?php

namespace Repositories;

use Carbon\Carbon;
use Models\CleanerLog;
use Repositories\Orm\AbstractRepository;

class CleanerLogRepository extends AbstractRepository
{
    public function __construct(CleanerLog $model)
    {
        parent::__construct($model);
    }

    public function addLog(int $whitelabelId, int $transactionId, string $message): void
    {
        $log = new CleanerLog();
        $log->whitelabelId = $whitelabelId;
        $log->whitelabelTransactionId = $transactionId;
        $log->message = $message;
        $log->createdAt = new Carbon($log->getTimezoneForField('createdAt'));
        $log->save();
    }
}

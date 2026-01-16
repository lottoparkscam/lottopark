<?php

namespace Repositories;

use Repositories\Orm\AbstractRepository;
use Models\SynchronizerLog;
use Carbon\Carbon;
use Wrappers\Db;
use Container;

class SynchronizerLogRepository extends AbstractRepository
{
    protected Db $db;

    public function __construct(SynchronizerLog $model)
    {
        parent::__construct($model);
        $this->db = Container::get(Db::class);
    }

    public function addLog(int $whitelabelId, int $transactionId, string $type, string $message): void
    {
        $log = new SynchronizerLog();
        $log->whitelabelId = $whitelabelId;
        $log->whitelabelTransactionId = $transactionId;
        $log->message = $message;
        $log->type = $type;
        $log->createdAt = new Carbon($log->getTimezoneForField('createdAt'));
        $log->save();
    }
}

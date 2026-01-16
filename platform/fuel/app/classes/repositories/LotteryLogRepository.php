<?php

namespace Repositories;

use Carbon\Carbon;
use Classes\Orm\Criteria\Model_Orm_Criteria_Order;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Helpers_Time;
use Models\LotteryLog;
use Repositories\Orm\AbstractRepository;
use Services\Logs\FileLoggerService;
use Throwable;

/** @method deleteRecordsOlderThanXDays(int $days, string $dateColumn = 'date'): void */
class LotteryLogRepository extends AbstractRepository
{
    private FileLoggerService $fileLoggerService;

    public function __construct(LotteryLog $model, FileLoggerService $fileLoggerService)
    {
        parent::__construct($model);
        $this->fileLoggerService = $fileLoggerService;
    }

    public function addInfoLog(int $lotteryId, string $message): void
    {
        $this->addLog(LotteryLog::TYPE_INFO, $lotteryId, $message);
    }

    public function addSuccessLog(int $lotteryId, string $message): void
    {
        $this->addLog(LotteryLog::TYPE_SUCCESS, $lotteryId, $message);
    }

    public function addWarningLog(int $lotteryId, string $message): void
    {
        $this->addLog(LotteryLog::TYPE_WARNING, $lotteryId, $message);
    }

    public function addErrorLog(int $lotteryId, string $message): void
    {
        $this->addLog(LotteryLog::TYPE_ERROR, $lotteryId, $message);
    }

    public function addLog(int $logTypeCode, int $lotteryId, string $message): void
    {
        try {
            $log = new LotteryLog();
            $currentTimestamp = new Carbon($log->getTimezoneForField('date'));
            $currentTimestamp = $currentTimestamp->format('Y-m-d H:i:s');

            $log->lotteryId = $lotteryId;
            $log->date = $currentTimestamp;
            $log->type = $logTypeCode;
            $log->message = $message;
            $log->save();
        } catch (Throwable $e) {
            $this->fileLoggerService->error(
                "Error during saving lottery_log: " . $e->getMessage()
            );
        }
    }

    public function successLogNotExistsInTheLastHour(int $lotteryId): bool
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('lottery_id', $lotteryId),
            new Model_Orm_Criteria_Where('type', LotteryLog::TYPE_SUCCESS),
            new Model_Orm_Criteria_Order('date', 'DESC'),
        ]);

        /** @var LotteryLog|null $successLog */
        $successLog = $this->findOne();
        if (is_null($successLog)) {
            return true;
        }

        $hourAgo = Carbon::now(Helpers_Time::TIMEZONE)->subHour();
        return $successLog->date->lessThan($hourAgo);
    }
}

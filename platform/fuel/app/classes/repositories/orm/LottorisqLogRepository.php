<?php

namespace Repositories;

use Carbon\Carbon;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Models\LottorisqLog;
use Repositories\Orm\AbstractRepository;
use Services\Logs\FileLoggerService;
use Throwable;

/** @method deleteRecordsOlderThanXDays(int $days, string $dateColumn = 'date'): void */
class LottorisqLogRepository extends AbstractRepository
{
    private FileLoggerService $fileLoggerService;

    public function __construct(LottorisqLog $model, FileLoggerService $fileLoggerService)
    {
        parent::__construct($model);
        $this->fileLoggerService = $fileLoggerService;
    }

    /** @property mixed $data */
    public function addLogIfDoesNotExistByWhitelabelUserTicketSlipId(
        int $type,
        ?int $whitelabelId,
        ?int $whitelabelUserTicketId,
        int $whitelabelUserTicketSlipId,
        string $message,
        $data = null,
        ?int $whitelabelLtechId = null,
        bool $addSlackLog = true
    ): void {
        $wasRecordNotAddedBefore = !$this->exists($whitelabelUserTicketSlipId, 'whitelabel_user_ticket_slip_id');

        if ($wasRecordNotAddedBefore) {
            $this->addLog(
                $type,
                $whitelabelId,
                $whitelabelUserTicketId,
                $whitelabelUserTicketSlipId,
                $message,
                $data,
                $whitelabelLtechId
            );

            if ($addSlackLog) {
                $this->fileLoggerService->error(
                    $message
                );
            }
        }
    }

    /**
     * We check if we received success 1h before to avoid spam on slack
     * If any success has not appeared before 1h we add slack log
     * @return bool true when slack log has been sent
     */
    public function addLtechResponseErrorLog(
        Throwable $exception
    ): bool {
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $datetimeBeforeSixHours = Carbon::now()->subHours(6)->format('Y-m-d H:i:s');

        $hadNotSuccess = !$this->recordExists([
            new Model_Orm_Criteria_Where('message', LottorisqLog::MESSAGE_SUCCESS_DRAW_DOWNLOAD),
            new Model_Orm_Criteria_Where('date', [$datetimeBeforeSixHours, $now], 'BETWEEN'),
        ]);

        $this->addErrorLog(null, null, null, $exception->getMessage());

        $isNot500Error = !str_contains($exception->getMessage(), "Ltech response error. Code: 500");
        $isNotNullDrawError = !str_contains($exception->getMessage(), "Error fetching draw. Ltech next draw date is null.");
        $shouldSendError = $isNotNullDrawError && $isNot500Error && $hadNotSuccess;
        if ($shouldSendError) {
            $this->fileLoggerService->error(
                $exception->getMessage()
            );
            return true;
        }

        return false;
    }

    /** @property mixed $data */
    public function addErrorLog(
        ?int $whitelabelId,
        ?int $whitelabelUserTicketId,
        ?int $whitelabelUserTicketSlipId,
        string $message,
        $data = null,
        ?int $whitelabelLtechId = null
    ): void {
        $this->addLog(
            LottorisqLog::TYPE_ERROR,
            $whitelabelId,
            $whitelabelUserTicketId,
            $whitelabelUserTicketSlipId,
            $message,
            $data,
            $whitelabelLtechId
        );
    }

    /** @property mixed $data */
    public function addWarningLog(
        ?int $whitelabelId,
        ?int $whitelabelUserTicketId,
        ?int $whitelabelUserTicketSlipId,
        string $message,
        $data = null,
        ?int $whitelabelLtechId = null
    ): void {
        $this->addLog(
            LottorisqLog::TYPE_WARNING,
            $whitelabelId,
            $whitelabelUserTicketId,
            $whitelabelUserTicketSlipId,
            $message,
            $data,
            $whitelabelLtechId
        );
    }

    /** @property mixed $data */
    public function addSuccessLog(
        ?int $whitelabelId,
        ?int $whitelabelUserTicketId,
        ?int $whitelabelUserTicketSlipId,
        string $message,
        $data = null,
        ?int $whitelabelLtechId = null
    ): void {
        $this->addLog(
            LottorisqLog::TYPE_SUCCESS,
            $whitelabelId,
            $whitelabelUserTicketId,
            $whitelabelUserTicketSlipId,
            $message,
            $data,
            $whitelabelLtechId
        );
    }

    /** @property mixed $data */
    public function addInfoLog(
        ?int $whitelabelId,
        ?int $whitelabelUserTicketId,
        ?int $whitelabelUserTicketSlipId,
        string $message,
        $data = null,
        ?int $whitelabelLtechId = null
    ): void {
        $this->addLog(
            LottorisqLog::TYPE_INFO,
            $whitelabelId,
            $whitelabelUserTicketId,
            $whitelabelUserTicketSlipId,
            $message,
            $data,
            $whitelabelLtechId
        );
    }

    /** @property mixed $data */
    public function addLog(
        int $type,
        ?int $whitelabelId,
        ?int $whitelabelUserTicketId,
        ?int $whitelabelUserTicketSlipId,
        string $message,
        $data = null,
        ?int $whitelabelLtechId = null
    ): void {

        try {
            $now = new Carbon($this->model->getTimezoneForField('date'));

            $log = new LottorisqLog();
            $log->whitelabelId = $whitelabelId;
            $log->whitelabelUserTicketId = $whitelabelUserTicketId;
            $log->whitelabelUserTicketSlipId = $whitelabelUserTicketSlipId;
            $log->message = $message;
            $log->date = $now->format('Y-m-d H:i:s');
            $log->type = $type;

            if (empty($data)) {
                $log->data = serialize($data);
            }

            if (empty($whitelabelLtechId)) {
                $log->whitelabelLtechId = $whitelabelLtechId;
            }

            $log->save();
        } catch (Throwable $e) {
            $this->fileLoggerService->error(
                'Failed to add new log for table lottorisq_log: ' . $e->getMessage()
            );
        }
    }
}

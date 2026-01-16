<?php

use Repositories\LotteryLogRepository;
use Repositories\LotteryRepository;

/** Parent of LCS chain tasks. */
abstract class Task_Lotterycentralserver_Chain_Task extends Task_Task
{

    protected $failure_mails = [
    ];

    /**
     * True if this task should be executed in database transaction.
     * Default false.
     *
     * @var boolean
     */
    protected $in_transaction = true;

    /**
     * Slug of the lottery.
     *
     * @var string
     */
    protected $lottery_slug;

    /**
     * Create chain task for synchronization of tickets.
     *
     * @param string $lottery_slug lottery_slug of the lottery, which tickets will be synchronized.
     */
    protected function __construct(string $lottery_slug)
    {
        parent::__construct();
        $this->lottery_slug = $lottery_slug;
    }

    /**
     * Throw failure of the task if provided task is failed.
     *
     * @param Task_Interface_Result $result
     * @param string                $message concrete message of the failure, will be suffixed with '; lottery = slug'.
     *
     * @return void
     * @throws Exception if result of the task is failure.
     */
    protected function throw_task_failure(Task_Interface_Result $result, string $message): void
    {
        /** @var mixed $result */
        // also it could be automatized via reflection (attach all fields to exception, but it may be overkill)
        if ($result->is_failed()) {
            $this->throw_failure($message);
        }
    }

    /**
     * Throw failure of the task.
     *
     * @param string $message concrete message of the failure, will be suffixed with '; lottery = slug'.
     * @return void
     * @throws \Exception failure exception created from message.
     */
    protected function throw_failure(string $message): void
    {
        // also it could be automatized via reflection (attach all fields to exception, but it may be overkill)
        throw new \Exception("$message; lottery = {$this->lottery_slug}");
    }

    protected function on_execution_failure(\Throwable $throwable, bool $shouldLogError = true): void
    {
        /** @var LotteryLogRepository $lotteryLogRepository */
        $lotteryLogRepository = Container::get(LotteryLogRepository::class);

        /** @var LotteryRepository $lotteryRepository */
        $lotteryRepository = Container::get(LotteryRepository::class);
        $lotteryId = $lotteryRepository->getIdBySlug($this->lottery_slug);

        global $shouldLogWhileLcsIsUnavailable;
        if ($shouldLogWhileLcsIsUnavailable === false) {
            $shouldLogError = false;
        }

        parent::on_execution_failure($throwable, $shouldLogError);

        $isLotteryId = !is_null($lotteryId);
        if ($isLotteryId) {
            $lotteryLogRepository->addErrorLog(
                $lotteryId,
                parent::get_last_error_message()
            );
        } else {
            $this->fileLoggerService->error(
                "Cannot find lottery with slug {$this->lottery_slug} 
                log error message:" . parent::get_last_error_message()
            );
        }
    }
}
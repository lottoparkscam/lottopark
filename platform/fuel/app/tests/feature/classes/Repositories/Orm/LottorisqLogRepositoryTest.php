<?php

namespace Tests\Feature\Classes\Repositories\Orm;

use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Exception;
use Wrappers\Db;
use Test_Feature;
use Models\LottorisqLog;
use Repositories\LottorisqLogRepository;

class LottorisqLogRepositoryTest extends Test_Feature
{
    private LottorisqLogRepository $lottorisqLogRepository;
    protected Db $db;

    public function setUp(): void
    {
        parent::setUp();
        $this->lottorisqLogRepository = $this->container->get(LottorisqLogRepository::class);
        $this->db = $this->container->get(Db::class);
    }

    /** @test */
    public function addLogIfDoesNotExistByWhitelabelUserTicketSlipId_WasAddedBefore_ShouldAddOnlyOne(): void
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS = false;')->execute();

        $type = LottorisqLog::TYPE_SUCCESS;
        $whitelabelId = 1;
        $whitelabelUserTicketId = 1;
        $whitelabelUserTicketSlipId = 1;
        $message = 'test message';

        $expectedCountOfThisLog = 1;

        // here should add log
        $this->lottorisqLogRepository->addLogIfDoesNotExistByWhitelabelUserTicketSlipId(
            $type,
            $whitelabelId,
            $whitelabelUserTicketId,
            $whitelabelUserTicketSlipId,
            $message
        );

        $countOfLogsAfterFirstInsert = $this->selectCountOfLogsByWhitelabelUserTicketSlipId($whitelabelUserTicketSlipId);
        $this->assertSame($expectedCountOfThisLog, $countOfLogsAfterFirstInsert);

        // here should skip; we need to add it only once
        $this->lottorisqLogRepository->addLogIfDoesNotExistByWhitelabelUserTicketSlipId(
            $type,
            $whitelabelId,
            $whitelabelUserTicketId,
            $whitelabelUserTicketSlipId,
            $message
        );
        $this->assertSame($expectedCountOfThisLog, $countOfLogsAfterFirstInsert);

        $this->db->query('SET FOREIGN_KEY_CHECKS = true;')->execute();
    }

    private function selectCountOfLogsByWhitelabelUserTicketSlipId(int $whitelabelUserTicketSlipId): int
    {
        return $this->selectCountOfLogs('whitelabel_user_ticket_slip_id', $whitelabelUserTicketSlipId);
    }

    private function selectCountOfLogs(string $field, $value): int
    {
        return $this->lottorisqLogRepository->pushCriteria(
            new Model_Orm_Criteria_Where($field, $value)
        )->getCount() ?? 0;
    }

    private function selectCountOfLogsByMessage(string $message): int
    {
        return $this->selectCountOfLogs('message', $message);
    }

    /** @test */
    public function addLtechResponseErrorLogReturnsNot200CodeShouldAddSlackLog(): void
    {
        $exception = new Exception('Ltech response error. Code: 500');
        $this->lottorisqLogRepository->addLtechResponseErrorLog($exception);
        $count = $this->selectCountOfLogsByMessage($exception->getMessage());
        $this->assertSame(1, $count);
    }

    /** @test */
    public function addLtechResponseErrorLogReturnsNot200CodeShouldNotAddSlackLog(): void
    {
        $this->lottorisqLogRepository->addSuccessLog(null, null, null, LottorisqLog::MESSAGE_SUCCESS_DRAW_DOWNLOAD);
        $exception = new Exception('Ltech response error. Code: 500');
        $hasAddedSlackLog = $this->lottorisqLogRepository->addLtechResponseErrorLog($exception);
        $count = $this->selectCountOfLogsByMessage($exception->getMessage());
        $this->assertSame(1, $count);
        $this->assertFalse($hasAddedSlackLog);
    }
}

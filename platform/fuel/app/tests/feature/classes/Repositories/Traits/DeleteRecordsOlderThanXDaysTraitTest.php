<?php

namespace Tests\Feature\Classes\Repositories\Traits;

use Container;
use Repositories\LotteryLogRepository;
use Test_Feature;
use Carbon\Carbon;
use Wrappers\Db;
use Models\LotteryLog;

class DeleteRecordsOlderThanXDaysTraitTest extends Test_Feature
{
    private LotteryLogRepository $lotteryLogRepository;
    private LotteryLog $lotteryLog;
    protected Db $db;

    public function setUp(): void
    {
        parent::setUp();
        $this->lotteryLogRepository = Container::get(LotteryLogRepository::class);
        $this->lotteryLog = Container::get(LotteryLog::class);
        $this->db = Container::get(Db::class);
    }

    /** @test */
    public function deleteRecordsOlderThanXDaysTrait_shouldRemoveAllRecordsOlderThan30Days(): void
    {
        $today = Carbon::now();
        $countOfDaysToRemove = 30;
        $dateBefore30Days = $today->subDays($countOfDaysToRemove)->format('Y-m-d');

        // get the actual count of logs
        $countOfLogsBeforeThoseDays = $this->selectCountOfLogsFromProvidedDate($dateBefore30Days);

        // add some log if count is empty
        if ($countOfLogsBeforeThoseDays === 0) {
            $testLog = new LotteryLog();
            $testLog->date = $dateBefore30Days . " 00:00:00";
            $testLog->type = 0;
            $testLog->message = 'Its a test';
            $testLog->lottery_id = 1;
            $testLog->save();

            $countOfLogsBeforeThoseDays = $this->selectCountOfLogsFromProvidedDate($dateBefore30Days);
            $this->assertSame(1, $countOfLogsBeforeThoseDays);
        }

        // remove logs
        $this->lotteryLogRepository->deleteRecordsOlderThanXDays($countOfDaysToRemove);

        // check again the count of logs; after cleaning should return 0
        $countOfLogsBeforeThoseDays = $this->selectCountOfLogsFromProvidedDate($dateBefore30Days);

        $this->assertSame(0, $countOfLogsBeforeThoseDays);
    }

    private function selectCountOfLogsFromProvidedDate(string $date): int
    {
        return $this->db->select(
            $this->db->expr("count(id) as count")
        )
            ->from($this->lotteryLog::get_table_name())
            ->where($this->db->expr("date(date)"), "<=", $date)
            ->execute()->as_array()[0]['count'];
    }
}

<?php

namespace Services\Lottery\Draw;

use Carbon\Carbon;
use Fuel\Core\Cache;
use Helpers_General;
use Helpers_Lottery;
use Helpers_Mail;
use Helpers_Time;
use Lotto_Helper;
use Model_Lottery;
use Model_Multidraw;
use Models\Lottery;
use Models\WhitelabelLottery;
use Model_Lottery_Provider;
use Wrappers\Db;
use Model_Whitelabel_User_Ticket;
use Models\WhitelabelUserTicket;
use Services\Logs\FileLoggerService;

class UnscheduledDrawService
{

    private array $logs = [];

    private ?Carbon $newNextDraw;

    private ?Carbon $previousNextDraw;

    private ?Carbon $deletedDraw;

    private ?array $newDrawDates;

    private Db $db;

    private Lottery $lottery;
    private FileLoggerService $fileLoggerService;

    public function __construct(Db $db, FileLoggerService $fileLoggerService)
    {
        $this->db = $db;
        $this->fileLoggerService = $fileLoggerService;
    }

    private function addLog(string $title, string $message, int $line, bool $isWarning = false): void
    {
        $logMessage = "$title - $message at line $line";
        if ($isWarning) {
            $this->fileLoggerService->warning($logMessage);
        } else {
            $this->fileLoggerService->info($logMessage);
        }

        $this->logs[] = [
            'title' => $title,
            'message' => $message
        ];
    }

    private function clearLogs(): void
    {
        $this->logs = [];
    }

    private function generateNewDrawDatesArray(): bool
    {
        $nextDrawFormatted = $this->newNextDraw->format(Helpers_Time::DRAWDATE_FORMAT);
        $drawDates = $this->lottery->drawDates;
        $lotteryTimezone = $this->lottery->timezone;

        $someDrawHasBeenDeleted = $this->deleteMissedDrawDate($drawDates);

        if ($someDrawHasBeenDeleted) {
            $drawDates = $this->newDrawDates;
        }

        if (in_array($nextDrawFormatted, $drawDates)) {
            return $someDrawHasBeenDeleted;
        }

        $nextDrawIndexInDrawsArray = -1;

        foreach ($drawDates as $index => $drawDate) {
            $drawDateTime = Carbon::parse($drawDate, $lotteryTimezone);

            $drawDayOfWeek = $drawDateTime->isoWeekday();
            $nextDrawDayOfWeek = $this->newNextDraw->isoWeekday();

            // Week day from draw is before next draw
            if ($drawDayOfWeek < $nextDrawDayOfWeek) {
                continue;
            }

            // First item where week day is after next draw week day
            if ($drawDayOfWeek > $nextDrawDayOfWeek) {
                $nextDrawIndexInDrawsArray = $index;
                break;
            }

            $nowWithDrawTime = Carbon::now($lotteryTimezone)->setTimeFrom($drawDateTime);
            $nowWithNextDrawTime = Carbon::now($lotteryTimezone)->setTimeFrom($this->newNextDraw);

            // If its the same week day, check time
            // First item where week days are the same and time is after next draw time
            if ($nowWithDrawTime > $nowWithNextDrawTime) {
                $nextDrawIndexInDrawsArray = $index;
                break;
            }
        }

        if ($nextDrawIndexInDrawsArray === -1) {
            $nextDrawIndexInDrawsArray = count($drawDates);
        }

        array_splice($drawDates, $nextDrawIndexInDrawsArray, 0, $nextDrawFormatted);
        $this->newDrawDates = $drawDates;

        return true;
    }

    public function updateLotteryOnNewDraw(Lottery $lottery, Carbon $previousNextDraw): bool
    {
        set_time_limit(30);

        if (empty($lottery->next_date_local)) {
            return false;
        }

        if ($lottery->id === Helpers_Lottery::SUPER_ENALOTTO_ID) {
            return false;
        }

        $this->lottery = $lottery;
        $this->previousNextDraw = $previousNextDraw;
        $this->newNextDraw = Carbon::parse($lottery->next_date_local, $lottery->timezone);

        $newDrawDatesWasGenerated = $this->generateNewDrawDatesArray();

        try {
            $this->db->start_transaction();

            if ($newDrawDatesWasGenerated) {
                $this->updateDrawDates();
            }

            if (in_array($lottery->id, Helpers_Lottery::SPECIAL_CLOSING_TIMES)) {
                $this->updateProvider();
            }

            $this->updateTickets();
            $this->updateMultiDraw();
            $this->updateUserDrawNotifications();

            $this->db->commit_transaction();

            $this->sendEmailReport();
            $this->clearLogs();

        } catch (\Throwable $e) {
            $this->db->rollback_transaction();

            $this->fileLoggerService->error(
                $e->getMessage()
            );

            return false;
        }

        return true;
    }

    private function updateDrawDates(): void
    {
        $previousDrawDates = json_encode($this->lottery->drawDates);
        $newDrawDates = json_encode($this->newDrawDates);

        $this->lottery->set(['draw_dates' => $newDrawDates]);
        $this->lottery->save();
        $this->lottery::flush_cache();

        Cache::delete('model_lottery');

        $logTitle = "Lotto Information: Unscheduled Draw Lottery Update";
        $logMessage = "Lottery: {$this->lottery->slug} draw dates changed from $previousDrawDates to $newDrawDates \n";

        $this->addLog($logTitle, $logMessage, __LINE__);
    }

    private function updateProvider(): void
    {
        /** @var WhitelabelLottery $whitelabelLottery */
        $whitelabelLottery = WhitelabelLottery::find('first', [
            'where' => [
                'lottery_id' => $this->lottery->id
            ]
        ]);
        $lotteryProvider = Model_Lottery_Provider::find_by_pk($whitelabelLottery->lottery_provider_id);

        $closingTimes = json_decode($lotteryProvider['closing_times'], true);
        $previousClosingTimes = json_decode($lotteryProvider['closing_times'], true);

        if (empty($this->newNextDraw)) {
            return;
        }

        $nextDrawInProviderTimezone = $this->newNextDraw->clone()->setTimezone($lotteryProvider['timezone']);
        $nextDrawWeekDay = $nextDrawInProviderTimezone->isoWeekday();
        $secondsOffsetFromLastDraw = $this->calculateSecondsOffsetFromLastDraw($lotteryProvider);
        $newClosingTime = $nextDrawInProviderTimezone->clone()->subSeconds($secondsOffsetFromLastDraw)->toTimeString();

        if (isset($closingTimes[$nextDrawWeekDay])) {
            $closingDateTime = $nextDrawInProviderTimezone->clone()->setTimeFromTimeString($closingTimes[$nextDrawWeekDay]);
        } else {
            $closingDateTime = $nextDrawInProviderTimezone->clone()->setTimeFromTimeString($lotteryProvider['closing_time']);
        }

		$isClosingTimeCorrect = $this->isClosingDateCorrect($closingDateTime);
        if ($isClosingTimeCorrect) {
            $logTitle = "[INFO] Lottery - unscheduled draw";
            $logMessage = sprintf(
                "Closing times for lottery %s has not been changed because current closing time %s [provider timezone] is correct for unscheduled draw %s. [lottery timezone]",
                $this->lottery->slug,
                $closingDateTime->format(Helpers_Time::DRAWDATE_FORMAT),
                $this->newNextDraw->format(Helpers_Time::DATETIME_FORMAT)
            );
            Helpers_Mail::send_emergency_email($logTitle, $logMessage);
            return;
        }

        if ($this->isManyDrawsPerDay($this->newNextDraw->shortEnglishDayOfWeek)) {
            $logTitle = "[WARNING] After unscheduled draw update Closing Time has been changed!";
            $logMessage = sprintf("%s lottery has may draws per day. Closing times set at %s when next_draw changed to %s. Please make sure that closing times are correct!",
                $this->lottery->slug,
                $newClosingTime,
                $this->newNextDraw->toDateTimeString()
            );
            $this->addLog($logTitle, $logMessage, __LINE__, true);
        }

        $closingTimes[$nextDrawWeekDay] = $newClosingTime;
        ksort($closingTimes);

        // Save change closingTimes and delete once
        $this->saveNewClosingTimes($lotteryProvider, $previousClosingTimes, $closingTimes);
    }

    private function updateTickets(): void
    {
        if ($this->lottery->isMultidrawEnabled) {
            $logTitle = "[WARNING] Lotto Information: Unscheduled Draw Lottery Update";
            $logMessage = "Lottery {$this->lottery->slug} has multidraw enabled. We don't know if this unscheduled draw is temporary or not.
            Please make sure that future tickets for multipledraw have correct draw dates.";

            $this->addLog($logTitle, $logMessage, __LINE__, true);
        }

        $newNextDrawFormatted = $this->newNextDraw->format(\Helpers_Time::DATETIME_FORMAT);
        $previousNextDrawFormatted = $this->previousNextDraw
            ->setTimezone($this->lottery->timezone)
            ->format(\Helpers_Time::DATETIME_FORMAT);

        // Find tickets with old draw date
        $tickets = Model_Whitelabel_User_Ticket::find([
                'where' => [
                    'lottery_id' => $this->lottery->id,
                    'paid' => Helpers_General::TICKET_PAID,
                    'status' => Helpers_General::TICKET_STATUS_PENDING,
                    'draw_date' => $previousNextDrawFormatted
                ]
            ]) ?? [];


        if (empty($tickets)) {
            return;
        }

        $ticketsIds = [];
        foreach ($tickets as $ticket) {
            $ticketsIds[] = $ticket->id;
        }

        $this->db
            ->update(WhitelabelUserTicket::get_table_name())
            ->set([
                'valid_to_draw' => $newNextDrawFormatted,
                'draw_date' => $newNextDrawFormatted
            ])
            ->where('id', 'IN', $ticketsIds)
            ->execute();

        $ticketIdsString = implode(', ', $ticketsIds);

        $logTitle = "Lotto Information: Unscheduled Draw Lottery Update";
        $logMessage = "Ticket with ids: $ticketIdsString 
                changed draw_date from $previousNextDrawFormatted
                to $newNextDrawFormatted \n";

        $this->addLog($logTitle, $logMessage, __LINE__);
    }

    private function sendEmailReport(): void
    {
        foreach ($this->logs as $log) {
            Helpers_Mail::send_emergency_email(
                $log['title'],
                $log['message']
            );
        }
    }

    private function isClosingDateCorrect(Carbon $closingDate): bool
    {
        $drawDate = $this->newNextDraw;

        // Real closing time - 1 hour before provider's closing time
        $exactClosingDate = $closingDate->clone()->subHour();

        // Do not close later than 1 hour before draw date
        if ($exactClosingDate > $drawDate->clone()->subHour()) {
            return false;
        }

        return true;
    }

    private function calculateSecondsOffsetFromLastDraw(Model_Lottery_Provider $provider): float
    {
        if (empty($this->lottery->lastDateLocal)) {
            // Return one hour by default
            return 60 * 60;
        }

        $lastDrawDate = Carbon::parse($this->lottery->lastDateLocal, $this->lottery->timezone);
        $lastDrawDateInProviderTimezone = $lastDrawDate->clone()->setTimezone($provider['timezone']);
        $closingTimes = json_decode($provider['closing_times'], true);
        $newNextDrawInProviderTimezone = $this->newNextDraw->clone()->setTimezone($provider['timezone']);
        $closingTime = $newNextDrawInProviderTimezone->clone()->setTimeFromTimeString($provider['closing_time']);
        $lastDrawWeekDay = $lastDrawDateInProviderTimezone->isoWeekday();

        if (isset($closingTimes[$lastDrawWeekDay])) {
            $lastDrawClosingTime = $lastDrawDateInProviderTimezone
                ->clone()
                ->setTimeFromTimeString($closingTimes[$lastDrawWeekDay]);
        } else {
            $lastDrawClosingTime = $lastDrawDateInProviderTimezone
                ->clone()
                ->setTimeFromTimeString($provider['closing_time']);
        }

        $diffFromLastDraw = $lastDrawDateInProviderTimezone->diffInRealSeconds($lastDrawClosingTime, true);
        $diffFromClosingTime = $newNextDrawInProviderTimezone->diffInRealSeconds($closingTime, true);

        return max([$diffFromLastDraw, $diffFromClosingTime, 60 * 60]);
    }

    private function isManyDrawsPerDay(string $weekday): bool
    {
        $drawDates = $this->lottery->drawDates;
        $drawsCount = 0;

        foreach ($drawDates as $drawDate) {
            $drawDay = substr($drawDate, 0, 3);
            if ($drawDay === $weekday) {
                ++$drawsCount;
            }
        }

        return $drawsCount > 1;
    }

    private function deleteMissedDrawDate(array $drawDates): bool
    {
        $previousNextDrawIsAfterLastDraw = true;

        if ($this->lottery->lastDateLocal) {
            $lastDraw = Carbon::parse($this->lottery->lastDateLocal, $this->lottery->timezone);
            $previousNextDrawIsAfterLastDraw = $this->previousNextDraw > $lastDraw;
        }

        $plannedNextDrawIsOmitted =  $previousNextDrawIsAfterLastDraw && $this->previousNextDraw < $this->newNextDraw;

        if ($plannedNextDrawIsOmitted) {
            $omittedDrawFormatted = $this->previousNextDraw->format(Helpers_Time::DRAWDATE_FORMAT);

            if (($index = array_search($omittedDrawFormatted, $drawDates)) !== false) {
                $this->deletedDraw = $this->previousNextDraw;
                unset($drawDates[$index]);
                $this->newDrawDates = array_values($drawDates);

                return true;
            }
        }

        return false;
    }

    private function saveNewClosingTimes(
        Model_Lottery_Provider $lotteryProvider,
        array $previousClosingTimes,
        array $newClosingTimes
    ): void
    {
        $previousClosingTimesString = json_encode($previousClosingTimes);
        $newClosingTimesJson = json_encode($newClosingTimes);

        $lotteryProvider->set(['closing_times' => $newClosingTimesJson]);
        $lotteryProvider->save();

        Cache::delete('model_lottery_provider');

        $logTitle = "[WARNING]: After unscheduled draw update Closing Times has been changed!";
        $logMessage = sprintf("%s Closing Times changed from %s to %s [lottery provider timezone]
         when next_draw changed to %s [lottery timezone]. Please make sure that closing times are correct!",
            $this->lottery->slug,
            $previousClosingTimesString,
            $newClosingTimesJson,
            $this->newNextDraw->toDateTimeString()
        );

        $this->addLog($logTitle, $logMessage, __LINE__, true);
    }

    private function updateMultiDraw(): void
    {
        $oldDatetime = $this->previousNextDraw->setTimezone($this->lottery['timezone'])
            ->format(Helpers_Time::DATETIME_FORMAT);

        if (!$this->previousNextDraw->isFuture()) {
            return;
        }

        $allMultiDrawsPerLottery = $this->db->selectArray(['id', 'first_draw', 'current_draw', 'valid_to_draw'])
            ->from(Model_Multidraw::getTableName())
            ->where('valid_to_draw', '>', $this->db->expr('NOW()'))
            ->and_where('is_finished', '=', 0)
            ->and_where('is_cancelled', '=', 0)
            ->and_where('lottery_id', '=', $this->lottery->id)
            ->and_where_open()
            ->where('first_draw', '=', $oldDatetime)
            ->or_where('current_draw', '=', $oldDatetime)
            ->or_where('valid_to_draw', '=', $oldDatetime)
            ->and_where_close()
            ->execute()
            ->as_array();

        if (empty($allMultiDrawsPerLottery)) {
            return;
        }

        $changesLog = [];

        foreach ($allMultiDrawsPerLottery as $multiDraw) {
            $previousFields = [
                'first_draw' => $multiDraw['first_draw'],
                'current_draw' => $multiDraw['current_draw'],
                'valid_to_draw' => $multiDraw['valid_to_draw']
            ];

            $multiDrawChangedFields = $this->calculateNewMultiDrawFields($multiDraw);

            $this->db->update(Model_Multidraw::getTableName())
                ->set($multiDrawChangedFields)
                ->where('id', '=', $multiDraw['id'])
                ->execute();

            $changesLog[] = [
                'multi_draw_id' => $multiDraw['id'],
                'previous_fields' => $previousFields,
                'new_fields' => $multiDrawChangedFields
            ];
        }

        $logTitle = "[WARNING]: After unscheduled draw update multi_draw rows changed!";
        $logMessage = sprintf("%s multi_draw dates changes [lottery timezone]: \n %s
         when next_draw changed to %s [lottery timezone]. Please make sure that multi draw dates are correct!",
            $this->lottery->slug,
            json_encode($changesLog),
            $this->newNextDraw->toDateTimeString()
        );

        $this->addLog($logTitle, $logMessage, __LINE__, true);
    }


    private function calculateNewMultiDrawFields(array $multiDraw): array
    {
        $timezone = $this->lottery->timezone;

        $oldDrawDateFormatted = $this->previousNextDraw->format(Helpers_Time::DATETIME_FORMAT);
        $newDrawDateFormatted = $this->newNextDraw->format(Helpers_Time::DATETIME_FORMAT);

        $previousCurrentDraw = Carbon::parse($multiDraw['current_draw'], $timezone);
        $previousValidToDraw = Carbon::parse($multiDraw['valid_to_draw'], $timezone);

        $leftDraws = $this->calculateDrawsCountInTimeRange($previousCurrentDraw, $previousValidToDraw);

        if ($multiDraw['first_draw'] === $oldDrawDateFormatted) {
            $multiDraw['first_draw'] = $newDrawDateFormatted;
        }

        if ($multiDraw['current_draw'] === $oldDrawDateFormatted) {
            $multiDraw['current_draw'] = $newDrawDateFormatted;
        }

        $newCurrentDraw = Carbon::parse($multiDraw['current_draw'], $timezone);
        $newValidToDraw = $this->calculateMultiDrawValidToDrawDate($newCurrentDraw, $leftDraws);

        $multiDraw['valid_to_draw'] = $newValidToDraw->format(Helpers_Time::DATETIME_FORMAT);

        return [
            'first_draw' => $multiDraw['first_draw'],
            'current_draw' => $multiDraw['current_draw'],
            'valid_to_draw' => $multiDraw['valid_to_draw']
        ];
    }

    private function calculateMultiDrawValidToDrawDate(Carbon $currentDraw, int $leftDraws): Carbon
    {
        /** @var Model_Lottery $lottery */
        $lottery = Model_Lottery::find_by_pk($this->lottery->id);

        $lottery->set([
            'draw_dates' => $this->newDrawDates
        ]);

        --$leftDraws; // because currentDraw is first draw
        $i = 1;

        $newValidToDraw = $currentDraw;
        while ($leftDraws > 0) {
            $newValidToDraw = Lotto_Helper::get_lottery_next_draw($lottery, false, $currentDraw, $i);
            ++$i;
            --$leftDraws;
        }

        return $newValidToDraw;
    }

    private function calculateDrawsCountInTimeRange(Carbon $startDate, Carbon $endDate): int
    {
        /** @var Model_Lottery $lottery */
        $lottery = Model_Lottery::find_by_pk($this->lottery->id);

        $drawsCount = 1; // because startDate is first draw
        $nextDrawDate = null;
        $i = 1;

        do {
            /** @var Carbon|null $nextDrawDate */
            $nextDrawDate = Lotto_Helper::get_lottery_next_draw($lottery, false, $startDate, $i);

            if (empty($nextDrawDate)) {
                break;
            }

            ++$i;
            ++$drawsCount;
        } while ($nextDrawDate <= $endDate);

        return $drawsCount;
    }

    private function updateUserDrawNotifications(): void
    {
        $previousNextDrawDate = $this->previousNextDraw
            ->setTimezone($this->lottery->timezone)
            ->format(Helpers_Time::DATE_FORMAT);

        $newNextDrawDate = $this->newNextDraw
            ->setTimezone($this->lottery->timezone)
            ->format(Helpers_Time::DATETIME_FORMAT);

        $this->db->update('user_draw_notification')
            ->set([
                'lottery_draw_date' => $newNextDrawDate
            ])
            ->where('lottery_draw_date', '<>', null)
            ->and_where('lottery_draw_date', 'LIKE', "$previousNextDrawDate%")
            ->and_where('lottery_id', $this->lottery->id)
            ->execute();
    }
}

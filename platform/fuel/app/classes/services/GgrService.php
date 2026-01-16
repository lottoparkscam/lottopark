<?php

namespace Services;

use Carbon\Carbon;
use Exceptions\Ggr\GgrIsNegativeException;
use Exceptions\Ggr\SalesTicketSumIsNegativeException;
use Exceptions\Ggr\WhitelabelMarginIsNegativeException;
use Exceptions\Ggr\WinTicketPrizeSumIsNegativeException;
use Helpers\FlashMessageHelper;
use Helpers\NumberHelper;
use Helpers_Lottery;
use Helpers_Time;
use Model_Whitelabel_User_Ticket;
use Repositories\WhitelabelRepository;
use Services\Logs\FileLoggerService;

/** Ggr -> Gross Gaming Revenue */
class GgrService
{
    private FileLoggerService $fileLoggerService;
    private WhitelabelRepository $whitelabelRepository;

    public function __construct(FileLoggerService $fileLoggerService, WhitelabelRepository $whitelabelRepository)
    {
        $this->fileLoggerService = $fileLoggerService;
        $this->whitelabelRepository = $whitelabelRepository;
    }

    public function getCalculatedGgrIncomeForMonthPerWhitelabel(int $whitelabelId, string $month, string $year): array
    {
        $whitelabel = $this->whitelabelRepository->findOneById($whitelabelId);
        $reportDate = new Carbon(Carbon::now()->format($year . '-' . $month . '-d'));
        $firstDayMonthReport = $reportDate->firstOfMonth()->format(Helpers_Time::DATETIME_FORMAT);
        $lastDayMonthReport = $reportDate->endOfMonth()->format(Helpers_Time::DATETIME_FORMAT);
        $sqlDateRangeCondition = 'AND wut.date >= :date_start AND wut.date <= :date_end';
        $dateRange = [
            [':date_start', $firstDayMonthReport],
            [':date_end', $lastDayMonthReport],
        ];
        $sumsOfPaidLotteriesReport = Model_Whitelabel_User_Ticket::getOptimizedSumsPaidForFullReports(
            $sqlDateRangeCondition,
            $dateRange,
            [],
            $whitelabel->type,
            $whitelabel->id,
        );

        /** Income it is what whitelabel earned. */
        $income = 0.00;
        /**
         * Royalties it is what we earned.
         * Royalties also called manager_margin.
         */
        $royalties = 0.00;
        foreach ($sumsOfPaidLotteriesReport as $singleLotteryReport) {
            if (Helpers_Lottery::isGgrEnabled($singleLotteryReport['type'])) {
                $ggr = $this->calculateGgr($singleLotteryReport['lottery_amount_manager_sum'], $singleLotteryReport['lottery_win_manager_sum']);
                $income += $ggr;
                $royalties += $this->calculateWhitelottoGgrRoyalties($ggr, $whitelabel->margin);
            }
        }
        return [
            'income' => $income,
            'royalties' => $royalties,
        ];
    }

    /** @link https://gginternational.slite.com/app/docs/S6bwXehGeQj-3D */
    public function calculateGgr(float $salesTicketSum, float $winTicketsPrizeSum): float
    {
        try {
            $this->verifySalesTicketSum($salesTicketSum);
            $this->verifyWinTicketsPrizeSum($winTicketsPrizeSum);
        } catch (SalesTicketSumIsNegativeException|WinTicketPrizeSumIsNegativeException $exception) {
            $this->fileLoggerService->warning($exception->getMessage());
            FlashMessageHelper::set(FlashMessageHelper::TYPE_WARNING, $exception->getMessage());
            return 0.0;
        }
        $ggr = bcsub($salesTicketSum, $winTicketsPrizeSum, 3);
        return max(NumberHelper::roundUpWhenNumberAfterPrecisionIsBiggerThenZero($ggr), 0.0);
    }

    /**
     * @throws SalesTicketSumIsNegativeException
     */
    private function verifySalesTicketSum(float $salesTicketSum): void
    {
        if (NumberHelper::isFloatNumberNegative($salesTicketSum)) {
            throw new SalesTicketSumIsNegativeException();
        }
    }

    /**
     * @throws WinTicketPrizeSumIsNegativeException
     */
    private function verifyWinTicketsPrizeSum(float $winTicketsPrizeSum): void
    {
        if (NumberHelper::isFloatNumberNegative($winTicketsPrizeSum)) {
            throw new WinTicketPrizeSumIsNegativeException();
        }
    }

    /**
     * @throws GgrIsNegativeException
     */
    private function verifyGgr(float $ggr): void
    {
        if (NumberHelper::isFloatNumberNegative($ggr)) {
            throw new GgrIsNegativeException();
        }
    }

    /**
     * @throws WhitelabelMarginIsNegativeException
     */
    private function verifyWhitelabelMargin(float $whitelabelMargin): void
    {
        if (NumberHelper::isFloatNumberNegative($whitelabelMargin)) {
            throw new WhitelabelMarginIsNegativeException();
        }
    }

    private function calculateWhitelottoIncome(float $ggr, float $whitelabelMargin): float
    {
        return $ggr * $whitelabelMargin / 100;
    }

    /** @link https://gginternational.slite.com/app/docs/S6bwXehGeQj-3D */
    public function calculateWhitelottoGgrRoyalties(float $ggr, float $whitelabelMargin): float
    {
        try {
            $this->verifyGgr($ggr);
            $this->verifyWhitelabelMargin($whitelabelMargin);
        } catch (GgrIsNegativeException|WhitelabelMarginIsNegativeException $exception) {
            $this->fileLoggerService->warning($exception->getMessage());
            FlashMessageHelper::set(FlashMessageHelper::TYPE_WARNING, $exception->getMessage());
            return 0.0;
        }
        return NumberHelper::roundUpWhenNumberAfterPrecisionIsBiggerThenZero(self::calculateWhitelottoIncome($ggr, $whitelabelMargin));
    }
}

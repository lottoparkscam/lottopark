<?php

use Repositories\LotteryLogRepository;
use Carbon\Carbon;

class Lotto_Lotteries_OzLotto extends Lotto_Lotteries_Lottery
{
    public const NORMAL_NUMBERS_COUNT = 7;
    public const EXTRA_NUMBERS_COUNT = 3; // non-selectable by user, these are extra numbers drawn by lottery provider
    public const PRIZE_TIERS_COUNT = 7;
    protected string $lottery_slug = 'oz-lotto';

    /**
     *
     * @throws Exception
     */
    public function get_results(): void
    {
        $sourceId = $this->lottery['source_id'];
        if ($sourceId == 18) { // thelott.com SITE OFFICIAL
            $this->get_results_primary();
        }
    }

    public function is_type_data_winning(
        Model_Lottery_Type $type,
        Model_Lottery_Type_Data $wintype,
        int $match_n,
        int $match_b,
        int $match_others = 0
    ): bool
    {
        return parent::is_type_data_winning($type, $wintype, $match_n, $match_b, $match_others) ||
            ($type['bextra'] == 2 &&
                $wintype->match_n == $match_n && ($wintype->match_b == 0 ||
                    ($wintype->match_b != 0 && $wintype->match_b == $match_b))) ||
            ($type['bextra'] == 3 &&
                $wintype->match_n == $match_n && ($wintype->match_b == 0 ||
                    ($wintype->match_b != 0 && $wintype->match_b == $match_b)));
    }

    /**
     * Note: we have the following tiers in Oz Lotto:
     * - 7
     * - 6+1
     * - 6
     * - 5+1
     * - 5
     * - 4
     * - 3+1
     *
     * This means that this function always needs to return "1" (or zero where none matched), even if more than one number was matched.
     * It is why we do match_b = 1 and not match_b++ like in lottery.php.
     * @param int   $match_b
     * @param array $type
     * @param array $line_bnumbers
     * @param array $line_numbers
     *
     * @return int
     */
    public function match_b($match_b, $type, $line_bnumbers, $line_numbers): int
    {
        if ($this->bonus_numbers !== null && count($this->bonus_numbers)) {
            foreach ($this->bonus_numbers as $number) {
                if ($type['bextra'] == 0 && in_array($number, $line_bnumbers)) {
                    $match_b++;
                }
                if ($type['bextra'] > 0 && in_array($number, $line_numbers)) {
                    $match_b = 1;
                }
            }
        }

        return $match_b;
    }

    /**
     *
     * @throws Exception
     */
    public function get_results_primary()
    {
        /** @var LotteryLogRepository $lotteryLogRepository */
        $lotteryLogRepository = Container::get(LotteryLogRepository::class);

        $lottery = $this->lottery;
        $numbers = null;
        $prizes = null;
        $drawDatetime = null;
        $date_utc = null;
        $bonus_numbers = null;
        $ch = curl_init();
        $json = json_encode(["CompanyId" => 'NTLotteries',
            "MaxDrawCountPerProduct" => 1,
            "OptionalProductFilter" => [0 => "OzLotto"]]);
        curl_setopt($ch, CURLOPT_URL, "https://api.thelott.com/sales/vmax/web/data/lotto/opendraws");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = curl_exec($ch);
        if ($data == true) {
            $data = json_decode($data);
            $draw = $data->Draws[0];
            $cleared_div_amount = str_replace(["float(", ")"], "", $draw->Div1Amount);
            $cleared_div_amount = (float)$cleared_div_amount;
            $jackpot = round($cleared_div_amount / 1000000, 2);
        } else {
            $jackpot = false;
            $lotteryLogRepository->addWarningLog(
                $lottery['id'],
                'Current jackpot not found. Received data is null.'
            );
        }
        curl_close($ch);

        $ch = curl_init();
        $json = json_encode(["CompanyId" => 'NTLotteries',
            "MaxDrawCountPerProduct" => 1,
            "OptionalProductFilter" => [0 => "OzLotto"]]);
        curl_setopt($ch, CURLOPT_URL, "https://api.thelott.com/sales/vmax/web/data/lotto/latestresults");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = curl_exec($ch);
        curl_close($ch);
        if ($data == true) {
            $data = json_decode($data);
            $draw = $data->DrawResults[0];

            if (!isset($draw->DrawDate)) {
                echo 'Lottery not updated - empty draw date';
                return;
            }

            $isBeforeDraw = Helpers_Time::isDateBeforeDate(Carbon::now(), Carbon::parse($draw->DrawDate, $this->lottery['timezone']));
            if ($isBeforeDraw) {
                echo "Lottery not updated - trying to get results before draw. \n";
                return;
            }
            
            $drawDatetime = $this->calculateNextDueDrawDatetime($draw->DrawDate, $jackpot);
            if ($drawDatetime === null) {
                echo "Lottery not updated - draw date is not due." . PHP_EOL;
                return; 
            }

            $date_utc = $drawDatetime->clone()->setTimezone(new DateTimeZone("UTC"));
            $numbers = $draw->PrimaryNumbers;
            if (empty($numbers) || count($numbers) != self::NORMAL_NUMBERS_COUNT) {
                throw new Exception('Bad number length.');
            }
            $bonus_numbers = $draw->SecondaryNumbers;
            if (empty($bonus_numbers) || count($bonus_numbers) != self::EXTRA_NUMBERS_COUNT) {
                throw new Exception('Bad bonus number value length.');
            }
            foreach ($draw->Dividends as $division) {
                $cleared_dividend = str_replace(["float(", ")"], "", $division->BlocDividend);
                $winners_prz = round($cleared_dividend, 2);
                $prizes[] = [$division->BlocNumberOfWinners, $winners_prz];
            }
            if (empty($prizes) || count($prizes) != self::PRIZE_TIERS_COUNT) {
                throw new Exception('Bad prizes length. Probably Dividends property does not exist in Ltech response.');
            }
        }
        $this->set_lottery_with_data($lottery, $jackpot, $drawDatetime, $date_utc, $numbers, $bonus_numbers, $prizes, $this->overwrite_jackpot);
    }

    public function process_lottery(): void
    {
        if ($this->isDrawDateDue) {
            $this->lottery_to_update->last_date_local = $this->lottery_to_update->next_date_local;
        }
        parent::process_lottery();
    }
}

<?php

use Repositories\LotteryLogRepository;

class Lotto_Lotteries_PowerballAU extends Lotto_Lotteries_Lottery
{
    protected string $lottery_slug = 'powerball-au';

    /**
     *
     * @throws Exception
     */
    public function get_results(): void
    {
        $lottery = $this->lottery;
        $sourceId = $lottery['source_id'];
        if ($sourceId == 19) { // thelott.com SITE OFFICIAL
            $this->get_results_primary();
        }
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
            "OptionalProductFilter" => [0 => "powerball"]]);
        curl_setopt($ch, CURLOPT_URL, "https://api.thelott.com/sales/vmax/web/data/lotto/opendraws");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = curl_exec($ch);
        if ($data == true) {
            $data = json_decode($data);
            if (empty($data)) {
                throw new Exception('Empty reply from server');
            }
            $draw = $data->Draws[0];
            $cleared_div_amount = str_replace(["float(", ")"], "", $draw->Div1Amount);
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
            "OptionalProductFilter" => [0 => "powerball"]]);
        curl_setopt($ch, CURLOPT_URL, "https://api.thelott.com/sales/vmax/web/data/lotto/latestresults");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = curl_exec($ch);
        if ($data == true) {
            $data = json_decode($data);
            $draw = $data->DrawResults[0];

            $drawDatetime = $this->calculateNextDueDrawDatetime($draw->DrawDate, $jackpot);
            if ($drawDatetime === null) {
                echo "Lottery not updated - draw date is not due." . PHP_EOL;
                return;
            }

            $date_utc = $drawDatetime->clone()->setTimezone(new DateTimeZone("UTC"));
            $numbers = $draw->PrimaryNumbers;
            if (empty($numbers) || count($numbers) != 7) {
                throw new Exception('Bad number length.');
            }
            $bonus_numbers = $draw->SecondaryNumbers;
            if (empty($bonus_numbers) || count($bonus_numbers) != 1) {
                throw new Exception('Bad bonus number value length.');
            }
            foreach ($draw->Dividends as $division) {
                $cleared_dividend = str_replace(["float(", ")"], "", $division->BlocDividend);
                $winners_prz = round($cleared_dividend, 2);
                $prizes[] = [$division->BlocNumberOfWinners, $winners_prz];
            }
            if (empty($prizes) || count($prizes) != 9) {
                throw new Exception('Bad prizes length. Probably Dividends property does not exist in Ltech response.');
            }
        }
        curl_close($ch);
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

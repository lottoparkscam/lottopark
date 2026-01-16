<?php

use Services\Logs\FileLoggerService;

final class Forms_Wordpress_Bonuses_Ticket_Line extends Forms_Main
{
    private FileLoggerService $fileLoggerService;

    /**
     *
     * @var null|array
     */
    private $lottery = null;

    /**
     *
     * @var int
     */
    private $lottery_minimum_lines = 1;

    /**
     *
     * @var int
     */
    private $numbers_count = 1;

    /**
     *
     * @var int
     */
    private $bonus_numbers_count = 0;

    /**
     *
     * @var int
     */
    private $numbers_max_range = 1;

    /**
     *
     * @var int
     */
    private $bonus_numbers_max_range = 1;

    /**
     *
     * @var int
     */
    private $bonus_extra = 0;

    /**
     *
     * @var null|int
     */
    private $whitelabel_user_ticket_id = null;

    /**
     * @var null|Model_Whitelabel_User_Ticket
     */
    private $whitelabelUserTicket = null;

    /**
     *
     * @var null|string
     */
    private $numbers = null;

    /**
     *
     * @var null|string
     */
    private $bonus_numbers = null;

    /**
     *
     * @param array $lottery
     * @param array $lottery_type
     * @param Model_Whitelabel_User_Ticket|null $whitelabelUserTicket
     */
    public function __construct(
        array $lottery,
        array $lottery_type,
        Model_Whitelabel_User_Ticket $whitelabelUserTicket = null
    )
    {
        if (empty($lottery_type)) {
            exit(_("Bad request! Please contact us!"));
        }

        $this->fileLoggerService = Container::get(FileLoggerService::class);

        $this->lottery = $lottery;

        if ($lottery['type'] === Helpers_Lottery::TYPE_KENO) {
            $this->numbers_count = Helpers_Lottery::KENO_DEFAULT_NUMBERS_PER_LINE;
        } else {
            $this->numbers_count = (int)$lottery_type["ncount"];
        }
        $this->numbers_max_range = (int)$lottery_type["nrange"];
        $this->bonus_numbers_count = (int)$lottery_type["bcount"];
        $this->bonus_numbers_max_range = (int)$lottery_type["brange"];
        $this->bonus_extra = (int)$lottery_type['bextra'];

        $this->whitelabelUserTicket = $whitelabelUserTicket;
        $this->whitelabel_user_ticket_id = $this->whitelabelUserTicket->id;
    }

    /**
     *
     * @return array|null
     */
    public function get_lottery(): ?array
    {
        return $this->lottery;
    }

    /**
     *
     * @return int
     */
    public function get_lottery_minimum_lines(): int
    {
        $this->lottery_minimum_lines = 1;

        $lottery = $this->get_lottery();
        if (!empty($lottery)) {
            $this->lottery_minimum_lines =
                (int)$lottery['min_bets'] >= $lottery['min_lines'] ? (int)$lottery['min_bets'] : $lottery['min_lines']; // Before was min_lines
        }

        return $this->lottery_minimum_lines;
    }

    /**
     *
     * @return int
     */
    public function get_whitelabel_user_ticket_id(): ?int
    {
        return $this->whitelabel_user_ticket_id;
    }

    /**
     *
     * @return string|null
     */
    public function get_numbers(): ?string
    {
        return $this->numbers;
    }

    /**
     *
     * @return string|null
     */
    public function get_bonus_numbers(): ?string
    {
        return $this->bonus_numbers;
    }

    /**
     *
     * @return string
     */
    public function generate_numbers(): string
    {
        $random_numbers = Lotto_Helper::get_random_values(
            $this->numbers_count,
            $this->numbers_max_range
        );
        sort($random_numbers);
        $this->numbers = implode(",", $random_numbers);

        return $this->numbers;
    }

    /**
     *
     * @return string
     */
    public function generate_bonus_numbers(): string
    {
        $this->bonus_numbers = "";

        if ($this->bonus_extra === 0 &&
            $this->bonus_numbers_count > 0
        ) {
            $random_bonus_numbers = Lotto_Helper::get_random_values(
                $this->bonus_numbers_count,
                $this->bonus_numbers_max_range
            );
            sort($random_bonus_numbers);
            $this->bonus_numbers = implode(",", $random_bonus_numbers);
        }

        return $this->bonus_numbers;
    }

    /**
     *
     * @return int
     * @throws \Exception
     */
    public function check_line(): int
    {
        try {
            $numbers = explode(",", $this->numbers);
            $bonus_numbers = explode(",", $this->bonus_numbers);

            $numbers_count = array_unique(array_values($numbers));
            $bonus_numbers_count = array_unique(array_values($bonus_numbers));

            if (!(
                count($numbers_count) === $this->numbers_count &&
                ($this->bonus_extra === 0 &&
                    count($bonus_numbers_count) === $this->bonus_numbers_count) ||
                ($this->bonus_extra === 1 &&
                    count($bonus_numbers_count) === 0) ||
                ($this->bonus_extra === 2 &&
                    count($bonus_numbers_count) === 0)
            )
            ) {
                $msg_text = "Incorrect amount of unique numbers. [" .
                    count($numbers_count) .
                    " " .
                    count($bonus_numbers_count) .
                    "]";
                throw new \Exception($msg_text);
            }

            if (count($numbers) !== $this->numbers_count ||
                count($bonus_numbers) !== $this->bonus_numbers_count
            ) {
                $msg_text = "Incorrect amount of numbers. [" .
                    count($numbers) .
                    " " .
                    count($bonus_numbers) .
                    "]";
                throw new \Exception($msg_text);
            }

            foreach ($numbers as $number) {
                if (intval($number) < 1 ||
                    intval($number) > $this->numbers_max_range
                ) {
                    throw new \Exception("Number out of range. [" . $number . "]");
                }
            }

            foreach ($bonus_numbers as $bnumber) {
                if (intval($bnumber) < 1 ||
                    intval($bnumber) > $this->bonus_numbers_max_range
                ) {
                    throw new \Exception("Bonus number out of range. [" . $bnumber . "]");
                }
            }
        } catch (\Exception $e) {
            $this->fileLoggerService->error(
                $e->getMessage()
            );

            return self::RESULT_WITH_ERRORS;
        }

        return self::RESULT_OK;
    }

    /**
     *
     * @return array
     */
    public function get_prepared_ticket_line_set(): array
    {
        $ticket_id = $this->get_whitelabel_user_ticket_id();

        if (empty($ticket_id)) {
            exit(_("Bad request! Please contact us!"));
        }

        $numbers = $this->generate_numbers();

        $bonus_numbers = $this->generate_bonus_numbers();

        $set = [
            'whitelabel_user_ticket_id' => $ticket_id,
            'numbers' => $numbers,
            'bnumbers' => $bonus_numbers,
            "amount_local" => 0,
            'amount' => 0,
            'amount_usd' => 0,
            'amount_payment' => 0,
            'amount_manager' => 0,
            'status' => Helpers_General::TICKET_STATUS_PENDING,
            'payout' => Helpers_General::TICKET_PAYOUT_PENDING
        ];

        return $set;
    }

    /**
     *
     * @return int
     */
    public function process_form(): int
    {
        $linesCount = $this->whitelabelUserTicket?->line_count ?? 1;

        for ($index = 0; $index < $linesCount; $index++) {
            try {
                $set = $this->get_prepared_ticket_line_set();
                $new_bonus_ticket_line = Model_Whitelabel_User_Ticket_Line::forge();
                $new_bonus_ticket_line->set($set);
                $new_bonus_ticket_line->save();
            } catch (\Exception $e) {
                $this->fileLoggerService->error(
                    $e->getMessage()
                );

                return self::RESULT_WITH_ERRORS;
            }
        }

        return self::RESULT_OK;
    }
}

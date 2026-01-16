<?php

use Helpers\ArrayHelper;
use Helpers\CurrencyHelper;
use Services\Logs\FileLoggerService;

final class Forms_Wordpress_Bonuses_Ticket_Ticket extends Forms_Main
{
    private FileLoggerService $fileLoggerService;

    /**
     *
     * @var array
     */
    private $whitelabel = [];
    
    /**
     *
     * @var array
     */
    private $user = [];
    
    /**
     *
     * @var array
     */
    private $lottery = [];

    /**
     *
     * @var array
     */
    private $user_currency_tab = [];
    
    /**
     *
     * @var array
     */
    private $lottery_currency_tab = [];
    
    /**
     *
     * @var array
     */
    private $system_currency_tab = [];
    
    /**
     *
     * @var array
     */
    private $manager_currency_tab = [];
    
    /**
     *
     */
    private $ticket_draw_date = null;
    
    /**
     *
     * @var bool
     */
    private $should_insure = true;
    
    /**
     * This is only for that kind of bonus
     *
     * @var int
     */
    private $counted_lines = 1;
    
    /**
     *
     * @var null|Model_Whitelabel_User_Ticket
     */
    private $new_bonus_ticket = null;
    
    /**
     *
     * @param array $whitelabel
     * @param array $user
     * @param array $lottery
     */
    public function __construct(array $whitelabel, array $user, array $lottery, int $countedLines = 1)
    {
        $this->whitelabel = $whitelabel;
        $this->user = $user;
        $this->lottery = $lottery;
        $this->counted_lines = $countedLines;
        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }

    /**
     *
     * @return array
     */
    public function get_whitelabel(): array
    {
        return $this->whitelabel;
    }

    /**
     *
     * @return int
     */
    public function get_counted_lines(): int
    {
        return $this->counted_lines;
    }

    /**
     *
     * @return int
     */
    public function get_token(): int
    {
        $token = Lotto_Security::generate_ticket_token($this->whitelabel['id']);
        return $token;
    }

    /**
     *
     * @return array|null
     */
    public function get_lottery():? array
    {
        return $this->lottery;
    }

    /**
     *
     * @return int
     */
    public function get_lottery_minimum_lines(): int
    {
        $lottery_minimum_lines = 1;
        
        $lottery = $this->get_lottery();
        if (!empty($lottery)) {
            $lottery_minimum_lines =
                    (int)$lottery['min_bets'] >= $lottery['min_lines'] ? (int)$lottery['min_bets'] : $lottery['min_lines']; // Before was min_lines
        }
        
        return $lottery_minimum_lines;
    }
    
    /**
     *
     * @return array|null
     */
    public function get_user_currency_tab():? array
    {
        if (empty($this->user_currency_tab)) {
            $user_currency_tab = CurrencyHelper::getCurrentCurrency()->to_array();
            $this->user_currency_tab = $user_currency_tab;
        }
        
        return $this->user_currency_tab;
    }
    
    /**
     *
     * @return array|null
     */
    public function get_lottery_currency_tab():? array
    {
        if (empty($this->lottery_currency_tab)) {
            $this->lottery_currency_tab = Helpers_Currency::get_mtab_currency(
                false,
                (string) $this->lottery["currency"]
            );
        }
        
        return $this->lottery_currency_tab;
    }
    
    /**
     *
     * @return array|null
     */
    public function get_system_currency_tab():? array
    {
        if (empty($this->system_currency_tab)) {
            $this->system_currency_tab = Helpers_Currency::get_mtab_currency(
                false,
                "USD"
            );
        }
        
        return $this->system_currency_tab;
    }
    
    /**
     *
     * @return array|null
     */
    public function get_manager_currency_tab():? array
    {
        if (empty($this->manager_currency_tab)) {
            $this->manager_currency_tab = Helpers_Currency::get_mtab_currency(
                false,
                null,
                $this->whitelabel['manager_site_currency_id']
            );
        }
        
        return $this->manager_currency_tab;
    }
    
    /**
     *
     * @return int
     */
    public function get_lottery_model(): int
    {
        return $this->lottery['model'];
    }
    
    /**
     *
     * @return null|\DateTime
     */
    public function get_ticket_draw_date():? \DateTime
    {
        $this->ticket_draw_date = null;
        if (Lotto_Helper::is_lottery_closed($this->lottery, null, $this->whitelabel)) {
            // TODO: adjust next draw on lottery changes
            $this->ticket_draw_date = Lotto_Helper::get_lottery_next_draw(
                $this->lottery,
                true,
                null,
                2
            );
        } else {
            $this->ticket_draw_date = Lotto_Helper::get_lottery_next_draw($this->lottery);
        }

        return $this->ticket_draw_date;
    }
    
    /**
     *
     * @return array
     * @throws \Exception
     */
    public function get_lottery_type(): array
    {
        $this->get_ticket_draw_date();
        
        $type = Model_Lottery_Type::get_lottery_type_for_date(
            $this->lottery,
            $this->ticket_draw_date->format(Helpers_Time::DATETIME_FORMAT)
        );

        if ($type === null) {
            throw new \Exception('No lottery type.');
        }

        return $type;
    }
    
    /**
     *
     * @return bool
     */
    public function get_should_insure(): bool
    {
        $this->should_insure = Lotto_Helper::should_insure(
            $this->lottery,
            $this->lottery['tier'],
            $this->lottery['volume']
        );
        
        return $this->should_insure;
    }
    
    /**
     *
     * @return bool
     */
    public function get_is_insured(): bool
    {
        $is_insured = false;
        $model = $this->get_lottery_model();

        if ($model == Helpers_General::LOTTERY_MODEL_MIXED &&
            $this->should_insure
        ) {
            $is_insured = true;
        }
        
        return $is_insured;
    }
    
    /**
     *
     * @return int
     */
    public function get_tier(): int
    {
        $tier = 0;
        $model = $this->get_lottery_model();

        if ($model == Helpers_General::LOTTERY_MODEL_MIXED &&
            $this->should_insure
        ) {
            $tier = $this->lottery['tier'];
        }
        
        return $tier;
    }

    /**
     *
     * @return string
     */
    public function get_price_lottery(): string
    {
        $counted_lines = $this->get_counted_lines();
        $lottery_price = Lotto_Helper::get_user_price($this->lottery);
        
        return $lottery_price * $counted_lines;
    }
    
    /**
     *
     * @return string
     */
    public function get_price_usd(): string
    {
        $counted_lines = $this->get_counted_lines();
        $system_currency_tab = $this->get_system_currency_tab();
        $itm_price_usd = Lotto_Helper::get_user_converted_price(
            $this->lottery,
            $system_currency_tab['id']
        );
        
        return $itm_price_usd * $counted_lines;
    }
    
    /**
     *
     * @return string
     */
    public function get_price_user(): string
    {
        $counted_lines = $this->get_counted_lines();
        $user_currency_tab = $this->get_user_currency_tab();
        $itm_price_user_curr = Lotto_Helper::get_user_converted_price(
            $this->lottery,
            $user_currency_tab['id']
        );
        
        return $itm_price_user_curr * $counted_lines;
    }
    
    /**
     *
     * @return array
     */
    public function get_price_of_ticket(): array
    {
        $calculated_cost = Lotto_Helper::get_price(
            $this->lottery,
            $this->lottery['model'],
            $this->lottery['tier'],
            $this->lottery['volume']
        );
        
        return $calculated_cost;
    }
    
    /**
     *
     * @return string
     */
    public function get_cost_lottery(): string
    {
        $counted_lines = $this->get_counted_lines();
        
        $price_of_ticket = $this->get_price_of_ticket();
        $cost_lottery_full = $price_of_ticket[0] + $price_of_ticket[1];
        
        return $cost_lottery_full * $counted_lines;
    }
    
    /**
     *
     * @param string $cost_lottery
     * @return string
     */
    public function get_cost_lottery_formatted(string $cost_lottery): string
    {
        return round($cost_lottery, 2);
    }
    
    /**
     *
     * @param string $cost_lottery
     * @return string
     */
    public function get_cost_usd(string $cost_lottery): string
    {
        $lottery_currency_tab = $this->get_lottery_currency_tab();
        $system_currency_tab = $this->get_system_currency_tab();
        
        $cost_usd = "0.00";
        if ((string)$lottery_currency_tab['code'] === (string)$system_currency_tab['code']) {
            $cost_usd = $cost_lottery;
        } else {
            $cost_usd = Helpers_Currency::convert_to_USD(
                $cost_lottery,
                $lottery_currency_tab['code']
            );
        }
        
        return $cost_usd;
    }
    
    /**
     *
     * @param string $cost_usd
     * @return string
     */
    public function get_cost_usd_formatted(string $cost_usd): string
    {
        return round($cost_usd, 2);
    }
    
    /**
     *
     * @param string $cost_lottery
     * @param string $cost_usd
     * @return string
     */
    public function get_cost_manager(
        string $cost_lottery,
        string $cost_usd
    ): string {
        $lottery_currency_tab = $this->get_lottery_currency_tab();
        $system_currency_tab = $this->get_system_currency_tab();
        $manager_currency_tab = $this->get_manager_currency_tab();
        
        $cost_manager = "0.00";
        if ((string)$lottery_currency_tab['code'] === (string)$manager_currency_tab['code']) {
            $cost_manager = $cost_lottery;
        } elseif ((string)$system_currency_tab['code'] === (string)$manager_currency_tab['code']) {
            $cost_manager = $cost_usd;
        } else {
            $cost_manager = Helpers_Currency::get_recalculated_to_given_currency(
                $cost_usd,
                $system_currency_tab,
                $manager_currency_tab['code'],
                2
            );
        }
        
        return $cost_manager;
    }
    
    /**
     *
     * @param string $cost_manager
     * @return string
     */
    public function get_cost_manager_formatted(string $cost_manager): string
    {
        return round($cost_manager, 2);
    }
    
    /**
     *
     * @param string $cost_lottery
     * @param string $cost_usd
     * @param string $cost_manager
     * @return string
     */
    public function get_cost_user(
        string $cost_lottery,
        string $cost_usd,
        string $cost_manager
    ): string {
        $lottery_currency_tab = $this->get_lottery_currency_tab();
        $system_currency_tab = $this->get_system_currency_tab();
        $manager_currency_tab = $this->get_manager_currency_tab();
        $user_currency_tab = $this->get_user_currency_tab();
        
        $cost_user = "0.00";
        if ((string)$lottery_currency_tab['code'] === (string)$user_currency_tab['code']) {
            $cost_user = $cost_lottery;
        } elseif ((string)$system_currency_tab['code'] === (string)$user_currency_tab['code']) {
            $cost_user = $cost_usd;
        } elseif ((string)$manager_currency_tab['code'] === (string)$user_currency_tab['code']) {
            $cost_user = $cost_manager;
        } else {
            $cost_user = Helpers_Currency::get_recalculated_to_given_currency(
                $cost_lottery,
                $lottery_currency_tab,
                $user_currency_tab['code'],
                2
            );
        }
        
        return $cost_user;
    }
    
    /**
     *
     * @param string $cost_user
     * @return string
     */
    public function get_cost_user_formatted(string $cost_user): string
    {
        return round($cost_user, 2);
    }
    
    /**
     * Income in that case should be negative
     * @return string
     */
    public function get_income_value_formatted(): string
    {
        return round($this->lottery['income'], 2);
    }
    
    /**
     *
     * @param string $price_lottery
     * @param string $cost_lottery
     * @return string
     */
    public function get_income_lottery(
        string $price_lottery,
        string $cost_lottery
    ): string {
        return -1 * $cost_lottery;
    }
    
    /**
     *
     * @param string $income_lottery
     * @return string
     */
    public function get_income_lottery_formatted(string $income_lottery): string
    {
        return round($income_lottery, 2);
    }
    
    /**
     *
     * @param string $price_usd
     * @param string $cost_usd
     * @return string
     */
    public function get_income_usd(string $price_usd, string $cost_usd): string
    {
        return -1 * $cost_usd;
    }
    
    /**
     *
     * @param string $income_usd
     * @return string
     */
    public function get_income_usd_formatted(string $income_usd): string
    {
        return round($income_usd, 2);
    }
    
    /**
     *
     * @param string $price_user
     * @param string $cost_user
     * @return string
     */
    public function get_income_user(string $price_user, string $cost_user): string
    {
        return -1 * $cost_user;
    }
    
    /**
     *
     * @param string $income_user
     * @return string
     */
    public function get_income_user_formatted(string $income_user): string
    {
        return round($income_user, 2);
    }
    
    /**
     *
     * @param string $income_lottery
     * @param string $income_usd
     * @return string
     */
    public function get_income_manager(
        string $income_lottery,
        string $income_usd
    ): string {
        $lottery_currency_tab = $this->get_lottery_currency_tab();
        $system_currency_tab = $this->get_system_currency_tab();
        $manager_currency_tab = $this->get_manager_currency_tab();
        
        $income_manager = "0.00";
        if ((string)$lottery_currency_tab['code'] === (string)$manager_currency_tab['code']) {
            $income_manager = $income_lottery;
        } elseif ((string)$system_currency_tab['code'] === (string)$manager_currency_tab['code']) {
            $income_manager = $income_usd;
        } else {
            $income_manager = Helpers_Currency::get_recalculated_to_given_currency(
                $income_usd,
                $system_currency_tab,
                $manager_currency_tab['code'],
                2
            );
        }
        
        return $income_manager;
    }

    /**
     *
     * @param string $income_manager
     * @return string
     */
    public function get_income_manager_formatted(string $income_manager): string
    {
        return round($income_manager, 2);
    }
    
    /**
     *
     * @return string
     */
    public function get_margin_value(): string
    {
        return $this->whitelabel['margin'];
    }
    
    /**
     *
     * @return string
     */
    public function get_margin_value_percentage(): string
    {
        $margin_value = $this->get_margin_value();
        return round($margin_value / 100, 4);
    }
    
    /**
     *
     * @param string $cost_lottery
     * @param string $margin_value_percentage
     * @return string
     */
    public function get_margin_lottery_formatted(
        string $cost_lottery,
        string $margin_value_percentage
    ): string {
        return round($cost_lottery * $margin_value_percentage, 2);
    }
    
    /**
     *
     * @param string $cost_usd
     * @param string $margin_value_percentage
     * @return string
     */
    public function get_margin_usd_formatted(
        string $cost_usd,
        string $margin_value_percentage
    ): string {
        return round($cost_usd * $margin_value_percentage, 2);
    }
    
    /**
     *
     * @param string $cost_user
     * @param string $margin_value_percentage
     * @return string
     */
    public function get_margin_user_formatted(
        string $cost_user,
        string $margin_value_percentage
    ): string {
        return round($cost_user * $margin_value_percentage, 2);
    }
    
    /**
     *
     * @param string $margin_usd_formatted
     * @return string
     */
    public function get_margin_manager_formatted(string $margin_usd_formatted): string
    {
        $system_currency_tab = $this->get_system_currency_tab();
        $manager_currency_tab = $this->get_manager_currency_tab();
        $margin_manager_formatted = Helpers_Currency::get_recalculated_to_given_currency(
            $margin_usd_formatted,
            $system_currency_tab,
            $manager_currency_tab['code']
        );
        
        return $margin_manager_formatted;
    }
    
    /**
     *
     * @return string
     */
    public function get_income_type(): string
    {
        $income_type = $this->lottery['income_type'];
        return $income_type;
    }
    
    /**
     *
     * @return string
     */
    public function get_bonus_cost_lottery(): string
    {
        $counted_lines = $this->get_counted_lines();
        
        $price_of_ticket = $this->get_price_of_ticket();
        $bonus_cost_lottery_full = $price_of_ticket[0] + $price_of_ticket[1];

        return $bonus_cost_lottery_full * $counted_lines;
    }
    
    /**
     *
     * @param string $bonus_cost_lottery
     * @return string
     */
    public function get_bonus_cost_lottery_formatted(string $bonus_cost_lottery): string
    {
        return round($bonus_cost_lottery, 2);
    }
    
    /**
     *
     * @param string $bonus_cost_lottery
     * @return string
     */
    public function get_bonus_cost_usd(string $bonus_cost_lottery): string
    {
        $lottery_currency_tab = $this->get_lottery_currency_tab();
        $system_currency_tab = $this->get_system_currency_tab();
        
        $bonus_cost_usd = "0.00";
        if ((string)$lottery_currency_tab['code'] === (string)$system_currency_tab['code']) {
            $bonus_cost_usd = $bonus_cost_lottery;
        } else {
            $bonus_cost_usd = Helpers_Currency::convert_to_USD(
                $bonus_cost_lottery,
                $lottery_currency_tab['code']
            );
        }
        
        return $bonus_cost_usd;
    }
    
    /**
     *
     * @param string $bonus_cost_usd
     * @return string
     */
    public function get_bonus_cost_usd_formatted(string $bonus_cost_usd): string
    {
        return round($bonus_cost_usd, 2);
    }
    
    /**
     *
     * @param string $bonus_cost_lottery
     * @param string $bonus_cost_usd
     * @return string
     */
    public function get_bonus_cost_manager(
        string $bonus_cost_lottery,
        string $bonus_cost_usd
    ): string {
        $lottery_currency_tab = $this->get_lottery_currency_tab();
        $system_currency_tab = $this->get_system_currency_tab();
        $manager_currency_tab = $this->get_manager_currency_tab();
        
        $bonus_cost_manager = "0.00";
        if ((string)$lottery_currency_tab['code'] === (string)$manager_currency_tab['code']) {
            $bonus_cost_manager = $bonus_cost_lottery;
        } elseif ((string)$system_currency_tab['code'] === (string)$manager_currency_tab['code']) {
            $bonus_cost_manager = $bonus_cost_usd;
        } else {
            $bonus_cost_manager = Helpers_Currency::get_recalculated_to_given_currency(
                $bonus_cost_usd,
                $system_currency_tab,
                $manager_currency_tab['code'],
                2
            );
        }
        
        return $bonus_cost_manager;
    }
    
    /**
     *
     * @param string $bonus_cost_manager
     * @return string
     */
    public function get_bonus_cost_manager_formatted(string $bonus_cost_manager): string
    {
        return round($bonus_cost_manager, 2);
    }
    
    /**
     *
     * @param string $bonus_cost_lottery
     * @param string $bonus_cost_usd
     * @param string $bonus_cost_manager
     * @return string
     */
    public function get_bonus_cost_user(
        string $bonus_cost_lottery,
        string $bonus_cost_usd,
        string $bonus_cost_manager
    ): string {
        $lottery_currency_tab = $this->get_lottery_currency_tab();
        $system_currency_tab = $this->get_system_currency_tab();
        $manager_currency_tab = $this->get_manager_currency_tab();
        $user_currency_tab = $this->get_user_currency_tab();
        
        $bonus_cost_user = "0.00";
        if ((string)$lottery_currency_tab['code'] === (string)$user_currency_tab['code']) {
            $bonus_cost_user = $bonus_cost_lottery;
        } elseif ((string)$system_currency_tab['code'] === (string)$user_currency_tab['code']) {
            $bonus_cost_user = $bonus_cost_usd;
        } elseif ((string)$manager_currency_tab['code'] === (string)$user_currency_tab['code']) {
            $bonus_cost_user = $bonus_cost_manager;
        } else {
            $bonus_cost_user = Helpers_Currency::get_recalculated_to_given_currency(
                $bonus_cost_lottery,
                $lottery_currency_tab,
                $user_currency_tab['code'],
                2
            );
        }
        
        return $bonus_cost_user;
    }
    
    /**
     *
     * @param string $bonus_cost_user
     * @return string
     */
    public function get_bonus_cost_user_formatted(string $bonus_cost_user): string
    {
        return round($bonus_cost_user, 2);
    }
    
    /**
     *
     * @return string
     */
    public function get_ip(): string
    {
        $ip = Lotto_Security::get_IP();
        return $ip;
    }
    
    /**
     *
     * @return array
     */
    public function get_prepared_ticket_set(): array
    {
        $ticket_token = $this->get_token();
        
        $lottery_type = $this->get_lottery_type();
        $lottery_type_id = (int)$lottery_type['id'];
        $type_of_lottery = $this->get_type_of_lottery();
        
        $user_currency_tab = $this->get_user_currency_tab();
        
        $lottery_model = $this->get_lottery_model();
        
        $this->get_should_insure();
        $is_insured = $this->get_is_insured();
        $tier = $this->get_tier();
        
        $price_lottery = $this->get_price_lottery();
        $price_usd = $this->get_price_usd();
        $price_user = $this->get_price_user();
        
        /**
         * Cost
         */
        $cost_lottery = $this->get_cost_lottery();
        $cost_lottery_formatted = $this->get_cost_lottery_formatted($cost_lottery);
        $cost_usd = $this->get_cost_usd($cost_lottery);
        $cost_usd_formatted = $this->get_cost_usd_formatted($cost_usd);
        $cost_manager = $this->get_cost_manager($cost_lottery, $cost_usd);
        $cost_manager_formatted = $this->get_cost_manager_formatted($cost_manager);
        $cost_user = $this->get_cost_user($cost_lottery, $cost_usd, $cost_manager);
        $cost_user_formatted = $this->get_cost_user_formatted($cost_user);
        /**
         * End of Cost
         */
        
        /**
         * Income
         */
        $income_lottery = $this->get_income_lottery($price_lottery, $cost_lottery);
        $income_lottery_formatted = $this->get_income_lottery_formatted($income_lottery);
        $income_usd = $this->get_income_usd($price_usd, $cost_usd);
        $income_usd_formatted = $this->get_income_usd_formatted($income_usd);
        $income_user = $this->get_income_user($price_user, $cost_user);
        $income_user_formatted = $this->get_income_user_formatted($income_user);
        $income_manager = $this->get_income_manager($income_lottery, $income_usd);
        $income_manager_formatted = $this->get_income_manager_formatted($income_manager);
        
        $income_value_formatted = $this->get_income_value_formatted();
        /**
         * End of income
         */
        
        $income_type = $this->get_income_type();
        
        /**
         * Margin
         */
        $margin_value = $this->get_margin_value();
        $margin_value_percentage = $this->get_margin_value_percentage();

        $isGgrLottery = Helpers_lottery::isGgrEnabled($this->lottery['type']);
        if ($isGgrLottery) {
            $margin_value = 0;
            $margin_value_percentage = 0;
        }
        $margin_lottery_formatted = $this->get_margin_lottery_formatted(
            $cost_lottery,
            $margin_value_percentage
        );
        $margin_usd_formatted = $this->get_margin_usd_formatted(
            $cost_usd,
            $margin_value_percentage
        );
        $margin_user_formatted = $this->get_margin_user_formatted(
            $cost_user,
            $margin_value_percentage
        );
        $margin_manager_formatted = $this->get_margin_manager_formatted($margin_usd_formatted);
        /**
         * End of Margin
         */
        
        /**
         * Bonus cost
         */
        $bonus_cost_lottery = $this->get_bonus_cost_lottery();
        $bonus_cost_lottery_formatted = $this->get_bonus_cost_lottery_formatted($bonus_cost_lottery);
        $bonus_cost_usd = $this->get_bonus_cost_usd($bonus_cost_lottery);
        $bonus_cost_usd_formatted = $this->get_bonus_cost_usd_formatted($bonus_cost_usd);
        $bonus_cost_manager = $this->get_bonus_cost_manager(
            $bonus_cost_lottery,
            $bonus_cost_usd
        );
        $bonus_cost_manager_formatted = $this->get_bonus_cost_manager_formatted($bonus_cost_manager);
        $bonus_cost_user = $this->get_bonus_cost_user(
            $bonus_cost_lottery,
            $bonus_cost_usd,
            $bonus_cost_manager
        );
        $bonus_cost_user_formatted = $this->get_bonus_cost_user_formatted($bonus_cost_user);
        /**
         * End of Bonus cost
         */
        
        $ip = $this->get_ip();
        $counted_lines = $this->get_counted_lines();
        
        $ticket_set = [
            'token' => $ticket_token,
            'whitelabel_transaction_id' => null,
            'whitelabel_id' => (int)$this->whitelabel['id'],
            'whitelabel_user_id' => (int)$this->user['id'],
            'lottery_id' => (int)$this->lottery['id'],
            'lottery_type_id' => $lottery_type_id,
            'currency_id' => $user_currency_tab['id'],
            'draw_date' => $this->ticket_draw_date->format(Helpers_Time::DATETIME_FORMAT),
            'valid_to_draw' => $this->ticket_draw_date->format(Helpers_Time::DATETIME_FORMAT),
            'amount_local' => 0,
            'amount' => 0,
            'amount_usd' => 0,
            'amount_payment' => 0,
            'amount_manager' => 0,
            'date' => DB::expr("NOW()"),
            'status' => Helpers_General::TICKET_STATUS_PENDING,
            'paid' => Helpers_General::TICKET_PAID,
            'payout' => Helpers_General::TICKET_PAYOUT_PENDING,
            'model' => $lottery_model,
            'is_insured' => $is_insured,
            'tier' => $tier,
            'cost_local' => $cost_lottery_formatted,
            'cost_usd' => $cost_usd_formatted,
            'cost' => $cost_user_formatted,
            'cost_manager' => $cost_manager_formatted,
            'income_local' => $income_lottery_formatted,
            'income_usd' => $income_usd_formatted,
            'income' => $income_user_formatted,
            'income_value' => $income_value_formatted,
            'income_manager' => $income_manager_formatted,
            'income_type' => $income_type,
            'margin_value' => $margin_value,
            'margin_local' => $margin_lottery_formatted,
            'margin_usd' => $margin_usd_formatted,
            'margin' => $margin_user_formatted,
            'margin_manager' => $margin_manager_formatted,
            'bonus_cost_local' => $bonus_cost_lottery_formatted,
            'bonus_cost_usd' => $bonus_cost_usd_formatted,
            'bonus_cost' => $bonus_cost_user_formatted,
            'bonus_cost_manager' => $bonus_cost_manager_formatted,
            'ip' => $ip,
            'line_count' => $counted_lines
        ];

        return $ticket_set;
    }

    private function prepare_keno_data(): void
    {
        $multipliers = Model_Lottery_Type_Multiplier::for_lottery($this->lottery['id']);
        $keno_data_set = [
            'whitelabel_user_ticket_id' => $this->new_bonus_ticket['id'],
            'lottery_type_multiplier_id' => ArrayHelper::first($multipliers)['id'],
            'numbers_per_line' => 10,
        ];
        $keno_data = Model_Whitelabel_User_Ticket_Keno_Data::forge();
        $keno_data->set($keno_data_set);
        $keno_data->save();
    }

    /**
     *
     * @return \Model_Whitelabel_User_Ticket|null
     */
    public function get_new_bonus_ticket():? Model_Whitelabel_User_Ticket
    {
        return $this->new_bonus_ticket;
    }

    private function get_type_of_lottery(): string
    {
        return $this->lottery['type'];
    }

    /**
     *
     * @return int
     */
    public function process_form(): int
    {
        try {
            $lotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel($this->whitelabel);

            $set = $this->get_prepared_ticket_set();
            $this->new_bonus_ticket = Model_Whitelabel_User_Ticket::forge();
            $this->new_bonus_ticket->set($set);
            $this->new_bonus_ticket->save();

            $lottery = $lotteries['__by_id'][$this->new_bonus_ticket->lottery_id];

            if ((int)$lottery['should_decrease_prepaid'] === 1) {
                $prepaid = new Forms_Admin_Whitelabels_Prepaid_New($this->whitelabel);
                $prepaid->subtract_prepaid($this->new_bonus_ticket->cost_manager, null, false);
            }

            if ($this->get_type_of_lottery() === Helpers_Lottery::TYPE_KENO){
                $this->prepare_keno_data();
            }
        } catch (\Exception $e) {
            $this->fileLoggerService->error(
                $e->getMessage()
            );
            return self::RESULT_WITH_ERRORS;
        }

        return self::RESULT_OK;
    }

}

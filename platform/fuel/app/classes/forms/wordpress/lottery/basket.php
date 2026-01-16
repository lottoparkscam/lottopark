<?php

use Carbon\Carbon;
use Helpers\CurrencyHelper;
use Helpers_Lottery;
use Interfaces\PromoCode\PromoCodeApplicableInterface;
use Interfaces\PromoCode\PromoCodeTransactionApplicableInterface;
use Services\Logs\FileLoggerService;
use Traits\Payments\PaymentMethodTrait;

class Forms_Wordpress_Lottery_Basket extends Forms_Main implements PromoCodeApplicableInterface, PromoCodeTransactionApplicableInterface
{
    use PaymentMethodTrait;

    const RESULT_WRONG_TOTAL_PRICE = 100;
    const RESULT_TOO_LOW_PAYMENT_AMOUNT = 200;
    const TICKET_MULTIPLIER_NOT_FOUND = 300;

    const KENO_WITH_CUT_OFF_TIME_IN_MINUTES = [
        Helpers_Lottery::FINNISH_KENO_ID => 15,
        Helpers_Lottery::FRENCH_KENO_ID => 30,
        Helpers_Lottery::HUNGARIAN_KENO_ID => 60,
        Helpers_Lottery::LATVIAN_KENO_ID => 30,
        Helpers_Lottery::SLOVAK_KENO_10_ID => 30,
        Helpers_Lottery::GERMAN_KENO_ID => 70,
        Helpers_Lottery::UKRAINIAN_KENO_ID => 75,
        Helpers_Lottery::BELGIAN_KENO_ID => 60,
        Helpers_Lottery::SWEDISH_KENO_ID => 35,
        // Helpers_Lottery::AUSTRALIAN_KENO_ID => 0,
        Helpers_Lottery::DANISH_KENO_ID => 60,
        Helpers_Lottery::NORWEGIAN_KENO_ID => 60,
        // Helpers_Lottery::LITHUANIAN_KENO_ID => 0,
        // Helpers_Lottery::CROATIAN_KENO_ID => 0,
        // Helpers_Lottery::BELARUSIAN_KENO_ID => 0,
        // Helpers_Lottery::ESTONIAN_KENO_ID => 0,
        // Helpers_Lottery::CANADIAN_KENO_ID => 0,
    ];

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
    private $emerchant_data = [];

    /**
     *
     * @var array
     */
    private $lotteries = [];

    /**
     *
     * @var array
     */
    private $basket;

    /**
     *
     * @var int
     */
    private $payment_type = 0;

    private int $transactionType = Helpers_General::TYPE_TRANSACTION_PURCHASE;

    /**
     *
     * @var int
     */
    private $payment_method_id = 0;

    /**
     *
     * @var int
     */
    private $whitelabel_payment_method_id = 0;

    /**
     *
     * @var string
     */
    private $single_error = "";

    /**
     *
     * @var array
     */
    private $errors = [];

    /**
     *
     * @var Model_Whitelabel_Transaction
     */
    private $transaction = null;

    /**
     *
     * @var array
     */
    private $user_currency_tab = [];

    /**
     *
     * @var array
     */
    private $gateway_currency_tab = [];

    /**
     *
     * @var array
     */
    private $user_currency_data = [];

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

    private ?Forms_Whitelabel_Bonuses_Promocodes_Code $promoCodeForm = null;

    public bool $promoCodeDiscountActive = false;

    private array $input_post;

    /**
     *
     * @param array $whitelabel
     * @param array $user
     * @param array $lotteries
     * @param array $basket
     * @param int   $payment_type
     * @param int   $payment_method_id
     * @param int   $whitelabel_payment_method_id
     * @param array|null $emerchant_data
     * @param array $input_post
     */
    public function __construct(
        array $whitelabel,
        array $user,
        array $lotteries,
        array $basket,
        int $payment_type,
        int $payment_method_id,
        int $whitelabel_payment_method_id,
        array $emerchant_data = null,
        array $input_post = []
    )
    {
        $this->whitelabel = $whitelabel;
        $this->user = $user;
        $this->lotteries = $lotteries;
        $this->basket = $basket;
        $this->payment_type = $payment_type;
        $this->payment_method_id = $payment_method_id;
        $this->whitelabel_payment_method_id = $whitelabel_payment_method_id;
        $this->emerchant_data = $emerchant_data;
        $this->input_post = $input_post;

        $this->user_currency_tab = CurrencyHelper::getCurrentCurrency()->to_array();

        $this->gateway_currency_tab = Helpers_Currency::get_currencies_tabs(
            $this->whitelabel,
            $this->payment_type,
            $this->whitelabel_payment_method_id
        );

        $this->user_currency_data = Model_Whitelabel_Default_Currency::get_for_user(
            $this->whitelabel,
            $this->user_currency_tab['id']
        );

        $this->system_currency_tab = Helpers_Currency::get_mtab_currency(false, "USD");

        $this->manager_currency_tab = Helpers_Currency::get_mtab_currency(
            false,
            null,
            $this->whitelabel['manager_site_currency_id']
        );
    }

    public function setPromoCodeForm(Forms_Whitelabel_Bonuses_Promocodes_Code $promoCodeForm): void
    {
        $this->promoCodeForm = $promoCodeForm;
    }

    public function getPromoCodeForm(): ?Forms_Whitelabel_Bonuses_Promocodes_Code
    {
        return $this->promoCodeForm;
    }

    public function getTransactionType(): int
    {
        return $this->transactionType;
    }

    /**
     *
     * @return \Model_Whitelabel_Transaction
     */
    public function get_transaction(): ?\Model_Whitelabel_Transaction
    {
        return $this->transaction;
    }

    /**
     *
     * @return array
     */
    public function get_errors(): array
    {
        return $this->errors;
    }

    /**
     *
     * @return Validation object
     */
    private function get_prepared_form(): Validation
    {
        $val = Validation::forge();

        return $val;
    }

    /**
     *
     * @param array $lines
     * @param array $lottery_type
     *
     * @throws Exception
     */
    private function check_lines(array $lines, array $lottery_type, array $lottery, ?int $numbers_per_line = null): void
    {
        foreach ($lines as $line) {
            $numbers = $line['numbers'];
            $bnumbers = $line['bnumbers'];

            $numc = array_unique(array_values($numbers));
            $bnumc = array_unique(array_values($bnumbers));

            if (!(
                (count($numc) == $lottery_type['ncount'] || count($numc) === (int)$numbers_per_line) &&
                ($lottery_type['bextra'] == 0 &&
                    count($bnumc) == $lottery_type['bcount']) ||
                ($lottery_type['bextra'] > 0 &&
                    count($bnumc) == 0)
            )) {
                throw new Exception("Incorrect amount of unique numbers. [" . count($numc) . " " . count($bnumc) . "], type={$lottery_type['ncount']}, npl=$numbers_per_line for lottery_id = {$lottery['id']}");
            }

            if (
                $lottery['type'] !== 'keno' && // we have that already validated earlier for Keno
                (
                    count($numbers) != $lottery_type['ncount'] ||
                    count($bnumbers) != $lottery_type['bcount']
                )
            ) {
                throw new Exception("Incorrect amount of numbers. [" . count($numbers) . " " . count($bnumbers) . "] for lottery_id = {$lottery['id']}");
            }

            foreach ($numbers as $number) {
                if (intval($number) < 1 ||
                    intval($number) > intval($lottery_type['nrange'])
                ) {
                    throw new Exception("Number out of range. [" . $number . "] for lottery_id = {$lottery['id']}");
                }
            }
            foreach ($bnumbers as $bnumber) {
                if (intval($bnumber) < 1 ||
                    intval($bnumber) > intval($lottery_type['brange'])
                ) {
                    throw new Exception("Bonus number out of range. [" . $bnumber . "] for lottery_id = {$lottery['id']}");
                }
            }
        }
    }

    /**
     *
     * @param array $lottery
     *
     * @return array
     * @throws Exception
     */
    private function get_lottery_type(array $lottery): array
    {
        $ticket_draw_date = null;
        $now = Carbon::now($lottery['timezone']);
        $drawDate = Carbon::parse($lottery['next_date_local'], $lottery['timezone']);
        $lotteryIsClosed = Lotto_Helper::is_lottery_closed($lottery, null, $this->whitelabel);
        $nowIsBeforeNextDraw = $now->lessThan($drawDate);
        if ($lotteryIsClosed && $nowIsBeforeNextDraw) {
            // TODO: adjust next draw on lottery changes
            $ticket_draw_date = Lotto_Helper::get_lottery_next_draw($lottery, true, null, 2);
        } else {
            // INFO: KENO REGULAR PATH
            $ticket_draw_date = Lotto_Helper::get_lottery_next_draw($lottery);
        }

        /**
         * Cut-off time override for Keno - it should not be considered during merge
         * All lotteries including keno should include option to set cutoff time
         */
        foreach (self::KENO_WITH_CUT_OFF_TIME_IN_MINUTES as $kenoId => $kenoCutOffMinutes) {
            if ((int)$lottery['id'] === $kenoId && $now->diffInMinutes($drawDate) < $kenoCutOffMinutes) {
                $ticket_draw_date = Lotto_Helper::get_lottery_next_draw($lottery, true, null, 2);
            }
        }

        $type = Model_Lottery_Type::get_lottery_type_for_date(
            $lottery,
            $ticket_draw_date->format(Helpers_Time::DATETIME_FORMAT)
        );
        //$type = Model_Lottery_Type::find_by_pk($lottery['lottery_type_id']);
        if ($type === null) {
            throw new Exception('Helper - No lottery type.');
        }

        return [
            $type,
            $ticket_draw_date
        ];
    }

    /**
     *
     * @return null|array
     * @throws Exception
     */
    private function prepare_totals(): ?array
    {
        $multidraw_helper = new Helpers_Multidraw($this->whitelabel);

        $total_user_currency_price = 0;
        $total_system_price = 0;
        $total_payment_price = 0;
        $total_manager_price = 0;
        $totalPriceWithoutMultiDraw = 0;

        $basket_temp = [];
        $lottery_type = [];
        $ticket_draw_date = [];
        $i = 0;

        foreach ($this->basket as $item) {
            $lottery_item = $item['lottery'];
            $lines = $item['lines'];
            $numbers_per_line = $item['numbers_per_line'] ?? null;
            $lines_count = count($lines);

            if (!isset($this->lotteries['__by_id'][$lottery_item])) {
                throw new Exception("Unknown lottery.");
            }

            if ($lines === null || $lines_count == 0) {
                continue;
            }
            $basket_temp[$i] = $item;

            $lottery = $this->lotteries['__by_id'][$lottery_item];

            // Check if item comes from multi-draw
            if (isset($item['multidraw']) && $lottery['is_multidraw_enabled'] == 1
                && $lottery['multidraws_enabled'] == 1) {
                $multi_draw_helper = new Helpers_Multidraw($this->whitelabel);
                $multi_draw = $multi_draw_helper->check_multidraw($item['multidraw']);
            }

            $isMultiDraw = false;

            if (isset($item['multidraw']) && !empty($multi_draw['tickets'])) {
                $isMultiDraw = true;
            }

            list(
                $lottery_type[$i],
                $ticket_draw_date[$i]
                ) = $this->get_lottery_type($lottery);

            $this->check_lines($lines, $lottery_type[$i], $lottery, $numbers_per_line);

            // Below are calculations in user currency
            $ticket_multiplier = 1;
            if (isset($item['ticket_multiplier'])) {
                $ticket_multiplier = $item['ticket_multiplier'];
            }
            $multiplied_price_user = Lotto_Helper::get_user_converted_price(
                $lottery,
                $this->user_currency_tab['id'],
                $ticket_multiplier
            );
            $multiplied_price_user = round($multiplied_price_user * $lines_count, 2);

            if ($isMultiDraw) {
                $multiplied_price_user = $multidraw_helper->calculate($multi_draw, $multiplied_price_user);
            }

            if (!$isMultiDraw) {
                $totalPriceWithoutMultiDraw += $multiplied_price_user;
            }

            $total_user_currency_price += $multiplied_price_user;

            // The same in system currency - USD
            $price_system_currency = Lotto_Helper::get_user_converted_price(
                $lottery,
                $this->system_currency_tab['id'],
                $ticket_multiplier
            );
            $multiplied_price_system = round($price_system_currency * $lines_count, 2);

            if ($isMultiDraw) {
                $multiplied_price_system = $multidraw_helper->calculate($multi_draw, $multiplied_price_system);
            }

            $total_system_price += $multiplied_price_system;

            // The same in gateway-payment currency
            $price_payment_currency = Lotto_Helper::get_user_converted_price(
                $lottery,
                $this->gateway_currency_tab['id'],
                $ticket_multiplier
            );
            $multiplied_price_payment = round($price_payment_currency * $lines_count, 2);

            if ($isMultiDraw) {
                $multiplied_price_payment = $multidraw_helper->calculate($multi_draw, $multiplied_price_payment);
            }

            $total_payment_price += $multiplied_price_payment;

            // The same in manager currency
            $price_manager_currency = Lotto_Helper::get_user_converted_price(
                $lottery,
                $this->manager_currency_tab['id'],
                $ticket_multiplier
            );
            $multiplied_price_manager = round($price_manager_currency * $lines_count, 2);

            if ($isMultiDraw) {
                $multiplied_price_manager = $multidraw_helper->calculate($multi_draw, $multiplied_price_manager);
            }

            $total_manager_price += $multiplied_price_manager;

            $i++;
        }

        return [
            $basket_temp,
            $lottery_type,
            $ticket_draw_date,
            $total_user_currency_price,
            $total_system_price,
            $total_payment_price,
            $total_manager_price,
            $totalPriceWithoutMultiDraw
        ];
    }

    /**
     *
     * @param float $total_user_currency_price
     *
     * @return int
     */
    private function check_mins(string $total_user_currency_price): int
    {
        if (Input::post("payment.type") == Helpers_General::PAYMENT_TYPE_OTHER) {
            list(
                $min_purchase_value,
                $currency_code
                ) = Helpers_Currency::get_min_purchase_for_payment_method(
                $this->whitelabel_payment_method_id,
                $this->user_currency_tab,
                $this->gateway_currency_tab
            );

            if (!empty($min_purchase_value)) {
                $min_payment = $min_purchase_value;
                if ($min_payment > $total_user_currency_price) {
                    $min_payment_text = Lotto_View::format_currency(
                        $min_payment,
                        $currency_code,
                        2
                    );

                    $message_error = sprintf(
                        _("The minimum order for this payment type is %s."),
                        $min_payment_text
                    );

                    Session::set("message", ["error", $message_error]);

                    return self::RESULT_TOO_LOW_PAYMENT_AMOUNT;
                }
            }
        }

        $user_currency_code = $this->user_currency_tab['code'];
        $purchanse_min_amount = $this->user_currency_data['min_purchase_amount'];

        if (($total_user_currency_price < $purchanse_min_amount &&
                $this->user['balance'] < $total_user_currency_price) ||
            $total_user_currency_price <= 0
        ) {
            $exc_txt = "Total price is wrong. [" .
                $total_user_currency_price . " " .
                $this->user['balance'] . "]";
            $this->single_error = $exc_txt;

            return self::RESULT_WRONG_TOTAL_PRICE;
        }

        if (Input::post("payment.type") == Helpers_General::PAYMENT_TYPE_CC) {
            $emerchant_data_settings = unserialize($this->emerchant_data['settings']);

            $emerchant_tab = [
                'id' => $this->emerchant_data['cid'],
                'code' => $this->emerchant_data['currency_code'],
                'rate' => $this->emerchant_data['currency_rate']
            ];

            $minorder_user_currency = Helpers_Currency::get_recalculated_to_given_currency(
                $emerchant_data_settings['minorder'],
                $emerchant_tab,
                $user_currency_code
            );

            $card_min_reached = true;
            if (bccomp($total_user_currency_price, $minorder_user_currency, 2) < 0) {
                $card_min_reached = false;
            }

            if (!$card_min_reached) {
                $message = _("You didn't reach the minimum order for this payment type!");
                Session::set("message", ["error", $message]);

                return self::RESULT_TOO_LOW_PAYMENT_AMOUNT;
            }
        }

        return self::RESULT_OK;
    }

    /**
     *
     * @param array $lottery
     *
     * @param null  $multi_draw
     * @param int   $ticket_multiplier
     *
     * @return array
     */
    private function prepare_single_prices(array $lottery, $multi_draw = null, int $ticket_multiplier = 1): array
    {
        // Single price in user currency
        $itm_price_user_curr = Lotto_Helper::get_user_converted_price(
            $lottery,
            $this->user_currency_tab['id'],
            $ticket_multiplier
        );

        // Single price in lottery currency
        $itm_price_lottery = Lotto_Helper::get_user_price($lottery) * $ticket_multiplier;

        // Single price in system (USD) currency
        $itm_price_usd = Lotto_Helper::get_user_converted_price(
            $lottery,
            $this->system_currency_tab['id'],
            $ticket_multiplier
        );

        // Single price in payment currency
        $itm_price_payment = Lotto_Helper::get_user_converted_price(
            $lottery,
            $this->gateway_currency_tab['id'],
            $ticket_multiplier
        );

        // Single price in manager currency
        $itm_price_manager = Lotto_Helper::get_user_converted_price(
            $lottery,
            $this->manager_currency_tab['id'],
            $ticket_multiplier
        );

        if (!empty($multi_draw)) {
            $multidraw_helper = new Helpers_Multidraw($this->whitelabel);

            $itm_price_user_curr = $multidraw_helper->calculate($multi_draw, $itm_price_user_curr);
            $itm_price_lottery = $multidraw_helper->calculate($multi_draw, $itm_price_lottery);
            $itm_price_usd = $multidraw_helper->calculate($multi_draw, $itm_price_usd);
            $itm_price_payment = $multidraw_helper->calculate($multi_draw, $itm_price_payment);
            $itm_price_manager = $multidraw_helper->calculate($multi_draw, $itm_price_manager);
        }

        return [
            $itm_price_user_curr,
            $itm_price_lottery,
            $itm_price_usd,
            $itm_price_payment,
            $itm_price_manager
        ];
    }

    /**
     * TODO: should return value instead of redirect!
     *
     * @return int
     * @throws Exception
     */
    public function process_form(): int
    {
        if (!empty($this->input_post['payment']['userSelectedCurrency'])) {
            // If user selected custom currency, then error here should terminate payment
            try {
                $userSelectedPaymentCurrencyTab = $this->getUserSelectedPaymentCurrencyTab();
            } catch (Throwable $exception) {
                $fileLoggerService = Container::get(FileLoggerService::class);
                $fileLoggerService->error(
                    "Basket purchase with user selected currency failed: {$this->input_post['payment']['userSelectedCurrency']} " .
                    "Whitelabel: {$this->whitelabel['id']} Payment type: {$this->payment_type} Whitelabel Payment Method ID: {$this->whitelabel_payment_method_id} " .
                    "Detailed message: " . $exception->getMessage()
                );
                $this->errors = ['payment' => _('Unknown error! Please try again later or contact us!')];

                return self::RESULT_WITH_ERRORS;
            }
        }

        if (!empty($userSelectedPaymentCurrencyTab)) {
            /**
             * User selected currency on payment page, it is valid for the gateway
             * Override gateway's default currency with selected to calculate amount to pay for transaction
             */
            $this->gateway_currency_tab = $userSelectedPaymentCurrencyTab;
        }

        try {
            DB::start_transaction();

            if ($this->payment_type === Helpers_General::PAYMENT_TYPE_BONUS_BALANCE && !Helpers_Currency::check_is_bonus_balance_in_use()) {
                $message = _("Bonus balance cannot be used in this transaction!");
                Session::set("message", ["error", $message]);

                return self::RESULT_WITH_ERRORS;
            }

            // little checking
            list(
                $basket_temp,
                $lottery_type,
                $ticket_draw_date,
                $total_user_curr_price,
                $total_system_price,
                $total_payment_price,
                $total_manager_price,
                $totalPriceWithoutMultiDraw
                ) = $this->prepare_totals();

            $total_user_price_undiscounted = null;

            $this->promoCodeForm = Forms_Whitelabel_Bonuses_Promocodes_Code::get_or_create(
                $this->whitelabel,
                Forms_Whitelabel_Bonuses_Promocodes_Code::TYPE_PURCHASE
            );

            $this->processPromoCode();

            if ($this->promoCodeDiscountActive) {
                $promo_code = $this->promoCodeForm->get_promo_code();
                $total_user_price_undiscounted = $totalPriceWithoutMultiDraw;
                $discount = $promo_code['discount_user'];
                $discount_usd = $promo_code['discount_usd'];
                $discount_manager = $promo_code['discount_manager'];
                $discount_payment = Helpers_Currency::get_recalculated_to_given_currency(
                    $discount_manager,
                    $this->manager_currency_tab,
                    $this->gateway_currency_tab['code']
                );
                $total_user_curr_price = $total_user_curr_price - $discount;
                $total_system_price = $total_system_price - $discount_usd;
                $total_payment_price = $total_payment_price - $discount_payment;
                $total_manager_price = $total_manager_price - $discount_manager;
            }

            $result = $this->check_mins($total_user_curr_price);

            switch ($result) {
                case self::RESULT_OK:     // OK
                    break;
                case self::RESULT_WRONG_TOTAL_PRICE:     // Total price is wrong
                    throw new Exception($this->single_error);
                case self::RESULT_TOO_LOW_PAYMENT_AMOUNT:
                    DB::rollback_transaction();

                    Model_Payment_Log::add_log(
                        Helpers_General::TYPE_ERROR,
                        null,
                        null,
                        null,
                        $this->whitelabel['id'],
                        null,
                        "Amount of payment is too low.",
                        null
                    );

                    return self::RESULT_TOO_LOW_PAYMENT_AMOUNT;
            }

            // let's start transaction
            $transaction_token = Lotto_Security::generate_transaction_token($this->whitelabel['id']);

            $transaction_bonus_amount = 0;
            $transaction_bonus_amount_usd = 0;
            $transaction_bonus_amount_payment = 0;
            $transaction_bonus_amount_manager = 0;

            $transaction_user_curr_amount = $total_user_curr_price;
            $transaction_amount_usd = $total_system_price;
            $transaction_amount_payment = $total_payment_price;
            $transaction_amount_manager = $total_manager_price;

            if ($this->payment_type === Helpers_General::PAYMENT_TYPE_BONUS_BALANCE) {
                $transaction_bonus_amount = $total_user_curr_price;
                $transaction_bonus_amount_usd = $total_system_price;
                $transaction_bonus_amount_payment = $total_payment_price;
                $transaction_bonus_amount_manager = $total_manager_price;
            }

            $transaction_set = [
                'token' => $transaction_token,
                'whitelabel_id' => $this->whitelabel['id'],
                'whitelabel_user_id' => $this->user['id'],
                'currency_id' => $this->user_currency_tab['id'],
                'payment_currency_id' => $this->gateway_currency_tab['id'],
                'amount' => $transaction_user_curr_amount,
                'amount_usd' => $transaction_amount_usd,
                'amount_payment' => $transaction_amount_payment,
                'amount_manager' => $transaction_amount_manager,
                'bonus_amount' => $transaction_bonus_amount,
                'bonus_amount_usd' => $transaction_bonus_amount_usd,
                'bonus_amount_payment' => $transaction_bonus_amount_payment,
                'bonus_amount_manager' => $transaction_bonus_amount_manager,
                'date' => DB::expr("NOW()"),
                'status' => Helpers_General::STATUS_TRANSACTION_PENDING,
                'type' => $this->transactionType // payment, not depo
            ];

            $transaction = Model_Whitelabel_Transaction::forge();
            $transaction->set($transaction_set);
            $transaction->save();

            $user = Model_Whitelabel_User::find_by_pk($this->user['id']);
            if ($user->sale_status < Helpers_General::SALE_STATUS_STARTED_PURCHASE) {
                $user->sale_status = Helpers_General::SALE_STATUS_STARTED_PURCHASE;
                $user->save();
            }

            $this->usePromoCodeForWhitelabelTransaction($transaction->id);

            $ip = Lotto_Security::get_IP();

            $income_total = $income_usd_total = $income_manager_total = 0;
            $cost_total = $cost_usd_total = $cost_manager_total = 0;
            $marginTotal = $marginTotalInUsd = $marginTotalInManagerCurrency = 0;

            //load ticket multipliers
            $multipliers = Model_Lottery_Type_Multiplier::for_ticket_saving() ?? [];
            $i = 0;
            foreach ($basket_temp as $item) {
                $lottery_item = $item['lottery'];
                $lines = $item['lines'];

                $lottery = $this->lotteries['__by_id'][$lottery_item];

                $multiplier = 1;
                if (Helpers_Lottery::supports_ticket_multipliers($lottery)) {
                    if (isset($item['ticket_multiplier'])) {
                        $multiplier = $item['ticket_multiplier'];
                    }
                    if (isset($multipliers[$lottery['id']][$multiplier]) === false) {
                        DB::rollback_transaction();

                        Model_Payment_Log::add_log(
                            Helpers_General::TYPE_ERROR,
                            null,
                            null,
                            null,
                            $this->whitelabel['id'],
                            null,
                            "Ticket multiplier not found in multipliers table",
                            null
                        );

                        return self::TICKET_MULTIPLIER_NOT_FOUND;
                    }
                }

                if (isset($item['multidraw'])) {
                    $multi_draw_helper = new Helpers_Multidraw($this->whitelabel);
                    $multi_draw = $multi_draw_helper->check_multidraw($item['multidraw']);
                }

                $isMultiDraw = false;

                $tickets = 1;

                if ($lottery['is_multidraw_enabled'] == 1 && $lottery['multidraws_enabled'] == 1
                    && isset($item['multidraw']) && !empty($multi_draw['tickets'])) {
                    $tickets = $multi_draw['tickets'];
                    $isMultiDraw = true;
                }

                $counted_lines = count($lines);

                $lottery_currency_tab = Helpers_Currency::get_mtab_currency(
                    false,
                    $lottery['currency']
                );

                $single_prices_multidraw = null;
                if ($isMultiDraw) {
                    $single_prices_multidraw = $multi_draw;
                }

                ///////// price calculations ////////
                list(
                    $itm_price_user_curr_formatted,
                    $itm_price_lottery_formatted,
                    $itm_price_usd_formatted,
                    $itm_price_payment_formatted,
                    $itm_price_manager_formatted
                    ) = $this->prepare_single_prices($lottery, null, $multiplier);

                if ($this->promoCodeDiscountActive && !$isMultiDraw) {
                    $ticket_price = $itm_price_user_curr_formatted * $counted_lines;
                    $fraction = $ticket_price / $total_user_price_undiscounted;
                    $discount_ticket_user = $promo_code['discount_user'] * $fraction;
                    $discount_ticket_usd = $promo_code['discount_usd'] * $fraction;
                    $discount_ticket_manager = $promo_code['discount_manager'] * $fraction;
                    $discount_ticket_lottery = (float)Helpers_Currency::get_recalculated_to_given_currency(
                        $discount_ticket_user,
                        $this->user_currency_tab,
                        $lottery_currency_tab['code']
                    );
                    $discount_ticket_payment = (float)Helpers_Currency::get_recalculated_to_given_currency(
                        $discount_ticket_user,
                        $this->user_currency_tab,
                        $this->gateway_currency_tab['code']
                    );

                    $discount = $discount_ticket_user / $counted_lines;
                    $discount_usd = $discount_ticket_usd / $counted_lines;
                    $discount_manager = $discount_ticket_manager / $counted_lines;
                    $discount_lottery = $discount_ticket_lottery / $counted_lines;
                    $discount_payment = $discount_ticket_payment / $counted_lines;

                    $itm_price_user_curr_formatted = $itm_price_user_curr_formatted - $discount;
                    $itm_price_user_curr_formatted = round($itm_price_user_curr_formatted, 5);

                    $itm_price_lottery_formatted = $itm_price_lottery_formatted - $discount_lottery;
                    $itm_price_lottery_formatted = round($itm_price_lottery_formatted, 5);

                    $itm_price_usd_formatted = $itm_price_usd_formatted - $discount_usd;
                    $itm_price_usd_formatted = round($itm_price_usd_formatted, 5);

                    $itm_price_payment_formatted = $itm_price_payment_formatted - $discount_payment;
                    $itm_price_payment_formatted = round($itm_price_payment_formatted, 5);

                    $itm_price_manager_formatted = $itm_price_manager_formatted - $discount_manager;
                    $itm_price_manager_formatted = round($itm_price_manager_formatted, 5);
                }
                // Mulitiplied prices by counted lines
                $price_user_curr = $itm_price_user_curr_formatted * $counted_lines;
                $price_lottery = $itm_price_lottery_formatted * $counted_lines;
                $price_usd = $itm_price_usd_formatted * $counted_lines;
                $price_payment = $itm_price_payment_formatted * $counted_lines;
                $price_manager = $itm_price_manager_formatted * $counted_lines;

                $amount_user_curr_formatted = round($price_user_curr, 2);
                $amount_lottery_formatted = round($price_lottery, 2);
                $amount_usd_formatted = round($price_usd, 2);
                $amount_payment_formatted = round($price_payment, 2);
                $amount_manager_formatted = round($price_manager, 2);

                $amount_user = $amount_lottery = $amount_usd = $amount_payment = $amount_manager = 0;
                $amount_bonus_user = $amount_bonus_lottery = $amount_bonus_usd = $amount_bonus_payment = $amount_bonus_manager = 0;
                $income_lottery = $income_usd = $income_manager = $income_user_curr = 0;
                $bonus_cost_local = $bonus_cost_usd = $bonus_cost = $bonus_cost_manager = 0;
                $line_amount_local = $line_amount = $line_amount_usd = $line_amount_payment = $line_amount_manager = 0;
                $line_bonus_amount_local = $line_bonus_amount = $line_bonus_amount_usd = $line_bonus_amount_payment = $line_bonus_amount_manager = 0;

                $model = $lottery['model'];

                $is_insured = false;
                $tier = 0;
                $should_insure = Lotto_Helper::should_insure(
                    $lottery,
                    $lottery['tier'],
                    $lottery['volume']
                );
                if ($model == Helpers_General::LOTTERY_MODEL_MIXED && $should_insure) {
                    $is_insured = true;
                    $tier = $lottery['tier'];
                }

                $calc_cost = Lotto_Helper::get_price(
                    $lottery,
                    $lottery['model'],
                    $lottery['tier'],
                    $lottery['volume']
                );

                $cost_lottery_full = $calc_cost[0] + $calc_cost[1];
                $cost_lottery = $cost_lottery_full * $counted_lines * $multiplier;
                $cost_lottery_formatted = $cost_lottery;
                $cost_usd = Helpers_Currency::convert_to_USD($cost_lottery, $lottery['currency']);
                $cost_usd_formatted = $cost_usd;
                $cost_manager = Helpers_Currency::get_recalculated_to_given_currency(
                    $cost_usd,
                    $this->system_currency_tab,
                    $this->manager_currency_tab['code'],
                    2
                );
                $cost_manager_formatted = $cost_manager;
                $cost_user_curr = Helpers_Currency::get_recalculated_to_given_currency(
                    $cost_lottery,
                    $lottery_currency_tab,
                    $this->user_currency_tab['code'],
                    2
                );
                $cost_user_curr_formatted = $cost_user_curr;

                $income_lottery = $price_lottery - $cost_lottery;
                $income_lottery_formatted = $income_lottery;
                $income_usd = $price_usd - $cost_usd;
                $income_usd_formatted = $income_usd;
                $income_manager = Helpers_Currency::get_recalculated_to_given_currency(
                    $income_usd,
                    $this->system_currency_tab,
                    $this->manager_currency_tab['code'],
                    2
                );
                $income_manager_formatted = $income_manager;
                $income_user_curr = $price_user_curr - $cost_user_curr;
                $income_user_curr_formatted = $income_user_curr;

                $income_value = $lottery['income'];
                $income_type = $lottery['income_type'];

                $marginValue = $this->whitelabel['margin'];

                $whitelabel_margin = $marginValue / 100;
                $marginInLotteryCurrency = round($income_lottery * $whitelabel_margin, 2);
                $marginInUsd = round($income_usd * $whitelabel_margin, 2);
                $marginInUserCurrency = round($income_user_curr * $whitelabel_margin, 2);
                $marginInManagerCurrency = round($income_manager * $whitelabel_margin, 2);


                if ($marginInLotteryCurrency <= 0) {
                    $marginInLotteryCurrency = round($cost_lottery * $whitelabel_margin, 2);
                    $marginInUsd = round($cost_usd * $whitelabel_margin, 2);
                    $marginInUserCurrency = round($cost_user_curr * $whitelabel_margin, 2);
                    $marginInManagerCurrency = round($cost_manager * $whitelabel_margin, 2);
                }

                ///////// end of price calculations ///////////

                // Multi-draw IF
                if ($isMultiDraw) {
                    $multi_draw_option = $multi_draw;

                    /**
                     * Cut-off time override for Keno - it should not be considered during merge
                     * All lotteries including keno should include option to set cutoff time
                     */
                    $isMultiDrawFixEnabledForKenoWithCutOff = array_key_exists((int)$lottery['id'], self::KENO_WITH_CUT_OFF_TIME_IN_MINUTES);
                    if ($isMultiDrawFixEnabledForKenoWithCutOff) {
                        $now = Carbon::now($lottery['timezone']);
                        $drawDate = Carbon::parse($lottery['next_date_local'], $lottery['timezone']);
                        $kenoCutOffInMinutes = self::KENO_WITH_CUT_OFF_TIME_IN_MINUTES[(int)$lottery['id']];

                        $isTimePastCutOff = $now->diffInMinutes($drawDate) < $kenoCutOffInMinutes;
                        if ($isTimePastCutOff) {
                            // Get first and last draw dates
                            $first_draw = Lotto_Helper::get_lottery_next_draw($lottery, true, null, 2)->format(Helpers_Time::DATETIME_FORMAT);
                            $valid_to_draw = Lotto_Helper::get_lottery_next_draw($lottery, true, null, $tickets+1)->format(Helpers_Time::DATETIME_FORMAT);
                            $isLotteryClosed = true;
                        } else {
                            // Get first and last draw dates
                            $first_draw = Lotto_Helper::get_lottery_next_draw($lottery, true, null, 1)->format(Helpers_Time::DATETIME_FORMAT);
                            $valid_to_draw = Lotto_Helper::get_lottery_next_draw($lottery, true, null, $tickets)->format(Helpers_Time::DATETIME_FORMAT);
                            $isLotteryClosed = false;
                        }
                    } else {
                        // Get first and last draw dates
                        $first_draw = Lotto_Helper::get_lottery_next_draw($lottery, true, null, 1)->format(Helpers_Time::DATETIME_FORMAT);
                        $valid_to_draw = Lotto_Helper::get_lottery_next_draw($lottery, true, null, $tickets)->format(Helpers_Time::DATETIME_FORMAT);
                        $isLotteryClosed = Lotto_Helper::is_lottery_closed($lottery, null, $this->whitelabel);
                    }

                    $multidraw_token = Lotto_Security::generate_multidraw_token($this->whitelabel['id']);

                    $multi_draw_amount = 0;
                    $multi_draw_amount_usd = 0;
                    $multi_draw_amount_manager = 0;
                    $multi_draw_bonus_amount = 0;
                    $multi_draw_bonus_amount_usd = 0;
                    $multi_draw_bonus_amount_manager = 0;

                    $old_multi_draw_amount = $amount_user_curr_formatted;

                    $itm_price_lottery_formatted = $multi_draw_helper->calculate_single($multi_draw_option, $itm_price_lottery_formatted);
                    $itm_price_user_curr_formatted = $multi_draw_helper->calculate_single($multi_draw_option, $itm_price_user_curr_formatted);
                    $itm_price_usd_formatted = $multi_draw_helper->calculate_single($multi_draw_option, $itm_price_usd_formatted);
                    $itm_price_payment_formatted = $multi_draw_helper->calculate_single($multi_draw_option, $itm_price_payment_formatted);
                    $itm_price_manager_formatted = $multi_draw_helper->calculate_single($multi_draw_option, $itm_price_manager_formatted);

                    $amount_lottery_formatted = $multi_draw_helper->calculate_single($multi_draw_option, $amount_lottery_formatted);
                    $amount_user_curr_formatted = $multi_draw_helper->calculate_single($multi_draw_option, $amount_user_curr_formatted);
                    $amount_usd_formatted = $multi_draw_helper->calculate_single($multi_draw_option, $amount_usd_formatted);
                    $amount_payment_formatted = $multi_draw_helper->calculate_single($multi_draw_option, $amount_payment_formatted);
                    $amount_manager_formatted = $multi_draw_helper->calculate_single($multi_draw_option, $amount_manager_formatted);

                    if ($this->payment_type === Helpers_General::PAYMENT_TYPE_BONUS_BALANCE) {
                        $multi_draw_bonus_amount = $multi_draw_helper->calculate($multi_draw, $price_user_curr);
                        $multi_draw_bonus_amount_usd = $multi_draw_helper->calculate($multi_draw, $price_usd);
                        $multi_draw_bonus_amount_manager = $multi_draw_helper->calculate($multi_draw, $price_manager);
                    } else {
                        $multi_draw_amount = $multi_draw_helper->calculate($multi_draw, $price_user_curr);
                        $multi_draw_amount_usd = $multi_draw_helper->calculate($multi_draw, $price_usd);
                        $multi_draw_amount_manager = $multi_draw_helper->calculate($multi_draw, $price_manager);
                    }

                    // New multidraw record
                    $new_multi_draw = Model_Multidraw::forge();
                    $new_multi_draw->token = $multidraw_token;
                    $new_multi_draw->whitelabel_id = $this->whitelabel['id'];
                    $new_multi_draw->whitelabel_user_id = $this->user['id'];
                    $new_multi_draw->whitelabel_transaction_id = $transaction->id;
                    $new_multi_draw->lottery_id = $lottery['id'];
                    $new_multi_draw->tickets = $multi_draw['tickets'];
                    $new_multi_draw->first_draw = $first_draw;
                    $new_multi_draw->valid_to_draw = $valid_to_draw;
                    $new_multi_draw->current_draw = $first_draw;
                    $new_multi_draw->date = DB::expr("NOW()");
                    $new_multi_draw->currency_id = $this->user_currency_tab['id'];
                    $new_multi_draw->amount = $multi_draw_amount;
                    $new_multi_draw->amount_usd = $multi_draw_amount_usd;
                    $new_multi_draw->amount_manager = $multi_draw_amount_manager;
                    $new_multi_draw->bonus_amount = $multi_draw_bonus_amount;
                    $new_multi_draw->bonus_amount_usd = $multi_draw_bonus_amount_usd;
                    $new_multi_draw->bonus_amount_manager = $multi_draw_bonus_amount_manager;
                    $new_multi_draw->discount = $multi_draw['discount'];
                    $new_multi_draw->old_ticket_price = $old_multi_draw_amount;
                    $multi_draw = $new_multi_draw->save();

                    $income_usd = $amount_usd_formatted - $cost_usd;
                    $income_manager = Helpers_Currency::get_recalculated_to_given_currency(
                        $income_usd,
                        $this->system_currency_tab,
                        $this->manager_currency_tab['code'],
                        2
                    );
                    $income_lottery = $amount_lottery_formatted - $cost_lottery_formatted;
                    $income_user_curr = $amount_user_curr_formatted - $cost_user_curr_formatted;

                    $marginInLotteryCurrency = round($income_lottery * $whitelabel_margin, 2);
                    $marginInUsd = round($income_usd * $whitelabel_margin, 2);
                    $marginInUserCurrency = round($income_user_curr * $whitelabel_margin, 2);
                    $marginInManagerCurrency = round($income_manager * $whitelabel_margin, 2);

                    // Add log
                    Model_Multidraw_Log::add_multidraw_log(
                        $new_multi_draw->id,
                        Model_Multidraw_Log::MULTIDRAW_LOG_STATUS_BUY,
                        'Multi-draw purchase',
                        [
                            'user_id' => $this->user['id'],
                            'multi_draw_option' => $multi_draw_option['id'],
                            'tickets' => $multi_draw_option['tickets'],
                            'discount' => $multi_draw_option['discount'],
                            'lottery' => $lottery['name']
                        ]
                    );
                } else {
                    $isLotteryClosed = Lotto_Helper::is_lottery_closed($lottery, null, $this->whitelabel);
                }

                if ($marginInLotteryCurrency <= 0) {
                    $marginInLotteryCurrency = round($cost_lottery * $whitelabel_margin, 2);
                    $marginInUsd = round($cost_usd * $whitelabel_margin, 2);
                    $marginInUserCurrency = round($cost_user_curr * $whitelabel_margin, 2);
                    $marginInManagerCurrency = round($cost_manager * $whitelabel_margin, 2);
                }

                if ($this->isPromoCodeBonusTypeDiscount()) {
                    $incomeToMarginInLotteryCurrency = max($lottery['income'], $lottery['minimum_expected_income']);
                    $incomeToMarginInUsd = Helpers_Currency::convert_to_USD($incomeToMarginInLotteryCurrency, $lottery['currency']);
                    $incomeToMarginInManagerCurrency = Helpers_Currency::get_recalculated_to_given_currency(
                        $incomeToMarginInUsd,
                        $this->system_currency_tab,
                        $this->manager_currency_tab['code'],
                        2
                    );
                    $incomeToMarginInUserCurrency = Helpers_Currency::get_recalculated_to_given_currency(
                        $incomeToMarginInUsd,
                        $this->system_currency_tab,
                        $this->user_currency_tab['code'],
                        2
                    );

                    $marginInLotteryCurrency = round($incomeToMarginInLotteryCurrency * $whitelabel_margin * $counted_lines, 2);
                    $marginInUsd = round($incomeToMarginInUsd * $whitelabel_margin * $counted_lines, 2);
                    $marginInUserCurrency = round($incomeToMarginInUserCurrency * $whitelabel_margin * $counted_lines, 2);
                    $marginInManagerCurrency = round($incomeToMarginInManagerCurrency * $whitelabel_margin * $counted_lines, 2);
                }

                $isGgrLottery = Helpers_lottery::isGgrEnabled($lottery['type']);
                if ($isGgrLottery) {
                    $marginValue = 0;
                    $marginInLotteryCurrency = 0.00;
                    $marginInUsd = 0.00;
                    $marginInUserCurrency = 0.00;
                    $marginInManagerCurrency = 0.00;
                }

                if ($this->payment_type === Helpers_General::PAYMENT_TYPE_BONUS_BALANCE) {
                    $amount_bonus_user = $amount_user_curr_formatted;
                    $amount_bonus_lottery = $amount_lottery_formatted;
                    $amount_bonus_usd = $amount_usd_formatted;
                    $amount_bonus_payment = $amount_payment_formatted;
                    $amount_bonus_manager = $amount_manager_formatted;

                    $line_bonus_amount_local = $itm_price_lottery_formatted;
                    $line_bonus_amount = $itm_price_user_curr_formatted;
                    $line_bonus_amount_usd = $itm_price_usd_formatted;
                    $line_bonus_amount_payment = $itm_price_payment_formatted;
                    $line_bonus_amount_manager = $itm_price_manager_formatted;

                    $income_lottery = -1 * $cost_lottery;
                    $income_usd = -1 * $cost_usd;
                    $income_manager = -1 * $cost_manager;
                    $income_user_curr = -1 * $cost_user_curr;

                    $bonus_cost_local = $cost_lottery;
                    $bonus_cost_usd = $cost_usd;
                    $bonus_cost = $cost_user_curr;
                    $bonus_cost_manager = $cost_manager;
                } else {
                    $amount_user = $amount_user_curr_formatted;
                    $amount_lottery = $amount_lottery_formatted;
                    $amount_usd = $amount_usd_formatted;
                    $amount_payment = $amount_payment_formatted;
                    $amount_manager = $amount_manager_formatted;

                    $line_amount_local = $itm_price_lottery_formatted;
                    $line_amount = $itm_price_user_curr_formatted;
                    $line_amount_usd = $itm_price_usd_formatted;
                    $line_amount_payment = $itm_price_payment_formatted;
                    $line_amount_manager = $itm_price_manager_formatted;
                }

                $income_lottery_formatted = $income_lottery;
                $income_usd_formatted = $income_usd;
                $income_manager_formatted = $income_manager;
                $income_user_curr_formatted = $income_user_curr;

                $bonus_cost_local_formatted = $bonus_cost_local;
                $bonus_cost_usd_formatted = $bonus_cost_usd;
                $bonus_cost_manager_formatted = $bonus_cost_manager;
                $bonus_cost_user_curr_formatted = $bonus_cost;

                for ($i2 = 1; $i2 <= $tickets; $i2++) {
                    $ticket_token = Lotto_Security::generate_ticket_token($this->whitelabel['id']);

                    $lottery_type_id = $lottery_type[$i]['id'];

                    if (!$isMultiDraw) {
                        $ticket_d_date = $ticket_draw_date[$i]->format(Helpers_Time::DATETIME_FORMAT);
                    } else {
                        $multi_draw_next_draw_iteration = $i2 + (int)$isLotteryClosed;

                        $draw_date = Lotto_Helper::get_lottery_next_draw($lottery, true, null, $multi_draw_next_draw_iteration);
                        $ticket_d_date = $draw_date->format(Helpers_Time::DATETIME_FORMAT);
                    }

                    $pnl_update_query = DB::query(
                        "UPDATE whitelabel_user 
                        SET pnl_manager = COALESCE(pnl_manager, 0) + :amount, 
                        total_net_income_manager = COALESCE(total_net_income_manager, 0) + :amount, 
                        last_update = NOW()
                        WHERE whitelabel_user.id = :user_id"
                    );
                    $pnl_update_query->param(":amount", $income_manager_formatted);
                    $pnl_update_query->param(":user_id", $user->id);

                    $ticket_set = [
                        'token' => $ticket_token,
                        'whitelabel_transaction_id' => $transaction->id,
                        'whitelabel_id' => $this->whitelabel['id'],
                        'whitelabel_user_id' => $this->user['id'],
                        'lottery_id' => $item['lottery'],
                        'lottery_type_id' => $lottery_type_id,
                        'currency_id' => $this->user_currency_tab['id'],
                        'draw_date' => $ticket_d_date,
                        'valid_to_draw' => $ticket_d_date,
                        'amount_local' => $amount_lottery,
                        'amount' => $amount_user,
                        'amount_usd' => $amount_usd,
                        'amount_payment' => $amount_payment,
                        'amount_manager' => $amount_manager,
                        'date' => DB::expr("NOW()"),
                        'status' => Helpers_General::TICKET_STATUS_PENDING,
                        'paid' => Helpers_General::TICKET_UNPAID,
                        'payout' => Helpers_General::TICKET_PAYOUT_PENDING,
                        'model' => $lottery['model'],
                        'is_insured' => $is_insured,
                        'tier' => $tier,
                        'cost_local' => $cost_lottery_formatted,
                        'cost_usd' => $cost_usd_formatted,
                        'cost' => $cost_user_curr_formatted,
                        'cost_manager' => $cost_manager_formatted,
                        'income_local' => $income_lottery_formatted,
                        'income_usd' => $income_usd_formatted,
                        'income' => $income_user_curr_formatted,
                        'income_value' => $income_value,
                        'income_manager' => $income_manager_formatted,
                        'income_type' => $income_type,
                        'margin_value' => $marginValue,
                        'margin_local' => $marginInLotteryCurrency,
                        'margin_usd' => $marginInUsd,
                        'margin' => $marginInUserCurrency,
                        'margin_manager' => $marginInManagerCurrency,
                        'bonus_amount_local' => $amount_bonus_lottery,
                        'bonus_amount_usd' => $amount_bonus_usd,
                        'bonus_amount' => $amount_bonus_user,
                        'bonus_amount_manager' => $amount_bonus_manager,
                        'bonus_cost_local' => $bonus_cost_local_formatted,
                        'bonus_cost_usd' => $bonus_cost_usd_formatted,
                        'bonus_cost' => $bonus_cost_user_curr_formatted,
                        'bonus_cost_manager' => $bonus_cost_manager_formatted,
                        'ip' => $ip,
                        'line_count' => count($lines),
                        'has_ticket_scan' => Helpers_General::ticket_scan_availability($this->whitelabel, $lottery) ? $lottery['scans_enabled'] : 0
                    ];

                    if ($isMultiDraw) {
                        $ticket_set['multi_draw_id'] = $multi_draw[0];
                    }


                    $user->save();
                    $pnl_update_query->execute();


                    $ticket = Model_Whitelabel_User_Ticket::forge();
                    $ticket->set($ticket_set);
                    $ticket->save();

                    //do additional processing according to lottery type
                    switch ($lottery['type']) {
                        case Helpers_Lottery::TYPE_KENO:
                            $multiplier_id = $multipliers[$lottery['id']][$multiplier]['id']; // TODO: select id from lottery_type_multiplier where multiplier = $multiplier and type like 'keno'
                            $numbers_per_line = $item['numbers_per_line'];
                            $keno_data_set = [
                                'whitelabel_user_ticket_id' => $ticket['id'],
                                'lottery_type_multiplier_id' => $multiplier_id,
                                'numbers_per_line' => $numbers_per_line,
                            ];
                            $keno_data = Model_Whitelabel_User_Ticket_Keno_Data::forge();
                            $keno_data->set($keno_data_set);
                            $keno_data->save();
                            break;
                        default:
                            break;
                    }

                    foreach ($lines as $line) {
                        $numbers_temp = $line['numbers'];
                        $bnumbers_temp = $line['bnumbers'];
                        asort($numbers_temp);
                        asort($bnumbers_temp);
                        $numbers = implode(',', $numbers_temp);
                        $bnumbers = implode(',', $bnumbers_temp);

                        $ticket_line_set = [
                            'whitelabel_user_ticket_id' => $ticket->id,
                            'numbers' => $numbers,
                            'bnumbers' => $bnumbers,
                            "amount_local" => $line_amount_local,
                            'amount' => $line_amount,
                            'amount_usd' => $line_amount_usd,
                            'amount_payment' => $line_amount_payment,
                            'amount_manager' => $line_amount_manager,
                            "bonus_amount_local" => $line_amount_local,
                            'bonus_amount' => $line_bonus_amount,
                            'bonus_amount_usd' => $line_bonus_amount_usd,
                            'bonus_amount_payment' => $line_bonus_amount_payment,
                            'bonus_amount_manager' => $line_bonus_amount_manager,
                            'status' => Helpers_General::TICKET_STATUS_PENDING,
                            'payout' => Helpers_General::TICKET_PAYOUT_PENDING
                        ];

                        $ticket_line = Model_Whitelabel_User_Ticket_Line::forge();
                        $ticket_line->set($ticket_line_set);
                        $ticket_line->save();
                    }

                    $income_total = $income_total + $income_user_curr;
                    $income_usd_total = $income_usd_total + $income_usd;
                    $income_manager_total = $income_manager_total + $income_manager;

                    $cost_total = $cost_total + $cost_user_curr;
                    $cost_usd_total = $cost_usd_total + $cost_usd;
                    $cost_manager_total = $cost_manager_total + $cost_manager;

                    $marginTotal += $marginInUserCurrency;
                    $marginTotalInUsd += $marginInUsd;
                    $marginTotalInManagerCurrency += $marginInManagerCurrency;
                }
                $i++;
            }

            $income_total_formatted = round($income_total, 2);
            $income_usd_total_formatted = round($income_usd_total, 2);
            $income_manager_total_formatted = round($income_manager_total, 2);

            $transaction_rest_set = [
                "income" => $income_total_formatted,
                "income_usd" => $income_usd_total_formatted,
                "income_manager" => $income_manager_total_formatted,
                "cost" => $cost_total,
                "cost_usd" => $cost_usd_total,
                "cost_manager" => $cost_manager_total,
                "margin" => $marginTotal,
                "margin_usd" => $marginTotalInUsd,
                "margin_manager" => $marginTotalInManagerCurrency
            ];
            $transaction->set($transaction_rest_set);
            Session::set("transaction", $transaction->id);

            $this->transaction = $transaction;

            DB::commit_transaction();
        } catch (Exception $e) {
            DB::rollback_transaction();

            $fileLoggerService = Container::get(FileLoggerService::class);
            $fileLoggerService->error('An error has occurred while processing order from basket form: ' . $e->getMessage());

            $error_data = $e->getMessage() . "\nFILE: " . $e->getFile() . "\nLINE: " . $e->getLine() . "\nTRACE: " . $e->getTraceAsString();

            Model_Payment_Log::add_log(
                Helpers_General::TYPE_ERROR,
                null,
                null,
                null,
                $this->whitelabel['id'],
                null,
                "Something unusual went wrong (Debug: X1).",
                [$error_data]
            );

            $this->errors = ['payment' => _("An error has occurred while processing your order. Please contact us.")];

            return self::RESULT_WITH_ERRORS;
        }

        return self::RESULT_OK;
    }

    public function processPromoCode(): void
    {
        if ($this->promoCodeForm) {
            $this->promoCodeForm->process_content();

            $this->promoCodeDiscountActive = $this->promoCodeForm->isDiscountPromoCodeApplicable()
                && !$this->promoCodeForm->hasErrors();
        }
    }

    public function isPromoCodeBonusTypeDiscount(): bool
    {
        return $this->promoCodeForm && $this->promoCodeForm->isPromoCodeBonusTypeDiscount();
    }

    public function usePromoCodeForWhitelabelTransaction(int $transactionId): void
    {
        if ($this->promoCodeForm) {
            if ($this->promoCodeForm->isPromoCodeBonusTypeFreeLine() || $this->promoCodeDiscountActive) {
                $this->promoCodeForm->useForWhitelabelTransaction($transactionId);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function getUserSelectedPaymentCurrencyTab(): array
    {
        $userSelectedCurrency = $this->input_post['payment']['userSelectedCurrency'];
        $paymentMethodService = $this->selectUserPaymentCurrency($userSelectedCurrency);
        $paymentCurrency = Helpers_Currency::findCurrencyById($this->whitelabel_payment_method_id);

        return Helpers_Currency::get_mtab_currency(
            true,
            $paymentCurrency['code'],
            $paymentMethodService->getCurrencyId(),
        );
    }
}

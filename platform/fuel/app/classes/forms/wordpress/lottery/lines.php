<?php

use Fuel\Core\Validation;
use Helpers\FlashMessageHelper;
use Repositories\CartRepository;
use Services\CartService;
use Helpers_Lottery;

/**
 * Description of Forms_Wordpress_Lottery_Lines
 */
final class Forms_Wordpress_Lottery_Lines extends Forms_Main
{
    use Traits_Checks_Block_Ltech;

    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @var array
     */
    private $lotteries;

    /**
     *
     * @var array
     */
    private $errors = [];
    private $pricing;
    private $lottery;
    private $lines;
    private $lottery_type;
    private CartService $cartService;

    /**
     *
     * @param array $whitelabel
     * @param array $lotteries
     */
    public function __construct($whitelabel, $lotteries)
    {
        $this->whitelabel = $whitelabel;
        $this->lotteries = $lotteries;
        $this->cartService = new CartService(
            Container::get(CartRepository::class),
        );
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
     * @param array $errors
     */
    public function set_errors($errors): void
    {
        $this->errors = $errors;
    }

    /**
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $validation = Validation::forge();

        $validation->add("order.lines", _("Lines"))
            ->add_rule("trim")
            ->add_rule('stripslashes')
            ->add_rule("required")
            ->add_rule('valid_string', ['numeric', 'punctuation', 'dashes']);

        $validation->add("order.lottery", _('Lottery'))
            ->add_rule("trim")
            ->add_rule('stripslashes')
            ->add_rule("required")
            ->add_rule("valid_string", ["numeric"]);

        $validation->add("order.numbers_per_line", _('Numbers per line'))
            ->add_rule("numeric_min", 1);

        $validation->add("order.ticket_multiplier", _('Multiplier'))
            ->add_rule("numeric_min", 1);

        $validation->add("order.multidraw_enabled", _('Is multidraw enabled'))
            ->add_rule('numeric_min', 0)
            ->add_rule('numeric_max', 1);

        return $validation;
    }

    /**
     * TODO: Return int value needed instead of Redirect
     *
     * @return array
     */
    private function process_lines(): array
    {
        $order = [];

        foreach ($this->lines as $line) {
            $num_data = explode('-', $line);

            if (empty($num_data)) {
                $message_text = _('Unknown error! Please contact us!') . " (A2)";
                Session::set("message", ["error", $message_text]);

                Response::redirect(lotto_platform_get_permalink_by_slug('order'));
            }

            // check if count equals to lottery data
            if (!(count($num_data) > 0 && count($num_data) <= 2)) {
                $message_text = _('Security error! Please contact us!') . '5';
                Session::set("message", ["error", $message_text]);

                Response::redirect(lotto_platform_get_permalink_by_slug('order'));
                break;
            }

            $numbers = explode('_', $num_data[0]);

            if (empty($numbers)) {
                $message_text = _('Unknown error! Please contact us!') . " (A3)";
                Session::set("message", ["error", $message_text]);

                Response::redirect(lotto_platform_get_permalink_by_slug('order'));
            }

            $bnumbers = [];
            if (count($num_data) == 2) {
                $bnumbers = explode('_', $num_data[1]);
            }

            $numc = array_unique(array_values($numbers));
            $bnumc = array_unique(array_values($bnumbers));

            if (!((count($numc) == $this->ltype['ncount'] || count($numc) == $this->ltype['numbers_per_line']) &&
                ($this->ltype['bextra'] == 0 && count($bnumc) == $this->ltype['bcount']) ||
                ($this->ltype['bextra'] > 0 && count($bnumc) == 0))
            ) {
                $message_text = _('Security error! Please contact us!') . '6';
                Session::set("message", ["error", $message_text]);

                Response::redirect(lotto_platform_get_permalink_by_slug('order'));
                break;
            }

            $ferror = false;
            foreach ($numbers as $num) {
                if (intval($num) < 1 || intval($num) > intval($this->ltype['nrange'])) {
                    $ferror = true;
                    break;
                }
            }

            foreach ($bnumbers as $num) {
                if (intval($num) < 1 || intval($num) > intval($this->ltype['brange'])) {
                    $ferror = true;
                    break;
                }
            }

            if ($ferror) {
                $message_text = _('Security error! Please contact us!') . '7';
                Session::set("message", ["error", $message_text]);

                Response::redirect(lotto_platform_get_permalink_by_slug('order'));
                break;
            }

            $order[] = [
                'numbers' => $numbers,
                'bnumbers' => $bnumbers,
            ];
        }

        return $order;
    }

    /**
     * TODO: Return int value needed instead of Redirect
     */
    private function fb_process(): void
    {
        // Check if specific Whitelabel's l-tech account is blocked for this lottery
        if ($this->is_ltech_blocked_for_this_lottery($this->lottery)) {
            Session::set("message", ["error", _("Internal error. Please contact support.")]);
            Response::redirect(lotto_platform_home_url('/'));
        }

        $ticket_type = Input::post("ticket_type");
        $multi_draw_type = Input::post("multi_draw_type");
        $order = $this->process_lines();
        $ticket_multiplier = $this->ltype['ticket_multiplier'];
        $numbers_per_line = $this->ltype['numbers_per_line'] ?? null;

        // This is strange but this variable is nowhere set to be different then empty
        if (empty($this->get_errors())) {
            $pos_order = Lotto_Helper::get_possible_order();
            $pos_order_cnt = Lotto_Helper::get_possible_order_count();
            $total_add_price = round($this->pricing * count($order), 2);

            if (!($pos_order_cnt > 0 && $total_add_price <= $pos_order)) {
                $message_text = _('Failed to add more tickets, you have reached the maximum order!');
                Session::set("message", ["error", $message_text]);

                Response::redirect(lotto_platform_get_permalink_by_slug('order'));
            }

            $multi_draw = null;

            $add = [
                'lottery' => $this->lottery['id'],
                'lines' => $order,
                'ticket_multiplier' => $ticket_multiplier
            ];

            if ($numbers_per_line !== null) {
                $add['numbers_per_line'] = $numbers_per_line;
            }

            if ($this->lottery['is_multidraw_enabled'] && $this->lottery['multidraws_enabled']
                && !empty($ticket_type) && !empty($multi_draw_type)
                && $ticket_type == Helpers_General::ORDER_TICKET_MULTIDRAW) {
                $multidraw_error = false;

                $multi_draw_insert = [
                    $ticket_type,
                    $multi_draw_type
                ];

                $multi_draw_helper = new Helpers_Multidraw($this->whitelabel);
                $multi_draw = $multi_draw_helper->check_multidraw($multi_draw_insert);

                if (!empty($multi_draw['tickets'])) {
                    $add['multidraw'] = $multi_draw_insert;
                } else {
                    $multidraw_error = true;
                }

                if ($multidraw_error) {
                    $message_text = _(
                        'There is something wrong with your order. Please contact us in order to resolve the issue!'
                    );
                    Session::set("message", ["error", $message_text]);

                    Response::redirect(lotto_platform_get_permalink_by_slug('order'));
                }
            }

            $basket = Session::get("order");
            if ($basket == null) {
                $basket = [];
            }
            $basket[] = $add;

            Session::set("order", $basket);

            $userId = lotto_platform_user_id();
            if ($userId) {
                $this->cartService->createOrUpdateCart($userId, $basket);
            }

//          Removed direct Facebook Pixel call; the event is now sent via GTM
//            $user_currency_code = lotto_platform_user_currency();
//
//            \Fuel\Core\Event::trigger('user_cart_add', [
//                'whitelabel_id' => $this->whitelabel['id'],
//                'user_id' => $userId,
//                'plugin_data' => ["items" => [[
//                    "id" => $this->whitelabel['prefix'] . '_' . Lotto_Helper::get_lottery_short_name($this->lottery) . '_TICKET',
//                    "name" => $this->lottery['name'],
//                    "list_name" => "Play",
//                    "quantity" => count($this->lines),
//                    "price" => $this->pricing,
//                    "currency" => $user_currency_code
//                ]]],
//            ]);

            Session::set("message", ["success", _('The ticket has been added to your order!')]);
            Session::set('ticket_added', true);

            Response::redirect(lotto_platform_get_permalink_by_slug('order'));
        }
    }

    /**
     *
     * @return int
     */
    public function process_form(): int
    {
        $validated_form = $this->validate_form();

        if ($validated_form->run()) {
            if (!isset($this->lotteries['__by_id'][$validated_form->validated("order.lottery")])) {
                $message_text = _('Security error! Please contact us!');
                $message_text .= '3';
                Session::set("message", ["error", $message_text]);

                return 3;
            }

            $lottery = $this->lotteries['__by_id'][$validated_form->validated("order.lottery")];
            $this->lottery = &$lottery;
            if (Lotto_Helper::is_lottery_closed($lottery, null, $this->whitelabel)) {
                $this->ltype = Model_Lottery_Type::get_lottery_type_for_date(
                    $lottery,
                    Lotto_Helper::get_lottery_next_draw($lottery, true, null, 2)->format(Helpers_Time::DATETIME_FORMAT)
                );
            } else {
                $this->ltype = Model_Lottery_Type::get_lottery_type_for_date(
                    $lottery,
                    Lotto_Helper::get_lottery_next_draw($lottery)->format(Helpers_Time::DATETIME_FORMAT)
                );
            }

            if (is_null($numbers_per_line = $validated_form->validated('order.numbers_per_line')) === false) {
                $this->ltype['numbers_per_line'] = $numbers_per_line;
                if (in_array($numbers_per_line, Lotto_Helper::get_numbers_per_line_array($lottery['id'])) === false) {
                    throw new Exception("Invalid numbers_per_line value.");
                }
            }
            $ticket_multiplier = $validated_form->validated('order.ticket_multiplier');
            if ($ticket_multiplier !== null) {
                $multipliers = Model_Lottery_Type_Multiplier::min_max_for_lottery($lottery['id']);
                if ($ticket_multiplier < (int)$multipliers['min'] || $ticket_multiplier > (int)$multipliers['max']) {
                    throw new Exception("Invalid ticket_multiplier value.");
                }
            } else {
                $ticket_multiplier = 1;
            }
            $this->ltype['ticket_multiplier'] = $ticket_multiplier;

            $this->pricing = lotto_platform_get_pricing($lottery, $ticket_multiplier);;
            $lines = explode(';', $validated_form->validated('order.lines'));
            $this->lines = &$lines;

            if (empty($lines)) {
                $message_text = _('Unknown error! Please contact us!');
                $message_text .= " (A1)";
                Session::set("message", ["error", $message_text]);

                return 10;
            }

            if (
                !$validated_form->validated('order.multidraw_enabled') &&
                ((count($lines) - 1) % $lottery['max_bets']) < ($lottery['min_bets'] - 1)
            ) {
                $msg = _('You need to choose at least %d lines for every %d-lines ticket!');
                $message_text = sprintf($msg, 2, 8);
                Session::set("message", ["error", $message_text]);

                return 8;
            }

            $isMiniLottery = in_array($validated_form->validated('order.lottery'), array_keys(Helpers_Lottery::MINI_LOTTERIES));
            $minimumLinesRequired = $validated_form->validated('order.multidraw_enabled') 
                ? ($isMiniLottery ? 10 : 1)
                : $lottery['min_lines'];
            $hasEnoughLines = count($lines) >= $minimumLinesRequired;
            $isMultiplierValid = $lottery['multiplier'] == 0 || (count($lines) % $lottery['multiplier'] == 0);
            if (!($hasEnoughLines && $isMultiplierValid)) {
                FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, 'You should choose minimum ' . $lottery['min_lines'] . ' lines.');
                return 4;
            }

            $this->fb_process();
        } else {
            $message_text = _('Security error! Please contact us!');
            $message_text .= '2';
            Session::set("message", ["error", $message_text]);

            return 2;
        }

        return 0;
    }
}

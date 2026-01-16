<?php

use Fuel\Core\Validation;
use Services\CartService;

/**
 * Description of Forms_Wordpress_Myaccount_Ticket_Playagain
 */
final class Forms_Wordpress_Myaccount_Ticket_Playagain extends Forms_Main
{
    const RESULT_ZERO_TICKET_LINES = 100;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var null|array
     */
    private $whitelabel = [];

    /**
     *
     * @var array
     */
    private $user = [];

    private CartService $cartService;

    /**
     *
     * @param array $whitelabel
     * @param array $user
     */
    public function __construct(array $whitelabel = null, array $user = null)
    {
        $this->whitelabel = $whitelabel;
        $this->user = $user;
        $this->cartService = Container::get(CartService::class);
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
     * @param array  $lottery
     * @param string $pricing
     * @param int    $lines_cnt
     */
    private function process_pixels($lottery, $pricing, $lines_cnt)
    {
        \Fuel\Core\Event::trigger('user_cart_add', [
            'whitelabel_id' => $this->whitelabel['id'],
            'user_id' => lotto_platform_is_user() ? lotto_platform_user()["id"] : null,
            'plugin_data' => ["items" => [[
                "id" => $this->whitelabel['prefix'] . '_' . Lotto_Helper::get_lottery_short_name($lottery) . '_TICKET',
                "name" => $lottery['name'],
                "list_name" => "Buy again",
                "quantity" => $lines_cnt,
                "price" => $pricing,
                "currency" => lotto_platform_user_currency()
            ]]],
        ]);
    }

    /**
     *
     * @return int
     */
    public function process_form(): int
    {
        $tickets = Model_Whitelabel_User_Ticket::find([
            "where" => [
                "whitelabel_id" => $this->whitelabel['id'],
                "token" => intval(get_query_var('id'))
            ]
        ]);

        if ($tickets === null || count($tickets) === 0) {
            $this->errors = [
                "details" => _("Incorrect ticket.")
            ];

            return self::RESULT_WITH_ERRORS;
        }

        $ticket = $tickets[0];

        if (!($ticket->whitelabel_id == $this->whitelabel['id'] &&
            $ticket->whitelabel_user_id == $this->user['id'] &&
            $ticket->status != Helpers_General::TICKET_STATUS_PENDING)
        ) {
            $this->errors = [
                "details" => _("Incorrect ticket.")
            ];

            return self::RESULT_WITH_ERRORS;
        }

        $lotteries = Model_Lottery::get_lotteries_for_whitelabel($this->whitelabel);

        if (!isset($lotteries["__by_id"][$ticket['lottery_id']]) ||
            $lotteries["__by_id"][$ticket['lottery_id']]['is_temporarily_disabled'] == 1) {
            $this->errors = [
                "details" => _("This lottery is no longer available.")
            ];

            return self::RESULT_WITH_ERRORS;
        }

        if (!isset($lotteries["__by_id"][$ticket['lottery_id']]) ||
            $lotteries["__by_id"][$ticket['lottery_id']]['playable'] != 1) {
            $this->errors = [
                "details" => _("This lottery is not playable.")
            ];

            return self::RESULT_WITH_ERRORS;
        }

        // I think that variable is not needed here based on rest of the code
        // within action_myaccount method within wordpress controller
        // $brandom = []

        $pos_order = Lotto_Helper::get_possible_order();
        $pos_order_cnt = Lotto_Helper::get_possible_order_count();

        $lottery = $lotteries["__by_id"][$ticket['lottery_id']];
        $pricing = lotto_platform_get_pricing($lottery);

        if (!($pos_order_cnt > 0 && $pricing <= $pos_order)) {
            $error_msg = _('Failed to add more tickets, you have reached the maximum order!');
            $this->errors = ["details" => $error_msg];

            return self::RESULT_WITH_ERRORS;
        }

        $lottery_type = Model_Lottery_Type::get_lottery_type_for_date(
            $lottery,
            Lotto_Helper::get_lottery_real_next_draw(
                $lottery,
                Lotto_Helper::is_lottery_closed($lottery, null, $this->whitelabel) ? 2 : 1
            )->format("Y-m-d")
        );

        if ((int)$ticket->lottery_type_id !== (int)$lottery_type['id']) {
            $error_msg = _("You cannot play with the same numbers because the lottery rules have changed.");
            $this->errors = ["details" => $error_msg];

            return self::RESULT_WITH_ERRORS;
        }

        $ticket_lines = Model_Whitelabel_User_Ticket_Line::find_by_whitelabel_user_ticket_id($ticket->id);

        if (count($ticket_lines) === 0) {
            return self::RESULT_ZERO_TICKET_LINES;
        }

        $order = Session::get("order");

        if (empty($order)) {
            $order = [];
        }

        $lines = [];
        foreach ($ticket_lines as $line) {
            $nums = explode(",", $line['numbers']);
            $bnums = !empty($line['bnumbers']) ? explode(',', $line['bnumbers']) : [];

            $lines[] = [
                'numbers' => $nums,
                'bnumbers' => $bnums
            ];
        }

        $ticket_in_order = [
            'lottery' => $lottery['id'],
            'lines' => $lines
        ];
        switch ($lottery['type']) {
            case Helpers_Lottery::TYPE_KENO:
                $keno_data = Model_Whitelabel_User_Ticket_Keno_Data::find_one_by('whitelabel_user_ticket_id', $ticket['id']);
                $ticket_in_order['ticket_multiplier'] = Model_Lottery_Type_Multiplier::find_by_pk($keno_data['lottery_type_multiplier_id'])['multiplier'];
                $ticket_in_order['numbers_per_line'] = $keno_data['numbers_per_line'];
                break;
            default:
                // For standard lotteries
                break;
        }
        array_push($order, $ticket_in_order);
        Session::set("order", $order);

        $this->cartService->createOrUpdateCart($this->user['id'], $order);

        // Removed direct Facebook Pixel call; the event is now sent via GTM
        //$this->process_pixels($lottery, $pricing, count($lines));

        $msg_txt = _("A ticket using the same numbers has been added to your order.");
        Session::set("message", ["success", $msg_txt]);

        return self::RESULT_OK;
    }
}

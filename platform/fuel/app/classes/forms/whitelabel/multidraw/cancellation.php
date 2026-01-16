<?php


/**
 *
 */
class Forms_Whitelabel_Multidraw_Cancellation
{
    /**
     * Get Trait for date range preparation
     */
    use Traits_Gets_Date;

    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @var array
     */
    private $multidraw;

    /**
     *
     * @var Model_Whitelabel_User|null
     */
    private $user;

    /**
     *
     * @var int
     */
    private $source;

    /**
     *
     * @var int
     */
    private $multidraw_helper;

    /**
     *
     * @var int
     */
    private $items_per_page = 25;

    /**
     *
     * @var View
     */
    private $inside = null;

    /**
     * Forms_Whitelabel_Multidraw_Cancellation constructor.
     *
     * @param string $token
     * @param array $whitelabel
     */
    public function __construct(string $token, array $whitelabel = [])
    {
        $this->whitelabel = $whitelabel;

        // Get multitdraw
        $multidraw_helper = new Helpers_Multidraw($this->whitelabel);
        $multidraw = $multidraw_helper->get_multidraw_for_cancellation($token);

        if (!$multidraw || empty($multidraw)) {
            Session::set_flash("message", ["danger", _("Multidraw doesn't exist")]);
            Response::redirect('multidraw_tickets');
        }

        $this->multidraw_helper = $multidraw_helper;
        $this->multidraw = $multidraw;

        // Get user
        $user = Model_Whitelabel_User::find_by_pk($multidraw['whitelabel_user_id']);
        $this->user = $user;
    }

    /**
     *
     * @return int
     */
    public function get_source(): int
    {
        return $this->source;
    }

    /**
     *
     * @return array
     */
    public function get_whitelabel():? array
    {
        return $this->whitelabel;
    }

    /**
     *
     * @return View
     */
    public function get_inside()
    {
        return $this->inside;
    }

    /**
     *
     * @return void
     */
    private function prepare_details(): void
    {
        $this->multidraw['token_with_prefix'] = $this->whitelabel['prefix'] .
            'M' .
            $this->multidraw['token'];
        $this->user['token'] = $this->whitelabel['prefix'] .
            'U' .
            $this->user['token'];
    }

    /**
     *
     * @param array $cancellation
     * @return array
     */
    private function prepare_amounts(array $cancellation): array
    {
        $cancellation['amount_md'] = Lotto_View::format_currency(
            $this->multidraw['amount'],
            $cancellation['currency'],
            true
        );

        $cancellation['amount'] = Lotto_View::format_currency(
            $cancellation['amount'],
            $cancellation['currency'],
            true
        );

        $cancellation['transaction_cost'] = Lotto_View::format_currency(
            $cancellation['transaction_cost'],
            $cancellation['currency'],
            true
        );

        $cancellation['single_ticket_amount'] = Lotto_View::format_currency(
            $cancellation['single_ticket_amount'],
            $cancellation['currency'],
            true
        );

        return $cancellation;
    }

    /**
     *
     * @param string $view_template
     * @return int
     */
    public function process_form(string $view_template, string $subaction)
    {
        $whitelabel = $this->get_whitelabel();

        // Prepare details
        $this->prepare_details();

        $ticket_cancellation = $this->multidraw_helper->calculate_single_cancellation($this->multidraw);

        $cancellation_details = $this->prepare_amounts($ticket_cancellation);

        $this->inside = View::forge($view_template);

        $this->inside->set('user', $this->user);
        $this->inside->set('multidraw', $this->multidraw);

        $this->inside->set('cancellation', $cancellation_details);

        if ($subaction == "confirm") {
            $this->confirm_cancellation($ticket_cancellation);
        }

        return true;
    }

    /**
     *
     * @param array $ticket_cancellation
     * @return void
     */
    private function confirm_cancellation(array $ticket_cancellation): void
    {
        // Add price to user balance in his currency
        Model_Whitelabel_User::update_balance(
            [$this->user['id']],
            (string)$ticket_cancellation['amount']
        );

        // Set tickets as cancelled status
        $where = [
            'multi_draw_id' => $this->multidraw['id']
        ];

        if (count($ticket_cancellation['processed_tickets_ids']) != 0) {
            $where[] = [
                'id',
                'NOT IN',
                $ticket_cancellation['processed_tickets_ids']
            ];
        }

        $unprocessed_tickets = Model_Whitelabel_User_Ticket::find([
            'where' => $where
        ]);

        foreach ($unprocessed_tickets as $ticket) {
            $ticket_set = [
                'status' => Helpers_General::TICKET_STATUS_CANCELED
            ];
            $ticket->set($ticket_set);
            $ticket->save();
        }

        $token = (string)$this->multidraw['token'];
        // Set multidraw as cancelled
        $multidraw_helper = $this->multidraw_helper->get_multidraw_for_cancellation($token);
        
        if (!$multidraw_helper || empty($multidraw_helper)) {
            Session::set_flash("message", ["danger", _("Multidraw doesn't exist")]);
            Response::redirect('multidraw_tickets');
        }
        
        $multidraw =
        $multidraw->is_finished = 1;
        $multidraw->is_cancelled = 1;
        $multidraw->save();
        
        Model_Multidraw_Log::add_multidraw_log(
            $multidraw['id'],
            Model_Multidraw_Log::MULTIDRAW_LOG_STATUS_SINGLE_CANCELLATION,
            'User multidraw cancellation',
            [
                'multi_draw_id' => $multidraw['id'],
                'cancellation_details' => $ticket_cancellation
            ]
        );

        $success_message = _("Multidraw has been successfully cancelled!");
        Session::set_flash("message", ["success", $success_message]);
        
        Response::redirect('multidraw_tickets');
    }
}

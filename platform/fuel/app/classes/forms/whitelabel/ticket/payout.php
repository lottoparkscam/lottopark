<?php

/**
 * Description of Forms_Whitelabel_Ticket_Payout
 */
final class Forms_Whitelabel_Ticket_Payout extends Forms_Main
{
    /**
     *
     * @var array
     */
    private $whitelabel = [];
    
    /**
     *
     * @var int|null
     */
    private $token = null;
    
    /**
     *
     * @var int
     */
    private $offset;
    
    /**
     *
     * @var string
     */
    private $redirect_url;
    
    /**
     *
     * @param int $token
     * @param array $whitelabel
     * @param int $offset
     */
    public function __construct(
        int $token = null,
        array $whitelabel = [],
        int $offset = null
    ) {
        $this->token = $token;
        $this->whitelabel = $whitelabel;
        $this->offset = $offset;
        $this->redirect_url = "";
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
     * @return int|null
     */
    public function get_token():? int
    {
        return $this->token;
    }

    /**
     *
     * @return int|null
     */
    public function get_offset():? int
    {
        return $this->offset;
    }
    
    /**
     *
     * @return string
     */
    public function get_redirect_url(): string
    {
        return $this->redirect_url;
    }

    /**
     *
     * @return int|null
     */
    public function process_form(): int
    {
        $whitelabel = $this->get_whitelabel();
        $token = $this->get_token();
        $offset = $this->get_offset();
        
        if (empty($whitelabel) || empty($token)) {
            return self::RESULT_WITH_ERRORS;
        }
        
        $this->redirect_url = 'tickets' . Lotto_View::query_vars();
        
        $result_line = Model_Whitelabel_User_Ticket_Line::get_single_by_ticket_token(
            $token,
            $offset
        );
        
        if (!($result_line !== null &&
            count($result_line) > 0 &&
            (int)$result_line['status'] === Helpers_General::TICKET_STATUS_WIN &&
            (int)$result_line['payout'] === Helpers_General::TICKET_PAYOUT_PENDING &&
            $result_line['lottery_type_data_id'] != null)
        ) {
            return self::RESULT_WITH_ERRORS;
        }
        
        $line = Model_Whitelabel_User_Ticket_Line::find_by_pk($result_line['id']);
        $type = Model_Lottery_Type_Data::find_by_pk($line->lottery_type_data_id);

        if (!($type !== null &&
                $type->is_jackpot == 0 &&
                (int)$type->type !== Helpers_General::LOTTERY_TYPE_DATA_QUICK_PICK)
        ) {
            return self::RESULT_WITH_ERRORS;
        }

        $ticket = Model_Whitelabel_User_Ticket::find_by_pk($line->whitelabel_user_ticket_id);

        if (!($ticket !== null &&
            (int)$ticket->whitelabel_id === (int)$whitelabel['id'] &&
            (int)$ticket->status === Helpers_General::TICKET_STATUS_WIN)
        ) {
            return self::RESULT_WITH_ERRORS;
        }

        $line_set = [
            'payout' => Helpers_General::TICKET_PAYOUT_PAIDOUT
        ];
        $line->set($line_set);
        $line->save();

        $user = Model_Whitelabel_User::find_by_pk($ticket['whitelabel_user_id']);
        $user_balance = $user['balance'];
        $balance = $user_balance + $line->prize;

        $user_set = [
            'balance' => $balance,
            'last_update' => DB::expr("NOW()")
        ];
        $user->set($user_set);
        $user->save();
        
        \Fuel\Core\Event::register(
            'whitelabel_ticket_payout',
            'Events_Whitelabel_Ticket_Payout::handle'
        );
        \Fuel\Core\Event::trigger('whitelabel_ticket_payout', [
            'whitelabel_id' => $whitelabel['id'],
            'user_id' => $user->id,
            'plugin_data' => [
                "last_balance_update" => time(),
                'balance' => $balance
            ],
        ]);

        $result = Model_Whitelabel_User_Ticket_Line::count_pending_by_ticket_id(
            $ticket->id
        );

        if (is_null($result)) {
            return self::RESULT_DB_ERROR;
        }

        $checklines = $result['count'];

        if ((int)$checklines === 0) {
            $ticket_set = [
                'payout' => Helpers_General::TICKET_PAYOUT_PAIDOUT
            ];
            $ticket->set($ticket_set);
            $ticket->save();
        }

        $this->redirect_url = 'tickets/view/' . $ticket->token . Lotto_View::query_vars();
        
        return self::RESULT_OK;
    }
}

<?php

/**
 * Description of Forms_Whitelabel_Ticket_Paidout
 */
final class Forms_Whitelabel_Ticket_Paidout extends Forms_Main
{
    const RESULT_PAIDOUT_PARTIALLY = 100;
    
    /**
     *
     * @var array
     */
    private $whitelabel = [];
    
    /**
     *
     * @var string|null
     */
    private $token = null;
    
    /**
     *
     * @var string
     */
    private $ticket_view_url = "";
    
    /**
     *
     * @param string|null $token
     * @param array $whitelabel
     */
    public function __construct(string $token = null, array $whitelabel = [])
    {
        $this->token = $token;
        $this->whitelabel = $whitelabel;
        
        $this->ticket_view_url = 'tickets/view/' . $this->token . Lotto_View::query_vars();
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
     * @return string|null
     */
    public function get_token():? string
    {
        return $this->token;
    }
    
    /**
     *
     * @return string
     */
    public function get_ticket_view_url(): string
    {
        return $this->ticket_view_url;
    }
    
    /**
     *
     * @return int
     */
    public function process_form(): int
    {
        $whitelabel = $this->get_whitelabel();
        $token = $this->get_token();
        
        if (empty($whitelabel) || empty($token)) {
            return self::RESULT_INCORRECT_TICKET;
        }
        
        $ticket_arr = Model_Whitelabel_User_Ticket::find_by([
            "whitelabel_id" => $whitelabel['id'],
            "token" => $token
        ]);
        
        if (!($ticket_arr !== null &&
            count($ticket_arr) > 0 &&
            (int)$ticket_arr[0]->whitelabel_id === (int)$whitelabel['id'] &&
            (int)$ticket_arr[0]->status === Helpers_General::TICKET_STATUS_WIN &&
            (int)$ticket_arr[0]->payout === Helpers_General::TICKET_PAYOUT_PENDING &&
            ($ticket_arr[0]->prize_net > 0 ||
                $ticket_arr[0]->prize_jackpot == 1))
        ) {
            return self::RESULT_INCORRECT_TICKET;
        }
        
        $ticket = $ticket_arr[0];
        
        // pay the lines
        // do not payout quickpick, it will be pay out automatically (with lottorisq)
        $res = Model_Whitelabel_User_Ticket_Line::update_payout_value(
            $ticket->id
        );
        
        if (empty($res) || $res !== 1) {
            return self::RESULT_INCORRECT_TICKET;
        }

        $res_count = Model_Whitelabel_User_Ticket_Line::count_pending_by_ticket_id(
            $ticket->id
        );

        // only mark as paid out if there is no more quick pick
        // lines left, so we will have this consistent
        // after quick pick payout it will auto-mark as paid out
        if ($res_count !== null &&
            count($res_count) > 0 &&
            $res_count['count'] == 0
        ) {
            $ticket_set = [
                'payout' => Helpers_General::TICKET_PAYOUT_PAIDOUT
            ];
            $ticket->set($ticket_set);
            $ticket->save();

            return self::RESULT_OK;
        }
        
        return self::RESULT_PAIDOUT_PARTIALLY;
    }
}

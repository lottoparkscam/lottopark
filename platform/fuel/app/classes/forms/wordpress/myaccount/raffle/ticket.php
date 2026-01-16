<?php

/**
 * Description of Forms_Wordpress_Myaccount_Ticket_Details
 */
class Forms_Wordpress_Myaccount_Raffle_Ticket extends Forms_Main
{
    /**
     * @var array
     */
    protected $errors = [];
    
    /**
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
     * @param array $whitelabel
     * @param array $user
     */
    public function __construct(array $whitelabel, array $user)
    {
        $this->whitelabel = $whitelabel;
        $this->user = $user;
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
     * @param View &$view
     * @return int
     */
    public function process_form(View &$view): int
    {
        $raffle_ticket = Model_Whitelabel_Raffle_Ticket::find(
            ['where' => [
                    'whitelabel_id' => $this->whitelabel['id'],
                    'token' => get_query_var('id')
                ]]
        )[0];
        
        if ($raffle_ticket === null) {
            $this->errors = ["details" => _("Incorrect ticket. Ticket not found.")];
            return self::RESULT_WITH_ERRORS;
        }
        
        $lines = Model_Whitelabel_Raffle_Ticket_Line::get_numbers_by_ticket_id($raffle_ticket['id']);
        
        if (is_null($lines) || count($lines) === 0) {
            $this->errors = ["details" => _("Incorrect ticket. Lines incorrect.")];
            return self::RESULT_WITH_ERRORS;
        }
        
        $raffle = Model_Raffle::find(['where' =>
            ['id' => $raffle_ticket['raffle_id']]
        ])[0];
        
        $currencies = Lotto_Settings::getInstance()->get("currencies");
        
        if (!empty($raffle_ticket->whitelabel_transaction_id)) {
            $transaction = Model_Whitelabel_Transaction::find([
                "where" => [
                    "whitelabel_id" => $this->whitelabel['id'],
                    "id" => $raffle_ticket->whitelabel_transaction_id
                ]
            ]);
            if ($transaction !== null && count($transaction) > 0) {
                $view->set('transaction', $transaction[0]);
            }
        }
        
        $view->set('ticket_type', 'raffle');
        $view->set('ticket', $raffle_ticket);
        $view->set('lines', $lines);
        $view->set('currencies', $currencies);
        $view->set('lottery', $raffle);
        
        return self::RESULT_OK;
    }
}

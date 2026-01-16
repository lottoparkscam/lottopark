<?php

/**
 * Description of Forms_Whitelabel_Transaction_Accept
 */
class Forms_Whitelabel_Transactions_Accept extends Forms_Main
{
    /**
     * This is in fact token
     *
     * @var int
     */
    private $param_id;
    
    /**
     *
     * @var type
     */
    private $whitelabel = [];
    
    /**
     *
     * @var int
     */
    private $source;
    
    /**
     *
     * @var string
     */
    private $rparam = "";
    
    /**
     *
     * @param int $source
     * @param int $param_id
     * @param array $whitelabel
     * @param string $rparam
     */
    public function __construct($source, $param_id, $whitelabel, $rparam)
    {
        if (!empty($source) && $source == Helpers_General::SOURCE_ADMIN) {
            if (Input::get("filter.whitelabel") != null &&
                Input::get("filter.whitelabel") != "a"
            ) {
                $whitelabel = [];
                $whitelabel['id'] = intval(Input::get("filter.whitelabel"));
            }
        }
        
        $this->source = $source;
        $this->param_id = $param_id;
        $this->whitelabel = $whitelabel;
        $this->rparam = $rparam;
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
    public function get_source(): int
    {
        return $this->source;
    }
    
    /**
     *
     * @return string
     */
    public function get_rparam(): string
    {
        return $this->rparam;
    }
    
    /**
     *
     * @return int
     */
    public function get_param_id(): int
    {
        return $this->param_id;
    }
    
    /**
     *
     * @param Model_Whitelabel_Transaction $transaction
     */
    private function process_tickets(Model_Whitelabel_Transaction $transaction): void
    {
        $tickets = Model_Whitelabel_User_Ticket::find_by_whitelabel_transaction_id($transaction->id);
        if ($tickets !== null) {
            foreach ($tickets as $ticket) {
                $ticket->set([
                    'paid' => Helpers_General::TICKET_PAID
                ]);
                $ticket->save();
            }
        }
        Lotto_Helper::create_slips($transaction);
    }
    
    /**
     *
     * @return int|null
     */
    public function process_form(): int
    {
        $whitelabel = $this->get_whitelabel();
        $rparam = $this->get_rparam();
        
        if (Helpers_Whitelabel::is_permitted($whitelabel)) {
            return self::RESULT_SECURITY_ERROR;
        }

        $transactions = Model_Whitelabel_Transaction::find_by([
            "whitelabel_id" => $whitelabel['id'],
            "token" => $this->get_param_id()
        ]);

        if (!($transactions !== null &&
            count($transactions) > 0 &&
            (int)$transactions[0]->whitelabel_id === (int)$whitelabel['id'] &&
            (int)$transactions[0]->status !== Helpers_General::STATUS_TRANSACTION_APPROVED)
        ) {
            Session::set_flash("message", ["danger", _("Incorrect transaction!")]);
            return self::RESULT_INCORRECT_TRANSACTION;
        }
        
        try {
            DB::start_transaction();

            $transaction = $transactions[0];

            $processed = Model_Whitelabel_User_Ticket::get_counted_by_transaction($transaction);

            if (is_null($processed) || (int)$processed !== 0) {
                throw new \Exception(_("Transaction still in process!"));
            }
            
            $transaction->set([
                'date_confirmed' => DB::expr("NOW()"),
                'status' => Helpers_General::STATUS_TRANSACTION_APPROVED
            ]);
            $transaction->save();

            if ($rparam === "transactions") {
                $this->process_tickets($transaction);
            }

            $user = Model_Whitelabel_User::find_by_pk($transaction->whitelabel_user_id);

            $msg = _("Transaction accepted!");
            $user_balance = $user['balance'];
            $balance = 0;
            // Only transaction connected with balance will be accepted!
            if ((int)$transaction->type === Helpers_General::TYPE_TRANSACTION_PURCHASE &&
                (int)$transaction->payment_method_type === Helpers_General::PAYMENT_TYPE_BALANCE
            ) {
                // shouldn't be used, but who knows lol
                $balance_update_query = DB::query(
                    "UPDATE whitelabel_user 
                    SET balance = balance - :amount
                    WHERE whitelabel_user.id = :user_id"
                );
                $balance_update_query->param(":amount", $transaction->amount);
                $balance_update_query->param(":user_id", $user->id);
        
                $balance_update_query->execute();
                $msg = _("Transaction accepted! The amount has been subtracted from user balance!");
            } elseif ((int)$transaction->type === Helpers_General::TYPE_TRANSACTION_DEPOSIT &&
                (int)$transaction->payment_method_type !== Helpers_General::PAYMENT_TYPE_BALANCE
            ) {
                // when depo transaction & not balance (user cannot pay with balance, but it's better to check this out)
                $balance_update_query = DB::query(
                    "UPDATE whitelabel_user 
                    SET balance = balance + :amount
                    WHERE whitelabel_user.id = :user_id"
                );
                $balance_update_query->param(":amount", $transaction->amount);
                $balance_update_query->param(":user_id", $user->id);
        
                $balance_update_query->execute();
                $msg = _("Transaction accepted! The amount has been added to user balance!");
            } else {
                throw new \Exception(_("Transaction could not be accepted!"));
            }
            $total_purchases_manager_update_query = DB::query(
                "UPDATE whitelabel_user 
                SET total_purchases_manager = COALESCE(total_purchases_manager, 0) + :amount, 
                last_update = NOW(), 
                WHERE whitelabel_user.id = :user_id"
            );
            $total_purchases_manager_update_query->param(":amount", $transaction->amount_manager);
            $total_purchases_manager_update_query->param(":user_id", $user->id);
    
            $total_purchases_manager_update_query->execute();

            $updated_user = Model_Whitelabel_User::find_by_pk($transaction->whitelabel_user_id);
            $user_set = [
                'balance' => $updated_user['balance'],
                'last_update' => $updated_user['last_update'],
                'total_purchases_manager' => $updated_user['total_purchases_manager']
            ];


            \Fuel\Core\Event::register('whitelabel_transaction_accept', 'Events_Whitelabel_Transaction_Accept::handle');
            \Fuel\Core\Event::trigger('whitelabel_transaction_accept', [
                'whitelabel_id' => $whitelabel['id'],
                'user_id' => $user->id,
                'plugin_data' => $user_set,
            ]);

            // type 0 other payment methods: do nothing
            // type 1 balance: do nothing as it cannot appear
            Session::set_flash("message", ["success", $msg]);

            DB::commit_transaction();
        } catch (\Exception $e) {
            DB::rollback_transaction();

            Session::set_flash("message", ["danger", $e->getMessage()]);
        }
        
        return self::RESULT_OK;
    }
}

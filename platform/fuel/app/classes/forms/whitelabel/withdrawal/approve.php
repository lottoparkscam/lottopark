<?php

/**
 * Description of Forms_Whitelabel_Withdrawal_Approve
 */
final class Forms_Whitelabel_Withdrawal_Approve extends Forms_Main
{
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
     * @param string $token
     * @param array $whitelabel
     */
    public function __construct(string $token = null, array $whitelabel = [])
    {
        $this->token = $token;
        $this->whitelabel = $whitelabel;
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
     * @return int
     */
    public function process_form(): int
    {
        $whitelabel = $this->get_whitelabel();
        $token = $this->get_token();
        
        if (empty($whitelabel) || empty($token)) {
            return self::RESULT_INCORRECT_WITHDRAWAL;
        }
        
        $withdrawals = Model_Withdrawal_Request::find_by([
            "whitelabel_id" => $whitelabel['id'],
            "token" => $token
        ]);
        
        if ($withdrawals !== null &&
            count($withdrawals) > 0 &&
            (int)$withdrawals[0]->whitelabel_id === (int)$whitelabel['id'] &&
            (int)$withdrawals[0]->status === Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_PENDING
        ) {
            $withdrawal = $withdrawals[0];
            $user = Model_Whitelabel_User::find_by_pk($withdrawal['whitelabel_user_id']);
            $withdrawal_set = [
                'date_confirmed' => DB::expr("NOW()"),
                'status' => Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_APPROVED
            ];
            $withdrawal->set($withdrawal_set);
            $withdrawal->save();

            \Fuel\Core\Event::register(
                'whitelabel_withdrawal_approve',
                'Events_Whitelabel_Withdrawal_Approve::handle'
            );
            \Fuel\Core\Event::trigger('whitelabel_withdrawal_approve', [
                'whitelabel_id' => $whitelabel['id'],
                'user_id' => $user->id,
                'plugin_data' => [
                    'balance' => $user['balance'],
                    'total_withdrawal_manager' => $user['total_withdrawal_manager'],
                    'last_update' => $user['last_update']
                ],
            ]);

            Session::set_flash("message", ["success", _("Withdrawal has been approved!")]);
        } else {
            Session::set_flash("message", ["danger", _("Incorrect withdrawal!")]);
            return self::RESULT_INCORRECT_WITHDRAWAL;
        }
        
        return self::RESULT_OK;
    }
}

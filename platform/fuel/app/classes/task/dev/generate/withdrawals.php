<?php

use Fuel\Core\Str;

final class Task_Dev_Generate_Withdrawals extends Task_Dev_Task
{
    /**
     * How many users should be generated.
     * @var int
     */
    private $withdrawals_count;

    /**
     * Whitelabel id of generated withdrawals
     * @var int
     */
    private $whitelabel_id;


    /**de
     * User id
     * @var int
     */
    private $user_id;

    public function __construct(int $withdrawals_count, int $user_id, int $whitelabel_id)
    {
        parent::__construct();

        $this->withdrawals_count = $withdrawals_count;
        $this->whitelabel_id = $whitelabel_id;
        $this->user_id = $user_id;
    }

    public function run(): void
    {
        for ($i = 0; $i < $this->withdrawals_count; $i++) {
            $request = Model_Withdrawal_Request::forge();

            $amount = mt_rand( 10, 100 );

            $request_token = Lotto_Security::generate_withdrawal_token($this->whitelabel_id);

            $data = [
                "name" => "Name",
                "surname" => "Surname",
                "address" => "Address",
                "account_no" => "123123123",
                "account_swift" => "123",
                "bank_name" => "Bank Name",
                "bank_address" => "Bank Address",
            ];

            $request->set([
                'token' => $request_token,
                'whitelabel_id' => $this->whitelabel_id,
                'whitelabel_user_id' => $this->user_id,
                'withdrawal_id' => 1,
                'currency_id' => 2,
                'amount' => $amount,
                'amount_usd' => $amount,
                'amount_manager' => $amount,
                'date' => DB::expr("NOW()"),
                'status' => Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_PENDING,
                'data' => serialize($data)
            ]);
            $request->save();
        }
    }
}
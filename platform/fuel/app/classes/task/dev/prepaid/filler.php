<?php

use Fuel\Core\DB;

/**
 * Description of Task_Dev_Prepaid_Filler
 */
final class Task_Dev_Prepaid_Filler extends Task_Dev_Task
{
    /**
     *
     * @var bool
     */
    protected $in_transaction = true;
    
    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     */
    public function __construct(bool $in_transaction = true)
    {
        parent::__construct();
        echo "Prepaid filler CONSTR\r\n";
        $this->in_transaction = $in_transaction;
    }
    
    /**
     *
     * @return string
     */
    private function get_cache_to_clear(): string
    {
        $cache_to_clear = "model_whitelabel.bydomain." .
            str_replace('.', '-', $this->whitelabel['domain']);
        
        return $cache_to_clear;
    }
    
    /**
     *
     * @return int
     */
    public function update_whitelabel_prepaid(
        int $whitelabel_id,
        string $whitelabel_prepaid,
        string $summary_prepaid_amount
    ): int {
        try {
            $prepaid_amount_summary = (float)$whitelabel_prepaid - (float)$summary_prepaid_amount;

            $whitelabel_set = [
                "prepaid" => $prepaid_amount_summary
            ];

            $new_whitelabel = Model_Whitelabel::find_by_pk($whitelabel_id);
            $new_whitelabel->set($whitelabel_set);
            $new_whitelabel->save();
        } catch (\Throwable $e) {
            echo
                "\r\n'name' => TASK FAILURE" .
                "\r\n'line' => {$e->getLine()}," .
                "\r\n'file' => {$e->getFile()}," .
                "\r\n'code' => {$e->getCode()}," .
                "\r\n'message' => {$e->getMessage()}," .
                "\r\n";
            
            return -1;
        }
        
        $cache_to_clear = $this->get_cache_to_clear();
        
        Lotto_Helper::clear_cache($cache_to_clear);
        
        return 0;
    }
    
    /**
     *
     * @param string $prepaid_amount
     * @param Model_Whitelabel_Transaction $whitelabel_transaction
     * @return int
     */
    public function create_whitelabel_prepaid(
        int $whitelabel_id,
        string $prepaid_amount,
        Model_Whitelabel_Transaction $whitelabel_transaction = null
    ): int {
        try {
            $set_prepaid = [
                "whitelabel_id" => $whitelabel_id,
                "amount" => -(float)$prepaid_amount
            ];
            
            $prepaid_date = DB::expr("NOW()");
            
            if (!is_null($whitelabel_transaction)) {
                $whitelabel_transaction_id = (int)$whitelabel_transaction->id;
                $set_prepaid["whitelabel_transaction_id"] = $whitelabel_transaction_id;
                $prepaid_date = $whitelabel_transaction->date_confirmed;
            } else {
                throw new Exception("Prepaid cound not be created. Whitelabel ID: " . $whitelabel_id);
            }

            $set_prepaid['date'] = $prepaid_date;
            
            $new_whitelabel_prepaid = Model_Whitelabel_Prepaid::forge();
            $new_whitelabel_prepaid->set($set_prepaid);
            $new_whitelabel_prepaid->save();
        } catch (\Throwable $e) {
            echo
                "\r\n'name' => TASK FAILURE" .
                "\r\n'line' => {$e->getLine()}," .
                "\r\n'file' => {$e->getFile()}," .
                "\r\n'code' => {$e->getCode()}," .
                "\r\n'message' => {$e->getMessage()}," .
                "\r\n";
            
            return -1;
        }
        
        return 0;
    }
    
    /**
     *
     * @return void
     */
    public function run(): void
    {
        echo "START\r\n";
        
        $whitelabels = Model_Whitelabel::find([
            'where' => [
                'type' => Helpers_General::WHITELABEL_TYPE_V2,
            ],
            'order_by' => [
                'id' => 'asc'
            ]
        ]);
        
        if (empty($whitelabels)) {
            echo "\r\nNo whitelabels found :(\r\n";
            return ;
        }
        
        try {
            foreach ($whitelabels as $whitelabel) {
                $this->whitelabel = $whitelabel;
                $whitelabel_id = (int)$whitelabel->id;

                $transactions = Model_Whitelabel_Transaction::find([
                    'where' => [
                        'whitelabel_id' => $whitelabel_id,
                        'payment_method_type' => Helpers_General::PAYMENT_TYPE_BALANCE,
                        'type' => Helpers_General::TYPE_TRANSACTION_PURCHASE,
                        'status' => Helpers_General::STATUS_TRANSACTION_APPROVED
                    ],
                    'order_by' => [
                        'id' => 'asc'
                    ]
                ]);

                if (empty($transactions)) {
                    echo "\r\nNo transactions found for Whitelabel ID " .
                        $whitelabel_id . "\r\n";
                    continue;
                }

                $whitelabel_prepaid = $whitelabel['prepaid'];
                $summary_prepaid_amount = 0;

                foreach ($transactions as $transaction) {
                    $prepaid_amount = $transaction->cost_manager;
                    
                    $summary_prepaid_amount += $prepaid_amount;

                    $result_create_prepaid = $this->create_whitelabel_prepaid(
                        $whitelabel_id,
                        $prepaid_amount,
                        $transaction
                    );

                    if ($result_create_prepaid !== 0) {
                        throw new Exception("Prepaid cound not be created. Transaction ID: " . (int)$transaction->id);
                    }
                }
                
                $result_prepaid = $this->update_whitelabel_prepaid(
                    $whitelabel_id,
                    $whitelabel_prepaid,
                    $summary_prepaid_amount
                );

                if ($result_prepaid !== 0) {
                    throw new Exception("Prepaid cound not be created. Whitelabel ID: " . (int)$whitelabel_id);
                }
                
                echo "\r\nSummary for whitelabelID: " . $whitelabel_id .
                    "\r\nCurrent whitelabel prepaid: " . $whitelabel_prepaid .
                    "\r\nSum of prepaid amount to subtract: " . $summary_prepaid_amount .
                    "\r\n" .
                    "\r\n";
            }
        } catch (\Throwable $e) {
            echo
                "\r\n'name' => TASK FAILURE" .
                "\r\n'line' => {$e->getLine()}," .
                "\r\n'file' => {$e->getFile()}," .
                "\r\n'code' => {$e->getCode()}," .
                "\r\n'message' => {$e->getMessage()}," .
                "\r\n";
        }
        
        echo "END\r\n" .
            "---------------------\r\n";
    }
}

<?php

use Fuel\Core\Validation;
use Services\Logs\FileLoggerService;

class Forms_Admin_Whitelabels_Prepaid_New extends Forms_Main
{
    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @var View
     */
    private $inside = null;
    private FileLoggerService $fileLoggerService;
    
    /**
     *
     * @param array $whitelabel
     * @param string $path_to_view If empty it is probably for accept_transaction only

     */
    public function __construct(
        array $whitelabel,
        string $path_to_view = ""
    ) {
        $this->whitelabel = $whitelabel;
        if (!empty($path_to_view)) {
            $this->inside = Presenter::forge($path_to_view);
            $this->inside->set("whitelabel", $this->whitelabel);
            
            // Get all languages
            $whitelabel_languages = Model_Whitelabel_Language::get_whitelabel_languages($whitelabel);
            $this->inside->set("langs", $whitelabel_languages);
        }
        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }

    /**
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge();

        $validation->add("input.amount", _("Amount"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", -99999999)
            ->add_rule("numeric_max", 99999999);
        
        return $validation;
    }

    /**
     *
     * @return \Presenter_Admin_Whitelabels_Prepaid_New
     */
    public function get_inside(): Presenter_Admin_Whitelabels_Prepaid_New
    {
        return $this->inside;
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
     * @param float $prepaid_amount
     * @param bool $within_transaction Adding/Subtracting new prepaid could be
     * processed within transaction
     * @return int
     */
    public function add_prepaid(
        float $prepaid_amount,
        bool $within_transaction = true
    ): int {
        try {
            if ($this->whitelabel['type'] != Helpers_General::WHITELABEL_TYPE_V2) {
                return self::RESULT_OK;
            }
            if ($within_transaction) {
                DB::start_transaction();
            }
            
            $set_prepaid = [
                "whitelabel_id" => $this->whitelabel['id'],
                "date" => DB::expr("NOW()"),
                "amount" => $prepaid_amount
            ];

            $new_whitelabel_prepaid = Model_Whitelabel_Prepaid::forge();
            $new_whitelabel_prepaid->set($set_prepaid);
            $new_whitelabel_prepaid->save();

            // This is done with query because we need fast one-transactional execution without involving getting and setting the variable
            $db_query = DB::query("UPDATE whitelabel SET prepaid = prepaid + :prepaid WHERE id = :whitelabel");
            $db_query->param(":prepaid", $prepaid_amount);
            $db_query->param(":whitelabel", $this->whitelabel['id']);
            $db_query->execute();
            
            if ($within_transaction) {
                DB::commit_transaction();
            }
        } catch (\Exception $e) {
            if ($within_transaction) {
                DB::rollback_transaction();
            }

            $this->fileLoggerService->error(
                $e->getMessage()
            );
            return self::RESULT_WITH_ERRORS;
        }
        
        $cache_to_clear = $this->get_cache_to_clear();
        
        Lotto_Helper::clear_cache($cache_to_clear);
        
        return self::RESULT_OK;
    }
    
    /**
     * This will be run on accept_transactions because all
     * cost should subtract prepaid value for whitelabel
     *
     * @param float $prepaid_amount
     * @param int $whitelabel_transaction_id Could be null
     * @param bool $within_transaction Adding/Subtracting new prepaid could be
     * processed within transaction
     * @return int
     */
    public function subtract_prepaid(
        float $prepaid_amount,
        int $whitelabel_transaction_id = null,
        bool $within_transaction = true
    ): int {
        if ($this->whitelabel['type'] != Helpers_General::WHITELABEL_TYPE_V2) {
            return self::RESULT_OK;
        }
        if ($prepaid_amount == '0.00') {
            return self::RESULT_OK;
        }
        try {
            if ($within_transaction) {
                DB::start_transaction();
            }
            
            $set_prepaid = [
                "whitelabel_id" => $this->whitelabel['id'],
                "date" => DB::expr("NOW()"),
                "amount" => -$prepaid_amount
            ];
            
            if (!is_null($whitelabel_transaction_id)) {
                $set_prepaid["whitelabel_transaction_id"] = $whitelabel_transaction_id;
            }

            $new_whitelabel_prepaid = Model_Whitelabel_Prepaid::forge();
            $new_whitelabel_prepaid->set($set_prepaid);
            $new_whitelabel_prepaid->save();

            // This is done with query because we need fast one-transactional execution without involving getting and setting the variable
            $db_query = DB::query("UPDATE whitelabel SET prepaid = prepaid - :prepaid WHERE id = :whitelabel");
            $db_query->param(":prepaid", $prepaid_amount);
            $db_query->param(":whitelabel", $this->whitelabel['id']);
            $db_query->execute();
            
            if ($within_transaction) {
                DB::commit_transaction();
            }
        } catch (\Exception $e) {
            if ($within_transaction) {
                DB::rollback_transaction();
            }

            $this->fileLoggerService->error(
                $e->getMessage()
            );
            return self::RESULT_WITH_ERRORS;
        }
        
        $cache_to_clear = $this->get_cache_to_clear();
        
        Lotto_Helper::clear_cache($cache_to_clear);
        
        return self::RESULT_OK;
    }
    
    /**
     *
     * @return int
     */
    public function process_form(): int
    {
        if (Input::post("input.amount") === null) {
            return self::RESULT_GO_FURTHER;
        }
        
        $validated_form = $this->validate_form();

        if ($validated_form->run()) {
            $amount = $validated_form->validated("input.amount");
            
            $result = $this->add_prepaid((float)$amount);
            return $result;
        } else {
            $errors = Lotto_Helper::generate_errors($validated_form->error());
            $this->inside->set("errors", $errors);
        }
        
        return self::RESULT_WITH_ERRORS;
    }

    /**
     * @param array $whitelabel
     * @param array $transaction
     * @return float
     */
    public function count_prepaid_amount(array $whitelabel, Model_Whitelabel_Transaction $transaction): float
    {
        $prepaid_amount = 0.00;
        if ($whitelabel['type'] != Helpers_General::WHITELABEL_TYPE_V2) {
            return $prepaid_amount;
        }
        $lotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel($whitelabel);
        $tickets = Model_Whitelabel_User_Ticket::find_by_whitelabel_transaction_id($transaction->id);

        foreach ($tickets as $id => $ticket) {
            $lottery = $lotteries['__by_id'][$ticket['lottery_id']];

            // Check if amount from this ticket's lottery should be decreased
            if ($lottery['should_decrease_prepaid'] == "1") {
                $prepaid_amount += (float)$ticket['cost_manager'];
            }
        }

        return $prepaid_amount;
    }
}

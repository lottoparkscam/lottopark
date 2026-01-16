<?php

use Helpers\FlashMessageHelper;
use Repositories\WhitelabelWithdrawalRepository;
use Services\Logs\FileLoggerService;

/**
 * Description of Forms_Wordpress_Myaccount_Withdrawal_List
 */
class Forms_Wordpress_Myaccount_Withdrawal_List extends Forms_Main
{
    const RESULT_ALREADY_REQUESTED = 100;
    const RESULT_WITH_MESSAGE = 200;

    private FileLoggerService $fileLoggerService;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var array
     */
    private $whitelabel = [];
    
    /**
     * @var array
     */
    private $user = null;
    
    /**
     *
     * @var string
     */
    private $accountlink = "";
    
    /**
     *
     * @var array
     */
    private $currencies = [];
    
    /**
     *
     * @var array
     */
    private $language = [];
    
    /**
     *
     * @var array
     */
    private $user_currency_tab = [];
    
    /**
     *
     * @var float
     */
    private $min_withdrawal = 0.00;

    /**
     *
     * @var array
     */
    private $messages = [];
    
    /**
     *
     * @param array $whitelabel
     * @param array $user
     */
    public function __construct($whitelabel, $user, $accountlink)
    {
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->whitelabel = $whitelabel;
        $this->user = $user;
        $this->accountlink = $accountlink;
        
        $this->currencies = Lotto_Settings::getInstance()->get("currencies");

        $this->system_currency_tab = Helpers_Currency::get_mtab_currency(false, "USD");
        
        $this->user_currency_tab = Helpers_Currency::get_mtab_currency(
            true,
            lotto_platform_user_currency()
        );
        
        $user_currency_id = $this->user_currency_tab['id'];
        $user_currency_raw = Model_Whitelabel_Default_Currency::get_for_user(
            $this->whitelabel,
            $user_currency_id
        );
        $this->min_withdrawal = $user_currency_raw['min_withdrawal'];
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
     * @param array $errors
     * @return $this
     */
    public function set_errors($errors)
    {
        $this->errors = $errors;
        return $this;
    }
    
    /**
     *
     * @return array
     */
    public function get_messages(): array
    {
        return $this->messages;
    }
    
    /**
     *
     * @param float $amount
     * @return float
     */
    private function get_amount_in_usd($amount): float
    {
        $amount_usd = $amount;
        
        if ($this->system_currency_tab['code'] !== $this->user_currency_tab['code']) {
            $amount_usd = Helpers_Currency::get_recalculated_to_given_currency(
                $amount,
                $this->user_currency_tab,
                $this->system_currency_tab['code']
            );
        }
        
        return $amount_usd;
    }
    
    /**
     * Check if one withdrawal is currently pending
     *
     * @return bool
     */
    private function check_one_is_pending(): bool
    {
        $result = false;
        
        $count = Model_Withdrawal_Request::fetch_count_pending_for_user(
            (int)$this->whitelabel['id'],
            (int)$this->user['id'],
            IS_CASINO
        );

        if ($count > 0) {
            $result = true;
        }
        
        return $result;
    }
    
    /**
     *
     * @param View $view Reference to view
     * @return int
     */
    public function process_form(&$view): int
    {
        try {
            /** @var WhitelabelWithdrawalRepository $whitelabelWithdrawalRepository */
            $whitelabelWithdrawalRepository = Container::get(WhitelabelWithdrawalRepository::class);
            $whitelabelWithdrawalMethods = $whitelabelWithdrawalRepository->getAvailableCachedMethodsForWhitelabel(
                $this->whitelabel,
                IS_CASINO
            );
        } catch (Throwable $exception) {
            // Set empty array to ensure view displays correct error to user and log error
            $this->fileLoggerService->error(
                "There are no withdrawal methods available for whitelabel ID: {$this->whitelabel['id']}. Detailed message: " . $exception->getMessage()
            );
            $whitelabelWithdrawalMethods = [];
        }

        $min_withdrawal = $this->min_withdrawal;
        $balanceField = IS_CASINO ? 'casino_balance' : 'balance';
        $max_withdrawal = $this->user[$balanceField];

        $count = Model_Withdrawal_Request::count_for_whitelabel(
            $this->whitelabel,
            $this->user
        );

        $pagination_url = $this->accountlink . 'withdrawal/?' .
            http_build_query(Input::get());

        $config = [
            'pagination_url' => $pagination_url,
            'total_items' => $count,
            'per_page' => 5,
            'uri_segment' => 'show_page'
        ];
        $pagination = Pagination::forge('withdrawalspagination', $config);

        $withdrawals = Model_Withdrawal_Request::get_data_for_user_and_whitelabel(
            $this->whitelabel,
            $this->user,
            $pagination->offset,
            $pagination->per_page
        );
        
        $view->set("minwithdrawal", $min_withdrawal);
        $view->set("maxwithdrawal", $max_withdrawal);
        $view->set("methods", $whitelabelWithdrawalMethods);
        
        $step_check = Lotto_Settings::getInstance()->get("withdrawal_step");
        
        $step = 1;
        if (isset($step_check)) {
            $step = $step_check;
        }
        
        $view->set("step", $step);
        
        $view->set("withdrawals", $withdrawals);
        $view->set("whitelabel", $this->whitelabel);
        $view->set("currencies", $this->currencies);
        $view->set("pages", $pagination);

        $messages = FlashMessageHelper::getAll();
        if (!empty($messages)) {
            $this->messages = $messages;
            $view->set("hideform", true);
            return self::RESULT_WITH_MESSAGE;
        }
        
        if (empty($whitelabelWithdrawalMethods)) {
            $message_text = _("There are no supported withdrawal methods available.");
            $this->messages[] = ['error', $message_text];
            $view->set("hideform", true);
            
            return self::RESULT_WITH_MESSAGE;
        }
        
        if ($this->check_one_is_pending()) {
            $message_text = _(
                "You have requested a withdrawal! " .
                "Please allow up to 24h for our acceptance."
            );
            $this->messages[] = ['error', $message_text];
            $view->set("hideform", true);
            
            return self::RESULT_ALREADY_REQUESTED;
        }
        
        if ($max_withdrawal <= 0) {
            $error_text = _(
                'You do not have enough balance to withdraw money ' .
                'from the account (minimum %s)!'
            );
            $min_withdrawal_temp = $min_withdrawal;
            $min_withdrawal_text = Lotto_View::format_currency(
                $min_withdrawal_temp,
                $this->user_currency_tab['code'],
                true
            );
            $error_text_final = sprintf($error_text, $min_withdrawal_text);
            
            $this->messages[] = ['error', $error_text_final];
            $view->set("hideform", true);
            
            return self::RESULT_WITH_MESSAGE;
        }
        
        if ($max_withdrawal < $min_withdrawal) {
            $error_text = _(
                'You do not have enough balance to withdraw money ' .
                'from the account (minimum %s)!'
            );
            $min_withdrawal_temp = $min_withdrawal;
            $min_withdrawal_text = Lotto_View::format_currency(
                $min_withdrawal_temp,
                $this->user_currency_tab['code'],
                true
            );
            $error_text_final = sprintf($error_text, $min_withdrawal_text);
            
            $this->messages[] = ['error', $error_text_final];
            $view->set("hideform", true);
            
            return self::RESULT_WITH_MESSAGE;
        }
        
        return self::RESULT_OK;
    }
}

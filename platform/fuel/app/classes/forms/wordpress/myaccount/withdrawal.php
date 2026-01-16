<?php

use Fuel\Core\Validation;
use Models\WhitelabelUser;
use Models\WhitelabelWithdrawal;
use Modules\Account\Balance\CasinoBalance;
use Modules\Account\Balance\RegularBalance;
use Repositories\Orm\WhitelabelUserRepository;
use Repositories\WhitelabelWithdrawalRepository;
use Wrappers\Db;
use Services\Logs\FileLoggerService;

class Forms_Wordpress_Myaccount_Withdrawal extends Forms_Main
{
    private FileLoggerService $fileLoggerService;

    const RESULT_GO_TO_NEXT_STEP = 100;
    
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
     * @var array
     */
    private $auser = null;

    /**
     *
     * @var string
     */
    private $account_link = "";
    
    /**
     *
     * @var array
     */
    private $currencies = [];
    
    /**
     *
     * @var array
     */
    private $system_currency_tab = [];
    
    /**
     *
     * @var array
     */
    private $manager_currency_tab = [];
    
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

    private Db $db;
    private RegularBalance $regularBalance;
    private CasinoBalance $casinoBalance;
    private WhitelabelUserRepository $whitelabelUserRepository;
    
    /**
     *
     * @param array $whitelabel
     * @param array $user
     * @param array $auser
     * @param string $account_link
     */
    public function __construct($whitelabel, $user, $auser, $account_link)
    {
        $this->whitelabel = $whitelabel;
        $this->user = $user;
        $this->auser = $auser;
        $this->account_link = $account_link;
        $this->db = Container::get(Db::class);
        $this->regularBalance = Container::get(RegularBalance::class);
        $this->casinoBalance = Container::get(CasinoBalance::class);
        $this->whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        
        $this->currencies = Lotto_Settings::getInstance()->get("currencies");

        $this->system_currency_tab = Helpers_Currency::get_mtab_currency(false, "USD");
        
        $this->manager_currency_tab = Helpers_Currency::get_mtab_currency(
            false,
            "",
            $whitelabel['manager_site_currency_id']
        );
        
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
     * @param string $amount
     * @return string
     */
    private function get_amount_in_usd(string $amount): string
    {
        $amount_usd = $amount;
        
        if ((int)$this->system_currency_tab['id'] !== (int)$this->user_currency_tab['id']) {
            $amount_usd = Helpers_Currency::get_recalculated_to_given_currency(
                $amount,
                $this->user_currency_tab,
                $this->system_currency_tab['code']
            );
        }
        
        return $amount_usd;
    }
    
    /**
     *
     * @param string $amount_usd
     * @return string
     */
    private function get_amount_manager(string $amount_usd): string
    {
        $amount_manager = $amount_usd;
        
        if ((int)$this->system_currency_tab['id'] !== (int)$this->manager_currency_tab['id']) {
            $amount_manager = Helpers_Currency::get_recalculated_to_given_currency(
                $amount_usd,
                $this->system_currency_tab,
                $this->manager_currency_tab['code']
            );
        }
        
        return $amount_manager;
    }
    
    /**
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $validation = Validation::forge();
        
        $min_withdrawal = floatval($this->min_withdrawal);
        $isBtcType = $validation->input('withdrawal.type') === '4';
        if ($isBtcType) {
            $min_withdrawal = $min_withdrawal * 2; // change agreed with the business
        }
        $balanceField = IS_CASINO ? 'casino_balance' : 'balance';
        $max_withdrawal = floatval($this->user[$balanceField]);
        
        $validation->add(
            "withdrawal.amount",
            _('Amount')
        )
            ->add_rule("required")
            ->add_rule("trim")
            ->add_rule(
                ["Validator_Wordpress_Currency", "is_currency_ok"]
            )
            ->add_rule(
                ["Validator_Wordpress_Currency", "check_min_formatted"],
                $min_withdrawal
            )
            ->add_rule(
                ["Validator_Wordpress_Currency", "check_max_formatted"],
                $max_withdrawal
            );

        $validation->add("withdrawal.type", _('Type'))
            ->add_rule("required")
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule(
                ["Validator_Wordpress_Withdrawal_Type", "check_min_value"]
            );
        
        $validation->add("withdrawal.step", _('Step'))
            ->add_rule("required")
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 1)
            ->add_rule("numeric_max", 2);
        
        return $validation;
    }
    
    /**
     * Email whitelabel of new request of withdrawal
     *
     * @param string $amount
     * @param string $amount_manager
     * @param int $token
     * @param int $payment_method_withdrawal_id
     * @return void
     */
    private function email_whitelabel(
        string $amount,
        string $amount_manager,
        int $token,
        int $payment_method_withdrawal_id
    ): void {
        \Package::load('email');
        $email = Email::forge();
        
        $email->from('noreply@' . Lotto_Helper::getWhitelabelDomainFromUrl(), $this->whitelabel['name']);
        
        $email->to($this->whitelabel['email']);
        
        $title = _("New withdrawal request has been made by user.");
        
        $email->subject($title);
        
        $user_id = $this->whitelabel['prefix'] . 'U' . $this->user['token'];
        
        $user_name = '';
        if (!empty($this->user['name'])) {
            $user_name .= $this->user['name'];
        }
        if (!empty($this->user['surname'])) {
            $user_name .= " " . $this->user['surname'];
        }
        if (empty($user_name)) {
            $user_name .= $this->user['email'];
        }
        
        $model_withdrawal = Model_Withdrawal::find_by_pk($payment_method_withdrawal_id);
        
        $amount_manager_with_code = Lotto_View::format_currency(
            $amount_manager,
            $this->manager_currency_tab['code'],
            true
        );
        $amount_with_code = Lotto_View::format_currency(
            $amount,
            $this->user_currency_tab['code'],
            true
        );
        
        $withdrawal_amount = $amount_manager_with_code;
        $withdrawal_amount .= " (";
        $withdrawal_amount .= _("User currency") . ": " . $amount_with_code;
        $withdrawal_amount .= ")";
        
        $withdrawal_method = $model_withdrawal->name;
        
        $url = "https://manager." . $this->whitelabel['domain'] . "/";
        $url .= "withdrawals/view/" . $token;
        
        $body_text = _("New withdrawal request has been made by user");
        $body_text .= " <b>" . $user_name . "</b> (<b>" . $user_id . "</b>) - <b>" . $this->user['email'] . "</b>";
        
        $body_text .= "<br><br>";
        
        $body_text .= _("Withdrawal amount") . ": " . $withdrawal_amount;
        $body_text .= "<br>";
        
        $body_text .= _("Withdrawal method") . ": " . $withdrawal_method;
        $body_text .= "<br><br>";
        
        $body_text .= "Click <a href=\"" . $url . "\">here</a> to view it.";
        
        $email->html_body($body_text);
        
        try {
            $email->send();
        } catch (Throwable $e) {
            $error_msg = "There is a problem with sending email to whitelabelID: " .
                $this->whitelabel['id'] .
                " from user with email: " .
                $this->user['email'] .
                " and userID: " .
                $this->user['id'] . "! " .
                "Error description: " . $e->getMessage();
            $this->fileLoggerService->error(
                $error_msg
            );
        }
    }

    private function processAndSaveWithdrawalData(
        WhitelabelWithdrawal $whitelabelWithdrawalMethod,
        string $amountFormatted
    ): int {
        $withdrawalFormValidation = null;
        $isIntegratedPayment = true;
        $formStepName = 'step2';
        $fields = [];
        switch ($whitelabelWithdrawalMethod->withdrawal_id) {
            case Helpers_Withdrawal_Method::WITHDRAWAL_BANK:
                $fields = ['name', 'surname', 'address', 'account_no', 'account_swift', 'bank_name', 'bank_address'];
                $bankForm = new Forms_Wordpress_Withdrawal_Bank($formStepName);
                $withdrawalFormValidation = $bankForm->validate_form();
                break;
            case Helpers_Withdrawal_Method::WITHDRAWAL_SKRILL:
                $fields = ['name', 'surname', 'skrill_email'];
                $skrillForm = new Forms_Wordpress_Withdrawal_Skrill($formStepName);
                $withdrawalFormValidation = $skrillForm->validate_form();
                break;
            case Helpers_Withdrawal_Method::WITHDRAWAL_NETELLER:
                $fields = ['name', 'surname', 'neteller_email'];
                $netellerForm = new Forms_Wordpress_Withdrawal_Neteller($formStepName);
                $withdrawalFormValidation = $netellerForm->validate_form();
                break;
            case Helpers_Withdrawal_Method::WITHDRAWAL_BTC:
                $fields = ['name', 'surname', 'bitcoin'];
                $btcForm = new Forms_Wordpress_Withdrawal_Btc($formStepName);
                $withdrawalFormValidation = $btcForm->validate_form();
                break;
            case Helpers_Withdrawal_Method::WITHDRAWAL_DEBIT_CARD:
                $fields = ['name', 'surname'];
                $debitCardForm = new Forms_Wordpress_Withdrawal_Debitcard($formStepName);
                $withdrawalFormValidation = $debitCardForm->validate_form();
                break;
            case Helpers_Withdrawal_Method::WITHDRAWAL_PAYPAL:
                $fields = ['name', 'surname', 'paypal_email'];
                $paypalForm = new Forms_Wordpress_Withdrawal_PayPal($formStepName);
                $withdrawalFormValidation = $paypalForm->validate_form();
                break;
            case Helpers_Withdrawal_Method::WITHDRAWAL_FAIREUM_MEMBERSHIP:
                $fields = ['name', 'surname', 'fairox_account_id'];
                $membershipForm = new Forms_Wordpress_Withdrawal_Membership($formStepName);
                $withdrawalFormValidation = $membershipForm->validate_form();
                break;
            case Helpers_Withdrawal_Method::WITHDRAWAL_FAIREUM_USDT:
                $fields = ['usdt_wallet_type', 'usdt_wallet_address', 'email'];
                $usdtForm = new Forms_Wordpress_Withdrawal_Usdt($formStepName);
                $withdrawalFormValidation = $usdtForm->validate_form();
                break;
            case Helpers_Withdrawal_Method::WITHDRAWAL_FAIREUM_CRYPTO_EXCHANGES:
                $fields = ['exchange', 'name', 'email'];
                $cryptoExchangesForm = new Forms_Wordpress_Withdrawal_CryptoExchanges($formStepName);
                $withdrawalFormValidation = $cryptoExchangesForm->validate_form();
                break;
            default:
                $isIntegratedPayment = false;
                break;
        }

        if ($isIntegratedPayment && $withdrawalFormValidation->run()) {
            $data = [];
            foreach ($fields as $field) {
                $data[$field] = htmlspecialchars($withdrawalFormValidation->validated("withdrawal.add." . $field));
            }
            $request = Model_Withdrawal_Request::forge();

            $amount = $amountFormatted;
            $amountUsd = $this->get_amount_in_usd($amount);
            $amountManager = $this->get_amount_manager($amountUsd);

            $requestToken = Lotto_Security::generate_withdrawal_token($this->whitelabel['id']);
            $userId = $this->user['id'];

            $request->set([
                'token' => $requestToken,
                'whitelabel_id' => $this->whitelabel['id'],
                'whitelabel_user_id' => $userId,
                'withdrawal_id' => $whitelabelWithdrawalMethod->withdrawal_id,
                'currency_id' => $this->user['currency_id'],
                'amount' => $amount,
                'amount_usd' => $amountUsd,
                'amount_manager' => $amountManager,
                'date' => $this->db->expr("NOW()"),
                'status' => Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_PENDING,
                'is_casino' => IS_CASINO,
                'data' => serialize($data)
            ]);

            $regularBalance = $this->regularBalance;
            $userRepository = $this->whitelabelUserRepository;
            /** @var WhitelabelUser $user */
            $user = WhitelabelUser::find($this->user['id']);
            $usersCurrency = $user->currency->code;

            $this->db->inTransaction(
                function() use (
                    $request,
                    $regularBalance,
                    $userId,
                    $amount,
                    $amountManager,
                    $userRepository,
                    $usersCurrency
                ) {
                    $request->save();
                    if (IS_CASINO) {
                        $this->casinoBalance->debit(
                            $userId,
                            $amount,
                            $usersCurrency
                        );
                        $this->casinoBalance->dispatch();
                    } else {
                        $regularBalance->debit(
                            $userId,
                            $amount,
                            $usersCurrency
                        );
                        $regularBalance->dispatch();
                    }
                    $userRepository->updateFloatField(
                        $userId,
                        'total_withdrawal_manager',
                        $amountManager
                    );
                }
            );

            $messageText = _(
                "We have received your withdrawal request. " .
                "Please allow up to 24h for our acceptance."
            );

            $this->email_whitelabel(
                $amount,
                $amountManager,
                $requestToken,
                $whitelabelWithdrawalMethod->withdrawal_id
            );
            Lotto_Settings::getInstance()->set("withdrawal_step", 1);
            Session::set("message", ["success", $messageText]);
        } else {
            if (!empty($withdrawalFormValidation)) {
                $this->errors = Lotto_Helper::generate_errors($withdrawalFormValidation->error());
            } else {
                $errorMessage = _('Security error! Please contact us!');
                $this->errors = ['withdrawal' => $errorMessage];
            }
            Lotto_Settings::getInstance()->set("withdrawal_step", 2);
            return self::RESULT_WITH_ERRORS;
        }

        return self::RESULT_OK;
    }

    /**
     * URL: /account/withdrawal/
     * This is fired when user selects withdrawal type, amount and clicks withdrawal
     * To validate user provided information in the form - prevent modifying ID in html to withdraw with disabled method
     */
    public function process_form(): int
    {
        if (Input::post("withdrawal") === null) {
            return self::RESULT_GO_FURTHER;
        }
        
        if (\Security::check_token() === false) {
            $errorMessage = _('Security error! Please contact us!');
            $this->errors = ['withdrawal' => $errorMessage];
            return self::RESULT_WITH_ERRORS;
        }

        $validate = $this->validate_form();

        if ($validate->run()) {
            // Example: Debit Card = 5
            $withdrawalId = intval($validate->validated("withdrawal.type"));

            try {
                /** @var WhitelabelWithdrawalRepository $whitelabelWithdrawalRepository */
                $whitelabelWithdrawalRepository = Container::get(WhitelabelWithdrawalRepository::class);
                $whitelabelWithdrawal = $whitelabelWithdrawalRepository->getAvailableMethodByIdForWhitelabel($withdrawalId, $this->whitelabel, IS_CASINO);
            } catch (Throwable $exception) {
                $this->fileLoggerService->error(
                    "User should not get here, invalid withdrawal method ({$withdrawalId}) provided for whitelabel ID: {$this->whitelabel['id']}. IS_CASINO: " . IS_CASINO . " Detailed message: " . $exception->getMessage()
                );

                $errorMessage = _("Incorrect withdrawal type.");
                $this->errors = ['withdrawal.type' => $errorMessage];
                return self::RESULT_WITH_ERRORS;
            }

            $validatedAmount = $validate->validated("withdrawal.amount");
            $valueToProcess = str_replace(",", ".", $validatedAmount);
            $amountFormatted = round($valueToProcess, 2);

            if (intval($validate->input("withdrawal.step")) === 2) {
                $result = $this->processAndSaveWithdrawalData($whitelabelWithdrawal, $amountFormatted);
                return $result;
            } else {
                Lotto_Settings::getInstance()->set("withdrawal_step", 2);
                return self::RESULT_GO_TO_NEXT_STEP;
            }
        } else {
            $this->errors = Lotto_Helper::generate_errors($validate->error());
            return self::RESULT_WITH_ERRORS;
        }
    }
}

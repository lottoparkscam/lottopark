<?php

use Fuel\Core\Response;
use Fuel\Core\Session;
use Fuel\Core\Validation;
use Helpers\CurrencyHelper;
use Models\PaymentMethod;
use Models\WhitelabelUser;
use Services\CartService;
use Services\Logs\FileLoggerService;
use Services\LotteryPurchaseLimitService;
use Services\PaymentRequestLockService;
use Helpers\Wordpress\LanguageHelper;

/**
 * Description of Forms_Order_Create
 */
class Forms_Order_Create extends Forms_Main
{
    use Traits_Checks_Block_Ltech;
    
    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @var null|array
     */
    private $user = null;
    
    /**
     *
     * @var bool
     */
    private $is_user = false;
    
    /**
     *
     * @var bool
     */
    private $deposit = false;
    
    /**
     *
     * @var null|array
     */
    private $input_post = null;
    
    /**
     *
     * @var array
     */
    private $errors = [];
    
    /**
     *
     * @var null|array
     */
    private $lotteries = null;
    
    /**
     *
     * @var null|array
     */
    private $user_currency = null;
    
    /**
     *
     * @var null|int
     */
    private $payment_type = null;
    
    /**
     *
     * @var array
     */
    private $whitelabel_payment_methods_indexed = [];
    
    /**
     *
     * @var null|Model_Whitelabel_Payment_Method
     */
    private $model_whitelabel_payment_method = null;
    
    /**
     *
     * @var null|array
     */
    private $emerchant_data = null;
    
    /**
     * This is needed only in the case that payment type is equal
     * to Helpers_General::PAYMENT_TYPE_CC
     * It is also needed to prevent breaking the rule
     * that variables should be used in their own scopes
     * (here $val variable was used in other scope)
     *
     * @var null|Forms_Wordpress_Payment_Emerchantpay
     */
    private $payment_type_cc_object = null;

    /**
     *
     * @var null|Validation
     */
    private $payment_method_user_validation = null;
    
    /**
     *
     * @var int
     */
    private $payment_method_id = 0;
    
    /**
     *
     * @var int
     */
    private $whitelabel_payment_method_id = 0;
    
    /**
     *
     * @var null|array
     */
    private $basket = null;
    
    /**
     *
     * @var null|Model_Whitelabel_Transaction
     */
    private $transaction = null;
    private PaymentRequestLockService $paymentRequestLockService;
    private FileLoggerService $fileLoggerService;
    private CartService $cartService;

    /**
     *
     * @param array $whitelabel
     * @param array $user
     * @param bool $deposit
     * @param bool $is_user
     * @param array $input_post
     */
    public function __construct(
        array $whitelabel,
        array $user,
        bool $deposit,
        bool $is_user,
        array $input_post = null
    ) {
        $this->whitelabel = $whitelabel;
        $this->user = $user;
        $this->deposit = $deposit;
        $this->is_user = $is_user;
        $this->input_post = $input_post;
        $this->lotteries = Model_Lottery::get_lotteries_for_whitelabel($whitelabel);
        $this->user_currency = CurrencyHelper::getCurrentCurrency()->to_array();
        $this->paymentRequestLockService = Container::get(PaymentRequestLockService::class);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->cartService = Container::get(CartService::class);;

        // Check if specific Whitelabel's l-tech account is blocked
        if ($this->is_ltech_blocked($whitelabel)) {
            Session::set("message", ["error", _("Internal error. Please contact support.")]);
            Response::redirect(lotto_platform_home_url('/'));
        }
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
     * @return array|null
     */
    public function get_errors():? array
    {
        return $this->errors;
    }
    
    /**
     *
     * @return int|null
     */
    public function get_whitelabel_id():? int
    {
        $whitelabel_id = null;
        if (!empty($this->whitelabel['id'])) {
            $whitelabel_id = (int)$this->whitelabel['id'];
        }
        
        return $whitelabel_id;
    }
    
    /**
     *
     * @return int|null
     */
    public function get_transaction_id():? int
    {
        $transaction_id = null;
        if (!empty($this->transaction->id)) {
            $transaction_id = (int)$this->transaction->id;
        }
        
        return $transaction_id;
    }
    
    /**
     *
     * @param string $message
     * @param int $type
     * @param int|null $payment_method_type
     * @return void
     */
    public function log(
        string $message,
        int $type = Helpers_General::TYPE_INFO,
        int $payment_method_type = null
    ): void {
        $whitelabel_id = $this->get_whitelabel_id();
        $transaction_id = $this->get_transaction_id();
        
        if (!empty($whitelabel_id)) {
            $whitelabel_payment_method_id = null;
            if (!empty($this->whitelabel_payment_method_id)) {
                $whitelabel_payment_method_id = $this->whitelabel_payment_method_id;
            }
            
            Model_Payment_Log::add_log(
                $type,
                $payment_method_type,
                null,
                null,
                $whitelabel_id,
                $transaction_id,
                $message,
                null,
                $whitelabel_payment_method_id
            );
        }
    }
    
    /**
     *
     * @param string $message
     * @param int|null $payment_method_type
     * @return void
     */
    public function log_success(
        string $message,
        int $payment_method_type = null
    ): void {
        $this->log($message, Helpers_General::TYPE_SUCCESS, $payment_method_type);
    }
    
    /**
     *
     * @param string $message
     * @param int|null $payment_method_type
     * @return void
     */
    public function log_error(
        string $message,
        int $payment_method_type = null
    ): void {
        $this->log($message, Helpers_General::TYPE_ERROR, $payment_method_type);
    }
    
    /**
     *
     * @return array
     */
    public function get_whitelabel_payment_methods_without_currency(): array
    {
        $whitelabel_payment_methods_without_currency = Model_Whitelabel_Payment_Method::get_payment_methods_for_whitelabel($this->whitelabel);
        return $whitelabel_payment_methods_without_currency;
    }
    
    /**
     *
     * @return array
     */
    public function get_whitelabel_payment_methods_with_currency(): array
    {
        $whitelabel_payment_methods_without_currency = $this->get_whitelabel_payment_methods_without_currency();
        
        $whitelabel_payment_methods_with_currency = Helpers_Currency::get_whitelabel_payment_methods_with_currency(
            $this->whitelabel,
            $whitelabel_payment_methods_without_currency,
            $this->user_currency
        );
        
        return $whitelabel_payment_methods_with_currency;
    }
    
    /**
     *
     * @return array
     */
    public function get_whitelabel_payment_methods(): array
    {
        $whitelabel_payment_methods_with_currency = $this->get_whitelabel_payment_methods_with_currency();
        
        $whitelabel_payment_methods = Lotto_Helper::get_whitelabel_payment_methods_for_language(
            $this->whitelabel,
            $whitelabel_payment_methods_with_currency
        );
        
        return $whitelabel_payment_methods;
    }
    
    /**
     *
     * @return array
     */
    public function get_whitelabel_payment_methods_indexed(): array
    {
        $whitelabel_payment_methods = $this->get_whitelabel_payment_methods();

        if ($whitelabel_payment_methods !== null) {
            $this->whitelabel_payment_methods_indexed = array_values($whitelabel_payment_methods);
        }
        
        return $this->whitelabel_payment_methods_indexed;
    }
    
    /**
     *
     * @return void
     */
    public function check_and_process_lines(): void
    {
        if (!$this->deposit &&
            !empty($this->input_post) &&
            !empty($this->input_post['order']) &&
            !empty($this->input_post['order']['lines'])
        ) {
            $lines_obj = new Forms_Wordpress_Lottery_Lines(
                $this->whitelabel,
                $this->lotteries
            );
            $lines_obj->set_errors($this->errors);
            $result = $lines_obj->process_form();

            if ($result !== 0) {
                Response::redirect(lotto_platform_get_permalink_by_slug('order'));
            }
        }
    }
    
    /**
     *
     * @return bool
     */
    public function check_token(): bool
    {
        if (!\Security::check_token()) {
            Session::delete("deposit_amount");
            Session::delete("deposit_amount_gateway");
            $this->errors = [
                'payment' => _("Security error! Please try again.")
            ];
            return false;
        }
        
        return true;
    }
    
    /**
     *
     * @return int|null
     */
    public function get_payment_type():? int
    {
        $this->payment_type = null;
        
        if (!empty($this->input_post) &&
            !empty($this->input_post['payment']) &&
            !empty($this->input_post['payment']['type'])
        ) {
            $this->payment_type = (int)$this->input_post['payment']['type'];
        }
        
        return $this->payment_type;
    }
    
    /**
     *
     * @return void
     */
    public function validate_payment_type(): void
    {
        // let's make some basic checks before creating a transaction
        if (!($this->payment_type >= Helpers_General::PAYMENT_TYPE_BALANCE &&
            $this->payment_type <= Helpers_General::PAYMENT_TYPE_BONUS_BALANCE)
        ) {
            exit(_("Bad request! Please contact us!"));
        }
    }
    
    /**
     *
     * @return int|null
     */
    public function get_whitelabel_payment_method_id():? int
    {
        $whitelabel_payment_methods_index = -1;
        if (!empty($this->input_post) &&
            !empty($this->input_post['payment']) &&
            !empty($this->input_post['payment']['subtype'])
        ) {
            $whitelabel_payment_methods_index = (int)($this->input_post['payment']['subtype']) - 1;
        }
        
        if (!isset($this->whitelabel_payment_methods_indexed[$whitelabel_payment_methods_index])) {
            exit(_("Bad request! Please contact us!"));
        }
        
        $whitelabel_payment_method_id = (int)$this->whitelabel_payment_methods_indexed[$whitelabel_payment_methods_index]['id'];
        
        return $whitelabel_payment_method_id;
    }
    
    /**
     *
     * @return \Model_Whitelabel_Payment_Method|null
     */
    public function get_model_whitelabel_payment_method():? Model_Whitelabel_Payment_Method
    {
        if ($this->payment_type === Helpers_General::PAYMENT_TYPE_OTHER &&
            !empty($this->input_post) &&
            !empty($this->input_post['payment']) &&
            !empty($this->input_post['payment']['subtype'])
        ) {
            $whitelabel_payment_method_id = $this->get_whitelabel_payment_method_id();
            
            $model_whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_by_pk($whitelabel_payment_method_id);

            if (is_null($model_whitelabel_payment_method) ||
                (int)$model_whitelabel_payment_method->whitelabel_id !== (int)$this->whitelabel['id']
            ) {
                exit(_("Bad request! Please contact us!"));
            }
            
            $this->model_whitelabel_payment_method = $model_whitelabel_payment_method;
        }
        
        return $this->model_whitelabel_payment_method;
    }
    
    /**
     *
     * @return array|null
     */
    public function get_emerchant_data():? array
    {
        if ($this->payment_type === Helpers_General::PAYMENT_TYPE_CC) {
            $emerchant_method_id = Model_Whitelabel_CC_Method::get_emerchant_method_id();
            
            $cc_methods = Model_Whitelabel_CC_Method::get_cc_methods_for_whitelabel($this->whitelabel);
            
            $cc_methods_merchant = [];
            foreach ($cc_methods as $cc_method) {
                $cc_methods_merchant[intval($cc_method['method'])] = $cc_method;
            }

            if (isset($cc_methods_merchant[$emerchant_method_id])) {
                // Remove of the unserialize function because I need the rest of the data
                //$emerchant_data = unserialize($ccmethods_merchant[1]['settings']);
                $this->emerchant_data = $cc_methods_merchant[$emerchant_method_id];
            }
        }
        
        return $this->emerchant_data;
    }
    
    /**
     *
     * @return void
     */
    public function process_emerchant_payment(): void
    {
        if ($this->payment_type === Helpers_General::PAYMENT_TYPE_CC) {
            $this->payment_type_cc_object = new Forms_Wordpress_Payment_Emerchantpay($this->whitelabel);
            $this->payment_type_cc_object->validate_form();
            $this->errors = $this->payment_type_cc_object->get_errors();
        }
    }
    
    /**
     *
     * @return void
     */
    public function extra_validation(): void
    {
        /** @var WhitelabelUser $whitelabelUser */
        $whitelabelUser = WhitelabelUser::find($this->user['id']);
//    	$shouldCheckCaptcha = $this->paymentRequestLockService->shouldDisplayCaptcha($whitelabelUser);
//		$captchaIsNotValid = !Lotto_Security::check_captcha();
//
//    	if ($shouldCheckCaptcha && $captchaIsNotValid) {
//    		$this->errors[] = 'Wrong captcha!';
//    		return;
//		}

		$paymentMethodIsNotShown = !empty($this->model_whitelabel_payment_method) && (int)$this->model_whitelabel_payment_method->show !== 1;
		if ($paymentMethodIsNotShown) {
			$this->errors[] = 'This method is not allowed';
            $this->fileLoggerService->error(
				"User [id: {$whitelabelUser->id}, email: {$whitelabelUser->email}] tried to use 
				whitelabel_payment_method [id: {$this->model_whitelabel_payment_method->id}, 
				name: {$this->model_whitelabel_payment_method->name}], which is not shown"
			);
			return;
		}

        if ($this->payment_type === Helpers_General::PAYMENT_TYPE_OTHER) {
            switch ($this->model_whitelabel_payment_method->payment_method_id) {
                case Helpers_Payment_Method::APCOPAY_CC:
                    $validate = new Forms_Whitelabel_Payment_Apcopaycc();
                    $this->payment_method_user_validation = $validate->get_prepared_user_form();
                    $this->errors = $validate->get_errors();
                    break;
                case Helpers_Payment_Method::EASY_PAYMENT_GATEWAY:
                    $this->payment_method_user_validation = Validator_Wordpress_Payments_Easypaymentgateway::validation();
                    $this->payment_method_user_validation->run();
                    $this->errors = Lotto_Helper::generate_errors($this->payment_method_user_validation->error());
                    break;
                case Helpers_Payment_Method::ASTRO_PAY:
                    $this->payment_method_user_validation = Validator_Wordpress_Payments_Astropay::validation();
                    $this->payment_method_user_validation->run();
                    $this->errors = Lotto_Helper::generate_errors($this->payment_method_user_validation->error());
                    break;
                case Helpers_Payment_Method::ASTRO_PAY_CARD:
                    $astropaycard = new Forms_Wordpress_Payment_Astropaycard();
                    $this->payment_method_user_validation = $astropaycard->get_prepared_user_form();
                    $this->errors = $astropaycard->get_errors();
                    break;
                case Helpers_Payment_Method::PSPGATE_ID:
                    $this->payment_method_user_validation = Validator_Wordpress_Payments_PspGate::validation();
                    $this->payment_method_user_validation->run();
                    $this->errors = Lotto_Helper::generate_errors($this->payment_method_user_validation->error());
                    break;
                case Helpers_Payment_Method::ZEN_ID:
                    $this->payment_method_user_validation = Validator_Wordpress_Payments_Zen::validation();
                    $this->payment_method_user_validation->run();
                    $this->errors = Lotto_Helper::generate_errors($this->payment_method_user_validation->error());
                    break;
                case Helpers_Payment_Method::CUSTOM:
                    $custom = new Forms_Wordpress_Payment_Custom();
                    $custom->set_model_whitelabel_payment_method($this->model_whitelabel_payment_method);
                    $custom->check_is_custom_url_to_redirect_set();
                    $this->errors = $custom->get_errors();
                    break;
            }
        }
    }
    
    /**
     *
     * @return void
     */
    public function get_payment_method_ids(): void
    {
        if ($this->payment_type === Helpers_General::PAYMENT_TYPE_CC &&
            !empty($this->input_post) &&
            !empty($this->input_post['payment']) &&
            isset($this->input_post['payment']['subtype']) &&
            (int)$this->input_post['payment']['subtype'] === 0
        ) {
            $this->payment_method_id = Helpers_Payment_Method::CC_EMERCHANT;
        } elseif (!empty($this->model_whitelabel_payment_method)) {
            $this->payment_method_id = (int)$this->model_whitelabel_payment_method->payment_method_id;
            $this->whitelabel_payment_method_id = (int)$this->model_whitelabel_payment_method->id;
        }
    }
    
    /**
     *
     * @return void
     */
    public function create_deposit_transaction(): void
    {
        $deposit = new Forms_Wordpress_Myaccount_Deposit(
            $this->whitelabel,
            $this->payment_type,
            $this->payment_method_id,
            $this->whitelabel_payment_method_id,
            $this->emerchant_data
        );
        $deposit->set_user($this->user);

        $result = $deposit->process_form();

        switch ($result) {
            case Forms_Wordpress_Myaccount_Deposit::RESULT_OK:
                $this->transaction = $deposit->get_transaction();
                break;
            case Forms_Wordpress_Myaccount_Deposit::RESULT_WITH_ERRORS:
                $this->errors = $deposit->get_errors();
                break;
        }
    }
    
    /**
     *
     * @return void
     */
    public function create_basket_transaction(): void
    {
        $basket = new Forms_Wordpress_Lottery_Basket(
            $this->whitelabel,
            $this->user,
            $this->lotteries,
            $this->basket,
            $this->payment_type,
            $this->payment_method_id,
            $this->whitelabel_payment_method_id,
            $this->emerchant_data,
            $this->input_post
        );
        $basket->process_form();
        $this->transaction = $basket->get_transaction();
        $this->errors = $basket->get_errors();
    }
    
    /**
     *
     * @return void
     */
    public function create_transaction(): void
    {
        if (count($this->errors) > 0) {
            return;
        }
        
        if ($this->deposit) {
            $this->create_deposit_transaction();
        } else {
            if (empty($this->basket) || count($this->basket) === 0) {
                return;
            }
            
            $this->create_basket_transaction();
        }
    }
    
    /**
     *
     * @return void
     */
    public function process_balance_type_transaction(): void
    {
        // depo payment
        if ((int)$this->transaction->whitelabel_id !== (int)$this->whitelabel['id'] ||
            (int)$this->transaction->whitelabel_user_id !== (int)$this->user['id'] ||
            (int)$this->transaction->type !== Helpers_General::TYPE_TRANSACTION_PURCHASE ||
            (int)$this->transaction->status === Helpers_General::STATUS_TRANSACTION_APPROVED
        ) {
            $this->log_error("Bad request.", Helpers_General::PAYMENT_TYPE_BALANCE);
            
            exit(_("Bad request! Please contact us!"));
        }

        if ($this->user['balance'] >= $this->transaction->amount) {
            $set = [
                'payment_method_type' => Helpers_General::PAYMENT_TYPE_BALANCE,
            ];
            $this->transaction->set($set);
            $this->transaction->save();

            $accept_transaction_result = Lotto_Helper::accept_transaction(
                $this->transaction,
                null,
                null,
                $this->whitelabel
            );

            // Now transaction returns result as INT value and
            // we can redirect user to fail page or success page
            // or simply inform system about that fact
            if ($accept_transaction_result === Forms_Transactions_Accept::RESULT_WITH_ERRORS) {
                $this->log_error("Payment failure.", Helpers_General::PAYMENT_TYPE_BALANCE);
                Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
            }
            
            $this->log_success("Payment successful.", Helpers_General::PAYMENT_TYPE_BALANCE);
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_SUCCESS));
        } else {
            $this->log_error("Insufficient funds.", Helpers_General::PAYMENT_TYPE_BALANCE);
            
            exit(_("Bad request! Please contact us!"));
        }
    }

    /**
     *
     * @return void
     */
    public function process_bonus_balance_type_transaction(): void
    {
        // depo payment
        if ((int)$this->transaction->whitelabel_id !== (int)$this->whitelabel['id'] ||
            (int)$this->transaction->whitelabel_user_id !== (int)$this->user['id'] ||
            (int)$this->transaction->type !== Helpers_General::TYPE_TRANSACTION_PURCHASE ||
            (int)$this->transaction->status === Helpers_General::STATUS_TRANSACTION_APPROVED
        ) {
            $this->log_error("Bad request.", Helpers_General::PAYMENT_TYPE_BONUS_BALANCE);
            
            exit(_("Bad request! Please contact us!"));
        }

        /** @var LotteryPurchaseLimitService $lotteryPurchaseLimitService */
        $lotteryPurchaseLimitService = Container::get(LotteryPurchaseLimitService::class);
        $lotteryPurchaseLimitService->setUserId($this->user['id']);
        $lotteryPurchaseLimitService->setWhitelabelId($this->whitelabel['id']);
        $isPurchaseNotAllowed = !$lotteryPurchaseLimitService->isAllowedToPurchaseBasketUsingBonusBalance($this->basket);
        if ($isPurchaseNotAllowed) {
            $this->log_error('User attempted to purchase lottery tickets with bonus balance above daily purchase limit', Helpers_General::PAYMENT_TYPE_BONUS_BALANCE);
            $this->errors['error'] = $lotteryPurchaseLimitService->getErrorMessage();
            return;
        }

        if ($this->user['bonus_balance'] >= $this->transaction->amount) {
            $set = [
                'payment_method_type' => Helpers_General::PAYMENT_TYPE_BONUS_BALANCE,
            ];
            $this->transaction->set($set);
            $this->transaction->save();

            $accept_transaction_result = Lotto_Helper::acceptBonusBalanceTransaction(
                $this->transaction,
                null,
                null,
                $this->whitelabel,
                $lotteryPurchaseLimitService
            );

            // Now transaction returns result as INT value and
            // we can redirect user to fail page or success page
            // or simply inform system about that fact
            if ($accept_transaction_result === Forms_Transactions_Accept::RESULT_WITH_ERRORS) {
                $this->log_error("Payment failure.", Helpers_General::PAYMENT_TYPE_BONUS_BALANCE);
            
                $redirect_url = lotto_platform_home_url(Helper_Route::ORDER_FAILURE);

                Response::redirect($redirect_url);
            }
            
            $this->log_success("Payment successful.", Helpers_General::PAYMENT_TYPE_BONUS_BALANCE);

            $redirect_url = lotto_platform_home_url(Helper_Route::ORDER_SUCCESS);
            
            Response::redirect($redirect_url);
        } else {
            $this->log_error("Insufficient funds.", Helpers_General::PAYMENT_TYPE_BONUS_BALANCE);
            
            exit(_("Bad request! Please contact us!"));
        }
    }
    
    /**
     *
     * @return void
     */
    public function process_cc_type_transaction(): void
    {
        // CC payment
        if ((int)$this->transaction->whitelabel_id !== (int)$this->whitelabel['id'] ||
            (int)$this->transaction->whitelabel_user_id !== (int)$this->user['id'] ||
            (int)$this->transaction->status === Helpers_General::STATUS_TRANSACTION_APPROVED ||
            empty($this->payment_type_cc_object)
        ) {
            exit(_("Bad request! Please contact us!"));
        }
        
        $this->payment_type_cc_object->set_user($this->user);
        $this->payment_type_cc_object->set_transaction($this->transaction);
        $this->payment_type_cc_object->set_lotteries($this->lotteries);
        $this->payment_type_cc_object->process_form();
        $this->errors = $this->payment_type_cc_object->get_errors();
    }
    
    /**
     *
     * @param array $list_of_payment_methods_classes
     * @param int $payment_method_id
     * @return void
     */
    public function validate_other_payment_method(
        array $list_of_payment_methods_classes,
        int $payment_method_id
    ): void {
        if (empty($list_of_payment_methods_classes[$payment_method_id])) {
            $set = [
                'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
            ];
            $this->transaction->set($set);
            $this->transaction->save();

            $this->log_error("Unknown payment subtype.", Helpers_General::PAYMENT_TYPE_OTHER);
            
            exit(_("Bad request! Please contact us!"));
        }
    }
    
    /**
     *
     * @return void
     */
    public function process_other_type_transaction(): void
    {
        if (is_null($this->model_whitelabel_payment_method) ||
            (int)$this->model_whitelabel_payment_method->whitelabel_id !== (int)$this->whitelabel['id'] ||
            (int)$this->transaction->status !== Helpers_General::STATUS_TRANSACTION_PENDING
        ) {
            $this->log_error("Bad request.", Helpers_General::PAYMENT_TYPE_OTHER);
            
            exit(_("Bad request! Please contact us!"));
        }
                            
        $list_of_payment_methods_classes = Helpers_Payment_Method::get_list_of_payment_method_classes();
        $payment_method_id = (int)$this->model_whitelabel_payment_method->payment_method_id;
        
        $this->validate_other_payment_method(
            $list_of_payment_methods_classes,
            $payment_method_id
        );
        
        // Get the name of the payment class
        $name_of_class_form = $list_of_payment_methods_classes[$payment_method_id];
        
        // Creation of the payment class
        $other_type_payment_method_form = new $name_of_class_form(
            $this->whitelabel,
            $this->user,
            $this->transaction,
            $this->model_whitelabel_payment_method,
            $this->payment_method_user_validation
        );
        $wlanguage = LanguageHelper::getCurrentWhitelabelLanguage();
        $code = substr($wlanguage['code'], 0, 2);
        
        $other_type_payment_method_form->set_code($code);
        $other_type_payment_method_form->set_lotteries($this->lotteries);
        
        // Whole creation of the paymant method is called in that function
        // from interface!
        $other_type_payment_method_form->create_payment();
    }
    
    /**
     *
     * @return void
     */
    public function process_default_type_transaction(): void
    {
        $set = [
            'status' => Helpers_General::STATUS_TRANSACTION_ERROR
        ];
        $this->transaction->set($set);
        $this->transaction->save();
        
        $this->log_error("Unknown payment type.");

        exit(_("Bad request! Please contact us!"));
    }

	/**
	 * @return void
	 * @throws Exception
	 */
    public function process_transaction(): void
    {
        if (count($this->errors) > 0 || empty($this->transaction)) {
            return;
        }

		if (!empty($this->model_whitelabel_payment_method)) {
			/** @var PaymentMethod $paymentMethod */
			$paymentMethod = PaymentMethod::find($this->model_whitelabel_payment_method['payment_method_id']);

            /** @var WhitelabelUser $whitelabelUser */
            $whitelabelUser = WhitelabelUser::find($this->user['id']);

			$this->paymentRequestLockService->setUserAndPaymentMethod($whitelabelUser, $paymentMethod);
			$isPaymentRequestLocked = $this->paymentRequestLockService->isSendingRequestLocked();

			if ($isPaymentRequestLocked) {
				$redirect_url = lotto_platform_home_url(Helper_Route::ORDER_FAILURE);
				Response::redirect($redirect_url);
			}

			$this->paymentRequestLockService->logSendingRequest();
		}
        
        switch ($this->payment_type) {
            case Helpers_General::PAYMENT_TYPE_BONUS_BALANCE:
                $this->process_bonus_balance_type_transaction();
                break;
            case Helpers_General::PAYMENT_TYPE_BALANCE:
                $this->process_balance_type_transaction();
                break;
            case Helpers_General::PAYMENT_TYPE_CC:
                $this->process_cc_type_transaction();
                break;
            case Helpers_General::PAYMENT_TYPE_OTHER:
                $this->process_other_type_transaction();
                break;
            default:
                $this->process_default_type_transaction();
                break;
        }
    }
    
    /**
     *
     * @return void
     */
    public function main_process(): void
    {
        if (empty($this->input_post) ||
            empty($this->input_post['payment']) ||
            empty($this->input_post['payment']['type']) ||
            !$this->is_user
        ) {
            return;
        }

        if (!$this->check_token()) {
             return;
        }
        
        $this->get_payment_type();
        
        $this->validate_payment_type();
        
        $this->get_model_whitelabel_payment_method();
        
        $this->get_emerchant_data();
        
        $this->process_emerchant_payment();
        
        $this->extra_validation();
        
        $this->basket = Session::get("order");
        
        $this->get_payment_method_ids();
        
        $this->create_transaction();
        
        $this->process_transaction();
    }
    
    /**
     *
     * @return void
     */
    public function prepare_new_basket(): void
    {
        $basket = Session::get("order");
        
        if (empty($basket) || count($basket) === 0) {
            return;
        }

        $newbasket = [];
        foreach ($basket as $item) {
            if (!isset($this->lotteries['__by_id'][$item['lottery']])) {
                continue;
            }
            $lottery = $this->lotteries['__by_id'][$item['lottery']];

            if ($lottery['is_temporarily_disabled'] == 1) {
                continue;
            }

            if ($lottery['playable'] != 1) {
                continue;
            }
            
            if (isset($item['multidraw']) && ($lottery['is_multidraw_enabled'] == 0
                || $lottery['multidraws_enabled'] == 0)) {
                continue;
            }
            $newbasket[] = $item;
        }

        if (count($basket) != count($newbasket)) {
            Session::set("order", $newbasket);

            $userId = lotto_platform_user_id();
            if ($userId) {
                $this->cartService->createOrUpdateCart($userId, $newbasket);
            }

            $message = _(
                'A part of your order was removed as ' .
                'we have disabled some of the lotteries.'
            );
            $this->errors = ['order' => $message];
        }
    }

    /**
     * If this payment method is available only for deposit cancel the order
     */
    private function check_deposit_only_block(): void
    {
        $model_whitelabel_payment_method = $this->get_model_whitelabel_payment_method();

        if (!empty($model_whitelabel_payment_method) && 
            (int)$model_whitelabel_payment_method['only_deposit'] === 1 &&
            $this->deposit !== true
        ) {
            $message = _(
                "This payment method is available in the deposit section, " .
                "because the confirmation may take longer."
            );
            Session::set("message", ["error", $message]);
            Response::redirect(lotto_platform_home_url('/'));
        }
    }

    /**
     *
     * @return void
     */
    private function prepare_pixels(): void
    {
        $basket = Session::get("order");

        if (empty($basket) && !$this->deposit) {
            return;
        }

        $total_price = 0;
        $currency = lotto_platform_user_currency();

        if (!$this->deposit) {
            foreach ($basket as $item) {
                $lottery = $this->lotteries['__by_id'][$item['lottery']];

                $price = lotto_platform_get_pricing($lottery);
                $total_price += $price * count($item['lines']);

                $items[] = [
                    "id" => $this->whitelabel['prefix'] . '_' . Lotto_Helper::get_lottery_short_name($lottery) . '_TICKET',
                    "name" => $lottery['name'],
                    "list_name" => "Checkout",
                    "quantity" => count($item['lines']),
                    "price" => $price,
                    "currency" => $currency
                ];
            }

            \Fuel\Core\Event::trigger('user_cart_checkout', [
                'whitelabel_id' => $this->whitelabel['id'],
                'user_id' => lotto_platform_is_user() ? lotto_platform_user()["id"] : null,
                'plugin_data' => [
                    "items" => $items,
                    "price" => round($total_price, 2),
                    "currency" => $currency
                ]
            ]);
        } else {
            $deposit_item = [[
                "id" => $this->whitelabel['prefix'] . '_DEPOSIT',
                "name" => "DEPOSIT",
                "list_name" => "Deposit",
                "quantity" => 1,
                "price" => "--deposit--",
                "currency" => $currency
            ]];

            \Fuel\Core\Event::trigger('user_cart_checkout', [
                'whitelabel_id' => $this->whitelabel['id'],
                'user_id' => lotto_platform_is_user() ? lotto_platform_user()["id"] : null,
                'plugin_data' => [
                    "items" => $deposit_item,
                    "price" => "--deposit--",
                    "currency" => $currency
                ]
            ]);
        }
    }

    /**
     *
     * @return void
     */
    public function process_form(): void
    {
        $this->prepare_new_basket();
        
//      Removed direct Facebook Pixel call; the event is now sent via GTM
//        $this->prepare_pixels();

        $this->get_whitelabel_payment_methods_indexed();

        $this->check_deposit_only_block();
        
        $this->check_and_process_lines();
        
        $this->main_process();
    }
}

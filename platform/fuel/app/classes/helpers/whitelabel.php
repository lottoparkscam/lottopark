<?php

use Services\Logs\FileLoggerService;
use Fuel\Core\DB;
use Helpers\Wordpress\LanguageHelper;

require_once APPPATH."vendor/emerchantpay/WebServices-SDK-php_20161013/WebServices-SDK-php-EMP/lib/WSSDK/WSSDK.php";

/**
 * @deprecated Helper shouldn't have constructor function. Nor should it do too much work.
 */
final class Helpers_Whitelabel
{

    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @var array
     */
    private $wlang = [];

    /**
     *
     * @var array
     */
    private $user = [];

    /**
     *
     * @var array
     */
    private $auser = [];

    /**
     *
     * @var array
     */
    private $euser = null;

    /**
     *
     * @var string
     */
    private $username = "";

    /**
     *
     * @var string
     */
    private $user_email = "";

    /**
     *
     * @var SimpleXMLElement
     */
    private $xerrors;

    private FileLoggerService $fileLoggerService;

    /**
     *
     * @param array $whitelabel
     * @param array $user
     * @param string $username
     * @param string $user_email
     * @param array $auser
     */
    public function __construct(
        $whitelabel = [],
        $user = [],
        $username = "",
        $user_email = "",
        $auser = []
    ) {
        $this->whitelabel = $whitelabel;
        $this->user = $user;
        $this->username = $username;
        $this->user_email = $user_email;
        $this->auser = $auser;
        $this->wlang = LanguageHelper::getCurrentWhitelabelLanguage();
        $this->fileLoggerService = Container::get(FileLoggerService::class);
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
     * @return \Model_Whitelabel_User|null
     */
    private function get_user():? Model_Whitelabel_User
    {
        $result = null;
        $user = $this->user;
        if (is_null($user)) {
            return $result;
        }
        
        $user_obj = Model_Whitelabel_User::find_by_pk($user['id']);
        
        if (is_null($user_obj)) {
            return $result;
        }
                
        return $user_obj;
    }

    /**
     *
     * @return string
     */
    public function get_username():? string
    {
        return $this->username;
    }

    /**
     *
     * @return string
     */
    public function get_user_email():? string
    {
        return $this->user_email;
    }

    /**
     *
     * @return object
     */
    public function get_auser():? object
    {
        return $this->auser;
    }

    /**
     *
     * @return Model_Emerchantpay_User
     */
    public function get_euser():? Model_Emerchantpay_User
    {
        if (empty($this->euser)) {
            $user_obj = $this->get_user();
            if (is_null($user_obj)) {
                return null;
            }
            $result = Model_Emerchantpay_User::find_by_whitelabel_user_id($user_obj->id);
            if (!is_null($result)) {
                $this->euser = $result[0];
            }
        }

        return $this->euser;
    }

    /**
     *
     * @return \SimpleXMLElement
     */
    public function get_xerrors()
    {
        return $this->xerrors;
    }

    /**
     *
     * @param SimpleXMLElement $xerrors
     */
    public function set_xerrors($xerrors): void
    {
        $this->xerrors = $xerrors;
    }

    /**
     * This function is needed for set the expired time for cache
     *
     * @return int
     */
    public static function get_expired_time(): int
    {
        $expiredTime = 86400;
        
        return $expiredTime;
    }

    /**
     *
     * @param array $ccmethods_merchant
     * @return bool
     */
    private function process_cc($ccmethods_merchant): bool
    {
        $result = false;
        $emerchant_method_id = Model_Whitelabel_CC_Method::get_emerchant_method_id();
        
        $euser = $this->get_euser();
        $pdata = unserialize($ccmethods_merchant[$emerchant_method_id]['settings']);

        if (!empty($euser) &&
            !empty($pdata['accountid']) &&
            !empty($pdata['apikey']) &&
            !empty($pdata['endpoint'])
        ) {
            $test_transaction = $pdata['test'];
            $api_domain = str_replace(
                ["https://", "http://"],
                "",
                $pdata['endpoint']
            );
            $myWSSDK = new \WSSDK(
                $pdata['accountid'],
                $pdata['apikey'],
                $api_domain
            );

            $username = $this->get_username();
            $user_email = $this->get_user_email();
            $model = new \WSSDK\Model\CustomerUpdate(
                $euser['customer_id'],
                $user_email,
                $username
            );
            $req = $myWSSDK->customerUpdateRequest($model, $test_transaction);

            $res = $req->send();
            $xerrors = $res->getError();
            if ($xerrors === null) {
                $result = true;
            } else {
                $this->set_xerrors($xerrors);
            }
        }

        return $result;
    }

    public function process_cc_method_user_edit(): bool
    {
        $success = true;
        $whitelabel = $this->get_whitelabel();
        $emerchant_method_id = Model_Whitelabel_CC_Method::get_emerchant_method_id();
        
        // make sure user e-mail and name is up to date
        try {
            $ccmethods = Model_Whitelabel_CC_Method::get_cc_methods_for_whitelabel($whitelabel);
            $ccmethods_merchant = [];
            foreach ($ccmethods as $ccmethod) {
                $ccmethods_merchant[intval($ccmethod['method'])] = $ccmethod;
            }

            // check if emerchant is set
            if (empty($ccmethods_merchant[$emerchant_method_id])) {
                return $success;
            }

            $euser = $this->get_euser();
            
            if (is_null($euser)) {
                return $success;
            }
                        
            $user_email = $this->get_user_email();
            $process_CC_OK = $this->process_cc($ccmethods_merchant);

            if (!$process_CC_OK) {
                $xerrors = $this->get_xerrors();
                $error_msg = var_export($xerrors, true);
                
                Model_Payment_Log::add_log(
                    Helpers_General::TYPE_ERROR,
                    Helpers_General::PAYMENT_TYPE_CC,
                    null,
                    Helpers_Payment_Method::CC_EMERCHANT,
                    $whitelabel['id'],
                    null,
                    "Cannot update user data.",
                    [
                        $euser['customer_id'],
                        $user_email,
                        $error_msg
                    ]
                );
                $success = false;
            } else {
                Model_Payment_Log::add_log(
                    Helpers_General::TYPE_SUCCESS,
                    Helpers_General::PAYMENT_TYPE_CC,
                    null,
                    Helpers_Payment_Method::CC_EMERCHANT,
                    $whitelabel['id'],
                    null,
                    "Updated user data.",
                    [
                        $euser['customer_id'],
                        $user_email
                    ]
                );
            }
        } catch (Exception $e) {
            $this->fileLoggerService->error(
                $e->getMessage()
            );

            $error_message = [
                $e->getFile(),
                $e->getLine(),
                $e->getMessage()
            ];
            
            Model_Payment_Log::add_log(
                Helpers_General::TYPE_ERROR,
                Helpers_General::PAYMENT_TYPE_CC,
                null,
                Helpers_Payment_Method::CC_EMERCHANT,
                $whitelabel['id'],
                null,
                "Cannot update user data - unknown error.",
                $error_message
            );
            $success = false;
        }

        return $success;
    }

    /**
     *
     * @return bool
     * @throws Exception
     */
    public function process_cc_method_user_email(): bool
    {
        $success = true;
        $user_obj = $this->get_user();
        if (is_null($user_obj)) {
            return false;
        }
        $whitelabel = $this->get_whitelabel();
        $user_email = $this->get_user_email();
        
        $emerchant_method_id = Model_Whitelabel_CC_Method::get_emerchant_method_id();
        
        try {
            DB::start_transaction();

            $user_obj->set([
                'email' => $user_email,
                'last_update' => DB::expr("NOW()")
            ]);
            $user_obj->save();

            // make sure user e-mail and name is up to date
            $ccmethods = Model_Whitelabel_CC_Method::get_cc_methods_for_whitelabel($whitelabel);
            $ccmethods_merchant = [];
            foreach ($ccmethods as $ccmethod) {
                $ccmethods_merchant[intval($ccmethod['method'])] = $ccmethod;
            }

            // check if eMerchantPay is set
            if (empty($ccmethods_merchant[$emerchant_method_id])) {
                DB::commit_transaction();   // No eMerchantPay account - this is OK
                // and we don't want to show error about that to the user
                return $success;
            }

            $euser = $this->get_euser();
            
            if (is_null($euser)) {
                DB::commit_transaction();
                return $success;
            }
            
            $process_CC_OK = $this->process_cc($ccmethods_merchant);

            if (!$process_CC_OK) {
                $xerrors = $this->get_xerrors();
                $error_msg = var_export($xerrors, true);
                throw new Exception($error_msg);
            } else {
                Model_Payment_Log::add_log(
                    Helpers_General::TYPE_SUCCESS,
                    Helpers_General::PAYMENT_TYPE_CC,
                    null,
                    Helpers_Payment_Method::CC_EMERCHANT,
                    $whitelabel['id'],
                    null,
                    "Updated user e-mail.",
                    [
                        $euser['customer_id'],
                        $user_email
                    ]
                );
            }

            DB::commit_transaction();
        } catch (Exception $e) {
            DB::rollback_transaction();

            $this->fileLoggerService->error(
                $e->getMessage()
            );
            
            $error_message = [
                $e->getFile(),
                $e->getLine(),
                $e->getMessage()
            ];
            
            Model_Payment_Log::add_log(
                Helpers_General::TYPE_ERROR,
                Helpers_General::PAYMENT_TYPE_CC,
                null,
                Helpers_Payment_Method::CC_EMERCHANT,
                $whitelabel['id'],
                null,
                "Cannot update user e-mail.",
                $error_message
            );
            
            $success = false;
        }

        return $success;
    }

    /**
     *
     * @return bool
     * @throws Exception
     */
    public function process_cc_method_myaccount_emailchange(): bool
    {
        $success = true;
        $auser = $this->get_auser();
        $user_email = $this->get_user_email();
        $whitelabel = $this->get_whitelabel();

        $emerchant_method_id = Model_Whitelabel_CC_Method::get_emerchant_method_id();
        
        try {
            DB::start_transaction();

            $is_active = 1;
            if ($whitelabel['user_activation_type'] == Helpers_General::ACTIVATION_TYPE_REQUIRED) {
                $is_active = 0;
            }
            
            $auser->set([
                'is_active' => $is_active,
                'is_confirmed' => 1,
                'pending_email' => $user_email,
                'last_update' => DB::expr("NOW()")
            ]);
            $auser->save();

            // make sure user e-mail and name is up to date
            $ccmethods = Model_Whitelabel_CC_Method::get_cc_methods_for_whitelabel($whitelabel);
            $ccmethods_merchant = [];
            foreach ($ccmethods as $ccmethod) {
                $ccmethods_merchant[intval($ccmethod['method'])] = $ccmethod;
            }

            // check if emerchant is set
            if (empty($ccmethods_merchant[$emerchant_method_id])) {
                DB::commit_transaction();   // No emerachants accounts - this is OK
                // and we don't want to show error about that to the user
                return $success;
            }

            $euser = $this->get_euser();
            
            if (is_null($euser)) {
                DB::commit_transaction();
                return $success;
            }
            
            $process_CC_OK = $this->process_cc($ccmethods_merchant);

            if (!$process_CC_OK) {
                $xerrors = $this->get_xerrors();
                $error_msg = var_export($xerrors, true);
                throw new Exception($error_msg);
            } else {
                Model_Payment_Log::add_log(
                    Helpers_General::TYPE_SUCCESS,
                    Helpers_General::PAYMENT_TYPE_CC,
                    null,
                    Helpers_Payment_Method::CC_EMERCHANT,
                    $whitelabel['id'],
                    null,
                    "Updated user e-mail.",
                    [
                        $euser['customer_id'],
                        $auser['email']
                    ]
                );
            }

            DB::commit_transaction();
        } catch (Exception $e) {
            DB::rollback_transaction();

            $this->fileLoggerService->error(
                $e->getMessage()
            );
            
            $error_message = [
                $e->getFile(),
                $e->getLine(),
                $e->getMessage()
            ];
            
            Model_Payment_Log::add_log(
                Helpers_General::TYPE_ERROR,
                Helpers_General::PAYMENT_TYPE_CC,
                null,
                Helpers_Payment_Method::CC_EMERCHANT,
                $whitelabel['id'],
                null,
                "Cannot update user e-mail.",
                $error_message
            );
            $success = false;
        }

        return $success;
    }

    public function process_cc_method_myaccount_profile(): bool
    {
        $success = true;
        $whitelabel = $this->get_whitelabel();
        
        $emerchant_method_id = Model_Whitelabel_CC_Method::get_emerchant_method_id();
        
        // make sure user e-mail and name is up to date
        try {
            $ccmethods = Model_Whitelabel_CC_Method::get_cc_methods_for_whitelabel($whitelabel);
            $ccmethods_merchant = [];
            foreach ($ccmethods as $ccmethod) {
                $ccmethods_merchant[intval($ccmethod['method'])] = $ccmethod;
            }

            // check if emerchant is set
            if (empty($ccmethods_merchant[$emerchant_method_id])) {
                return $success;
            }

            $euser = $this->get_euser();
            
            if (is_null($euser)) {
                return $success;
            }
            
            $user_email = $this->get_user_email();
            $process_CC_OK = $this->process_cc($ccmethods_merchant);

            if (!$process_CC_OK) {
                $xerrors = $this->get_xerrors();
                $error_msg = var_export($xerrors, true);
                
                Model_Payment_Log::add_log(
                    Helpers_General::TYPE_ERROR,
                    Helpers_General::PAYMENT_TYPE_CC,
                    null,
                    Helpers_Payment_Method::CC_EMERCHANT,
                    $whitelabel['id'],
                    null,
                    "Cannot update user data.",
                    [
                        $euser['customer_id'],
                        $user_email,
                        $error_msg
                    ]
                );
                $success = false;
            } else {
                Model_Payment_Log::add_log(
                    Helpers_General::TYPE_SUCCESS,
                    Helpers_General::PAYMENT_TYPE_CC,
                    null,
                    Helpers_Payment_Method::CC_EMERCHANT,
                    $whitelabel['id'],
                    null,
                    "Updated user data.",
                    [
                        $euser['customer_id'],
                        $user_email
                    ]
                );
            }
        } catch (Exception $e) {
            $this->fileLoggerService->error(
                $e->getMessage()
            );

            $error_message = [
                $e->getFile(),
                $e->getLine(),
                $e->getMessage()
            ];
            
            Model_Payment_Log::add_log(
                Helpers_General::TYPE_ERROR,
                Helpers_General::PAYMENT_TYPE_CC,
                null,
                Helpers_Payment_Method::CC_EMERCHANT,
                $whitelabel['id'],
                null,
                "Cannot update user e-mail and data - unknown error.",
                $error_message
            );
            $success = false;
        }

        return $success;
    }

    public function prepare_eaffs(string $email): array
    {
        $whitelabel = $this->get_whitelabel();

        $params = [];
        
        if (isset($whitelabel) && !empty($whitelabel["id"])) {
            $params["where"] = [
                "whitelabel_id" => $whitelabel["id"],
                'email' => $email,
            ];
        }
        
        $params["order_by"] = [
            "name" => "ASC",
            "surname" => "ASC",
            "login" => "ASC"
        ];

        $affs = Model_Whitelabel_Aff::find($params);

        $eaffs = [];

        if (!empty($affs) && count($affs) > 0) {
            foreach ($affs as $aff) {
                $eaffs[$aff['email']] = $aff;
            }
        }

        return $eaffs;
    }
    
    /**
     *
     * @param int $type
     * @return bool
     */
    public static function is_V1(int $type = null): bool
    {
        $result = false;
        if (!empty($type) &&
            $type === Helpers_General::WHITELABEL_TYPE_V1
        ) {
            $result = true;
        }
        
        return $result;
    }
    
    /**
     *
     * @param int $whitelabel_id
     * @return bool
     */
    public static function is_special_ID(int $whitelabel_id = null): bool
    {
        $result = false;
        if (!empty($whitelabel_id) &&
            $whitelabel_id === Helpers_General::WHITELABEL_ID_SPECIAL
        ) {
            $result = true;
        }
        
        return $result;
    }
    
    /**
     * The function checked if current whitelabel is permitted to see fully
     * features in different situations (based on type - V1 of V2).
     * @param array $whitelabel
     * @return bool
     */
    public static function is_permitted(array $whitelabel = null): bool
    {
        $is_permitted = true;
        if (!empty($whitelabel) &&
            self::is_V1($whitelabel['type']) &&
            !self::is_special_ID($whitelabel['id'])
        ) {
            $is_permitted = false;
        }
        
        return $is_permitted;
    }
    
    /**
     * Based on permission function shows 'Bad request' or go further
     * @param array $whitelabel
     * @return void
     */
    public static function check_permission(array $whitelabel = null): void
    {
        $is_permitted = self::is_permitted($whitelabel);
        if (!$is_permitted) {
            exit('Bad request');
        }
    }

}

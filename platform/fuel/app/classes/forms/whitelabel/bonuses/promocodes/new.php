<?php

use \Fuel\Core\Validation;
use Repositories\Aff\WhitelabelAffRepository;

class Forms_Whitelabel_Bonuses_Promocodes_New extends Forms_Main
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

    private WhitelabelAffRepository $whitelabelAffRepository;

    /**
     *
     * @return type
     */
    public function get_inside()
    {
        return $this->inside;
    }

    /**
    *
    * @param array $whitelabel
    */
    public function __construct($whitelabel)
    {
        $this->whitelabel = $whitelabel;
        $this->whitelabelAffRepository = Container::get(WhitelabelAffRepository::class);
    }
    
    /**
     *
     * @return int
     */
    public function get_max_lottery_id(): int
    {
        //Borrowed from Forms_Whitelabel_Bonuses_Referafriend
        $max_lottery_id = 0;

        $last_key_id = 0;
        if (!empty($this->lotteries)) {
            $last_key_id = count($this->lotteries) - 1;
            $max_lottery_id = (int) $this->lotteries[$last_key_id]['id'];
        }

        return $max_lottery_id;
    }

    /**
     *
     * @param int $codes_num
     * @param int $code_length
     * @return int
     */
    private function check_token_length($codes_num, $code_length)
    {
        $combinations_num = self::get_combinations_num_for_range($code_length);
        if ($combinations_num - $codes_num >= 0) {
            return $code_length;
        }

        return 0;
    }

    /**
     *
     * @param int $range
     * @return int
     */
    private function get_combinations_num_for_range($range)
    {
        //WE CAN USE 35 SIGNS - 25 ALPHA AND 10 NUM
        $all_combinations = pow(35, $range);
        $codes = DB::select(DB::expr('count(*) as count'))
            ->from('whitelabel_promo_code')
            ->where(DB::expr('CHAR_LENGTH(token)'), '=', $range)
            ->execute()->as_array();
        
        $count = 0;
        if ($codes[0]['count'] > 0) {
            $count = $codes[0]['count'];
        }
        $all_combinations = $all_combinations - $count;

        return $all_combinations;
    }

    /**
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $val = Validation::forge();
        $val->add_callable($this);

        $val->set_message('unique', _('This code prefix has already been used.'));
        
        $val->add("input.purchase", _("Purchase"))
            ->add_rule("trim")
            ->add_rule("match_collection", [0,1]);
        $val->add("input.deposit", _("Deposit"))
            ->add_rule("trim")
            ->add_rule("match_collection", [0,1]);
        $val->add("input.register", _("Register"))
            ->add_rule("trim")
            ->add_rule("match_collection", [0,1]);
        $val->add("input.aff", _("Affiliate"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('valid_email')
            ->add_rule('max_length', 254);
        $val->add("input.codes_type", _("Codes number"))
            ->add_rule("trim")
            ->add_rule("match_collection", [0,1]);
        $val->add("input.code", _("Code"))
            ->add_rule("required")
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 30)
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes'])
            ->add_rule('unique');
        if (Input::post("input.codes_type") === "1") {
            $val->add("input.code_length", _("Code length"))
                ->add_rule("required")
                ->add_rule("trim")
                ->add_rule('numeric_min', 6)
                ->add_rule('numeric_max', 20)
                ->add_rule("is_numeric");
            $val->add("input.codes_num", _("Number of codes"))
                ->add_rule("required")
                ->add_rule("trim")
                ->add_rule('numeric_min', 2)
                ->add_rule("is_numeric");
            if (Input::post("input.codes_user_num")) {
                $val->add("input.codes_user_num", _("Number of codes one user can use"))
                    ->add_rule("trim")
                    ->add_rule("is_numeric");
            }
        }
        $val->add("input.bonus_type", _("Bonus type"))
            ->add_rule("trim")
            ->add_rule("match_collection", [0,1,2]);
        if (Input::post("input.bonus_type") === "0") {
            $val->add('input.lottery', _('Lottery'))
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('is_numeric')
            ->add_rule('numeric_min', 0)
            ->add_rule('numeric_max', $this->get_max_lottery_id());
        }
        if (Input::post("input.users_num")) {
            $val->add("input.users_num", _("How many users can use one code"))
            ->add_rule("trim")
            ->add_rule("is_numeric");
        }
        if (Input::post("input.users_limit")) {
            $val->add("input.users_limit", _("Max number of users who can use this bonus"))
            ->add_rule("trim")
            ->add_rule("is_numeric");
        }
        if ((Input::post("input.bonus_type") === "1") || (Input::post("input.bonus_type") === "2")) {
            $val->add("input.discount_type", _("Amount type"))
            ->add_rule("required")
            ->add_rule("trim")
            ->add_rule("match_collection", [0,1]);
            $val->add("input.amount", _("Discount amount"))
            ->add_rule("required")
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 9999999);
        }
        $val->add("input.start_date", _("Start date"))
            ->add_rule("required")
            ->add_rule('trim')
            ->add_rule('valid_string', ['numeric', 'forwardslashes']);
        $val->add("input.end_date", _("End date"))
            ->add_rule("required")
            ->add_rule('trim')
            ->add_rule('valid_string', ['numeric', 'forwardslashes']);
        $val->add("input.is_active", _("Enabled"))
            ->add_rule("trim")
            ->add_rule("match_value", "1");
        
        return $val;
    }

    public static function _validation_unique($val)
    {
        $result = DB::select(DB::expr("LOWER ('prefix')"))
        ->where('prefix', '=', Str::lower($val))
        ->from('whitelabel_campaign')->execute();

        return ! ($result->count() > 0);
    }

    /**
     *
     * @return null
     */
    public function process_form()
    {
        $inside = View::forge("whitelabel/bonuses/promocodes/new.php");

        $this->lotteries = Model_Lottery::get_all_lotteries_for_whitelabel_short($this->whitelabel['id']);
        $currency = Model_Currency::find_by_pk($this->whitelabel['manager_site_currency_id']);
                    
        $inside->set('currency_code', $currency->code);
        $inside->set('lotteries', $this->lotteries);

        $inside->set('rparam', 'promocodes');
        $this->inside = $inside;

        if (Input::post()) {
            $val = $this->validate_form();

            if ($val->run()) {
                if ((int)$val->validated("input.deposit") === 1 && (int)$val->validated("input.bonus_type") === 1) {
                    $errors = ['input.bonus_type' => _("Discount cannot be applied to deposit bonus!")];
                    $this->inside->set("errors", $errors);
                    return self::RESULT_WITH_ERRORS;
                }
                $is_type_bonus_balance = ((int)$val->validated("input.purchase") === 1 && (int)$val->validated("input.bonus_type") === 2);
                if ($is_type_bonus_balance) {
                    $errors = ['input.bonus_type' => _("Bonus money cannot be applied to purchase bonus!")];
                    $this->inside->set("errors", $errors);
                    return self::RESULT_WITH_ERRORS;
                }

                $type = 0;
                if (((int)$val->validated("input.purchase") === 1) && ((int)$val->validated("input.deposit") === 1) && ((int)$val->validated("input.register") === 1)) {
                    $type = Helpers_General::PROMO_CODE_TYPE_PURCHASE_DEPOSIT_REGISTER;
                } elseif (((int)$val->validated("input.purchase") === 0) && ((int)$val->validated("input.deposit") === 1) && ((int)$val->validated("input.register") === 1)) {
                    $type = Helpers_General::PROMO_CODE_TYPE_DEPOSIT_REGISTER;
                } elseif (((int)$val->validated("input.purchase") === 1) && ((int)$val->validated("input.deposit") === 0) && ((int)$val->validated("input.register") === 1)) {
                    $type = Helpers_General::PROMO_CODE_TYPE_PURCHASE_REGISTER;
                } elseif (((int)$val->validated("input.purchase") === 1) && ((int)$val->validated("input.deposit") === 1) && ((int)$val->validated("input.register") === 0)) {
                    $type = Helpers_General::PROMO_CODE_TYPE_PURCHASE_DEPOSIT;
                } elseif (((int)$val->validated("input.purchase") === 0) && ((int)$val->validated("input.deposit") === 0) && ((int)$val->validated("input.register") === 1)) {
                    $type = Helpers_General::PROMO_CODE_TYPE_REGISTER;
                } elseif (((int)$val->validated("input.purchase") === 0) && ((int)$val->validated("input.deposit") === 1) && ((int)$val->validated("input.register") === 0)) {
                    $type = Helpers_General::PROMO_CODE_TYPE_DEPOSIT;
                } elseif (((int)$val->validated("input.purchase") === 1) && ((int)$val->validated("input.deposit") === 0) && ((int)$val->validated("input.register") === 0)) {
                    $type = Helpers_General::PROMO_CODE_TYPE_PURCHASE;
                } else {
                    $errors = ['input.purchase' => _("Choose campaign type!")];
                    $this->inside->set("errors", $errors);
                    return self::RESULT_WITH_ERRORS;
                }

                $affNew = $this->whitelabelAffRepository->findAffiliateByEmail($val->validated("input.aff"), $this->whitelabel['id']);

                $isValidAff = !empty($affNew['id']) && $affNew['is_deleted'] == 0 && $affNew['is_active'] == 1 && $affNew['is_accepted'] == 1;
                if ($isValidAff) {
                    $affId = $affNew['id'];
                } else {
                    $affId = null;
                }

                $max_codes_user = null;
                if ((int)$val->validated("input.codes_type") === 1) {
                    if ($val->validated("input.codes_user_num") > 0) {
                        $max_codes_user = $val->validated("input.codes_user_num");
                    }
                }

                $max_users_per_code = null;
                if (Input::post("input.users_num")) {
                    $max_users_per_code = $val->validated("input.users_num");
                }

                $max_users = null;
                if (Input::post("input.users_limit")) {
                    $max_users = $val->validated("input.users_limit");
                }

                $lottery_id = null;
                $discount_type = null;
                $bonus_balance_type = null;
                if ((int)$val->validated("input.bonus_type") === 0) {
                    if ((int)$val->validated("input.lottery") === 0) {
                        $errors = ["input.lottery" => _("Choose lottery!")];
                        $this->inside->set("errors", $errors);
                        return self::RESULT_WITH_ERRORS;
                    }
                    $lottery_id = (int)$val->validated("input.lottery");
                } elseif ((int)$val->validated("input.bonus_type") === 1) {
                    $discount_type = (int)$val->validated("input.discount_type");
                } elseif ((int)$val->validated("input.bonus_type") === 2) {
                    $bonus_balance_type = (int)$val->validated("input.discount_type");
                }

                $discount_amount = null;
                if ($discount_type === Helpers_General::PROMO_CODE_DISCOUNT_TYPE_AMOUNT) {
                    $discount_amount = $val->validated("input.amount");
                } elseif ($discount_type === Helpers_General::PROMO_CODE_DISCOUNT_TYPE_PERCENT) {
                    if (!((int)$val->validated("input.amount") < 100)) {
                        $errors = ["input.amount" => _("Wrong amount!")];
                        $this->inside->set("errors", $errors);
                        return self::RESULT_WITH_ERRORS;
                    }
                    $discount_amount = $val->validated("input.amount");
                }
                
                $bonus_balance_amount = null;
                if ($bonus_balance_type === Helpers_General::PROMO_CODE_BONUS_BALANCE_TYPE_AMOUNT) {
                    $bonus_balance_amount = number_format($val->validated("input.amount"), 2, ".", "");
                } elseif ($bonus_balance_type === Helpers_General::PROMO_CODE_BONUS_BALANCE_TYPE_PERCENT) {
                    if ((int)$val->validated("input.register") === 1) {
                        $errors = array("input.amount" => _("Wrong amount!"));
                        $this->inside->set("errors", $errors);
                        return self::RESULT_WITH_ERRORS;
                    }
                    if (!((int)$val->validated("input.amount") < 100)) {
                        $errors = ["input.amount" => _("Wrong amount!")];
                        $this->inside->set("errors", $errors);
                        return self::RESULT_WITH_ERRORS;
                    }
                    $bonus_balance_amount = $val->validated("input.amount");
                }

                list(
                $start_date_ok,
                $sdt
            ) = Helpers_General::validate_date(
                $val->validated('input.start_date'),
                "m/d/Y"
            );

                if (!$start_date_ok) {
                    $errors = ['input.start_date' => _("Wrong start date!")];
                    $this->inside->set("errors", $errors);
                    return self::RESULT_WITH_ERRORS;
                }

                list(
                $end_date_ok,
                $edt
            ) = Helpers_General::validate_date(
                $val->validated('input.end_date'),
                "m/d/Y"
            );

                if (!$end_date_ok) {
                    $errors = ['input.end_date' => _("Wrong end date!")];
                    $this->inside->set("errors", $errors);
                    return self::RESULT_WITH_ERRORS;
                }

                $date_start = $sdt->format('Y-m-d');
                $date_end = $edt->format('Y-m-d');

                $token = Lotto_Security::generate_whitelabel_campaign_token($this->whitelabel['id']);

                $campaign = Model_Whitelabel_Campaign::forge();

                $campaign_values = [
                    "token" => $token,
                    "whitelabel_id" => $this->whitelabel['id'],
                    "bonus_type" => (int)$val->validated("input.bonus_type"),
                    "type" => $type,
                    "whitelabel_aff_id" => $affId,
                    "lottery_id" => $lottery_id,
                    'max_codes_user' => $max_codes_user,
                    'max_users_per_code' => $max_users_per_code,
                    "prefix" => $val->validated("input.code"),
                    "is_active" => (int)$val->validated("input.is_active"),
                    "date_start" => $date_start,
                    "date_end" => $date_end,
                    "max_users" => $max_users,
                    "discount_amount" => $discount_amount,
                    "discount_type" => $discount_type,
                    "bonus_balance_amount" => $bonus_balance_amount,
                    "bonus_balance_type" => $bonus_balance_type
                ];

                $campaign->set($campaign_values);
                $id = $campaign->save();

                if ((int)$val->validated("input.codes_type") === 1) {
                    $codes_num = $val->validated("input.codes_num");
                    $code_length = $val->validated("input.code_length");
                    $length = self::check_token_length($codes_num, $code_length);
                    if ($length == 0) {
                        $errors = ["input.codes_num" => _("Number of codes is too big for this code length!")];
                        $this->inside->set("errors", $errors);
                        return self::RESULT_WITH_ERRORS;
                    }
                
                    $codes_data = [];
                    for ($i = 0; $i < $codes_num; $i++) {
                        $token = Lotto_Security::generate_promo_code_token($length);
                        array_push($codes_data, [$token, $id[0]]);
                    }
                    $insert = DB::insert('whitelabel_promo_code')->columns(['token', 'whitelabel_campaign_id'])->values($codes_data)->execute();
                    if (!isset($insert[0])) {
                        Session::set_flash("message", ["danger", _("There is something wrong with DB!")]);
                        Response::redirect("bonuses/promocodes");
                    }
                } else {
                    $insert = DB::insert('whitelabel_promo_code')->columns(['whitelabel_campaign_id'])->values([$id[0]])->execute();
                    if (!isset($insert[0])) {
                        Session::set_flash("message", ["danger", _("There is something wrong with DB!")]);
                        Response::redirect("bonuses/promocodes");
                    }
                }

                Session::set_flash("message", ["success", _("Campaign has been successfully created!")]);
            } else {
                $errors = Lotto_Helper::generate_errors($val->error());
                $this->inside->set("errors", $errors);

                return self::RESULT_WITH_ERRORS;
            }

            return self::RESULT_OK;
        } else {
            return self::RESULT_GO_FURTHER;
        }
    }
}

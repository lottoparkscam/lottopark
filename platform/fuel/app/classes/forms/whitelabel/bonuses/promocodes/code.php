<?php

use \Fuel\Core\Validation;
use Helpers\CurrencyHelper;
use Repositories\WhitelabelUserPromoCodeRepository;

class Forms_Whitelabel_Bonuses_Promocodes_Code extends Forms_Main
{
    const TYPE_PURCHASE = 0;
    const TYPE_DEPOSIT = 1;
    const TYPE_REGISTER = 2;

    /**
     *
     * @var Forms_Whitelabel_Bonuses_Promocodes_Code
     */
    private static $instance;

    /**
     *
     * @var array
     */
    private $errors = [];

    /**
     *
     * @var array
     */
    private $user = null;

    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @var int
     */
    private $type = null;

    /**
     *
     * @var array
     */
    private $promo_code = null;

    /**
     *
     * @var bool
     */
    private $is_disabled = false;

    /**
     *
     * @var bool
     */
    private $has_multi_draw = false;

    /**
     *
     * @var string
     */
    private $message = "";

    private WhitelabelUserPromoCodeRepository $whitelabelUserPromoCodeRepository;

    public function get_errors():? array
    {
        return $this->errors;
    }

    public function get_promo_code():? array
    {
        return $this->promo_code;
    }

    public function getPromoCodeId(): ?int
    {
        return $this->issetPromoCode() ? (int)$this->promo_code['code_id'] : null;
    }

    public function getPromoCodeCampaign(): ?array
    {
        return $this->promo_code['campaign'] ?? null;
    }

    public function getUserBonusBalance(): float
    {
        return $this->promo_code['bonus_balance'] ?? 0.00;
    }

    public function getLotteries(): array
    {
        return lotto_platform_get_lotteries();
    }

    public function getPricing(array $lottery): string
    {
        return lotto_platform_get_pricing($lottery);
    }

    /**
     * Promo Code is set after successful processing with 'process_content' or 'process_form'
     */
    public function issetPromoCode(): bool
    {
        return isset($this->promo_code['code_id'], $this->promo_code['campaign']);
    }

    public function hasErrors(): bool
    {
        $promoErrors = $this->get_errors();

        return count($promoErrors) > 0;
    }

    public function isEnabled(): bool
    {
        return !$this->get_is_disabled();
    }

    public function isDiscountApplied(): bool
    {
        return $this->issetPromoCode() && isset(
                $this->promo_code['discount_user'],
                $this->promo_code['discount_usd'],
                $this->promo_code['discount_manager']
            );
    }

    public function isDiscountPromoCodeApplicable(): bool
    {
        return $this->isEnabled()
            && $this->isPromoCodeBonusTypeDiscount()
            && $this->isDiscountApplied();
    }

    public function isBonusMoneyPromoCodeApplicable(): bool
    {
        return $this->isEnabled()
            && $this->isPromoCodeBonusTypeBonusMoney()
            && (float)$this->promo_code['campaign']['bonus_balance_amount'] > 0;
    }

    public function isPromoCodeBonusTypeDiscount(): bool
    {
        return $this->issetPromoCode()
            && (int)$this->promo_code['campaign']['bonus_type'] === Helpers_General::PROMO_CODE_BONUS_TYPE_DISCOUNT;
    }

    public function isPromoCodeBonusTypeFreeLine(): bool
    {
        return $this->issetPromoCode()
            && (int)$this->promo_code['campaign']['bonus_type'] === Helpers_General::PROMO_CODE_BONUS_TYPE_FREE_LINE;
    }

    public function isPromoCodeBonusTypeBonusMoney(): bool
    {
        return $this->issetPromoCode()
            && (int)$this->promo_code['campaign']['bonus_type'] === Helpers_General::PROMO_CODE_BONUS_TYPE_BONUS_MONEY;
    }

    public function get_is_disabled(): bool
    {
        return $this->is_disabled;
    }

    public function getHasMultiDraw(): bool
    {
        return $this->has_multi_draw;
    }

    public function get_message(): string
    {
        return $this->message;
    }

    /**
     * Create only once instance of Forms_Whitelabel_Bonuses_Promocodes_Code class
     *
     * @return Forms_Whitelabel_Bonuses_Promocodes_Code
     */
    public static function get_or_create($whitelabel, $type)
    {
        if (self::$instance === null) {
            self::$instance = new Forms_Whitelabel_Bonuses_Promocodes_Code($whitelabel, $type);
        } else {
            self::$instance->promo_code = null;
            self::$instance->whitelabel = $whitelabel;
            self::$instance->type = $type;
            if ($type !== self::TYPE_PURCHASE) {
                self::$instance->errors = [];
            }
            $user = Lotto_Settings::getInstance()->get('user');
            if (isset($user) && count($user) > 0) {
                self::$instance->user = $user;
            }
        }
        return self::$instance;
    }

    public function set_errors(array $errors = null): Forms_Whitelabel_Bonuses_Promocodes_Code
    {
        $this->errors = $errors;
        
        return $this;
    }

    /**
    *
    * @param array $whitelabel
    */
    public function __construct($whitelabel, $type)
    {
        $this->whitelabel = $whitelabel;
        $this->type = $type;
        $this->whitelabelUserPromoCodeRepository = Container::get(WhitelabelUserPromoCodeRepository::class);

        $user = Lotto_Settings::getInstance()->get('user');
        if (isset($user) && count($user) > 0) {
            $this->user = $user;
        }
    }

    /**
     *
     * @return void
     */
    public function remove_code()
    {
        $this->promo_code = null;
    }

    /**
     *
     * @param string $code
     * @return array|bool
     */
    public function check_code($code)
    {
        $campaigns = Model_Whitelabel_Campaign::get_all_active_with_codes($this->whitelabel['id'], $this->type);
        if (empty($campaigns) || count($campaigns) == 0) {
            return false;
        }
        $campaign = null;
        foreach ($campaigns as $campaign_obj) {
            $code_candidate = mb_strtolower($campaign_obj['prefix']) . mb_strtolower($campaign_obj['code']);
            if ($code_candidate === mb_strtolower($code)) {
                $campaign = $campaign_obj;
                break;
            }
        }
        if ($campaign == null) {
            return false;
        }
        $code_id = $campaign['code_id'];

        if (($this->type === self::TYPE_PURCHASE) || ($this->type === self::TYPE_DEPOSIT)) {
            if (!$this->user) {
                return false;
            }
            $code_already_used_by_user = Model_Whitelabel_User_Promo_Code::is_code_used_by_user($this->user['id'], $code_id);
            
            if ($code_already_used_by_user) {
                return false;
            }

            $user_codes_used = Model_Whitelabel_Promo_Code::get_user_usage_counts($campaign['id'], $this->user['id']);
            if (isset($campaign['max_codes_user']) && ($user_codes_used >= $campaign['max_codes_user'])) {
                return false;
            }
        }
        list(
            $code_used,
            $campaign_used
        ) = Model_Whitelabel_Promo_Code::get_usage_counts($code_id, $campaign['id']);

        if (isset($campaign['max_users_per_code']) && ($code_used >= $campaign['max_users_per_code'])) {
            return false;
        }
        if (isset($campaign['max_users']) && ($campaign_used >= $campaign['max_users'])) {
            return false;
        }

        return [
            $code,
            $code_id,
            $campaign
        ];
    }

    /**
     *
     * @return null
     */
    public function check_and_set_code()
    {
        $user_currency_tab = CurrencyHelper::getCurrentCurrency()->to_array();
        $manager_currency_tab = Helpers_Currency::get_mtab_currency(
            false,
            null,
            $this->whitelabel['manager_site_currency_id']
        );

        if ($this->promo_code['campaign']['bonus_type'] == Helpers_General::PROMO_CODE_BONUS_TYPE_DISCOUNT) {
            $discount = $this->promo_code['campaign']['discount_amount'];
            $discount_type = (int)$this->promo_code['campaign']['discount_type'];
            $discount_amount = 0;
            $discount_usd = 0;
            $system_currency_tab = Helpers_Currency::get_mtab_currency(false, 'USD');
            if ($this->type === self::TYPE_PURCHASE) {
                if ($this->is_disabled) {
                    return;
                }
                $order = Session::get('order');
                $order_sum = Helpers_Currency::sum_order(false);
                if ($order_sum == '0.00' || empty($order)) {
                    return;
                }

                $this->is_disabled = true;
                $multi_draw_helper = new Helpers_Multidraw($this->whitelabel);

                foreach ($order as $item) {
                    if (isset($item['multidraw'])) {
                        $multi_draw = $multi_draw_helper->check_multidraw($item['multidraw']);
                        if (!$multi_draw) {
                            $this->is_disabled = false;
                        } else {
                            $this->has_multi_draw = true;
                        }
                    } else {
                        $this->is_disabled = false;
                    }
                }

                switch ($discount_type) {
                        case Helpers_General::PROMO_CODE_DISCOUNT_TYPE_PERCENT:
                            $discount_percent = $discount / 100;
                            $discount_display = Lotto_View::format_percentage($discount_percent);
                            $this->message = sprintf(_("You used <b>%s</b> promo code. You have received <b>%s</b> discount."), $this->promo_code['promocode'], $discount_display);

                            $discount_amount = 0;
                            $total_sum_discounted = 0;
                            $lotteries = $this->getLotteries();

                            foreach ($order as $item) {
                                if (isset($lotteries['__by_id'][$item['lottery']])) {
                                    if (isset($item['multidraw']) && $multi_draw_helper->check_multidraw($item['multidraw'])) {
                                        $ticket_discount = 0;
                                    } else {
                                        $lottery = $lotteries['__by_id'][$item['lottery']];
                                        $pricing = $this->getPricing($lottery);

                                        $isKenoLottery = $lottery['type'] === 'keno';
                                        if ($isKenoLottery) {
                                            $pricing = $pricing * $item['ticket_multiplier'];
                                        }

                                        $lines_count = !empty($item['lines']) ? count($item['lines']) : 0;
                                        $ticket_price = $pricing * $lines_count;
                                        $ticket_price_float = round($ticket_price, 2);

                                        $ticket_discount = $ticket_price_float * $discount_percent;
                                    }

                                    $discount_amount += $ticket_discount;
                                }
                            }
                            
                            $discount_amount = round($discount_amount, 2);

                            $discount = (float)Helpers_Currency::get_recalculated_to_given_currency(
                                $discount_amount,
                                $user_currency_tab,
                                $manager_currency_tab['code']
                            );
                            $discount_usd = Helpers_Currency::get_value_in_USD(
                                $discount_amount,
                                $user_currency_tab,
                                $system_currency_tab
                            );

                        break;
                        case Helpers_General::PROMO_CODE_DISCOUNT_TYPE_AMOUNT:
                            $discount_amount = (float)Helpers_Currency::get_recalculated_to_given_currency(
                                $discount,
                                $manager_currency_tab,
                                $user_currency_tab['code']
                            );
                            $discount_usd = (float)Helpers_Currency::get_value_in_USD(
                                $discount,
                                $manager_currency_tab,
                                $system_currency_tab
                            );
                            $discount_display = Lotto_View::format_currency($discount_amount, $user_currency_tab['code'], true);
                            $this->message = sprintf(_("You used <b>%s</b> promo code. You have received <b>%s</b> discount."), $this->promo_code['promocode'], $discount_display);

                            if ($this->getHasMultiDraw()) {
                                $order_sum_without_multidraw = Helpers_Currency::sum_order(false, false);
                            }
                    }

                $discounted_price = round(($order_sum_without_multidraw ?? $order_sum) - $discount_amount, 2);

                if ($discounted_price <= 0) {
                    $errors = ['input.promo_code' => _('Your order amount is insufficient to apply this promo code.')];
                    $this->set_errors($errors);
                    return;
                }

                $this->promo_code['discount_manager'] = $discount;
                $this->promo_code['discount_user'] = $discount_amount;
                $this->promo_code['discount_usd'] = $discount_usd;
            } elseif ($this->type === self::TYPE_DEPOSIT) {
                //not applicable yet
            }
        } elseif ($this->promo_code['campaign']['bonus_type'] == Helpers_General::PROMO_CODE_BONUS_TYPE_BONUS_MONEY) {
            $amount = (float)$this->promo_code['campaign']['bonus_balance_amount'];
            $bonus_balance_type = (int)$this->promo_code['campaign']['bonus_balance_type'];
            $bonus_balance_amount = 0;

            switch ($bonus_balance_type) {
                case Helpers_General::PROMO_CODE_BONUS_BALANCE_TYPE_PERCENT:
                    $bonus_balance_amount_percent = $amount / 100;
                    $bonus_balance_amount = Lotto_View::format_percentage($bonus_balance_amount_percent);
                    $this->message = sprintf(_("You used <b>%s</b> promo code. <b>%s</b> of the deposit amount will be added to your bonus balance after the transaction is completed."), $this->promo_code['promocode'], $bonus_balance_amount);
                break;
                case Helpers_General::PROMO_CODE_BONUS_BALANCE_TYPE_AMOUNT:
                    $bonus_balance_amount = (float)Helpers_Currency::get_recalculated_to_given_currency(
                        $amount,
                        $manager_currency_tab,
                        $user_currency_tab['code']
                    );
                    $bonus_balance_amount = Lotto_View::format_currency($bonus_balance_amount, $user_currency_tab['code'], true);
                    $this->message = sprintf(_("You used <b>%s</b> promo code. <b>%s</b> will be added to your bonus balance after the transaction is completed."), $this->promo_code['promocode'], $bonus_balance_amount);
            }
        } elseif ($this->promo_code['campaign']['bonus_type'] == Helpers_General::PROMO_CODE_BONUS_TYPE_FREE_LINE) {
            $lottery_id = $this->promo_code['campaign']['lottery_id'];
            $lottery = Model_Lottery::find_by_pk($lottery_id);
            if (empty($lottery)) {
                $errors = ['input.promo_code' => _('Bonus ticket cannot be applied!')];
                $this->set_errors($errors);
                return;
            }
            $this->message = sprintf(_("You used <b>%s</b> promo code. Your free <b>%s</b> ticket will be added to your account after the transaction is completed."), $this->promo_code['promocode'], $lottery->name);
        }
    }

    /**
    *
    * @return null
    */
    public function check_and_set_register_code()
    {
        $code = Model_Whitelabel_Promo_Code::get_register_bonus_for_user_id($this->user['id']);

        if (isset($code)) {
            //Temporary if below as there is no deposit discount option yet
            if ($code['bonus_type'] == Helpers_General::PROMO_CODE_BONUS_TYPE_DISCOUNT && $this->type == self::TYPE_DEPOSIT) {
                return;
            }
            $this->set_code($code);
        }
    }

    /**
    *
    * @return null
    */
    public function check_and_set_deposit_code()
    {
        $code = Model_Whitelabel_Promo_Code::get_deposit_bonus_for_user_id($this->user['id']);

        if (isset($code)) {
            $this->set_code($code);
        }
    }

    /**
    *
    * @return null
    */
    public function check_and_set_purchase_code()
    {
        $code = Model_Whitelabel_Promo_Code::get_purchase_bonus_for_user_id($this->user['id']);

        if (isset($code)) {
            $this->set_code($code);
        }
    }

    /**
     *
     * @return void
     */
    private function set_code($code)
    {
        $this->promo_code['code_id'] = $code['code_id'];
        $used_code = $code['prefix'] . $code['code'];
        $this->promo_code['promocode'] = $used_code;
        $this->promo_code['campaign'] = $code;

        $this->check_and_set_code();
    }

    protected function validate_form(): Validation
    {
        $val = Validation::instance();
        if ($val === null) {
            $val = Validation::forge();
        }
        
        if (!$val->field('input.promo_code')) {
            $val->add('input.promo_code', _('Promo Code'))
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 50)
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);
        }
        
        return $val;
    }

    public function calcUserBonusBalance(int $userCurrencyId): float
    {
        if ($this->isBonusMoneyPromoCodeApplicable()) {
            $amount = (float) $this->promo_code['campaign']['bonus_balance_amount'];

            $user_currency_tab = Helpers_Currency::get_mtab_currency(
                false,
                null,
                $userCurrencyId
            );

            return (float)Helpers_Currency::get_recalculated_to_given_currency(
                $amount,
                Helpers_Currency::get_mtab_currency(),
                $user_currency_tab['code']
            );
        }

        return 0.00;
    }

    /**
    *
    * @return null
    */
    public function process_content()
    {
        $this->validateType();

        if (isset($this->user)) {
            if ($this->type == self::TYPE_PURCHASE) {
                $this->check_and_set_purchase_code();
            } elseif ($this->type == self::TYPE_DEPOSIT) {
                $this->check_and_set_deposit_code();
            }
            if ($this->type === self::TYPE_REGISTER && ($this->user['first_purchase'] == null) && ($this->user['first_deposit'] == null)) {
                $this->check_and_set_register_code();
            }
        }
    }

    public function process_form(): void
    {
        $this->validateType();

        if ($this->type === self::TYPE_REGISTER && Input::post('register.promo_code')) {
            $promoCode = $this->check_code(Input::post('register.promo_code'));

            if (!$promoCode) {
                $errors = ['register.promo_code' => _('Wrong promo code!')];
                $this->set_errors($errors);

                return;
            }

            list($promoCode, $codeId, $campaign) = $promoCode;

            $this->promo_code['promocode'] = $promoCode;
            $this->promo_code['code_id'] = $codeId;
            $this->promo_code['campaign'] = $campaign;

            if ($this->isPromoCodeBonusTypeFreeLine()) {

                $lottery_id = $this->promo_code['campaign']['lottery_id'];
                $lottery = Model_Lottery::find_by_pk($lottery_id);

                if (empty($lottery)) {
                    $errors = ['register.promo_code' => _('Bonus ticket cannot be applied!')];
                    $this->set_errors($errors);
                }
            }

            return;
        }

    	if (Input::post('input.delete')) {
    		if (empty($this->promo_code['campaign']['assign_id'])) {
    			return;
			}
    		$id = $this->promo_code['campaign']['assign_id'];
            $code_user = Model_Whitelabel_User_Promo_Code::find_by_pk($id);
            if (isset($code_user)) {
                $code_user->delete();
            }
            $this->promo_code = null;
            $this->message = "";
            $errors = ['input.promo_code' => _('Promo code has been removed!')];
            $this->set_errors($errors);
            return;
        }
        $errors = [];
        $code = null;

        if (Input::post('input.promo_code')) {
            $validated_form = $this->validate_form();
            if ($validated_form->run()) {
                $code = $this->check_code($validated_form->validated('input.promo_code'));
                if (!$code) {
                    $errors = ['input.promo_code' => _('Promo code cannot be applied!')];
                    $this->set_errors($errors);
                    return;
                }
            } else {
                $errors = Lotto_Helper::generate_errors($validated_form->error());
                $this->set_errors($errors);
            
                return;
            }

            if ($code && isset($code[1])) {
                $code_id = $code[1];
                $promo_code_set = [
                    'whitelabel_promo_code_id' => $code_id,
                    'whitelabel_user_id' => $this->user['id']
                ];
                $promo_code = Model_Whitelabel_User_Promo_Code::forge();
                $promo_code->set($promo_code_set);
                $promo_code->save();

                $this->promo_code['promocode'] = $code[0];
                $this->promo_code['code_id'] = $code[1];
                $this->promo_code['campaign'] = $code[2];
                
                $this->check_and_set_code();
            }
        }
    }

    /**
     * @throws Exception
     */
    public function useForWhitelabelTransaction(int $transactionId): void
    {
        if ($this->issetPromoCode()) {
            $this->whitelabelUserPromoCodeRepository->setPromoCodeUsedForTransaction(
                $transactionId,
                $this->getPromoCodeId(),
                (int)$this->user['id'],
                $this->type
            );
        }
    }

    /**
     * @throws Exception
     */
    public function saveUserPromoCode(int $userId): void
    {
        if ($this->issetPromoCode()) {
            $this->whitelabelUserPromoCodeRepository->savePromoCodeUsed(
                $this->getPromoCodeId(),
                $userId,
                $this->type
            );
        }
    }

    private function validateType(): void
    {
        if(!in_array($this->type, [self::TYPE_PURCHASE, self::TYPE_DEPOSIT, self::TYPE_REGISTER])) {
            throw new Exception('Unsupported Form Type: ' . $this->type);
        }
    }
}

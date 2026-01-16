<?php

use Fuel\Core\Validation;
use Services\PageCacheService;
use Helpers\DatabaseHelper;

/**
 * Description of Forms_Whitelabel_Lottery_Settings_Edit
 */
class Forms_Whitelabel_Lottery_Settings_Edit extends Forms_Main
{
    const MAX_LINES_PER_TICKET = 25;
    const MAX_QUICK_PICK_VALUE = 12;

    /** @var array */
    private $whitelabel = [];

    /** @var View */
    private $inside = null;
    
    /** @var int */
    private $type_data_count = 0;

    /** @var array */
    private $lottery_type = [];

    /** @var Model_Lottery_Provider */
    private $lottery_provider;

    /** @param array $whitelabel */
    public function __construct(array $whitelabel)
    {
        $this->whitelabel = $whitelabel;
    }

    /**
     *
     * @return array|null
     */
    public function get_whitelabel():? array
    {
        return $this->whitelabel;
    }

    /**
     * @return array|null
     */
    public function get_whitelabel_lottery(): ?array
    {
        return $this->whitelabel_lottery;
    }

    /**
     *
     * @return View
     */
    public function get_inside()
    {
        return $this->inside;
    }
    
    /**
     *
     * @return int
     */
    public function get_type_data_count(): int
    {
        return $this->type_data_count;
    }

    /**
     *
     * @return array
     */
    public function get_lottery_type(): array
    {
        return $this->lottery_type;
    }

    /**
     * @param Model_Lottery_Provider $lottery_provider
     */
    public function set_lottery_provider(Model_Lottery_Provider $lottery_provider): void
    {
        $this->lottery_provider = $lottery_provider;
    }


    public function get_lottery_provider(): Model_Lottery_Provider
    {
        return $this->lottery_provider;
    }


    /**
     *
     * @param int $type_data_count
     * @return $this
     */
    public function set_type_data_count(int $type_data_count): Forms_Whitelabel_Lottery_Settings_Edit
    {
        $this->type_data_count = $type_data_count;
        return $this;
    }

    /**
     *
     * @param array $lottery_type
     * @return $this
     */
    public function set_lottery_type(array $lottery_type): Forms_Whitelabel_Lottery_Settings_Edit
    {
        $this->lottery_type = $lottery_type;
        return $this;
    }

    /**
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $type_data_count = $this->get_type_data_count();
        
        $lottery_type = $this->get_lottery_type();
        
        $validation = Validation::forge();

        $validation->add("input.enabled", _("Enabled"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);

        $validation->add("input.multidraws_enabled", _("Multidraws Enabled"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);

        $validation->add("input.is_bonus_balance_in_use", _("Can be purchased with bonus balance"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);

        $validation->add("input.minlines", _("Minimum Lines"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 1)
            ->add_rule("numeric_max", self::MAX_QUICK_PICK_VALUE);

        $min_lines = $validation->input('input.minlines');

        // min and max lines amount from ltech or lcs
        $lottery_provider = $this->get_lottery_provider();
        $min_bets = $lottery_provider['min_bets'];
        $multiplier = $lottery_provider->multiplier;

        $some_valid_multiplier_values = [];

        for ($i = 0; $i < 12; $i++) {
            $some_valid_multiplier_values[] = $multiplier * $i;
        }

        // multiplier cannot me smaller then the bigest one (min_lines or min_bets)
        $min_quick_pick = max([$min_lines, $min_bets]);

        $validation->add("input.quick_pick_lines", _("Mobile Quick-Pick Lines"))
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", $min_quick_pick)
            ->add_rule("numeric_max", self::MAX_QUICK_PICK_VALUE);

        if ($multiplier > 0) {
            $field = $validation->field("input.quick_pick_lines");
            $field->add_rule("in_array", $some_valid_multiplier_values);
        }

        $valid_models = [
            (string) Helpers_General::LOTTERY_MODEL_PURCHASE,
            (string) Helpers_General::LOTTERY_MODEL_PURCHASE_SCAN
        ];

        if (!Helpers_Whitelabel::is_V1($this->whitelabel['type']) ||
            Helpers_Whitelabel::is_special_ID($this->whitelabel['id'])
        ) {
            $valid_models[] = (string) Helpers_General::LOTTERY_MODEL_NONE;
        }

        $bonusBalancePurchaseLimit = $validation->input('input.bonusBalancePurchaseLimitPerUser');
        $minimumValueErrorMessage = _('You cannot set negative purchase limits.');
        $minLimitValue = 0;
        if (($bonusBalancePurchaseLimit > 0) && ($bonusBalancePurchaseLimit <= $min_quick_pick)) {
            $minLimitValue = $min_quick_pick;
            $minimumValueErrorMessage = sprintf(_("Minimum lines option is set to %d. If you set bonus balance purchase limit lower, then user is not able to purchase any lines."), $min_quick_pick);
        }

        $validation->add("input.bonusBalancePurchaseLimitPerUser", _("User daily bonus balance purchase"))
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", $minLimitValue)
            ->add_rule("numeric_max", DatabaseHelper::SMALLINT_UNSIGNED_MAX_VALUE)
            ->set_error_message("numeric_max", sprintf(_("Maximum value for bonus balance purchase per user is %d."), DatabaseHelper::SMALLINT_UNSIGNED_MAX_VALUE))
            ->set_error_message(
                'numeric_min',
                $minimumValueErrorMessage
            );

        $validation->add("input.model", _("Model"))
            ->add_rule("trim")
            ->add_rule('is_numeric')
            ->add_rule("match_collection", $valid_models, true);

        $validation->add("input.income_type", _("Type"))
            ->add_rule("trim")
            ->add_rule('is_numeric')
            ->add_rule('match_collection', ["0", "1"], true);

        $validation->add("input.income", _("Expected income"))
            ->add_rule("trim")
            ->add_rule('is_numeric')
            ->add_rule('numeric_min', 0)
            ->add_rule("numeric_max", 999.99);

        if (Input::post("input.model") == Helpers_General::LOTTERY_MODEL_MIXED) {
            if (Helpers_Whitelabel::is_V1($this->whitelabel['type'])) {
                $validation->add("input.insured_tiers", _("Insured tiers"))
                ->add_rule("trim")
                ->add_rule('is_numeric')
                ->add_rule('numeric_min', $lottery_type['def_insured_tiers'])
                ->add_rule("numeric_max", $type_data_count);
            } else {
                $validation->add("input.insured_tiers", _("Insured tiers"))
                ->add_rule("trim")
                ->add_rule('is_numeric')
                ->add_rule('numeric_min', 1)
                ->add_rule("numeric_max", $type_data_count);
            }
        }
        
        //$val->add("input.provider", _("Provider"))->add_rule("trim")->add_rule("required")
        //->add_rule("is_numeric")->add_rule("numeric_min", 0);
            
        return $validation;
    }
    
    /**
     *
     * @param int $edit_id
     * @return void
     */
    public function process_form(int $edit_id = null): void
    {
        $lotteries = Model_Lottery::get_all_lotteries_for_whitelabel($this->whitelabel);
        // we want to temporarily disable the display and editing of GG World Keno in the manager
        // the business team decided to do so
        $lotteries = array_filter(
            $lotteries,
            fn($lottery) => isset($lottery['type']) && Helpers_Lottery::isGgrNotEnabled($lottery['type'])
        );

        $currencies = Helpers_Currency::getCurrencies();
        
        $real_id = $edit_id - 1;
        
        if (!isset($lotteries[$real_id])) {
            Session::set_flash("message", ["danger", _("Incorrect lottery!")]);
            Response::redirect("lotterysettings");
        }
            
        // TODO: more currencies - what exactly does it mean in the context
        // of the fact that every lottery could have different currency?
        $this->inside = Presenter::forge("whitelabel/lottery/settings/edit");

        $lottery = $lotteries[$real_id];
        $this->inside->set("lottery", $lottery);
        $this->inside->set("edit_lp", $edit_id);
        $this->inside->set("currencies", $currencies);

        $date = new DateTime("now", new DateTimeZone("UTC"));
        
        $lottery_type = Model_Lottery_Type::get_lottery_type_for_date(
            $lottery,
            $date->format('Y-m-d')
        );
        
        $type_data = Model_Lottery_Type_Data::find([
            'where' => [
                'lottery_type_id' => $lottery_type['id'],
            ],
            'order_by' => 'id'
        ]);

        $type_data_count = 0;
        if (!empty($type_data)) {
            $type_data_count = count($type_data);
        }

        $this->set_type_data_count($type_data_count);
        $this->set_lottery_type($lottery_type);
        
        $this->inside->set("lottery_type", $lottery_type);
        $this->inside->set("type_data", $type_data);

        $providers = Model_Lottery_Provider::find([
            "where" => [
                "lottery_id" => $lottery['id']
            ],
            "order_by" => ["id" => "asc"]
        ]);
        $this->inside->set("providers", $providers);

        if (is_null(Input::post("input.minlines"))) {
            return;
        }

        $lotteries = Model_Whitelabel_Lottery::find([
            "where" => [
                "whitelabel_id" => $this->whitelabel['id'],
                "lottery_id" => $lottery['id'],
            ]
        ]);

        $lottery_exists = $lotteries === null || count($lotteries) === 0;

        if ($lottery_exists) {
            $errors = ["input" => _("Security error! Please try again.")];
            $this->inside->set("errors", $errors);
            return ;
        }

        /** @var Model_Whitelabel_Lottery $whitelabel_lottery */
        $whitelabel_lottery = $lotteries[0];
        $lottery_provider_id = $whitelabel_lottery['lottery_provider_id'];

        /** @var Model_Lottery_Provider $main_provider */
        $main_provider = Model_Lottery_Provider::find_by_pk($lottery_provider_id);

        $this->set_lottery_provider($main_provider);

        $validated_form = $this->validate_form();
            
        if ($validated_form->run()) {
            if ($validated_form->validated("input.model") == Helpers_General::LOTTERY_MODEL_MIXED) {
                if (!(isset($type_data[Input::post("input.insured_tiers") - 1]) &&
                    $type_data[Input::post("input.insured_tiers") - 1]['type'] != 2)
                ) {
                    $errors = ["input" => _("Security error! Please try again.")];
                    $this->inside->set("errors", $errors);
                    return;
                }
            }
            
            $tier_value = 0;
            if ($validated_form->validated("input.model") == Helpers_General::LOTTERY_MODEL_MIXED) {
                $tier_value = $validated_form->validated("input.insured_tiers");
            }

            $set = [
                "is_enabled" => $validated_form->validated("input.enabled") == 1 ? 1 : 0,
                "is_multidraw_enabled" => $validated_form->validated("input.multidraws_enabled") == 1 ? 1 : 0,
                "is_bonus_balance_in_use" => $validated_form->validated("input.is_bonus_balance_in_use") === "1" ? 1 : 0,
                "bonus_balance_purchase_limit_per_user" => $validated_form->validated("input.bonusBalancePurchaseLimitPerUser"),
                "model" => $validated_form->validated("input.model"),
                "income" => $validated_form->validated("input.income"),
                "income_type" => $validated_form->validated("input.income_type"),
                "tier" => $tier_value,
                "min_lines" => $validated_form->validated("input.minlines"),
                "quick_pick_lines" => $validated_form->validated("input.quick_pick_lines")
                //"lottery_provider_id" => $val->validated("input.provider")
            ];
            
            $whitelabel_lottery->set($set);

            try {
                $whitelabel_lottery->save();
            } catch (\Throwable $e) {
                $errors = ["input" => _("Security error! Please try again.")];
                $this->inside->set("errors", $errors);
                return;
            }
            
            Lotto_Helper::clear_cache(["model_lottery.lotteriesallforwl." . $this->whitelabel['id']]);
            Lotto_Helper::clear_cache(["model_lottery.lotteriesforwl." . $this->whitelabel['id']]);
            Lotto_Helper::clear_cache(["model_lottery.lotteriesreallyallforwl." . $this->whitelabel['id']]);
            Lotto_Helper::clear_cache(["model_whitelabel.lotteriesbynearestdraw." . $this->whitelabel['id']]);
            Lotto_Helper::clear_cache(["model_whitelabel.lotteriesbycustomorder." . $this->whitelabel['id']]);
            Lotto_Helper::clear_cache(["model_whitelabel.lotteriesbyorder." . $this->whitelabel['id']]);
            Lotto_Helper::clear_cache(["model_whitelabel.lotteriesbyhighestjackpot." . $this->whitelabel['id']]);
            Lotto_Helper::clear_cache(["model_whitelabel.alllotteriesbyhighestjackpot." . $this->whitelabel['id']]);

            $pageCacheService = Container::get(PageCacheService::class);
            $pageCacheService->clearWhitelabel();

            Session::set_flash("message", ["success", _("Settings have been saved!")]);
            
            Response::redirect("lotterysettings");
        } else {
            $errors = Lotto_Helper::generate_errors($validated_form->error());
            $this->inside->set("errors", $errors);
        }
    }
}

<?php

use Fuel\Core\Presenter;
use \Fuel\Core\Validation;
use Fuel\Core\View;
use Helpers\AffGroupHelper;
use Repositories\WhitelabelAffCasinoGroupRepository;
use Repositories\Aff\WhitelabelAffRepository;

/**
 * @deprecated
 * Description of Forms_Whitelabel_Aff_Edit
 */
class Forms_Whitelabel_Aff_Edit extends Forms_Main
{
    use Forms_Whitelabel_Aff_Trait_Options;

    private array $whitelabel;
    private Model_Whitelabel_Aff $user_aff;
    private Presenter $inside;
    private array $countries;

    private WhitelabelAffCasinoGroupRepository $whitelabelAffCasinoGroupRepository;
    private WhitelabelAffRepository $whitelabelAffRepository;

    public function __construct(array $whitelabel, array $countries)
    {
        $this->whitelabel = $whitelabel;
        $this->countries = $countries;
        $this->whitelabelAffCasinoGroupRepository = Container::get(WhitelabelAffCasinoGroupRepository::class);
        $this->whitelabelAffRepository = Container::get(WhitelabelAffRepository::class);
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
     * @return \Model_Whitelabel_Aff
     */
    public function get_user_aff(): Model_Whitelabel_Aff
    {
        return $this->user_aff;
    }

    public function get_inside(): Presenter
    {
        return $this->inside;
    }

    protected function validate_form(): Validation
    {
        $validatation = Validation::forge();
        
        $validatation->add("input.login", _('Login'))
            ->add_rule('trim')
            ->add_rule("required")
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 30)
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);

        $validatation->add("input.name", _("First Name"))
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']);

        $validatation->add("input.surname", _("Last Name"))
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']);

        $validatation->add("input.company", _('Company'))
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'numeric', 'specials', 'dashes', 'dots', 'spaces', 'utf8']);

        $validatation->add("input.city", _("City"))
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']);

        $validatation->add("input.zip", _("Postal/ZIP Code"))
            ->add_rule('trim')
            ->add_rule('max_length', 20)
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes', 'spaces']);

        $validatation->add("input.state", _("Region"))
            ->add_rule('trim')
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);

        $validatation->add("input.address", _("Address #1"))
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'numeric', 'dashes', 'spaces', 'dots', 'commas', 'forwardslashes', 'utf8']);

        $validatation->add("input.address_2", _("Address #2"))
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'numeric', 'dashes', 'spaces', 'dots', 'commas', 'forwardslashes', 'utf8']);

        $validatation->add("input.phone", _("Phone"))
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['numeric', 'dashes', 'spaces']);

        $validatation->add("input.country", _('Country'))
            ->add_rule('trim')
            ->add_rule('exact_length', 2)
            ->add_rule('valid_string', ['alpha']);

        $validatation->add("input.prefix", _("Phone"))
            ->add_rule('trim')
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);

        $validatation->add("input.timezone", _("Time Zone"))
            ->add_rule('trim')
            ->add_rule('valid_string', ['alpha', 'forwardslashes', 'dashes']);

        $validatation->add("input.birthdate", _("Birthdate"))
            ->add_rule('trim')
            ->add_rule('valid_string', ['numeric', 'forwardslashes']);

        $validatation->add('input.lotteryGroup', 'Lottery Group')
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('valid_string', ['numeric']);

        $validatation->add('input.parentToken', 'Parent Aff Token')
            ->add_rule('trim')
            ->add_rule('min_length', 10)
            ->add_rule('max_length', 10)
            ->add_rule('valid_string', ['alpha', 'numeric']);

        $validatation->add('input.casinoGroup', 'Casino Group')
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('valid_string', ['numeric']);

        $this->initialize_aff_input_validation($validatation);

        return $validatation;
    }

    public function process_form(string $token): int
    {
        $whitelabel = $this->get_whitelabel();
        
        $user_obj = Model_Whitelabel_Aff::find_by([
            "whitelabel_id" => $whitelabel['id'],
            "token" => $token
        ]);

        if (!($user_obj !== null &&
            count($user_obj) > 0 &&
            (int)$user_obj[0]->whitelabel_id === (int)$whitelabel['id'] &&
            (int)$user_obj[0]->is_deleted === 0 &&
            (int)$user_obj[0]->is_accepted === 1 &&
            (((int)$whitelabel['aff_activation_type'] !== Helpers_General::ACTIVATION_TYPE_REQUIRED &&
                    (int)$user_obj[0]->is_active === 1) ||
                ((int)$whitelabel['aff_activation_type'] === Helpers_General::ACTIVATION_TYPE_REQUIRED &&
                    (int)$user_obj[0]->is_active === 1 &&
                    (int)$user_obj[0]->is_confirmed === 1)))
        ) {
            Session::set_flash("message", ["danger", _("Incorrect user.")]);
            return self::RESULT_DB_ERROR;
        }

        $user = $user_obj[0];

        $timezones = Lotto_Helper::get_timezone_list();

        $lotteryGroups = Model_Whitelabel_Aff_Group::get_whitelabel_groups($whitelabel);
        $casinoGroups = AffGroupHelper::prepareCasinoGroups(
            $this->whitelabelAffCasinoGroupRepository->getGroupsByWhitelabelId($whitelabel['id'])
        );
        $casinoGroups = iterator_to_array($casinoGroups);

        $affiliateIds = [$user['whitelabel_aff_parent_id']];
        $whitelabelAffRepository = Container::get(WhitelabelAffRepository::class);
        $affiliateParentDetails = $whitelabelAffRepository->getAffiliatesDetailsByIds($affiliateIds, $whitelabel['id'])[0] ?? [];

        $pcountries = Lotto_Helper::filter_phone_countries($this->countries);

        $prefixes = Lotto_Helper::get_telephone_prefix_list();

        $this->inside = Presenter::forge("whitelabel/affs/edit");
        $this->inside->set('lotteryGroups', $lotteryGroups);
        $this->inside->set('casinoGroups', $casinoGroups);
        $this->inside->set('affiliateParentDetails', $affiliateParentDetails);
        $this->inside->set("prefixes", $prefixes);
        $this->inside->set("countries", $this->countries);
        $this->inside->set("pcountries", $pcountries);
        $this->inside->set("timezones", $timezones);
        $this->inside->set("user", $user);

        if (null === Input::post("input.name")) {
            return self::RESULT_WITH_ERRORS;
        }

        $validated_form = $this->validate_form();

        if ($validated_form->run()) {
            $result = Model_Whitelabel_Aff::get_count_for_whitelabel(
                $whitelabel,
                $validated_form->validated("input.email"),
                $validated_form->validated("input.login"),
                $user->id
            );

            if (is_null($result)) {
                Session::set_flash("message", ["danger", _("There is something wrong with DB!")]);
                return self::RESULT_DB_ERROR;
            }

            $aff_count = $result[0]['count'];

            if ($aff_count > 0) {
                $errors = ['input.login' => _("This login is already in use by another affiliate!")];
                $this->inside->set("errors", $errors);
                return self::RESULT_WITH_ERRORS;
            }
            
            if (!($validated_form->validated('input.country') === "" ||
                isset($this->countries[$validated_form->validated('input.country')]))
            ) {
                $errors = ['input.country' => _("Wrong country!")];
                $this->inside->set("errors", $errors);
                return self::RESULT_WITH_ERRORS;
            }
                
            if (!($validated_form->validated('input.state') == "" ||
                ($validated_form->validated('input.country') !== "" &&
                Lotto_Helper::check_region($validated_form->validated("input.state"), $validated_form->validated("input.country"))))
            ) {
                $errors = ['input.region' => _("Wrong region!")];
                $this->inside->set("errors", $errors);
                return self::RESULT_WITH_ERRORS;
            }
            
            list(
                $is_date_ok,
                $date_time
            ) = Helpers_General::validate_birthday(
                $validated_form->validated('input.birthdate'),
                "m/d/Y"
            );

            if (!$is_date_ok) {
                $errors = ['input.birthdate' => _("Wrong birthdate!")];
                $this->inside->set("errors", $errors);
                return self::RESULT_WITH_ERRORS;
            }
            
            if (!(empty($validated_form->validated('input.timezone')) ||
                isset($timezones[$validated_form->validated('input.timezone')]))
            ) {
                $errors = ['input.timezone' => _("Wrong timezone!")];
                $this->inside->set("errors", $errors);
                return self::RESULT_WITH_ERRORS;
            }
            
            $errors = [];
            list(
                $phone,
                $phone_country,
                $phone_validation_errors
            ) = Helpers_General::validate_phonenumber(
                $validated_form->validated('input.phone'),
                $validated_form->validated("input.prefix"),
                $pcountries
            );

            if (!empty($phone_validation_errors)) {
                // should be single key
                $key = key($phone_validation_errors);
                $key_modified = "input." . $key;
                $errors = [
                    $key_modified => $phone_validation_errors[$key]
                ];
                $this->inside->set("errors", $errors);
            }

            if (count($errors) > 0) {
                return self::RESULT_WITH_ERRORS;
            }

            // Null means that affiliate will be assigned to default group
            $whitelabelAffLotteryGroupId = null;
            if ($validated_form->validated('input.lotteryGroup') > 0) {
                $whitelabelAffLotteryGroupId = $lotteryGroups[(int)$validated_form->validated('input.lotteryGroup')]['id'];
            }

            // Null means that affiliate will be assigned to default group
            $whitelabelAffCasinoGroupId = null;
            if ($validated_form->validated('input.casinoGroup') > 0) {
                $whitelabelAffCasinoGroupId = $casinoGroups[(int)$validated_form->validated('input.casinoGroup')]['id'];
            }

            $state = '';
            if ($validated_form->validated('input.state') !== null) {
                $state = $validated_form->validated('input.state');
            }

            $birthdate = null;
            if ($date_time !== null) {
                $birthdate = $date_time->format('Y-m-d');
            }

            $parentAffiliateId = null;
            if ($parentAffiliateToken = $validated_form->validated('input.parentToken')) {
                $parentAffiliate = $this->whitelabelAffRepository->findOneByToken($parentAffiliateToken);
                if ($parentAffiliate) {
                    $parentAffiliateId = $parentAffiliate->id;
                } else {
                    $errors = ['input.parentToken' => 'Incorrect Parent Token'];
                    $this->inside->set('errors', $errors);
                    return self::RESULT_WITH_ERRORS;
                }
            }

            $aff_values = [
                'login' => $validated_form->validated("input.login"),
                'company' => $validated_form->validated('input.company'),
                'name' => $validated_form->validated('input.name'),
                'surname' => $validated_form->validated('input.surname'),
                'whitelabel_aff_group_id' => $whitelabelAffLotteryGroupId,
                'whitelabel_aff_casino_group_id' => $whitelabelAffCasinoGroupId,
                'whitelabel_aff_parent_id' => $parentAffiliateId,
                'city' => $validated_form->validated('input.city'),
                'zip' => $validated_form->validated('input.zip'),
                'state' => $state,
                'phone' => $phone,
                'phone_country' => $phone_country,
                'address_1' => $validated_form->validated('input.address'),
                'address_2' => $validated_form->validated('input.address_2'),
                'country' => $validated_form->validated('input.country'),
                'birthdate' => $birthdate,
                'timezone' => $validated_form->validated('input.timezone')
            ];
            
            $set = $this->attach_affiliate_options($aff_values, $validated_form);
            
            $user->set($set);
            $user->save();

            $this->user_aff = $user;
            
            Session::set_flash("message", ["success", _("Affiliate details has been saved!")]);
        } else {
            $errors = Lotto_Helper::generate_errors($validated_form->error());
            $this->inside->set("errors", $errors);
            
            return self::RESULT_WITH_ERRORS;
        }
        
        return self::RESULT_OK;
    }
}

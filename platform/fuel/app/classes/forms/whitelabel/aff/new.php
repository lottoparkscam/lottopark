<?php

use Fuel\Core\Presenter;
use \Fuel\Core\Validation;
use Fuel\Core\View;
use Helpers\AffGroupHelper;
use Repositories\Aff\WhitelabelAffRepository;
use Repositories\WhitelabelAffCasinoGroupRepository;

/**
 * @deprecated
 * Description of Forms_Whitelabel_Aff_New
 */
class Forms_Whitelabel_Aff_New extends Forms_Main
{
    use Forms_Whitelabel_Aff_Trait_Options; // Maybe that trait should be moved to main trait folder

    private array $whitelabel;
    private Presenter $inside;
    private WhitelabelAffCasinoGroupRepository $whitelabelAffCasinoGroupRepository;
    private WhitelabelAffRepository $whitelabelAffRepository;

    public function __construct(array $whitelabel)
    {
        $this->whitelabel = $whitelabel;
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
     * @return View
     */
    public function get_inside()
    {
        return $this->inside;
    }

    protected function validateForm(): Validation
    {
        $validation = Validation::forge();

        $validation->add('input.login', 'Login')
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 30)
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);

        $validation->add('input.email', 'E-mail')
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('valid_email');

        $validation->add('input.password', 'Password')
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('min_length', 6);

        $validation->add('input.lotteryGroup', 'Group')
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('valid_string', ['numeric']);

        $validation->add('input.casinoGroup', 'Casino Group')
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('valid_string', ['numeric']);

        $validation->add('input.parentToken', 'Parent Aff Token')
            ->add_rule('trim')
            ->add_rule('min_length', 10)
            ->add_rule('max_length', 10)
            ->add_rule('valid_string', ['alpha', 'numeric']);

        $this->initialize_aff_input_validation($validation);

        return $validation;
    }

    public function process_form(): int
    {
        $this->inside = Presenter::forge('whitelabel/affs/new');

        $whitelabel = $this->get_whitelabel();
        $lotteryGroups = Model_Whitelabel_Aff_Group::get_whitelabel_groups($whitelabel);
        $casinoGroups = AffGroupHelper::prepareCasinoGroups(
            $this->whitelabelAffCasinoGroupRepository->getGroupsByWhitelabelId($whitelabel['id'])
        );
        $casinoGroups = iterator_to_array($casinoGroups);

        $this->inside->set('lotteryGroups', $lotteryGroups);
        $this->inside->set('casinoGroups', $casinoGroups);

        if (empty(Input::post('input.login'))) {
            return self::RESULT_WITH_ERRORS;
        }

        $validatedForm = $this->validateForm();

        if ($validatedForm->run()) {
            $defaultLangId = Helpers_General::get_default_language_id();

            $result = Model_Whitelabel_Aff::get_count_for_whitelabel(
                $whitelabel,
                $validatedForm->validated('input.email'),
                $validatedForm->validated('input.login')
            );

            if (is_null($result)) {
                Session::set_flash('message', ['danger', 'There is something wrong with DB']);
                return self::RESULT_WITH_ERRORS;
            }

            $affiliatesCount = (int)$result[0]['count'];
            $noAffiliates = $affiliatesCount === 0;

            if ($noAffiliates) {
                $salt = Lotto_Security::generate_salt();
                $hash = Lotto_Security::generate_hash(
                    $validatedForm->validated('input.password'),
                    $salt
                );

                $token = Lotto_Security::generate_aff_token($whitelabel['id']);
                $subToken = Lotto_Security::generate_aff_token($whitelabel['id']);

                // This currency is set for manager site and the same
                // currency should be for affiliate
                $currencyId = $whitelabel['manager_site_currency_id'];

                $whitelabelAffLotteryGroupId = null;
                if ($validatedForm->validated('input.group') > 0) {
                    $whitelabelAffLotteryGroupId = $lotteryGroups[(int)$validatedForm->validated('input.lotteryGroup')]['id'];
                }

                $whitelabelAffCasinoGroupId = null;
                if ($validatedForm->validated('input.casinoGroup') > 0) {
                    $whitelabelAffCasinoGroupId = $casinoGroups[(int)$validatedForm->validated('input.casinoGroup')]['id'];
                }

                $parentAffiliateId = null;
                if ($parentAffiliateToken = $validatedForm->validated('input.parentToken')) {
                    $parentAffiliate = $this->whitelabelAffRepository->findOneByToken($parentAffiliateToken);
                    if ($parentAffiliate) {
                        $parentAffiliateId = $parentAffiliate->id;
                    } else {
                        Session::set_flash('message', ['danger', 'Incorrect Parent Token']);
                        return self::RESULT_WITH_ERRORS;
                    }
                }

                $affiliate = Model_Whitelabel_Aff::forge();
                $newAffiliateData = [
                    'whitelabel_id' => $whitelabel['id'],
                    'token' => $token,
                    'sub_affiliate_token' => $subToken,
                    'currency_id' => $currencyId,
                    'language_id' => $defaultLangId,
                    'whitelabel_aff_group_id' => $whitelabelAffLotteryGroupId,
                    'whitelabel_aff_casino_group_id' => $whitelabelAffCasinoGroupId,
                    'whitelabel_aff_parent_id' => $parentAffiliateId,
                    'is_active' => true,
                    'is_confirmed' => true,
                    'is_accepted' => true,
                    'login' => $validatedForm->validated('input.login'),
                    'email' => $validatedForm->validated('input.email'),
                    'hash' => $hash,
                    'salt' => $salt,
                    'is_deleted' => false,
                    'name' => '',
                    'surname' => '',
                    'address_1' => '',
                    'address_2' => '',
                    'city' => '',
                    'country' => '',
                    'state' => '',
                    'zip' => '',
                    'phone_country' => '',
                    'birthdate' => null,
                    'phone' => '',
                    'timezone' => '',
                    'date_created' => DB::expr("NOW()")
                ];
                $set = $this->attach_affiliate_options($newAffiliateData, $validatedForm);

                $affiliate->set($set);
                $affiliate->save();

                Session::set_flash('message', ['success', 'Affiliate has been successfully created!']);
            } else {
                $errors = ['input.emaillogin' => 'The email address and/or login you have provided are already in use.'];
                $this->inside->set('errors', $errors);

                return self::RESULT_WITH_ERRORS;
            }
        } else {
            $errors = Lotto_Helper::generate_errors($validatedForm->error());
            $this->inside->set('errors', $errors);

            return self::RESULT_WITH_ERRORS;
        }

        return self::RESULT_OK;
    }
}

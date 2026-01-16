<?php

use Fuel\Core\Validation;

/**
 * @deprecated
 * Description of Forms_Whitelabel_Aff_Password
 */
class Forms_Whitelabel_Aff_Password extends Forms_Main
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
    
    /**
     *
     * @var Model_Whitelabel_Aff
     */
    private $user_aff = null;
    
    /**
     *
     * @param array $whitelabel
     */
    public function __construct(array $whitelabel)
    {
        $this->whitelabel = $whitelabel;
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

    /**
     *
     * @return View
     */
    public function get_inside()
    {
        return $this->inside;
    }
    
    /**
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $validation = Validation::forge();
        
        $validation->add("input.password", _("New password"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule('min_length', 6);
            
        return $validation;
    }
    
    /**
     *
     * @param string $token
     * @return int
     */
    public function process_form(string $token): int
    {
        $whitelabel = $this->get_whitelabel();
        
        $users = Model_Whitelabel_Aff::find_by([
            "whitelabel_id" => $whitelabel['id'],
            "token" => $token
        ]);
        
        if (!($users !== null &&
            count($users) > 0 &&
            (int)$users[0]->whitelabel_id === (int)$whitelabel['id'] &&
            (int)$users[0]->is_deleted === 0 &&
            (int)$users[0]->is_accepted === 1 &&
            (((int)$whitelabel['aff_activation_type'] !== Helpers_General::ACTIVATION_TYPE_REQUIRED &&
                (int)$users[0]->is_active === 1) ||
                ((int)$whitelabel['aff_activation_type'] === Helpers_General::ACTIVATION_TYPE_REQUIRED &&
                    (int)$users[0]->is_active === 1 &&
                    (int)$users[0]->is_confirmed === 1)))
        ) {
            Session::set_flash("message", ["danger", _("Wrong affiliate!")]);
            return self::RESULT_WRONG_AFF;
        }
        
        $user = $users[0];

        $inside = View::forge("whitelabel/affs/password");

        if (null === Input::post("input.password")) {
            $inside->set("user", $user);
            $this->inside = $inside;
            return self::RESULT_WITH_ERRORS;
        }

        $validated_form = $this->validate_form();

        if ($validated_form->run()) {
            $new_salt = Lotto_Security::generate_salt();
            $new_hash = Lotto_Security::generate_hash(
                $validated_form->validated('input.password'),
                $new_salt
            );
            
            $user_set = [
                'salt' => $new_salt,
                'hash' => $new_hash
            ];
            $user->set($user_set);
            $user->save();

            $this->user_aff = $user;

            Session::set_flash("message", ["success", _("Affiliate password has been changed!")]);
        } else {
            $errors = Lotto_Helper::generate_errors($validated_form->error());
            $inside->set("errors", $errors);

            $inside->set("user", $user);
            $this->inside = $inside;
            return self::RESULT_WITH_ERRORS;
        }
        
        return self::RESULT_OK;
    }
}

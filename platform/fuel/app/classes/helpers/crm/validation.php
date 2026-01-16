<?php

class Helpers_Crm_Validation
{
    /**
    *
    * @access public
    * @return bool|array
    * @param array $user
    */
    public function check_for_errors(array $user)
    {
        $errors = [];

        $val = Validation::forge();
        $val->add_callable($this);

        unset($user['accessList']);
        if (isset($user['username'])) {
            $val->set_message('unique', _('This username is already taken.'));
            $val->add('username', _('Username'))
            ->add_rule("required")
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'numeric', 'specials', 'dashes', 'spaces', 'utf8'])
            ->add_rule('unique', 'admin_user.username');
        }
        if (isset($user['name'])) {
            $val->add('name', _('Name'))
            ->add_rule("required")
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']);
        }
        if (isset($user['surname'])) {
            $val->add("surname", _('Surname'))
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']);
        }
        if (isset($user['email'])) {
            $val->add("email", _('E-mail'))
            ->add_rule("required")
            ->add_rule("trim")
            ->add_rule("valid_email")
            ->add_rule("max_length", 254);
        }
        if (isset($user['password'])) {
            $val->add("password", _('Password'))
            ->add_rule("required")
            ->add_rule("trim")
            ->add_rule('min_length', 6);
        }
        if (isset($user['confirmPassword'])) {
            $val->set_message('passwords_match', _('Passwords do not match.'));
            $val->add("confirmPassword", _('Confirm Password'))
            ->add_rule("required")
            ->add_rule("trim")
            ->add_rule("passwords_match", $user['password']);
        }
        if (isset($user['timezone'])) {
            $val->add("timezone", _('Timezone'))
            ->add_rule("required")
            ->add_rule('trim')
            ->add_rule('valid_string', ['alpha', 'forwardslashes', 'dashes']);
        }
        if (isset($user['language_id'])) {
            $val->set_message('value_exists', _('Invalid value.'));
            $val->add("language_id", _('Language'))
            ->add_rule("required")
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 1)
            ->add_rule("value_exists", "language.id");
        }
        if (isset($user['role_id'])) {
            $val->add("role_id", _('Role'))
            ->add_rule("required")
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule('match_collection', Model_Admin_User::get_roles_keys());
        }

        if (isset($user['supportEmail'])) {
            $val->add('supportEmail', _('Payment email'))
                ->add_rule('required')
                ->add_rule('valid_email')
                ->add_rule('max_length', 80);
        }

        if (isset($user['paymentEmail'])) {
            $val->add('paymentEmail', _('Payment email'))
                ->add_rule('required')
                ->add_rule('valid_email')
                ->add_rule('max_length', 80);
        }

        $fields = [];
        foreach ($user as $type => $value) {
            $fields[$type] = $value;
        }

        if (!$val->run($fields)) {
            $errors = Lotto_Helper::generate_errors($val->error());
        }
        
        if (count($errors) > 0) {
            return $errors;
        }
        return false;
    }

    /**
    *
    * @access public
    * @return bool|array
    * @param array $user
    * @param array $groups
    * @param int $actual_prize_payout_group
    */
    public function check_for_errors_whitelabel_user(array $user, array $groups, ?int $actual_prize_payout_group)
    {
        $errors = [];

        $val = Validation::forge();
        $val->add_callable($this);

        if (isset($user['login'])) {
            $val->set_message('unique', _('This login is already taken.'));
            $val->add('login', _('Login'))
            ->add_rule("required")
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'numeric', 'specials', 'dashes', 'spaces', 'utf8'])
            ->add_rule('unique', 'whitelabel_user.login');
        }
        if (isset($user['name'])) {
            $val->add('name', _('Name'))
            ->add_rule("required")
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']);
        }
        if (isset($user['surname'])) {
            $val->add("surname", _('Surname'))
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']);
        }
        if (isset($user['city'])) {
            $val->add("city", _('City'))
            ->add_rule("required")
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']);
        }
        if (isset($user['zip'])) {
            $val->add("zip", _('Postal/ZIP Code'))
            ->add_rule("required")
            ->add_rule('trim')
            ->add_rule('max_length', 20)
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes', 'spaces']);
        }
        if (isset($user['state'])) {
            $val->add("state", _('Region'))
            ->add_rule("required")
            ->add_rule('trim')
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);
        }
        if (isset($user['address_1'])) {
            $val->add('address_1', _('Address #1'))
            ->add_rule("required")
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'numeric', 'dashes', 'spaces', 'commas', 'dots', 'forwardslashes', 'utf8']);
        }
        if (isset($user['address_2'])) {
            $val->add('address_2', _('Address #2'))
            ->add_rule("required")
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'numeric', 'dashes', 'spaces', 'commas', 'dots', 'forwardslashes', 'utf8']);
        }
        if (isset($user['phone'])) {
            $val->add("phone", _('Phone'))
            ->add_rule("required")
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['numeric', 'dashes', 'spaces']);
        }
        if (isset($user['country'])) {
            $val->add("country", _('Country'))
            ->add_rule("required")
            ->add_rule('trim')
            ->add_rule('exact_length', 2)
            ->add_rule('valid_string', ['alpha']);
        }
        if (isset($user['phone_country'])) {
            $val->add("phone_country", _('Phone prefix'))
            ->add_rule("required")
            ->add_rule('trim')
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);
        }
        if (isset($user['timezone'])) {
            $val->add("timezone", _('Timezone'))
            ->add_rule("required")
            ->add_rule('trim')
            ->add_rule('valid_string', ['alpha', 'forwardslashes', 'dashes']);
        }
        if (isset($user['national_id'])) {
            $val->add("national_id", _('National ID'))
            ->add_rule("required")
            ->add_rule("trim")
            ->add_rule("valid_string", ['alpha', "numeric"])
            ->add_rule("max_length", 30);
        }
        if (isset($user['gender'])) {
            $val->add("gender", _('Gender'))
            ->add_rule("required")
            ->add_rule("trim")
            ->add_rule('match_collection', Model_Whitelabel_User::get_gender_keys());
        }
        if (isset($user['email'])) {
            $val->add("email", _('E-mail'))
            ->add_rule("required")
            ->add_rule("trim")
            ->add_rule("valid_email")
            ->add_rule("max_length", 254);
        }
        if (isset($user['password'])) {
            $val->add("password", _('Password'))
            ->add_rule("required")
            ->add_rule("trim")
            ->add_rule('min_length', 6);
        }
        if (isset($user['confirmPassword'])) {
            $val->set_message('passwords_match', _('Passwords do not match.'));
            $val->add("confirmPassword", _('Confirm Password'))
            ->add_rule("required")
            ->add_rule("trim")
            ->add_rule("passwords_match", $user['password']);
        }
        if (isset($user['language_id'])) {
            $val->set_message('value_exists', _('Invalid value.'));
            $val->add("language_id", _('Language'))
            ->add_rule("required")
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 1)
            ->add_rule("value_exists", "language.id");
        }
        if (isset($user['prize_payout_whitelabel_user_group_id']) || ((count($groups) > 0) && (empty($actual_prize_payout_group)))) {
            $val->add("prize_payout_whitelabel_user_group_id", _('Prize payout group'))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 1)
            ->add_rule('match_collection', $groups);
        }

        $fields = [];
        foreach ($user as $type => $value) {
            $fields[$type] = $value;
        }

        if (!$val->run($fields)) {
            $errors = Lotto_Helper::generate_errors($val->error());
        }
        
        if (count($errors) > 0) {
            return $errors;
        }
        return false;
    }

    /**
    *
    * @return array
    * @param array $group
    * @param int $whitelabel_id
    */
    public function check_for_errors_user_group(array $group, int $whitelabel_id = null): array
    {
        $errors = [];

        $val = Validation::forge();
        $val->add_callable($this);

        if (isset($group['name'])) {
            $val->set_message('unique_for_whitelabel', _('This group name is already used.'));
            $val->add('name', _('Name'))
            ->add_rule("required")
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'numeric', 'specials', 'dashes', 'spaces', 'utf8'])
            ->add_rule('unique_for_whitelabel', 'whitelabel_user_group.name.' . $whitelabel_id);
        }
        if (isset($group['prize_payout_percent'])) {
            $val->add("prize_payout_percent", _("Prize payout percent"))
            ->add_rule("required")
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 100);
        }
        if (isset($group['is_selectable_by_user'])) {
            $val->add("is_selectable_by_user", _("Is selectable by user"))
            ->add_rule("trim")
            ->add_rule("match_collection", [0,1]);
        }
        $fields = [];
        foreach ($group as $type => $value) {
            $fields[$type] = $value;
        }

        if (!$val->run($fields)) {
            $errors = Lotto_Helper::generate_errors($val->error());
        }
        
        return $errors;
    }

    public function check_for_errors_manual_deposit(array $params): array
    {
        $errors = [];

        $validation = Validation::forge();
        $validation->add("amount", _("Amount"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 9999999999999);

        $validation->add("method", _("Method"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 1);

        $fields = [];
        foreach ($params as $type => $value) {
            $fields[$type] = $value;
        }

        if (!$validation->run($fields)) {
            $errors = Lotto_Helper::generate_errors($validation->error());
        }
        
        return $errors;
    }

    public static function _validation_unique($val, $options)
    {
        list($table, $field) = explode('.', $options);

        $result = DB::select(DB::expr("LOWER (\"$field\")"))
        ->from($table)
        ->where($field, '=', Str::lower($val))
        ->execute();

        return ! ($result->count() > 0);
    }

    public static function _validation_unique_for_whitelabel($val, $options)
    {
        list($table, $field, $whitelabel_id) = explode('.', $options);

        $query = DB::select(DB::expr("LOWER (\"$field\")"))
        ->from($table)
        ->where($field, '=', Str::lower($val));

        if ($whitelabel_id) {
            $query->and_where('whitelabel_id', '=', $whitelabel_id);
        }
        $result = $query->execute();

        return ! ($result->count() > 0);
    }

    public static function _validation_value_exists($val, $options)
    {
        list($table, $field) = explode('.', $options);

        $result = DB::select(DB::expr("LOWER (\"$field\")"))
        ->where($field, '=', Str::lower($val))
        ->from($table)->execute();

        return ($result->count() > 0);
    }

    public static function _validation_passwords_match($confirm_password, $password)
    {
        return (($password === $confirm_password));
    }

    /**
     *
     * @access public
     * @param int $aff_id
     * @return bool
     */
    public static function _validation_check_affiliate($aff_id)
    {
        $affiliate = Model_Whitelabel_Aff::find_by_pk($aff_id);
        if (!$affiliate) {
            return false;
        }
        if ($affiliate->is_deleted == 1) {
            return false;
        }
        if ($affiliate->is_active == 0) {
            return false;
        }
        if ($affiliate->is_accepted == 0) {
            return false;
        }

        return true;
    }
}

<?php

namespace Validators\Auth;

use Helpers\WhitelabelHelper;
use Validators\Rules\Checkbox;
use Validators\Validator;
use Validators\Rules\Login;
use Validators\Rules\Email;
use Validators\Rules\Password;
use Fuel\Core\Input;

class LoginValidator extends Validator
{
    protected static string $method = Validator::POST;

    protected function buildValidation(...$args): void
    {
        $loginRule = WhitelabelHelper::isLoginByUserLoginAllowed() ? Login::build('login.login') : Email::build('login.email');
        $this->addFieldRule($loginRule);
        $this->addFieldRule(Password::build('login.password'));

        $shouldRemember = !empty(Input::post('login.remember'));
        if ($shouldRemember) {
            $this->addFieldRule(Checkbox::build('login.remember'));
        }
    }
}

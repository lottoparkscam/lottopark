<?php

/**
 * Description of Presenter_Aff_Signin
 */
class Presenter_Aff_Signin extends Presenter_Presenter
{
    /**
     *
     */
    public function view()
    {
        $error_login_classes = $this->prepare_login_error_classes();
        $cookieAffName = Helpers_General::COOKIE_AFF_NAME;

        // link to ref is always to be visible if cookies aren't empty
        if ((int)$this->whitelabel["aff_enable_sign_ups"] === 1 || Cookie::get($cookieAffName)) {
            $sign_up_link = 'https://aff.' . $this->whitelabel['domain'] . '/sign_up';
            $this->set("sign_up_link", $sign_up_link);
        }
        
        $this->set("error_classes", $error_login_classes);
    }
    
    /**
     *
     * @return array
     */
    private function prepare_login_error_classes(): array
    {
        $prepare_login_error_classes = [];
        
        $error_classes_name = '';
        if (isset($this->errors['login.name'])) {
            $error_classes_name = ' has-error';
        }
        $prepare_login_error_classes["name"] = $error_classes_name;

        $error_classes_password = '';
        if (isset($this->errors['login.password'])) {
            $error_classes_password = ' has-error';
        }
        $prepare_login_error_classes["password"] = $error_classes_password;
        
        return $prepare_login_error_classes;
    }
}

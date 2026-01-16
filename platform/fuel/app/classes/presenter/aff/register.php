<?php

/**
 * Description of Presenter_Aff_Register
 */
class Presenter_Aff_Register extends Presenter_Presenter
{
    /**
     *
     */
    public function view()
    {
        $sign_in_link = 'https://aff.' . $this->whitelabel['domain'] . '/';
        $this->set("sign_in_link", $sign_in_link);
        
        $error_register_classes = $this->prepare_register_error_classes();
        $this->set("error_classes", $error_register_classes);

        $register_values = $this->prepare_register_values();
        $this->set("register_values", $register_values);
    }
    
    /**
     *
     * @return array
     */
    private function prepare_register_error_classes(): array
    {
        $prepared_register_error_classes = [];

        $error_classes_register_email = '';
        $error_classes_register_login = '';
        // In that case that email field has error,
        // login field should be selected as well as error
        if (isset($this->errors['register.email'])) {
            $error_classes_register_email = ' has-error';
            $error_classes_register_login = ' has-error';
        }
        $prepared_register_error_classes["register_email"] = $error_classes_register_email;
        
        // It could happened that only login has error but not email
        // and in that case login field will be selected with error
        if (isset($this->errors['register.login'])) {
            $error_classes_register_login = ' has-error';
        }
        $prepared_register_error_classes["register_login"] = $error_classes_register_login;

        $error_classes_register_password = '';
        if (isset($this->errors['register.password'])) {
            $error_classes_register_password = ' has-error';
        }
        $prepared_register_error_classes["register_password"] = $error_classes_register_password;

        $error_classes_register_password_repeat = '';
        if (isset($this->errors['register.password_repeat'])) {
            $error_classes_register_password_repeat = ' has-error';
        }
        $prepared_register_error_classes["register_password_repeat"] = $error_classes_register_password_repeat;
        
        return $prepared_register_error_classes;
    }
    
    /**
     *
     * @return array
     */
    private function prepare_register_values(): array
    {
        $prepared_register_values = [];
        
        $register_email = '';
        if (Input::post("register.email") !== null) {
            $register_email = Input::post("register.email");
        }
        $prepared_register_values["register_email"] = $register_email;
        
        $register_login = '';
        if (Input::post("register.login") !== null) {
            $register_login = Input::post("register.login");
        }
        $prepared_register_values["register_login"] = $register_login;
        
        $register_login_entered = 0;
        if (is_string($register_login) && strlen($register_login) > 0) {
            $register_login_entered = 1;
        }
        $prepared_register_values["register_login_entered"] = $register_login_entered;
        
        return $prepared_register_values;
    }
}

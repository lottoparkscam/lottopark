<?php

class Presenter_Aff_New extends Presenter_Presenter
{
    public function view(): void
    {
        $errorSubAffClasses = $this->prepareSubAffErrorClasses();
        $this->set("errorClasses", $errorSubAffClasses);

        $subAffValues = $this->prepareSubAffValues();
        $this->set("subAffValues", $subAffValues);
    }

    private function prepareSubAffErrorClasses(): array
    {
        $preparedSubAffErrorClasses = [];

        $loginErrorClass = '';
        if (isset($this->errors['input.login'])) {
            $loginErrorClass = ' has-error';
        }
        $preparedSubAffErrorClasses['login'] = $loginErrorClass;

        $passwordErrorClass = '';
        if (isset($this->errors['input.password'])) {
            $passwordErrorClass = ' has-error';
        }
        $preparedSubAffErrorClasses['password'] = $passwordErrorClass;

        $emailErrorClass = '';
        if (isset($this->errors['input.email'])) {
            $emailErrorClass = ' has-error';
        }
        $preparedSubAffErrorClasses['email'] = $emailErrorClass;

        return $preparedSubAffErrorClasses;
    }

    private function prepareSubAffValues(): array
    {
        $preparedSubAffValues = [];

        $loginValue = '';
        if (!is_null(Input::post("input.login"))) {
            $loginValue = Input::post("input.login");
        }
        $preparedSubAffValues['login'] = Security::htmlentities($loginValue);

        $emailValue = '';
        if (!is_null(Input::post("input.email"))) {
            $emailValue = Input::post("input.email");
        }
        $preparedSubAffValues['email'] = Security::htmlentities($emailValue);

        return $preparedSubAffValues;
    }
}
<?php

namespace Helpers;

final class ValidationMessageHelper
{
    /** 
     * Usage:
     * 1. ValidationMessageHelper::displayOnFront($this->{yourValidator}->getErrors())
     * 2. Create <div id="validationErrors"></div> inside your view
     * 
     * eg. 
     * platform/fuel/app/classes/services/ContactService.php
     * wordpress/wp-content/themes/base/Views/Partials/ContactFormView.twig
     */
    public static function displayOnFront(array $errors): void
    {
        if (empty($errors)) {
            return;
        }

        $errorsString = '';

        foreach ($errors as $error) {
            $errorsString .= <<<ALERT
            <div class="platform-alert platform-alert-error"><p><span class="fa fa-exclamation-circle"></span>$error</p></div>
            ALERT;
        }

        echo <<<SCRIPT
            <script>
            window.addEventListener("load", function () {
                const validationErrorsDiv = document.getElementById('validationErrors');
                validationErrorsDiv.innerHTML = '$errorsString';
            });
            </script>
        SCRIPT;
    }
}

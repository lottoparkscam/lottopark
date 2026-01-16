<?php

namespace Services\SocialMediaConnect;

use Fuel\Core\Session;
use Helpers\FlashMessageHelper;
use Helpers\PasswordHelper;
use Helpers\SocialMediaConnect\ConnectHelper;
use Hybridauth\User\Profile;
use Services\RedirectService;

class FormService
{
    private RedirectService $redirectService;

    public const SESSION_REGISTRATION_VALUES_KEY = 'registrationPostValues';
    public function __construct(RedirectService $redirectService)
    {
        $this->redirectService = $redirectService;
    }

    public function loadFormErrorOnLastSteps($error): void
    {
        if (count($error) > 0 && Session::get(ConnectHelper::SOCIAL_CONNECT_KEY)) {
            FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, reset($error), true);
            /**
             * These values are set so that the user does not have to re-enter them on the last steps page after error.
             * Values is set in LastStepsService on the line 191 when FillRegisterFormException is thrown.
             */
            Session::set(self::SESSION_REGISTRATION_VALUES_KEY, $_POST['register'] ?? $_POST);
            $this->redirectService->redirectToLastSteps(Session::get('socialType'));
        }
    }

    public function setRegisterFormValuesAndDeleteRegisterValuesFromSessionAfterUse(): void
    {
        $registerParameters = Session::get(self::SESSION_REGISTRATION_VALUES_KEY);
        $_POST['register.email'] = $registerParameters['email'] ?? null;
        $_POST['register.phone'] = $registerParameters['phone'] ?? null;
        $_POST['register.name'] = $registerParameters['name'] ?? null;
        $_POST['register.surname'] = $registerParameters['surname'] ?? null;
        $_POST['register.company'] = $registerParameters['company'] ?? null;
        $_POST['register.prefix'] = $registerParameters['prefix'] ?? null;
        $_POST['register.group'] = $registerParameters['group'] ?? null;

        /** Registration values are removed because they may be displayed in the wrong form */
        Session::delete(self::SESSION_REGISTRATION_VALUES_KEY);
    }
}

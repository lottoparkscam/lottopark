<?php

use Abstracts\Controllers\Internal\AbstractPublicController;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Helpers\FlashMessageHelper;
use Helpers\SanitizerHelper;

class Controller_Api_Internal_FlashMessage extends AbstractPublicController
{
    public function post_all(): Response
    {
        $resendLink = SanitizerHelper::sanitizeString(Input::post('resendLink') ?: '');
        $activationText = strip_tags(Input::post('activationText') ?: '', '<a>');
        $isFrontPage = Input::post('isFrontPage') === 'true';

        $flashMessagesHtml = FlashMessageHelper::getAll(
            true,
            $isFrontPage,
            true,
            $resendLink,
            $activationText
        );

        return $this->returnResponse([
            'flashMessages' => $flashMessagesHtml
        ]);
    }

    public function option_all(): void
    {
    }
}

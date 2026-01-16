<?php

namespace Helpers;

use Container;
use Fuel\Core\Session;
use Helpers_General;
use Lotto_Settings;
use Services\Auth\UserActivationService;

class FlashMessageHelper
{
    public const TYPE_ERROR = 'error';
    public const TYPE_WARNING = 'warning';
    public const TYPE_SUCCESS = 'success';
    public const TYPE_INFO = 'info';

    public const EXCALMATION_ICON = 'fa-exclamation-circle';
    public const CHECK_ICON = 'fa-check-circle';
    public const INFO_ICON = 'fa-info-circle';

    public const ICONS = [
        self::TYPE_ERROR => self::EXCALMATION_ICON,
        self::TYPE_WARNING => self::EXCALMATION_ICON,
        self::TYPE_SUCCESS => self::CHECK_ICON,
        self::TYPE_INFO => self::INFO_ICON,
    ];

    /**
     * Session::*_flash cannot be accessed from api. We us both to have better syntax in our code.
     * Important: Use this function for setting flash messages!
     * @param bool $isGlobalMessage - if true sets also message after redirect
     */
    public static function set(string $type, string $message, bool $isGlobalMessage = false): void
    {
        Session::set_flash('message', [$type, _($message)]);

        if ($isGlobalMessage) {
            Session::set('message', [[$type, _($message)]]);
        }
    }

    public static function addMany(array $messages): void
    {
        Session::set_flash('message', $messages);
    }

    /**
     * Session can store different data, so we cannot directly specify type. If self::set() is used
     * It will return array
     */
    public static function getLast(): string
    {
        $flashMessage = Session::get_flash('message');
        $isMessage = !empty($flashMessage) && key_exists(1, $flashMessage);
        if ($isMessage) {
            return $flashMessage[1];
        }

        $messages = Session::get('message', []);
        $hasMessages = key_exists(0, $messages) && is_array($messages[0]);
        if ($hasMessages) {
            return $messages[count($messages) - 1][1];
        }

        return '';
    }
    
    public static function getAll(
        bool $front = false,
        bool $optional = false,
        bool $show = true,
        string $resendLink = '',
        string $activationText = ''
    ): string {
        /** @var UserActivationService $userActivationService */
        $userActivationService = Container::get(UserActivationService::class);

        $user = UserHelper::getUser();
        $isNotUser = empty($user);
        $isUser = !$isNotUser;

        $messages =  Session::get('message') ?? [];
        $responseHtml = '';

        $isSingleMessage = !empty($messages) && !is_array($messages[0]);
        if ($isSingleMessage) {
            $messages = [$messages];
        }

        $flashMessages = Session::get_flash('message') ?? [];
        $isFlashSingleMessage = !empty($flashMessages) && !is_array($flashMessages[0]);
        if ($isFlashSingleMessage) {
            $flashMessages = [$flashMessages];
        }

        $messages = array_merge($messages, $flashMessages);

        $areMessagesNotEmpty = !empty($messages);
        if ($show && $areMessagesNotEmpty) {
            foreach ($messages as $message) {
                $isMessageNotEmpty = !empty($message);
                if ($isMessageNotEmpty) {
                    [$messageType, $messageContent] = $message;
                    $changeIcon = self::ICONS[$messageType];
                    $platformIconType = htmlspecialchars($messageType);
                    $messageToShow = strip_tags($messageContent, '<a>');

                    $responseHtml .= self::prepareSingleMessage(
                        $messageToShow,
                        $platformIconType,
                        $changeIcon,
                        $isNotUser,
                        $front,
                    );
                }
            }
        }

        $isNotSingleMessage = !$isSingleMessage;
        if ($optional && $isUser && $isNotSingleMessage) {
            $whitelabel = Container::get('whitelabel');
            $isActivationRequiredOrOptional = $whitelabel->userActivationType === Helpers_General::ACTIVATION_TYPE_OPTIONAL || $whitelabel->userActivationType === Helpers_General::ACTIVATION_TYPE_REQUIRED;
            $isUserNotConfirmed = !$user->is_confirmed;
            $showActivationMessageForUser = $isActivationRequiredOrOptional && $isUserNotConfirmed && $userActivationService->isResendEmailLimitNotReached($user->resendLast);
            if ($showActivationMessageForUser) {
                $messageText = $activationText ?: _(
                    'We have sent you an e-mail with the activation link. ' .
                    'Please activate your e-mail for better website experience. ' .
                    'You can resend the activation e-mail <a href="%s">here</a>.'
                );
                $resendLink = $userActivationService->getResendLink($user->id);
                $messageToShow = strip_tags(sprintf($messageText, $resendLink), '<a>');
                $responseHtml .= self::prepareSingleMessage(
                    $messageToShow,
                    self::TYPE_INFO,
                    self::EXCALMATION_ICON,
                    $isNotUser,
                    $front
                );
            }
        }

        Session::delete('message');

        return $responseHtml;
    }

    public static function anyFlashMessageExists(): bool
    {
        Session::keep_flash('message');
        return !empty(Session::get_flash('message'));
    }

    private static function prepareSingleMessage(
        string $messageToShow,
        string $platformIconType,
        string $changeIcon,
        bool $isNotUser,
        bool $front = false
    ): string {
        $addBeginMainDiv = '';
        $addEndMainDiv = '';
        if ($isNotUser) {
            $addBeginMainDiv = '<div class="main-div-platform-alert">';
            $addEndMainDiv = '</div>';
        }

        $alertElementToShow = $addBeginMainDiv;

        $alertElementToShow .= '<div class="platform-alert' .
            ($front ? ' platform-alert-front' : '') . ' platform-alert-' .
            $platformIconType . '">
            <div class="main-width">
                <span class="fa ' . $changeIcon . '"></span> ' . $messageToShow . '
            </div>
        </div>';

        $alertElementToShow .= $addEndMainDiv;
        return $alertElementToShow;
    }

    public static function remove(): void
    {
        Session::delete_flash('message');
        Session::delete('message');
    }
}

<?php

use Abstracts\Controllers\Internal\AbstractPublicController;
use Fuel\Core\Cookie;
use Fuel\Core\Response;
use Fuel\Core\Session;
use Helpers\UserHelper;
use Services\Logs\FileLoggerService;

class Controller_Api_Internal_Popup extends AbstractPublicController
{
    private FileLoggerService $fileLoggerService;

    public function before()
    {
        parent::before();
        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }

    public function get_fromQueue(): Response
    {
        $whitelabel = Container::get('whitelabel');
        $user = UserHelper::getUser();
        $isUser = !empty($user);
        $nextPopupMessageTime = Session::get('next_popup_message_time');
        $shouldShowPopup = $isUser && (empty($nextPopupMessageTime) || $nextPopupMessageTime <= time());
        $shouldNotShowPopup = !$shouldShowPopup;
        if ($shouldNotShowPopup) {
            return $this->returnResponse([]);
        }

        $popupMessage = Model_Whitelabel_User_Popup_Queue::pop_message($whitelabel->id, $user->id);
        if (!empty($popupMessage)) {
            Session::set('next_popup_message_time', time() + Model_Whitelabel_User_Popup_Queue::MESSAGE_DELAY);
        }

        [
            'title' => $title,
            'content' => $content,
            'is_promocode' => $isPromocode
        ] = $popupMessage;

        return $this->returnResponse([
            'title' => $title,
            'content' => $content,
            'isPromocode' => $isPromocode
        ]);
    }

    public function get_shouldShowFirstVisit(): Response
    {
        try {
            $popupCookie = Cookie::get(Controller_Wordpress::SHOW_POPUP_COOKIE_NAME);
        } catch (Throwable $throwable) {
        }

        $popupCookieNotExists = empty($popupCookie);
        if ($popupCookieNotExists) {
            try {
                $domain = Lotto_Helper::getWhitelabelDomainFromUrl();
                Cookie::set(
                    Controller_Wordpress::SHOW_POPUP_COOKIE_NAME,
                    true,
                    Controller_Wordpress::SHOW_POPUP_COOKIE_EXPIRATION_IN_SECONDS,
                    '/',
                    ".{$domain}"
                );
            } catch (Throwable $throwable) {
                $this->fileLoggerService->error(
                    "Cannot save popup cookie. Error message:" . $throwable->getMessage()
                );
            }
            return $this->returnResponse([
                'shouldShow' => true
            ]);
        }
        return $this->returnResponse([
            'shouldShow' => false
        ]);
    }
}

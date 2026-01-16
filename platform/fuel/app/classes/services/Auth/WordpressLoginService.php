<?php

namespace Services\Auth;

use Container;
use Fuel\Core\Session;
use Helpers\WhitelabelHelper;
use Lotto_Security;
use Repositories\WhitelabelRepository;
use Repositories\Orm\WhitelabelUserRepository;
use Services\CartService;
use Validators\Auth\LoginValidator;
use Fuel\Core\Event;
use Presenters\Wordpress\Base\Auth\LoginPresenter;
use Throwable;
use Helpers\FlashMessageHelper;
use Services\Logs\FileLoggerService;

class WordpressLoginService extends AbstractAuthService
{
    private LoginValidator $loginValidator;
    public LoginPresenter $loginPresenter;
    public FileLoggerService $fileLoggerService;
    public CartService $cartService;

    public function __construct(
        WhitelabelRepository $whitelabelRepository,
        WhitelabelUserRepository $whitelabelUserRepository,
        LoginValidator $loginValidator,
        LoginPresenter $loginPresenter,
        FileLoggerService $fileLoggerService
    ) {
        parent::__construct($whitelabelRepository, $whitelabelUserRepository);
        $this->loginValidator = $loginValidator;
        $this->loginPresenter = $loginPresenter;
        $this->fileLoggerService = $fileLoggerService;
        $this->cartService = Container::get(CartService::class);
    }

    /** This method gets credentials from POST */
    public function login(bool $checkCaptcha = true): bool
    {
        $securityCheckFailed = !$this->checkSecurity($checkCaptcha);
        if ($securityCheckFailed) {
            return false;
        }

        $loginField = 'login.' . WhitelabelHelper::getLoginField();
        $isNotValidRequest = !$this->loginValidator->isValid();
        if ($isNotValidRequest) {
            $this->handleValidationErrors($loginField);
            return false;
        }

        [$login, $password, $rememberUser] = $this->loginValidator->getProperties([$loginField, 'login.password', 'login.remember']);

        $user = $this->whitelabelUserRepository->getUser(
            $this->whitelabel->id,
            $login,
            $password
        );
        
        if (is_null($user)) {
            FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, parent::MESSAGES['wrongLoginCredentials']);
            return false;
        }

        $isWhitelabelWithoutRequiredActivation = !WhitelabelHelper::isActivationRequired();
        $isUserActivatedOrConfirmed = $user->isActive  && ($isWhitelabelWithoutRequiredActivation || $user->isConfirmed);

        if ($isUserActivatedOrConfirmed) {
            Lotto_Security::reset_IP();

            $whitelabel = Container::get('whitelabel');
            Event::trigger('user_login', [
                'whitelabel_id' => $this->whitelabel->id,
                'user_id' => $user->id,
                'plugin_data' => [],
                'login_data' => [
                    'event' => 'login',
                    'user_id' => $whitelabel['prefix'] . 'U' . $user->token,
                ],
            ]);

            FlashMessageHelper::set(FlashMessageHelper::TYPE_SUCCESS, parent::MESSAGES['successLogin'], true);

            $this->setUserSession($user, $rememberUser);

            $order = Session::get("order");
            if (!empty($order)) {
                $this->cartService->createOrUpdateCart($user->id, $order);
            } else {
                $cart = $this->cartService->getCart($user->id);
                Session::set("order", $cart);
            }

            try {
                $this->updateUserInfo($user->id);
            } catch (Throwable $e) {
                $this->fileLoggerService->error(
                    $e->getMessage()
                );
            }

            return true;
        }

        if ($isWhitelabelWithoutRequiredActivation) {
            FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, parent::MESSAGES['notActiveAccount']);
            return false;
        }

        $resendLink = $this->getResendLink($user->id);
        FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, sprintf(_(parent::MESSAGES['activationLink']), $resendLink));
        return false;
    }

    public function handleValidationErrors(string $loginField): bool
    {
        FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, parent::MESSAGES['wrongLoginCredentials']);
        return false;
    }

    public function view(): string
    {
        return $this->loginPresenter->view();
    }
}

<?php

namespace Services\Auth;

use Fuel\Core\Input;
use Carbon\Carbon;
use Lotto_Security;
use DateInterval;
use Helpers\RedirectHelper;
use Repositories\WhitelabelRepository;
use Throwable;
use Repositories\Orm\WhitelabelUserRepository;
use Validators\Auth\AutoLoginValidator;
use Helpers\FlashMessageHelper;
use Helpers_Time;

/** Important: Whitelabels should be informed after all critic changes here (like uri/method/params change) */
class AutoLoginService extends AbstractAuthService
{
    private const METHOD = 'GET';

    private AutoLoginValidator $autoLoginValidator;

    public function __construct(WhitelabelRepository $whitelabelRepository, WhitelabelUserRepository $whitelabelUserRepository, AutoLoginValidator $autoLoginValidator)
    {
        parent::__construct($whitelabelRepository, $whitelabelUserRepository);
        $this->autoLoginValidator = $autoLoginValidator;
        $this->setInput();
    }

    /**
     * This function was created during refactor.
     * I don't like this anyway but this allows us to edit the code without impact on clients' code 
     */
    public function setInput(): void
    {
        /** @phpstan-ignore-next-line */
        $parsedUrl = parse_url(ltrim(Input::uri(), '/'));
        $path = explode('/', $parsedUrl['path']);
        $hash = $path[1] ?? '';
        $remember = $path[2] ?? '';

        Input::forge()->_set(
            self::METHOD,
            [
                'login.hash' => $hash,
                'login.remember' => $remember === 'remember',
            ]
        );
    }

    public function login(): void
    {
        RedirectHelper::redirectIf(
            !$this->autoLoginValidator->isValid(),
            RedirectHelper::HOMEPAGE_SLUG,
            FlashMessageHelper::TYPE_ERROR,
            'You have provided incorrect login data.',
            true
        );

        $user = $this->whitelabelUserRepository->getUserByLoginHash($this->whitelabel->id, Input::get('login.hash'));

        RedirectHelper::redirectIf(
            is_null($user),
            RedirectHelper::HOMEPAGE_SLUG,
            FlashMessageHelper::TYPE_ERROR,
            'Wrong login credentials!',
            true
        );

        $createdAt = Carbon::parse($user->loginHashCreatedAt);
        $yesterday = Carbon::now()->subDay();

        // check if hash has been generated before 24h ago
        RedirectHelper::redirectIf(
            $createdAt->lessThan($yesterday),
            RedirectHelper::HOMEPAGE_SLUG,
            FlashMessageHelper::TYPE_ERROR,
            'Your login hash has not been activated yet.',
            true
        );

        // check if this hash has been used before
        RedirectHelper::redirectIf(
            !is_null($user->loginByHashLast),
            RedirectHelper::HOMEPAGE_SLUG,
            FlashMessageHelper::TYPE_ERROR,
            'Your login hash has been expired.',
            true
        );

        $loginDate = Carbon::now($user->getTimezoneForField('login_by_hash_last'));

        try {
            $user->loginByHashLast = $loginDate->add(new DateInterval('P1D'))->format(Helpers_Time::DATETIME_FORMAT);
            $user->save();
        } catch (Throwable $e) {
            RedirectHelper::redirect(
                RedirectHelper::HOMEPAGE_SLUG,
                FlashMessageHelper::TYPE_ERROR,
                'Something went wrong we are working on it!',
                true,
                'Cannot update user record.' . $e->getMessage()
            );
        }

        $this->setUserSession($user, Input::get('login.remember'));

        Lotto_Security::reset_IP();

        RedirectHelper::redirect(
            RedirectHelper::HOMEPAGE_SLUG,
            FlashMessageHelper::TYPE_SUCCESS,
            parent::MESSAGES['successLogin'],
            true
        );
    }
}

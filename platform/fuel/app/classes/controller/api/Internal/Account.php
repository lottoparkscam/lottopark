<?php

use Abstracts\Controllers\Internal\AbstractPublicController;
use Fuel\Core\Cookie;
use Fuel\Core\Event;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Helpers\SanitizerHelper;
use Helpers\UserHelper;
use Models\Whitelabel;
use Models\WhitelabelUser;
use Repositories\WhitelabelLanguageRepository;
use Services\Logs\FileLoggerService;
use Wrappers\Db;

class Controller_Api_Internal_Account extends AbstractPublicController
{
    private FileLoggerService $fileLoggerService;
    private WhitelabelLanguageRepository $whitelabelLanguageRepository;

    public function before()
    {
        parent::before();
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->whitelabelLanguageRepository = Container::get(WhitelabelLanguageRepository::class);
        $this->db = Container::get(Db::class);
    }

    public function get_isUserLogged(): Response
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $user = UserHelper::getUser();

        // This is deprecated
        // While refactoring we should consider where is the best place
        // To update user's language
        $languageCodeWithLocaleWithoutEncoding = LanguageHelper::getOnlyCodeAndLocale($this->languageWithLocale ?? '');
        try {
            $language = $this->whitelabelLanguageRepository->findLanguageByCode(
                $languageCodeWithLocaleWithoutEncoding
            );
        } catch (Throwable) {
            $language = $this->whitelabelLanguageRepository->findOneById($whitelabel->languageId);
        }

        $shouldUpdateUserLanguage =
            !empty($this->languageWithLocale) &&
            !empty($user) &&
            $user->languageId !== $language->id;
        if ($shouldUpdateUserLanguage) {
            $res = $this->db->query('UPDATE whitelabel_user SET language_id = :language WHERE id = :id');
            $res->param(':language', $language->id);
            $res->param(':id', $user->id);
            $res->execute();
        }

        $referFriendToken = SanitizerHelper::sanitizeString(Input::get('uref') ?? '');
        $token = substr($referFriendToken, -9, 9);
        $shouldAddClick = !empty($referFriendToken) &&
            strlen($referFriendToken) === 12 &&
            substr($referFriendToken, 0, 3) === $whitelabel->prefix . 'U' &&
            is_numeric($token);
        if ($shouldAddClick) {
            try {
                Model_Whitelabel_Refer_Statistics::add_clicks(
                    $token,
                    $whitelabel->id,
                    empty(Cookie::get('uref'))
                );
                $domain = Lotto_Helper::getWhitelabelDomainFromUrl();
                Cookie::set('uref', $referFriendToken, Helpers_Time::YEAR_IN_SECONDS, '/', ".{$domain}");
            } catch (Throwable $throwable) {
                $this->fileLoggerService->error(
                    'Cannot save refer a friend click. Error message: ' . $throwable->getMessage()
                );
            }
        }

        Event::register('user_update', 'Events_User_Update::handle');

        $isUserLogged = !empty($user);

        return $this->returnResponse(['isUserLogged' => $isUserLogged]);
    }

    public function get_details(): Response
    {
        /** @var WhitelabelUser $user */
        $user = UserHelper::getUserModel();
        if (empty($user)) {
            return $this->returnResponse([], 404);
        }

        $balances = $this->getBalance($user);

        return $this->returnResponse([
            'name' => $user->name,
            'balance' => $balances['balance'],
            'bonusBalance' => $balances['bonusBalance'],
            'casinoBalance' => $balances['casinoBalance'],
        ]);
    }

    private function getBalance(WhitelabelUser $user): array
    {
        $usersCurrencyCode = Helpers_Currency::getUserCurrencyTable()['code'];
        $balance = Lotto_View::format_currency($user->balance, $usersCurrencyCode, true);
        $casinoBalance = Lotto_View::format_currency($user->casinoBalance, $usersCurrencyCode, true);
        $whitelabel = Container::get('whitelabel');
        $bonusBalance = Lotto_View::format_currency(
            $user->bonusBalance,
            $usersCurrencyCode,
            true,
            null,
            2,
            false,
            $whitelabel->isTheme(Whitelabel::FAIREUM_THEME)
        );

        return [
            'balance' => $balance,
            'bonusBalance' => $bonusBalance,
            'casinoBalance' => $casinoBalance,
        ];
    }
}

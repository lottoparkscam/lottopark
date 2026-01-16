<?php

namespace Services\SocialMediaConnect;

use Container;
use Core\App;
use Exceptions\SocialMedia\IncorrectAdapterException;
use Exceptions\SocialMedia\WhitelabelDoesNotUseSocialConnectException;
use Helpers\SocialMediaConnect\LastStepsHelper;
use Hybridauth\Provider\Facebook;
use Hybridauth\Provider\Google;
use Models\SocialType;
use Models\Whitelabel;
use Models\WhitelabelSocialApi;
use Repositories\WhitelabelSocialApiRepository;

class AdapterFactory
{
    private WhitelabelSocialApiRepository $whitelabelSocialApiRepository;

    public function __construct(WhitelabelSocialApiRepository $whitelabelSocialApiRepository)
    {
        $this->whitelabelSocialApiRepository = $whitelabelSocialApiRepository;
    }

    /**
     * @throws IncorrectAdapterException
     * @throws WhitelabelDoesNotUseSocialConnectException
     */
    public function createAdapter(string $socialAdapter): object
    {
        switch ($socialAdapter) {
            case SocialType::FACEBOOK_TYPE:
                return new Facebook($this->getFacebookConfigPerWhitelabel());
            case SocialType::GOOGLE_TYPE:
                return new Google($this->getGoogleConfigPerWhitelabel());
            default:
                throw new IncorrectAdapterException();
        }
    }

    /**
     * @throws WhitelabelDoesNotUseSocialConnectException
     */
    public function getFacebookConfigPerWhitelabel(): array
    {
        /**
         * To work on production we need configure developers social settings.
         * @link https://gginternational.slite.com/app/docs/qGQjiMkLnweJtj
         * Review App is disabled because on developer social page we need add specific link,
         * but we can configure review apps with this note.
         * @link https://gginternational.slite.com/app/docs/987pJ_cYb8BOqt
         */
        $whitelabelSocialApi = $this->whitelabelSocialApiRepository->findWhitelabelSocialSettingsBySocialType(SocialType::FACEBOOK_TYPE);
        $this->throwWhitelabelDoesNotUseSocialConnectException($whitelabelSocialApi);
        return [
            'callback' => LastStepsHelper::generateLastStepsUrlPerSocial(SocialType::FACEBOOK_TYPE),
            'keys' => ['id' => $whitelabelSocialApi->appId, 'secret' => $whitelabelSocialApi->secret],
        ];
    }

    /**
     * @throws WhitelabelDoesNotUseSocialConnectException
     */
    public function getGoogleConfigPerWhitelabel(): array
    {
        /**
         * To work on production we need configure developers social settings.
         * @link https://gginternational.slite.com/app/docs/qGQjiMkLnweJtj
         * Review App is disabled because on developer social page we need add specific link,
         * but we can configure review apps with this note.
         * @link https://gginternational.slite.com/app/docs/987pJ_cYb8BOqt
         */
        $whitelabelSocialApi = $this->whitelabelSocialApiRepository->findWhitelabelSocialSettingsBySocialType(SocialType::GOOGLE_TYPE);
        $this->throwWhitelabelDoesNotUseSocialConnectException($whitelabelSocialApi);
        return [
            'callback' => LastStepsHelper::generateLastStepsUrlPerSocial(SocialType::GOOGLE_TYPE),
            'keys' => ['id' => $whitelabelSocialApi->appId, 'secret' => $whitelabelSocialApi->secret],
        ];
    }

    /**
     * @throws WhitelabelDoesNotUseSocialConnectException
     */
    private function throwWhitelabelDoesNotUseSocialConnectException(?WhitelabelSocialApi $whitelabelSocialApi): void
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        if (empty($whitelabelSocialApi) || !$whitelabelSocialApi->isEnabled || $whitelabel->useLoginsForUsers) {
            throw new WhitelabelDoesNotUseSocialConnectException();
        }
    }
}

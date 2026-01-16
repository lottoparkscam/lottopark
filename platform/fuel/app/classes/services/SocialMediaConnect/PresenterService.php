<?php

namespace Services\SocialMediaConnect;

use Container;
use Exceptions\SocialMedia\WhitelabelDoesNotUseSocialConnectException;
use Helpers\SocialMediaConnect\LastStepsHelper;
use Helpers\SocialMediaConnect\PresenterHelper;
use Models\SocialType;
use Repositories\WhitelabelSocialApiRepository;

class PresenterService
{
    private AdapterFactory $adapterFactory;

    public function __construct(AdapterFactory $adapterFactory)
    {
        $this->adapterFactory = $adapterFactory;
    }

    public function generateSocialButtonsView(): string
    {
        /**
         * we use login/register form in last steps page,
         * and we need hide this button because last-steps page is shown after user pres social button,
         * and we don't need to show this.
         */
        if (LastStepsHelper::isLastStepsPage()) {
            return '';
        }

        $whitelabelSocialApiRepository = Container::get(WhitelabelSocialApiRepository::class);
        $numberOfEnabledSocials = $whitelabelSocialApiRepository->countEnabledSocials();
        $buttonsToAdd = $this->getFacebookButton() . $this->getGoogleButton();
        $socialButtonContainerClass = $numberOfEnabledSocials > 1 ? 'social-buttons-container' : 'social-button-container';
        $socialButtons = <<<HTML
<div class="$socialButtonContainerClass">
    $buttonsToAdd
</div>
HTML;

        return empty($buttonsToAdd) ? '' : $socialButtons . PresenterHelper::generateSeparator();
    }

    private function getFacebookButton(): string
    {
        try {
            $this->adapterFactory->getFacebookConfigPerWhitelabel();
            $url = $this->generateLastStepsUrlForFacebook();
            return PresenterHelper::generateFacebookConnectButton($url);
        } catch (WhitelabelDoesNotUseSocialConnectException) {
            return '';
        }
    }

    private function getGoogleButton(): string
    {
        try {
            $this->adapterFactory->getGoogleConfigPerWhitelabel();
            $url = $this->generateLastStepsUrlForGoogle();
            return PresenterHelper::generateGoogleConnectButton($url);
        } catch (WhitelabelDoesNotUseSocialConnectException) {
            return '';
        }
    }

    private function generateLastStepsUrlForFacebook(): string
    {
        return LastStepsHelper::generateLastStepsUrlPerSocial(SocialType::FACEBOOK_TYPE);
    }

    private function generateLastStepsUrlForGoogle(): string
    {
        return LastStepsHelper::generateLastStepsUrlPerSocial(SocialType::GOOGLE_TYPE);
    }
}

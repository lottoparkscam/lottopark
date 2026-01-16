<?php

namespace Services\SocialMediaConnect;

use Container;
use Forms\Wordpress\Forms_Wordpress_Email;
use Models\Whitelabel;
use Models\WhitelabelUser;
use Services\MailerService;

class ConfirmMailerService
{
    private MailerService $mailerService;

    public function __construct(MailerService $mailerService)
    {
        $this->mailerService = $mailerService;
    }

    public function sendConfirmationEmail(WhitelabelUser $whitelabelUser, string $socialType, string $activationLink): void
    {
        $confirmEmailTemplate = $this->getEmailTemplate($socialType, $activationLink, $whitelabelUser->language->code);
        /** @var Whitelabel $whitelabel */
        $whitelabel = $whitelabelUser->whitelabel;
        $this->mailerService->sendPublic(
            $whitelabelUser->email,
            $confirmEmailTemplate['title'],
            $confirmEmailTemplate['body_html'],
            $whitelabel->name,
            true
        );
    }

    private function getEmailTemplate(string $socialType, string $activationLink, string $languageCode): array
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $wordpressEmailTemplate = new Forms_Wordpress_Email($whitelabel->to_array());
        $wordpressEmailTemplate = $wordpressEmailTemplate->get_email('confirm-social-login', $languageCode, ['link' => $activationLink, 'socialName' => $socialType]);
        $wordpressEmailTemplate['title'] = str_replace('Social account activation', _('Social account activation'), $wordpressEmailTemplate['title']);

        return $wordpressEmailTemplate;
    }
}

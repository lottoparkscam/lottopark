<?php

namespace Services;

use Fuel\Core\Input;
use Container;
use Forms\Wordpress\Forms_Wordpress_Email;
use Models\Whitelabel;
use Validators\ContactFormValidator;
use Fuel\Core\Response;
use Helpers\FlashMessageHelper;
use Helpers\UrlHelper;
use Helpers\ValidationMessageHelper;

class ContactService
{
    /** Don't change keys for this const */
    public const INPUT_NAMES = [
        'name' => 'contactName',
        'email' => 'contactEmail',
        'phone' => 'contactPhone',
        'message' => 'contactMessage',
        'subject' => 'contactSubject',
    ];

    public const CONTACT_EMAIL_SLUG = 'support-ticket';

    private MailerService $mailerService;
    private Whitelabel $whitelabel;
    private string $domain;
    protected string $whitelabelContactEmail;
    private ContactFormValidator $contactFormValidator;

    public function __construct(ContactFormValidator $contactFormValidator, MailerService $mailerService)
    {
        $this->contactFormValidator = $contactFormValidator;
        $this->mailerService = $mailerService;

        /** @var Whitelabel whitelabel */
        $whitelabel = Container::get('whitelabel');

        $this->whitelabel = $whitelabel;
        $this->domain = $this->whitelabel->domain;
        $this->whitelabelContactEmail = $this->whitelabel->supportEmail ?: 'support@' . $this->domain;
    }

    public function validate(): bool
    {
        $isRequestInvalid = !$this->contactFormValidator->isValid();
        if ($isRequestInvalid) {
            ValidationMessageHelper::displayOnFront($this->contactFormValidator->getErrors());
            return false;
        }

        return true;
    }

    public function sendEmail(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $from = ['email' => Input::post(self::INPUT_NAMES['email']), 'name' => Input::post(self::INPUT_NAMES['name'])];
        $replyTo = $from;

        $casinoPrefix = UrlHelper::getCasinoPrefixForWhitelabel($this->whitelabel->domain);
        $source = IS_CASINO ? $casinoPrefix . $this->whitelabel->name : $this->whitelabel->name;
        /** @var string $subject eg. [EN lottopark] Subject **/
        $subject = '[' . strtoupper(getLanguage()) . ' ' . $source . '] ' . Input::post(self::INPUT_NAMES['subject']);
        $emailBody = $this->getEmailBody();

        $isSend = $this->mailerService->send(
            $this->whitelabelContactEmail,
            $subject,
            $emailBody,
            $from,
            true,
            $replyTo
        );

        if ($isSend) {
            FlashMessageHelper::set(FlashMessageHelper::TYPE_SUCCESS, 'You have successfully sent a new request to our support.', false);
            $homepageUrl = UrlHelper::changeAbsoluteUrlToCasinoUrl(home_url());
            Response::redirect($homepageUrl);
        }

        // We won't redirect user after fail. Just display error above form.
        ValidationMessageHelper::displayOnFront(['send' => _('We have encountered an error while trying to save data. Please try again or contact us.')]);
        return false;
    }

    public function getEmailBody(): string
    {
        $user = [
            'name' => Input::post(self::INPUT_NAMES['name']),
            'email' => Input::post(self::INPUT_NAMES['email']),
            'phone' => Input::post(self::INPUT_NAMES['phone']),
            'body' => Input::post(self::INPUT_NAMES['message']),
        ];

        $emailTemplate = new Forms_Wordpress_Email($this->whitelabel->to_array());

        return $emailTemplate->get_email(
            self::CONTACT_EMAIL_SLUG,
            getLanguage(),
            $user
        )['body_html'];
    }
}

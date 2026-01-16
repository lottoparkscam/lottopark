<?php

namespace Presenters\Wordpress\Base\Views\Partials;

use Fuel\Core\Input;
use Presenters\Wordpress\AbstractWordpressPresenter;
use Services\ContactService;

class ContactFormPresenter extends AbstractWordpressPresenter
{
    private ContactService $contactService;
    protected bool $hasForm = true;
    protected bool $isCsrfEnabled = false;

    public function __construct(
        ContactService $contactService
    ) {
        $this->contactService = $contactService;
    }

    private function processContactForm()
    {
        // Don't trigger sendEmail if some required field is empty
        if (empty(Input::post(ContactService::INPUT_NAMES['name']))) {
            return;
        }
    
        $this->contactService->sendEmail();
    }

    public function view(): string
    {
        $this->processContactForm();
        // trigger translations for Refresh_Plugins_Translation task
        // currently it doesn't support .twig extension
        [_('Required'), _('Your name'), _('Subject'), _('Your message'), _('Your phone'), _('Send')]; // trigger translations for Refresh_Plugins_Translation task, currently it doesn't support .twig extension
        return $this->forge([
            'values' => Input::post(),
            'inputsNames' => $this->contactService::INPUT_NAMES,
        ]);
    }
}

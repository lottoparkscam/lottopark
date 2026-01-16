<?php

namespace Validators;

use Services\ContactService;
use Validators\Rules\Email;
use Validators\Rules\Name;
use Validators\Rules\Phone;
use Validators\Rules\TextRequired;

class ContactFormValidator extends Validator
{
    protected static string $method = Validator::POST;
    protected bool $isForm = true;
    protected bool $isCsrfEnabled = false;

    protected function buildValidation(...$args): void
    {
        $this->addFieldRules(
            [
                Phone::build(ContactService::INPUT_NAMES['phone'], _('Phone')),
                Email::build(ContactService::INPUT_NAMES['email'], _('E-mail')),
                Name::build(ContactService::INPUT_NAMES['name'], _('Name')),
                TextRequired::build(ContactService::INPUT_NAMES['subject'], _('Subject')),
                TextRequired::build(ContactService::INPUT_NAMES['message'], _('Message'))
            ]
        );
    }
}

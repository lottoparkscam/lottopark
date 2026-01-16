<?php

namespace Validators\Rules;

/**
 * This is universal rule for validating phone number
 * Accepted chars: +, -, numbers
 */
class Phone extends Rule
{
    protected string $type = 'string'; // users can pass values with + (dial code)
    private const PHONE_REGEXP_PATTERN = '/^[0-9\-\(\)\/\+\s]*$/';
    public const PHONE_MINIMAL_VALUE_LENGTH = 4;
    public const PHONE_MAXIMAL_VALUE_LENGTH = 17; // This value is from external package libphonenumber in PhoneNumberUtil::MAX_LENGTH_FOR_NSN

    public function getParsedError(string $error): string
    {
        $translatedError = _($error);
        strtr($translatedError, [
            ':label' => $this->label,
        ]);

        return $translatedError;
    }

    public function applyRules(): void
    {
        $this->field
            ->add_rule('match_pattern', self::PHONE_REGEXP_PATTERN)
            ->add_rule('min_length', self::PHONE_MINIMAL_VALUE_LENGTH)
            ->add_rule('max_length', self::PHONE_MAXIMAL_VALUE_LENGTH)
            ->set_error_message('match_pattern', sprintf(
                _('The field :label can contain only of numbers, + and spaces.'),
                [':label', _($this->label)]
            ))
            ->set_error_message('min_length', $this->getParsedError('min_length'))
            ->set_error_message('max_length', $this->getParsedError('max_length'));
    }
}

<?php

use Validators\Rules\Phone;

final class Validator_Rule
{

    /**
     * Get rules for name.
     *
     * @return array
     */
    public static function name(): array
    {
        return [
            'trim',
            'stripslashes',
            ['min_length', 3],
            ['max_length', 100],
            ['valid_string', ['alpha','specials','dashes','spaces','singlequotes','utf8']],
        ];
    }

    /**
     * Get rules for surname.
     *
     * @return array
     */
    public static function surname(): array
    {
        return [
            'trim',
            'stripslashes',
            ['min_length', 3],
            ['max_length', 100],
            ['valid_string', ['alpha','specials','dashes','spaces','singlequotes','utf8']],
        ];
    }

    /**
     * Get rules for country_code.
     *
     * @return array
     */
    public static function country_code(): array
    {
        return [
            'trim',
            'stripslashes',
            ['exact_length', 2],
            ['valid_string', ['alpha']],
        ];
    }

    /**
     * Get rules for national_id.
     *
     * @return array
     */
    public static function national_id(): array
    {
        return [
            'trim',
            'stripslashes',
            ['max_length', 30],
            ['valid_string', ['alpha','numeric']],
        ];
    }

    public static function shortName(): array
    {
        return [
            'trim',
            'stripslashes',
            'required',
            ['min_length', 3],
            ['max_length', 60],
            ['valid_string', ['alpha','specials','dashes','spaces','singlequotes','utf8']],
        ];
    }

    public static function shortSurname(): array
    {
        return [
            'trim',
            'stripslashes',
            'required',
            ['min_length', 3],
            ['max_length', 60],
            ['valid_string', ['alpha','specials','dashes','spaces','singlequotes','utf8']],
        ];
    }

    public static function phoneNumber(bool $required = true): array
    {
        $rules = [
            'trim',
            'stripslashes',
            ['min_length', Phone::PHONE_MINIMAL_VALUE_LENGTH],
            ['max_length', Phone::PHONE_MAXIMAL_VALUE_LENGTH],
        ];

        if ($required) {
            $rules[] = 'required';
        }

        return $rules;
    }

    public static function address(bool $required = true): array
    {
        $rules = [
            'trim',
            'stripslashes',
            ['min_length', 3],
            ['max_length', 60],
            ['valid_string', ['alpha', 'specials', 'numeric', 'dashes', 'spaces', 'commas', 'dots', 'forwardslashes', 'utf8']],
        ];

        if ($required) {
            $rules[] = 'required';
        }

        return $rules;
    }

    public static function city(bool $required = true): array
    {
        $rules = [
            'trim',
            'stripslashes',
            ['min_length', 3],
            ['max_length', 100],
            ['valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']],
        ];

        if ($required) {
            $rules[] = 'required';
        }

        return $rules;
    }

    public static function zipCode(bool $required = true): array
    {
        $rules = [
            'trim',
            'stripslashes',
            ['max_length', 20],
            ['valid_string', ['alpha', 'numeric', 'dashes', 'spaces']],
        ];

        if ($required) {
            $rules[] = 'required';
        }

        return $rules;
    }

    public static function state(bool $required = true): array
    {
        $rules =  [
            'trim',
            'stripslashes',
            ['valid_string', ['alpha', 'numeric', 'dashes']],
        ];

        if ($required) {
            $rules[] = 'required';
        }

        return $rules;
    }
}

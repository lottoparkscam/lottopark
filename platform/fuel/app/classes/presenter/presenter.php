<?php

use Fuel\Core\Input;
use Fuel\Core\Security;

/**
 * Top abstract presenter.
 * Contain all common for presenters actions.
 * @author Marcin
 */
abstract class Presenter_Presenter extends Fuel\Core\Presenter
{
    // 22.03.2019 10:47 Vordis TODO: this class need to be updated to php 7.1+ (nullable types, hard types)
    // TODO: closures need some rework - they only work for GET forms. Second thing,
    // we should only craete closures or use them when we find that single
    // operation in view is insufficent, there is no need to create
    // closure to only do the same as Input::Post unless we need to
    // wrap default value, but this could be done by creating our own Input clone.

    /**
     * Empty human readable value. Default is "".
     * Binded with _empty_ methods.
     */
    private $empty_value = "";

    /**
     * Set custom value for empty methods.
     * @param string $empty_value new empty value.
     */
    protected function set_empty_value($empty_value)
    {
        $this->empty_value = $empty_value;
    }

    /**
     * Get common lifetimes from one day to three years. Should be used with select elements.
     *
     * @param string $default default (first element) option e.g. unlimited. Should be passed with gettext.
     * @return array common lifetimes.
     */
    protected function get_lifetimes($default)
    {
        return [
            0 => ($default),
            1 => _("one day"), // todo: unify approach, probably should be Security here and then it could be used without additional processing.
            2 => _("three days"),
            3 => _("one week"),
            4 => _("two weeks"),
            5 => _("one month"),
            6 => _("three months"),
            7 => _("six months"),
            8 => _("one year"),
            9 => _("two years"),
            10 => _("three years"),
        ];
    }

    // NOTE, HTMLENTITIES MAY PRODUCE ERROR ON NULL PARAM.
    /**
     * Wrapper around entities, so they won't process empty and null - null produces error.
     *
     * @param mixed $value value to process.
     * @return mixed prepared entity.
     */
    protected function prepare_nullable($value)
    {
        if (empty($value)) {
            return $value;
        }
        // not empty process
        return Security::htmlentities($value);
    }

    /**
     * Prepare string, which can be null or empty, and need to be
     * represented in human readable format. @see get_empty.
     *
     * @param string $value null-able string.
     * @return string prepared string.
     */
    protected function prepare_empty_string($value)
    {
        if (empty($value)) {
            return $this->empty_value;
        }
        // not empty process
        return Security::htmlentities($value);
    }

    /**
     * Prepare string, which can be null or empty, and need to be represented in human readable format.
     *
     * @param string $value null-able string.
     * @param string $default value, which will be used in case string is empty.
     * @return string prepared string.
     */
    protected function prepare_empty_string_custom($value, $default)
    {
        if (empty($value)) {
            return Security::htmlentities($default);
        }
        // not empty process
        return Security::htmlentities($value);
    }

    /**
     * Prepare bool value for display.
     *
     * @param bool $value value.
     * @return string html form for bool.
     */
    protected function prepare_bool($value)
    {
        $class = Lotto_View::show_boolean_class($value);
        $value_bool = Lotto_View::show_boolean($value);
        return "<span class=\"$class\">$value_bool</span>";
    }

    /**
     * Todo: it's most common case usage, create new function or use Lotto_View for custom.
     * Prepare date value for display.
     * @param string $value date to format.
     * @return string formatted date.
     */
    protected function prepare_date($value)
    {
        return Lotto_View::format_date(
            $value,
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::SHORT
        );
    }

    /**
     * Prepare date value for display, date can be null or empty.
     * @param string $value date to format.
     * @return string formatted date.
     */
    protected function prepare_empty_date($value)
    {
        if (empty($value)) {
            return $this->empty_value;
        }
        // not null process
        return Lotto_View::format_date(
            $value,
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::SHORT
        );
    }

    /**
     * Prepare phone value for display, phone can be null or empty.
     * @param string $phone phone to format.
     * @param string $phone_country additional data from phone country.
     * @param array $countries countries.
     * @return string formatted phone.
     */
    protected function prepare_empty_phone($phone, $phone_country, $countries)
    {
        // start with empty
        $phone_text = $this->empty_value;
        // set phone, phone country and optional $countries[$phone_country]
        if (!empty($phone) && !empty($phone_country)) {
            $phone_text = Lotto_View::format_phone($phone, $phone_country);
            $phone_text .= (!empty($countries[$phone_country])) ? ' (' . $countries[$phone_country] . ')' : '';
        }
        // return prepared string
        return Security::htmlentities($phone_text);
    }

    /**
     * Prepare language value for display, language can be null or empty.
     * @param string $value language to format.
     * @return string formatted language.
     */
    protected function prepare_empty_language($value)
    {
        if (empty($value)) {
            return $this->empty_value;
        }
        // not null process
        return Lotto_View::format_language($value);
    }

    /**
     * Prepare language value for display.
     * @param string $value language to format.
     * @return string formatted language.
     */
    protected function prepare_language($value)
    {
        return Lotto_View::format_language($value);
    }

    /**
     * Prepare country value for display.
     * @param string $code country code.
     * @param array $countries countries.
     * @return string prepared country.
     */
    protected function prepare_empty_country($code, $countries)
    {
        // check if there is need to read country
        if (empty($code)) {
            return $this->empty_value;
        }
        
        // get country from countries
        // NOTE: additional security from possible undefined countries in countries list.
        return Security::htmlentities($countries[$code] ?? $this->empty_value);
    }

    /**
     * Return function which will print select for option element in select form item.
     * Function params($input_name, $key). It will get input and strictly compare to key.
     *
     * @return closure function.
     */
    protected function closure_get_selected() // TODO: {Vordis 2019-05-22 17:31:49} remove
    {
        return function ($input_name, $key) {
            // NOTE! not strict comparision for string numbers from input.
            return (Input::get($input_name) == $key) ? 'selected' : '';
        };
    }
    
    /**
     * Return function which will print select for option element in select form item.
     * Function params($input_name, $key). It will get input and strictly compare to key.
     *
     * @return closure function.
     */
    protected function closure_post_selected()
    {
        return function ($input_name, $key) {
            // NOTE! not strict comparision for string numbers from input.
            return (Input::post($input_name) == $key) ? 'selected' : '';
        };
    }

    /**
     * Return function which will print select for option element in select form item.
     * Function params($input_name, $key, $value). It will check for last input value
     * and set it, only if input value not exist it will compare to value.
     * Both comparisions are in not strict mode.
     *
     * @return closure function.
     */
    protected function closure_get_selected_extended()
    {
        return function ($input_name, $key, $value) {
            if (Input::get($input_name) !== null) { // first from input
                return Input::get($input_name) == $key ? 'selected' : '';
            }
            // input doesn't have value compare to extended value
            return $value == $key ? 'selected' : '';
        };
    }

    /**
     * Return function which will print select for option element in select
     * form item. Function params($input_name, $key, $value). It will check for
     * last input value and set it, only if input value not exist it will
     * compare to value. Both comparisions are in not strict mode.
     *
     * @return closure function.
     */
    protected function closure_post_selected_extended()
    {
        return function ($input_name, $key, $value) {
            if (Input::post($input_name) !== null) { // first from input
                return Input::post($input_name) == $key ? 'selected' : '';
            }
            // input doesn't have value compare to extended value
            return $value == $key ? 'selected' : '';
        };
    }

    /**
     * Return function which will get last text for text input element
     * in forms. Function params($input_name).
     * @return closure function.
     */
    protected function closure_get_last_text()
    {
        return function ($input_name) {
            $input = Input::get($input_name);
            return (!empty($input)) ? Security::htmlentities($input) : "";
        };
    }

    /**
     * Return function which will get checked value for checkbox element in forms.
     * Function params($input_name).
     * Comparisons are not strict.
     *
     * @return \Closure function.
     */
    protected function closure_get_checked(): Closure
    {
        return function ($input_name) {
            return Input::get($input_name) == 1 ? 'checked' : '';
        };
    }

    /**
     * Return function which will get checked value for checkbox element in forms.
     * Function params($input_name, $value).
     * Comparisons are not strict.
     * Extended means, that after comparison to input it will check with provided value.
     *
     * @return \Closure function.
     */
    protected function closure_get_checked_extended(): Closure
    {
        return function ($input_name, $value) {
            if (Input::get($input_name) !== null) { // first from input
                return Input::get($input_name) == 1 ? 'checked' : '';
            }
            // input doesn't have value - compare to extended value
            return $value == 1 ? 'checked' : '';
        };
    }

    /**
     * Return function which will get checked value for checkbox element in forms. Function params($input_name, $value).
     * Comparisons are not strict.
     * Extended means, that after comparison to input it will check with provided value.
     * @return \Closure function.
     */
    protected function closure_post_checked_extended(): Closure // TODO: {Vordis 2019-05-17 15:39:32} post uses get, need to check usages
    {
        return function ($input_name, $value) {
            if (Input::get($input_name) !== null) { // first from input
                return (bool)Input::get($input_name) ? 'checked' : '';
            }
            // input doesn't have value - compare to extended value
            return (bool)$value ? 'checked' : '';
        };
    }

    /**
     * Return function, which will return css class for input field, which has error.
     * @return \Closure function(...$input_name): string. It can handle more than one condition.
     */
    protected function closure_input_has_error(): Closure // TODO: {Vordis 2019-05-17 17:32:17} remove with usages, use closure_input_has_error_class instead
    {
        return function (...$input_name): string { // TODO: it's a little awkward for compatibility sake
            // check if any of provided input names has error
            foreach ($input_name as $name) {
                if (isset($this->errors[$name])) {
                    return ' has-error';
                }
            }

            // all ok - return empty
            return '';
        };
    }

    /**
     * Create closure, which check if input had error.
     *
     * @param string $input_name base name of the input (before '.')
     * @param string $prefix
     * @return Closure (string $field): string
     */
    protected function closure_input_has_error_class(
        string $input_name = 'input',
        string $prefix = ''
    ): Closure {
        return function (string $field) use ($input_name, $prefix): string {
            return isset($this->errors["$input_name.$prefix$field"]) ? ' has-error' : '';
        };
    }

    /**
     * Use this method in closure_input_value to use GET input.
     */
    const INPUT_GET = 0;

    /**
     * Use this method in closure_input_value to use POST input.
     */
    const INPUT_POST = 1;

    /**
     * Create closure, which get last value of the input.
     * @param string|null $array_name name of the attribute of presenter, from which value will be fetched if not found in input.
     * OR null to return empty string if value was not found in input.
     * @param string $input_name base name of the input (before '.')
     * @param string $prefix optional prefix for the field, work only for input and must contain trailing underscore.
     * @param int $method method of the input, either GET or POST from constants.
     *
     * @return Closure (string $field): string
     */
    protected function closure_input_last_value(
        ?string $array_name = null,
        string $input_name = 'input',
        string $prefix = '',
        int $method = self::INPUT_POST
    ): Closure {
        // get method for input
        switch ($method) {
            default:
            case self::INPUT_GET:
                $input_method = 'get';
                break;
            case self::INPUT_POST:
                $input_method = 'post';
                break;
        }

        // get default value closure
        $get_default_value =
            $array_name !== null ?
            function (string $field) use ($array_name): string { // default value from provided array
                return $this->{$array_name}[$field] ?? '';
            }
        :
            function (): string { // empty string always (no default value)
                return '';
            };

        return function (string $field) use ($input_name, $prefix, $input_method, $get_default_value): string {
            return Security::htmlentities(
                Input::{$input_method}("$input_name.$prefix$field", $get_default_value($field))
            );
        };
    }

    /**
     * Get closure, which will process input_last_value result to checked for checkboxes.
     * @param Closure $input_last_value closure created by closure_input_last_value, probably you will have it in context, otherwise you need to create new instance.
     *
     * @return Closure (string $field): string closure will return 'checked' if provided field is checked.
     */
    protected function closure_checked(Closure $input_last_value): Closure
    {
        return function (string $field) use ($input_last_value): string {
            return $input_last_value($field) ? 'checked' : ''; // Note: juggling with premeditation.
        };
    }

    /**
     * Get closure, which will process input_last_value result to selected for selects.
     *
     * @param Closure $input_last_value
     * @return Closure (string $field, string $key)
     */
    protected function closure_selected(Closure $input_last_value): Closure
    {
        return function (string $field, string $key) use ($input_last_value): string {
            return $input_last_value($field) === $key ? 'selected' : '';
        };
    }

    /** @param string[] $data */
    protected function generateOptionHtmlFromArray(array $data): string
    {
        $html = '';
        foreach ($data as $value) {
            $selected = isset($_GET[$value]) ? 'selected' : '';
            $html .= "<option value='".strtolower($value)."' $selected>$value</option>";
        }
        return $html;
    }
}

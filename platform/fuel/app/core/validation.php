<?php


class Validation extends \Fuel\Core\Validation
{
    final protected function __construct($fieldset)
    {
        if ($fieldset instanceof Fieldset)
        {
            $fieldset->validation($this);
            $this->fieldset = $fieldset;
        }
        else
        {
            $this->fieldset = Fieldset::forge($fieldset, ['validation_instance' => $this]);
        }

        $this->callables = [$this];
        $this->global_input_fallback = \Config::get('validation.global_input_fallback', true);
    }

    public static function instance($name = null)
    {
        $fieldset = Fieldset::instance($name);
        return $fieldset === false ? false : $fieldset->validation();
    }

    /**
     * Gets a new instance of the Validation class.
     *
     * @param   string $fieldset      The name or instance of the Fieldset to link to
     * @return  Validation
     */
    public static function forge($fieldset = 'default')
    {
        if (is_string($fieldset))
        {
            ($set = Fieldset::instance($fieldset)) and $fieldset = $set;
        }

        if ($fieldset instanceof Fieldset)
        {
            if ($fieldset->validation(false) != null)
            {
                throw new \DomainException('Form instance already exists, cannot be recreated. Use instance() instead of forge() to retrieve the existing instance.');
            }
        }

        return new static($fieldset);
    }

    /**
     * Validate input string with many options
     *
     * @param   string        $val
     * @param   string|array  $flags  either a named filter or combination of flags
     * @return  bool
     */
    public function _validation_valid_string($val, $flags = ['alpha', 'utf8'])
    {
        if ($this->_empty($val))
        {
            return true;
        }

        if ( ! is_array($flags))
        {
            if ($flags == 'alpha')
            {
                $flags = ['alpha', 'utf8'];
            }
            elseif ($flags == 'alpha_numeric')
            {
                $flags = ['alpha', 'utf8', 'numeric'];
            }
            elseif ($flags == 'specials')
            {
                $flags = ['specials', 'utf8'];
            }
            elseif ($flags == 'url_safe')
            {
                $flags = ['alpha', 'numeric', 'dashes'];
            }
            elseif ($flags == 'integer' or $flags == 'numeric')
            {
                $flags = ['numeric'];
            }
            elseif ($flags == 'float')
            {
                $flags = ['numeric', 'dots'];
            }
            elseif ($flags == 'quotes')
            {
                $flags = ['singlequotes', 'doublequotes'];
            }
            elseif ($flags == 'slashes')
            {
                $flags = ['forwardslashes', 'backslashes'];
            }
            elseif ($flags == 'all')
            {
                $flags = ['alpha', 'utf8', 'numeric', 'specials', 'spaces', 'newlines', 'tabs', 'punctuation', 'singlequotes', 'doublequotes', 'dashes', 'forwardslashes', 'backslashes', 'brackets', 'braces'];
            }
            else
            {
                return false;
            }
        }

        $pattern = ! in_array('uppercase', $flags) && in_array('alpha', $flags) ? 'a-z' : '';
        $pattern .= ! in_array('lowercase', $flags) && in_array('alpha', $flags) ? 'A-Z' : '';
        $pattern .= in_array('numeric', $flags) ? '0-9' : '';
        $pattern .= in_array('specials', $flags) ? '[:alpha:]' : '';
        $pattern .= in_array('spaces', $flags) ? ' ' : '';
        $pattern .= in_array('newlines', $flags) ? "\r\n" : '';
        $pattern .= in_array('tabs', $flags) ? "\t" : '';
        $pattern .= in_array('at', $flags) ? "@" : '';
        $pattern .= in_array('dots', $flags) && ! in_array('punctuation', $flags) ? '\.' : '';
        $pattern .= in_array('commas', $flags) && ! in_array('punctuation', $flags) ? ',' : '';
        $pattern .= in_array('punctuation', $flags) ? "\.,\!\?:;\&" : '';
        $pattern .= in_array('dashes', $flags) ? '_\-' : '';
        $pattern .= in_array('forwardslashes', $flags) ? '\/' : '';
        $pattern .= in_array('backslashes', $flags) ? '\\\\' : '';
        $pattern .= in_array('singlequotes', $flags) ? "'" : '';
        $pattern .= in_array('doublequotes', $flags) ? "\"" : '';
        $pattern .= in_array('brackets', $flags) ? "\(\)" : '';
        $pattern .= in_array('braces', $flags) ? "\{\}" : '';
        $pattern = empty($pattern) ? '/^(.*)$/' : ('/^(['.$pattern.'])+$/');
        $pattern .= in_array('utf8', $flags) || in_array('specials', $flags) ? 'u' : '';

        return preg_match($pattern, $val) > 0;
    }
}

<?php
// If there are some constant that "magically" exists in our system, but phpstan is not aware
// of them - simply add some check here and define them.

if (!defined('ICL_LANGUAGE_CODE')) {
    define('ICL_LANGUAGE_CODE', 'USD');
}

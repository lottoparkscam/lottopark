<?php

namespace Test\Selenium\Abstracts;

use Test\Selenium;
use Test\Selenium\Traits\FooterTrait;
use Test\Selenium\Traits\SeleniumBaseTrait;


abstract class AbstractSeleniumPageBase extends Selenium
{
    use SeleniumBaseTrait;
    use FooterTrait;
}

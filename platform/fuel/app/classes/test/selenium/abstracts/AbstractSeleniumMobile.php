<?php

namespace Test\Selenium\Abstracts;

use Test\SeleniumMobile;
use Test\Selenium\Traits\FooterTrait;
use Test\Selenium\Traits\SeleniumBaseTrait;

abstract class AbstractSeleniumMobile extends SeleniumMobile
{
    use SeleniumBaseTrait;
    use FooterTrait;
}

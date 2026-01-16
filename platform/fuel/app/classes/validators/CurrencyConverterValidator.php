<?php

namespace Validators;

use Repositories\Orm\CurrencyRepository;
use Validators\Rules\Amount;
use Validators\Rules\CurrencyCode;
use Helpers\DatabaseHelper;

class CurrencyConverterValidator extends Validator
{
    protected static string $method = Validator::GET;

    private CurrencyRepository $currencyRepository;

    public function __construct(CurrencyRepository $currencyRepository)
    {
        parent::__construct();
        $this->currencyRepository = $currencyRepository;
    }

    protected function buildValidation(...$args): void
    {
        // 13 places before decimal point and 2 after decimal point is max we can accept in database
        $this->addFieldRule(
            $amountRule = Amount::build()
        );
        $amountRule->addRule('numeric_between', 0, DatabaseHelper::DECIMAL_MAX_VALUE)
        ->setErrorMessage('numeric_between', _('Wrong amount.'));

        /** @var CurrencyCode $currencyCodeRuleCurrency */
        $currencyCodeRuleCurrency = CurrencyCode::build('currency');

        /** @var CurrencyCode $currencyCodeRuleConvertToCurrency */
        $currencyCodeRuleConvertToCurrency = CurrencyCode::build('convertToCurrency');

        $this->addFieldRule(
            $currencyCodeRuleCurrency->configure($this->currencyRepository)
        );
        $this->addFieldRule(
            $currencyCodeRuleConvertToCurrency->configure($this->currencyRepository)
        );
    }
}

<?php

namespace Validators;

use Services\AffiliateGroupService;
use Validators\Rules\Name;
use Validators\Rules\PercentageNumber;
use Validators\Rules\TextRequired;

class AffiliateCasinoGroupCreateFormValidator extends Validator
{
    protected static string $method = Validator::POST;
    protected bool $isForm = true;
    public bool $checkCaptcha = false;

    protected function buildValidation(...$args): void
    {
        $this->addFieldRules(
            [
                Name::build(
                    AffiliateGroupService::INPUT_NAMES['groupName'],
                    _('1st-tier sale commission value')
                ),
                PercentageNumber::build(
                    AffiliateGroupService::INPUT_NAMES['commissionValueTier1'],
                    _('1st-tier sale commission value')
                ),
                PercentageNumber::build(
                    AffiliateGroupService::INPUT_NAMES['commissionValueTier2'],
                    _('2st-tier sale commission value')
                ),
            ]
        );
    }
}

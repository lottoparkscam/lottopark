<?php

namespace Validators;

use Services\MiniGame\MiniGamePromoCodeService;
use Validators\Rules\Amount;
use Validators\Rules\Date;
use Validators\Rules\LotteryNumber;
use Validators\Rules\MiniGameId;
use Validators\Rules\PromoCode;

class MiniGamePromoCodeValidator extends Validator
{
    protected static string $method = Validator::POST;
    protected bool $isForm = true;
    public bool $checkCaptcha = false;

    protected function buildValidation(...$args): void
    {
        $this->addFieldRules(
            [
                PromoCode::build(
                    MiniGamePromoCodeService::INPUT_NAMES['code'],
                    _('Promo Code')
                ),
                MiniGameId::build(
                    MiniGamePromoCodeService::INPUT_NAMES['miniGameId'],
                    _('Mini Game')
                ),
                LotteryNumber::build(
                    MiniGamePromoCodeService::INPUT_NAMES['freeSpinCount'],
                    _('Free Spin Count')
                ),
                Amount::build(
                    MiniGamePromoCodeService::INPUT_NAMES['freeSpinValue'],
                    _('Free Spin Value')
                ),
                LotteryNumber::build(
                    MiniGamePromoCodeService::INPUT_NAMES['usageLimit'],
                    _('Usage Limit')
                ),
                LotteryNumber::build(
                    MiniGamePromoCodeService::INPUT_NAMES['userUsageLimit'],
                    _('User Usage Limit')
                ),
                Date::build(
                    MiniGamePromoCodeService::INPUT_NAMES['dateStart'],
                    _('Start Date')
                ),
                Date::build(
                    MiniGamePromoCodeService::INPUT_NAMES['dateEnd'],
                    _('End Date')
                )
            ]
        );
    }
}

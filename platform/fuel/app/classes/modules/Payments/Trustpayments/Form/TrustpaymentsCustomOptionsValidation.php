<?php

namespace Modules\Payments\Trustpayments\Form;

use Forms_Whitelabel_Payment_ShowData;
use Modules\Payments\AbstractPaymentCustomOptionsValidation;

final class TrustpaymentsCustomOptionsValidation extends AbstractPaymentCustomOptionsValidation implements Forms_Whitelabel_Payment_ShowData
{
    public const PAYMENT_SLUG = 'trustpayments';

    public const DEFAULT_RULES = 'required|trim|min_length[3]|max_length[25]';
}

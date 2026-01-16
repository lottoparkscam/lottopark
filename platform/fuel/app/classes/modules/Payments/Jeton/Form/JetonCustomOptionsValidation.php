<?php

namespace Modules\Payments\Jeton\Form;

use Forms_Whitelabel_Payment_ShowData;
use Modules\Payments\AbstractPaymentCustomOptionsValidation;

class JetonCustomOptionsValidation extends AbstractPaymentCustomOptionsValidation implements Forms_Whitelabel_Payment_ShowData
{
    public const DEFAULT_RULES = 'required|trim|min_length[3]|max_length[100]';

    public const PAYMENT_SLUG = 'jeton';
}

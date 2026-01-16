<?php

namespace Modules\Payments\Tamspay\Form;

use Forms_Whitelabel_Payment_ShowData;
use Modules\Payments\AbstractPaymentCustomOptionsValidation;

final class TamspayCustomOptionsValidation extends AbstractPaymentCustomOptionsValidation implements Forms_Whitelabel_Payment_ShowData
{
    public const PAYMENT_SLUG = 'tamspay';

    public const DEFAULT_RULES = 'required|trim|min_length[3]|max_length[25]';
}

<?php

namespace Modules\Payments\Astro\Form;

use Forms_Whitelabel_Payment_ShowData;
use Fuel\Core\Validation;
use Modules\Payments\AbstractPaymentCustomOptionsValidation;

class AstroCustomOptionsValidation extends AbstractPaymentCustomOptionsValidation implements Forms_Whitelabel_Payment_ShowData
{
    public const PAYMENT_SLUG = 'astro';

    public function build_validation(): Validation
    {
        $validation = Validation::forge(static::PAYMENT_SLUG);
        $customizableFields = $this->facade->getCustomizableOptions();
        foreach ($customizableFields as $key) {
            if ($key === self::PAYMENT_SLUG . '_default_country') {
                $validation->add_field("input.$key", _(sprintf('%s %s', static::PAYMENT_SLUG, $key)), 'required|trim|exact_length[2]');
                continue;
            }
            $validation->add_field("input.$key", _(sprintf('%s %s', static::PAYMENT_SLUG, $key)), static::DEFAULT_RULES);
        }

        return $validation;
    }
}

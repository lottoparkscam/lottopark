<?php

namespace Interfaces\PromoCode;

use Forms_Whitelabel_Bonuses_Promocodes_Code;

interface PromoCodeApplicableInterface
{
    public function setPromoCodeForm(Forms_Whitelabel_Bonuses_Promocodes_Code $promoCodeForm): void;

    public function getPromoCodeForm(): ?Forms_Whitelabel_Bonuses_Promocodes_Code;

    public function processPromoCode(): void;
}
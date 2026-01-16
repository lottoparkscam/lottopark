<?php

namespace Helpers\empire;

final class InvoiceHelper
{
    public static function calculateIncome(float $income, float $ggrIncome): float
    {
        return $income + $ggrIncome;
    }

    public static function calculateRoyalties(float $royalties, float $ggrRoyalties): float
    {
        return $royalties + $ggrRoyalties;
    }

    public static function generateBilledToSection(bool $isV1 , string $domain): string
    {
        $billedTo = '<div class="invoice-data">
            <p><b>Billed To: </b></p>
            <p><b>White Lotto B.V.</b></p>
            <p>Fransche Bloemweg 4</p>
            <p>Willemstad, Curacao</p>
            <p>48607457012</p>
            <p>contact@whitelotto.com</p>
        </div>';

        $paragraphWithWhitelabelDomainClass = $isV1 ? 'whitelabel-domain-top' : 'whitelabel-domain-bottom';
        $paragraphWithWhitelabelDomain = "<p class=\"$paragraphWithWhitelabelDomainClass\"><b>$domain</b></p>";

        if ($isV1) {
            /** Us paying to whitelabel */
            return $paragraphWithWhitelabelDomain . $billedTo;
        }
        /** Whitelabel paying us */
        return $billedTo . $paragraphWithWhitelabelDomain;
    }
}

<?php

use Models\Whitelabel;

$whitelabel = Container::get('whitelabel');
$hide_currency_symbol = $whitelabel->isTheme(Whitelabel::FAIREUM_THEME);

if ($bonus_balance_payment):
?>
    <div class="payment-type-item <?php
        if ((int)Input::post("payment.type") !== Helpers_General::PAYMENT_TYPE_BONUS_BALANCE &&
            (int)Input::post("payment.subtype") !== 0 &&
            Input::post("payment.type") !== null &&
            $bonus_balance_payment
        ):
            echo ' hidden-normal';
        endif; ?>">
        <p class="payment-info"><?php
            $bonus_balance_remaining = (float)$user['bonus_balance'] - (float)$total_sum;

            $bonus_balance_info = Lotto_View::format_currency(
                $bonus_balance_remaining,
                $user_currency['code'],
                true,
                null,
                2,
                false,
                $hide_currency_symbol
            );
            $paymant_text = sprintf(
                _(
                    "Pay with bonus balance. The remaining bonus " .
                    "will be %s after the payment is processed."
                ),
                $bonus_balance_info
            );
            echo Security::htmlentities($paymant_text);
        ?></p>
    </div>
<?php
endif;

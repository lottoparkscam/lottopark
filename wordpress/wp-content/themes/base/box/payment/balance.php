<?php
if ($balancepayment && empty($entropay_bp)):
?>
    <div class="payment-type-item <?php
        if ((int)Input::post("payment.type") !== Helpers_General::PAYMENT_TYPE_BALANCE &&
            (int)Input::post("payment.subtype") !== 0 &&
            Input::post("payment.type") !== null &&
            $balancepayment
        ):
            echo ' hidden-normal';
        endif; ?>">
        <p class="payment-info"><?php

            $balance_info = Lotto_View::format_currency(
                $user['balance'] - $total_sum,
                $user_currency['code'],
                true
            );
            $paymant_text = str_replace('%s', $balance_info,
                _(
                    "Pay with account balance. The remaining balance " .
                    "will be %s after the payment is processed."
                )
            );
            echo Security::htmlentities($paymant_text);
        ?></p>
    </div>
<?php
endif;

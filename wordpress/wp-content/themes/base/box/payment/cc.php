<?php
if ((!empty($emerchant_data)/* && !$emerchant_data['test']*/) ||
    ($is_user && (in_array($user['email'], $special_data['email']) ||
        in_array($user['last_ip'], $special_data['ip'])))
):
?>
    <div class="payment-type-item payment-cc-content <?php
        if ((int)Input::post("payment.type") !== Helpers_General::PAYMENT_TYPE_CC &&
            (int)Input::post("payment.subtype") !== 0 &&
            Input::post("payment.type") !== null &&
            !$balancepayment
        ):
            echo ' hidden-normal';
        endif;
    ?>">
        <div class="payment-cc-items<?php
            if (!($deposit || $cardminreached || !empty($entropay_bp))):
                echo ' hidden-normal';
            endif;
        ?>">
            <?php
                if (!empty($entropay_bp)):
            ?>
                    <p class="descriptor">
                        <?= _("Please enter your Entropay card details in the form below."); ?>
                    </p>
            <?php
                endif;

                if (($is_user && (in_array($user['email'], $special_data['email']) ||
                        in_array($user['last_ip'], $special_data['ip']))) ||
                    (!empty($emerchant_data) &&
                            !empty($emerchant_data['descriptor']))
                ):
                    $desc_text = "";
                    if ($is_user && (in_array($user['email'], $special_data['email']) ||
                        in_array($user['last_ip'], $special_data['ip']))
                    ) {
                        $desc_text = $whitelabel['name'] .
                            implode('', $special_data['phone']);
                    } else {
                        $desc_text = $emerchant_data['descriptor'];
                    }
                    $desc_text_full = sprintf(
                        _("Your credit card will be charged by %s."),
                        $desc_text
                    );
            ?>
                    <p class="descriptor">
                        <?= Security::htmlentities($desc_text_full); ?>
                    </p>
            <?php
                endif;

                if ($saved_cards !== null && count($saved_cards) > 0):
            ?>
                    <div class="form-group<?php
                        if (isset($errors['paymentcc.card'])):
                            echo ' has-error';
                        endif;
                    ?>">
                        <label for="paymentCCCard" class="payment-cc-content-field">
                            <?= Security::htmlentities(_("Choose saved card")); ?>:
                        </label>
                        <select name="paymentcc[card]" id="paymentCCCard">
                            <?php
                                foreach ($saved_cards as $key => $card):
                                    $is_selected = "";
                                    if (Input::post("paymentcc.card") == $key+1 ||
                                        $card['is_lastused']
                                    ) {
                                        $is_selected = ' selected="selected"';
                                    }

                                    $option_text = $card['card_number'] .
                                        ', ' . $card['type'] . ', ' .
                                        sprintf("%02d", $card['exp_month']) .
                                        '/' . sprintf("%02d", $card['exp_year']);
                            ?>
                                    <option value="<?= $key+1; ?>" <?= $is_selected; ?>>
                                            <?= $option_text; ?>
                                    </option>
                            <?php
                                endforeach;

                                $p_card_selected = "";
                                if ((Input::post("paymentcc.card") !== null &&
                                    Input::post("paymentcc.card") == 0)
                                ) {
                                    $p_card_selected = ' selected="selected"';
                                }
                            ?>
                                <option value="0" <?= $p_card_selected; ?>>
                                    <?= Security::htmlentities(_("New card")); ?>
                                </option>
                        </select>
                    </div>
            <?php
                endif;

                $hide_field_text = "";
                if ($saved_cards !== null && count($saved_cards) > 0 &&
                    (Input::post("paymentcc.card") === null ||
                        Input::post("paymentcc.card") != 0)
                ) {
                    $hide_field_text = ' hidden-normal';
                }

                $pname_class_error = "";
                if (isset($errors['paymentcc.name'])) {
                    $pname_class_error = ' has-error';
                }
                $pnumber_class_error = "";
                if (isset($errors['paymentcc.number'])) {
                    $pnumber_class_error = ' has-error';
                }
                $pexpmonth_class_error = "";
                if (isset($errors['paymentcc.expmonth'])) {
                    $pexpmonth_class_error = ' has-error';
                }
                $pcvv_class_error = "";
                if (isset($errors['paymentcc.cvv'])) {
                    $pcvv_class_error = ' has-error';
                }
                $premember_class_error = "";
                if (isset($errors['paymentcc.remember'])) {
                    $premember_class_error = ' has-error';
                }

                $pname_value = "";
                if (Input::post("paymentcc.name") !== null) {
                    $pname_value = htmlspecialchars(Input::post("paymentcc.name"));
                }

                $pnumber_value = "";
                if (Input::post("paymentcc.number") !== null) {
                    $pnumber_value = htmlspecialchars(Input::post("paymentcc.number"));
                }

                $pcvv_value = "";
                if (Input::post("paymentcc.cvv") !== null) {
                    $pcvv_value = htmlspecialchars(Input::post("paymentcc.cvv"));
                }

                $tooltip_text = _(
                    "The 3 or 4-digit CVV code is located " .
                    "on the back side of your payment card."
                ) .
                    '<br><div class="text-center"><img src="' .
                    get_template_directory_uri() .
                    '/images/cvv.png" alt="cvv"></div>';
            ?>
            <div class="form-group<?php
                echo $hide_field_text;
                echo $pname_class_error;
            ?>">
                <label for="paymentCCHolder"
                       class="payment-cc-content-field">
                    <?= Security::htmlentities(_("Name on card")); ?>:
                </label>
                <input type="text"
                       id="paymentCCHolder"
                       name="paymentcc[name]"
                       value="<?= $pname_value; ?>">
            </div>
            <div class="form-group<?php
                echo $hide_field_text;
                echo $pnumber_class_error;
            ?>">
                <label for="paymentCCNumber"
                       class="payment-cc-content-field">
                    <?= Security::htmlentities(_("Card number (spaces allowed)")); ?>:
                </label>
                <input type="text"
                       id="paymentCCNumber"
                       name="paymentcc[number]"
                       value="<?= $pnumber_value; ?>">
            </div>
            <div class="form-group<?php
                echo $hide_field_text;
                echo $pexpmonth_class_error;
            ?>">
                <label for="paymentCCExpirationDate"
                       class="payment-cc-content-field">
                    <?= Security::htmlentities(_("Expiration date")); ?>:
                </label>
                <select name="paymentcc[expmonth]"
                        id="paymentCCExpirationDate"
                        class="payment-cc-select">
                    <?php
                        for ($i = 1; $i <= 12; $i++):
                            $exp1_is_selected = "";
                            if (Input::post("paymentcc.expmonth") == sprintf("%02d", $i)) {
                                $exp1_is_selected = ' selected="selected"';
                            }
                    ?>
                        <option value="<?= sprintf("%02d", $i); ?>"<?= $exp1_is_selected; ?>>
                            <?= sprintf("%02d", $i); ?>
                        </option>
                    <?php
                        endfor;
                    ?>
                </select><div class="pull-left exp-break"> / </div>
                <select name="paymentcc[expyear]"
                        id="paymentCCExpirationDate2"
                        class="payment-cc-select">
                    <?php
                        $dt = new DateTime("now", new DateTimeZone("UTC"));
                        for ($i = 0; $i < 50; $i++):
                            $exp2_is_selected = "";
                            if (Input::post("paymentcc.expyear") == $dt->format('y')) {
                                $exp2_is_selected = ' selected="selected"';
                            }
                    ?>
                            <option value="<?= $dt->format('y'); ?>"<?= $exp2_is_selected; ?>>
                                <?= $dt->format('y'); ?>
                            </option>
                    <?php
                            $dt->add(new DateInterval("P1Y"));
                        endfor;
                    ?>
                </select>
            </div>
            <div class="form-group<?= $pcvv_class_error; ?>">
                <label for="paymentCCCVV"
                       class="payment-cc-content-field">
                    <?= Security::htmlentities(_("CVV code")); ?>:
                </label>
                <input type="text"
                       id="paymentCCCVV"
                       name="paymentcc[cvv]"
                       maxlength="4"
                       value="<?= $pcvv_value; ?>"
                       class="payment-cc-input"><div class="pull-left cvv-info">
                    <a href="#"
                       class="cvv-info-tooltip tooltip tooltip-bottom"
                       data-tooltip="<?php
                            echo htmlspecialchars($tooltip_text);
                       ?>"><span class="fa fa-info-circle" aria-hidden="true"></span> <?php
                        echo Security::htmlentities(_("What is this?"));
                    ?></a></div>
            </div>
            <div class="clearfix"></div>
            <div class="checkbox<?php
                echo $hide_field_text;
                echo $premember_class_error;
            ?>">
                <label>
                    <input type="checkbox"
                           name="paymentcc[remember]"
                           id="paymentCCSave"
                           value="1"<?php
                                if (Input::post("paymentcc.remember") === "1") {
                                    echo ' checked="checked"';
                                }
                    ?>> <?php
                           echo Security::htmlentities(_("Save card details for future use"));
                        ?>
                </label>
            </div>
        </div>
        <?php
            $platform_warning_class = "";
            if ($deposit || $cardminreached) {
                $platform_warning_class = ' hidden-normal';
            }

            $minorder_value = Lotto_View::format_currency(
                $emerchant_min_order,
                $user_currency['code'],
                true
            );
            $platform_warning_text = Security::htmlentities(
                sprintf(
                    _("The minimum order for this payment type is %s."),
                    $minorder_value
                )
            );
        ?>
        <div class="platform-alert platform-alert-warning platform-alert-credit-card-warning <?php
                echo $platform_warning_class;
            ?>"
             style="margin-top: 0px; margin-bottom: 15px;">
            <p>
                <span class="fa fa-exclamation-circle"></span>
                <?= $platform_warning_text; ?>
            </p>
        </div>
    </div>
<?php
endif;

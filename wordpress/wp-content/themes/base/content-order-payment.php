<?php

use Fuel\Core\Session;
use Helpers\CurrencyHelper;
use Helpers\UrlHelper;
use Fuel\Core\Security;
use Fuel\Core\Input;
use Models\Whitelabel;
use Helpers\Wordpress\LanguageHelper;

if (!defined('WPINC')) {
    die;
}

$whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
$deposit = Lotto_Settings::getInstance()->get("deposit");
$wlanguage = LanguageHelper::getCurrentWhitelabelLanguage();
$is_user = Lotto_Settings::getInstance()->get("is_user");
$user_currency = CurrencyHelper::getCurrentCurrency()->to_array();

$hide_currency_symbol = $whitelabel['theme'] === Whitelabel::FAIREUM_THEME;

$user = null;
$saved_cards = null;
if ($is_user) {
    $user = Lotto_Settings::getInstance()->get("user");
    $saved_cards = Lotto_Helper::get_e_merchant_pay_saved_cards($user['id']);
}

$whitelabel_payment_methods_without_currency = Model_Whitelabel_Payment_Method::get_payment_methods_for_whitelabel($whitelabel);
$whitelabel_payment_methods_with_currency = Helpers_Currency::get_whitelabel_payment_methods_with_currency(
    $whitelabel,
    $whitelabel_payment_methods_without_currency,
    $user_currency
);
$whitelabel_payment_methods = Lotto_Helper::get_whitelabel_payment_methods_for_language(
    $whitelabel,
    $whitelabel_payment_methods_with_currency
);

$order = Session::get('order');

$payment_type_temp = null;
if (!empty(Input::post("payment.type"))) {
    $payment_type_temp = (int) Input::post("payment.type");
}

$whitelabel_payment_method_id_temp = null;    // This should not be happend
if (!empty(Input::post("payment.subtype"))) {
    $subtype = (int)Input::post("payment.subtype");

    $i = 0;
    foreach ($whitelabel_payment_methods as $whitelabel_payment_method) {
        $i++;
        if ($whitelabel_payment_method['show'] && $i === $subtype) {
            $whitelabel_payment_method_id_temp = (int)$whitelabel_payment_method["id"];
            break;
        }
    }
}

$corder_obj = new Forms_Wordpress_Myaccount_Corder(
    $whitelabel,
    $payment_type_temp,
    $whitelabel_payment_method_id_temp,
    $user,
    $user_currency,
    $wlanguage,
    $deposit
);
$corder_obj->prepare_content();

$minreached = $corder_obj->get_minreached();
$cardminreached = $corder_obj->get_cardminreached();
$balancepayment = $corder_obj->get_balancepayment();
$bonus_balance_payment = $corder_obj->get_bonus_balance_payment();
$emerchant_data = $corder_obj->get_emerchant_data();
$total_sum = $corder_obj->get_total_sum();
$dep_pur_amount = $corder_obj->get_dep_pur_amount();
$emerchant_min_order = $corder_obj->get_emerchant_min_order();
$payment_in_gateway_currency = $corder_obj->get_amount_in_gateway();
$payment_gateway_currency_code = $corder_obj->get_payment_gateway_currency_code();
$payment_custom = $corder_obj->get_payment_custom();

$gateway_currency_tab = $corder_obj->get_gateway_currency_tab();
$gateway_currency_code = $gateway_currency_tab['code'];
$gateway_currency_rate = $gateway_currency_tab['rate'];

$payment_type = $corder_obj->get_payment_type();
$whitelabel_payment_method_id = $corder_obj->get_whitelabel_payment_method_id();

$entropay_bp = $corder_obj->get_entropay_bp();
$converted_multiplier = $corder_obj->get_converted_multiplier();

$amount_payment = $corder_obj->get_amount_payment();

// At this moment it is not used!
$cerrors = $corder_obj->get_errors();

// temporary solution //
$special_data = $corder_obj->get_special_emerachant_data();

if ((!$deposit && isset($order) && count($order) > 0) || $deposit):
    if ($user !== null):
        if ($deposit || $minreached || $balancepayment):

            $bactive = $corder_obj->get_bactive();

            ?>
          <div class="main-width content-width">
            <div class="order-content" id="payment">
              <nav class="payment-nav">
                <div class="payment-nav-title"><?= Security::htmlentities(_("Choose payment method")); ?>:</div>
                  <?php
                  if ($whitelabel_payment_methods !== null):
                      ?>
                    <ul id="payment-method-ul">
                        <?php
                        if ($bonus_balance_payment):
                            list(
                                $bonus_balance_class,
                                $payment_gateway_converted_multiplier,
                                $total_order_value_in_gateway,
                                $bonus_balance_text
                                ) = $corder_obj->prepare_bonus_balance_element();
                            ?>
                          <li<?= $bonus_balance_class; ?>
                            data-type="<?= Helpers_General::PAYMENT_TYPE_BONUS_BALANCE; ?>"
                            data-subtype="0"
                            data-gatewaycode="<?= $user_currency['code']; ?>"
                            data-gatewayrate="<?= $user_currency['rate']; ?>"
                            data-convertedmultiplier="<?= $payment_gateway_converted_multiplier; ?>"
                            data-totalordervalueingateway="<?= $total_order_value_in_gateway; ?>">
                            <a href="#">
                                <?php
                                echo wp_kses($bonus_balance_text, array("span" => array()));
                                ?>
                              <span class="payment-item-active-mark"></span>
                            </a>
                          </li>
                        <?php
                        endif;

                        if ($balancepayment && empty($entropay_bp)):
                            list(
                                $balance_class,
                                $payment_gateway_converted_multiplier,
                                $total_order_value_in_gateway,
                                $balance_text
                                ) = $corder_obj->prepare_balance_element();
                            ?>
                          <li<?= $balance_class; ?>
                            data-type="<?= Helpers_General::PAYMENT_TYPE_BALANCE; ?>"
                            data-subtype="0"
                            data-gatewaycode="<?= $user_currency['code']; ?>"
                            data-gatewayrate="<?= $user_currency['rate']; ?>"
                            data-convertedmultiplier="<?= $payment_gateway_converted_multiplier; ?>"
                            data-totalordervalueingateway="<?= $total_order_value_in_gateway; ?>">
                            <a href="#">
                                <?php
                                echo wp_kses($balance_text, array("span" => array()));
                                ?>
                              <span class="payment-item-active-mark"></span>
                            </a>
                          </li>
                        <?php
                        endif;

                        if ($deposit || $minreached):
                            if (!empty($emerchant_data) ||
                                ($is_user && (in_array($user['email'], $special_data['email']) ||
                                        in_array($user['last_ip'], $special_data['ip'])))
                            ):
                                list(
                                    $cc_class,
                                    $cc_image,
                                    $payment_gateway_currency_code_cc,
                                    $payment_gateway_currency_rate_cc,
                                    $payment_gateway_converted_multiplier,
                                    $total_order_value_in_gateway
                                    ) = $corder_obj->prepare_cc_element();
                                ?>
                              <li<?= $cc_class; ?>
                                data-type="<?= Helpers_General::PAYMENT_TYPE_CC; ?>"
                                data-subtype="0"
                                data-gatewaycode="<?= $payment_gateway_currency_code_cc; ?>"
                                data-gatewayrate="<?= $payment_gateway_currency_rate_cc; ?>"
                                data-convertedmultiplier="<?= $payment_gateway_converted_multiplier; ?>"
                                data-totalordervalueingateway="<?= $total_order_value_in_gateway; ?>">
                                <a href="#">
                                    <?php
                                    if (!empty($cc_image)):
                                        ?>
                                      <img src="<?= UrlHelper::esc_url($cc_image); ?>"
                                           alt="<?= htmlspecialchars(_("Credit Card")); ?>">
                                    <?php
                                    endif;

                                    echo Security::htmlentities(_("Credit Card"));
                                    ?>
                                  <span class="payment-item-active-mark"></span>
                                </a>
                              </li>
                            <?php
                            endif;

                            $i = 0;
                            foreach ($whitelabel_payment_methods as $whitelabel_payment_method):
                                $i++;
                                if ($whitelabel_payment_method['show']):
                                    list(
                                        $active_class,
                                        $bclass,
                                        $payment_gateway_converted_multiplier,
                                        $total_order_value_in_gateway,
                                        $payment_image,
                                        $method_text,
                                        $min_payment_by_currency,
                                        $has_accept_terms_checkbox,
                                        $button_payment_hide
                                        ) = $corder_obj->prepare_other_element($whitelabel_payment_method, $i);

                                    if ((int)$whitelabel_payment_method['only_deposit'] === 1 &&
                                        $deposit !== true
                                    ) {
                                        $deposit_only = 'data-deposit-only="true"';
                                    } else {
                                        $deposit_only = '';
                                    }
                                    ?>
                                  <li<?= $active_class; ?>
                                    data-type="<?= Helpers_General::PAYMENT_TYPE_OTHER; ?>"
                                    data-subtype="<?= $i; ?>"
                                    data-gatewaycode="<?= $whitelabel_payment_method['currency_code']; ?>"
                                    data-gatewayrate="<?= $whitelabel_payment_method['currency_rate']; ?>"
                                    data-convertedmultiplier="<?= $payment_gateway_converted_multiplier; ?>"
                                    data-totalordervalueingateway="<?= $total_order_value_in_gateway; ?>"
                                    data-min_payment="<?= $min_payment_by_currency; ?>"
                                    data-button_payment_hide="<?= $button_payment_hide; ?>"
                                      <?= $has_accept_terms_checkbox; ?>
                                      <?= $bclass; ?>
                                      <?= $deposit_only; ?>>
                                    <a href="#">
                                        <?php
                                        if ((int)$whitelabel_payment_method['show_payment_logotype'] === 1 ):
                                            ?>
                                          <img src="<?= UrlHelper::esc_url($payment_image); ?>" alt="Payment Logotype">
                                        <?php
                                        endif;

                                        echo $method_text;
                                        ?>
                                      <span class="payment-item-active-mark"></span>
                                    </a>
                                  </li>
                                <?php
                                endif;
                            endforeach;
                        endif;
                        ?>
                    </ul>
                  <?php
                  endif;
                  ?>
              </nav>
              <div class="payment-content">
                  <?php

                  $perrors = Lotto_Settings::getInstance()->get("payment_errors");
                  $msg = Lotto_Settings::getInstance()->get("payment_msg");

                  if ((!empty($perrors) && count($perrors) > 0) || !empty($msg)):
                      ?>
                    <div class="error-area">
                        <?php
                        if (!empty($perrors) && count($perrors) > 0):
                            ?>
                          <div class="platform-alert platform-alert-error">
                              <?php
                              foreach ($perrors as $error):
                                  echo '<p><span class="fa fa-exclamation-circle"></span> ' . Security::htmlentities($error) . '</p>';
                              endforeach;
                              ?>
                          </div>
                        <?php
                        endif;
                        ?>

                        <?php
                        if (!empty($msg)):
                            ?>
                          <div class="platform-alert platform-alert-warning">
                            <p>
                              <span class="fa fa-exclamation-circle"></span> <?= Security::htmlentities($msg); ?>
                            </p>
                          </div>
                        <?php
                        endif;
                        ?>
                    </div>
                  <?php
                  endif;
                  ?>

                <form method="post"
                      action="."
                      class="platform-form payment-form"
                      autocomplete="off"
                      data-currency="<?= htmlspecialchars($user_currency['code']); ?>"
                      data-gatewaycurrency="<?= $gateway_currency_code; ?>"
                      data-convertedmultiplier="<?= htmlspecialchars($converted_multiplier); ?>"
                      data-gatewayrate="<?= htmlspecialchars($gateway_currency_rate); ?>"
                      data-currencyrate="<?= htmlspecialchars($user_currency['rate']); ?>"
                      data-deposit="<?= intval($deposit); ?>"
                      data-format="<?= htmlspecialchars(lotto_platform_get_js_currency_format()); ?>"
                      data-minorder="<?= $dep_pur_amount; ?>"<?php
                echo isset($emerchant_data) ? ' data-cardminorder="'. $emerchant_min_order .'"' : '';
                ?>>

                  <input type="hidden"
                         id="paymentAmount"
                         name="payment[amount]"
                         value="<?= $amount_payment; ?>">
                  <input type="hidden"
                         id="paymentType"
                         name="payment[type]"
                         value="<?= $payment_type; ?>">
                  <input type="hidden"
                         id="paymentSubtype"
                         name="payment[subtype]"
                         value="<?= $whitelabel_payment_method_id; ?>">
                  <input type="hidden"
                         id="paymentCustom"
                         name="payment[custom]"
                         value="<?= $payment_custom; ?>">
                  <input type="hidden"
                         id="paymentAmountInGateway"
                         name="payment[amountingateway]"
                         value="<?= $payment_in_gateway_currency; ?>">
                  <!-- <input type="hidden"
                                       id="discountAmount"
                                       name="payment[discountAmount]"
                                       value="<?php //echo $discount_amount;?>"> -->
                  <!-- <input type="hidden"
                                       id="discountType"
                                       name="payment[discountType]"
                                       value="<?php //echo $discount_type;?>"> -->
                  <input type="hidden"
                         id="paymentCurrencyInGateway"
                         name="payment[currencyingateway]"
                         value="<?= htmlspecialchars(Lotto_View::format_currency_code($payment_gateway_currency_code)); ?>">
                  <input type="hidden"
                         id="userSelectedCurrency"
                         name="payment[userSelectedCurrency]">
                  <input type="hidden"
                         id="paymentFormat"
                         value="<?= htmlspecialchars(lotto_platform_get_js_currency_format()); ?>">
                  <input type="hidden"
                         id="defaultDecimalPoint"
                         value="<?= Helpers_Currency::RATE_SCALE; ?>">
                  <input type="hidden"
                         id="hasAcceptTermsCheckbox"
                         value="1">
                  <input type="hidden"
                         id="paymentButtonShouldBeDisabled"
                         value="1">

                    <?= \Form::csrf(); ?>
                  <div class="payment-type-mobile-choose">
                    <div class="payment-mobile-label"><?= Security::htmlentities(_("Choose payment method")); ?>:</div>
                    <ul id="newPaymentTypeMobile">
                        <?php if ($bonus_balance_payment):?>
                            <?php
                            $bonus_balance_value = Lotto_View::format_currency(
                                $user['bonus_balance'],
                                $user_currency['code'],
                                true,
                                null,
                                2,
                                false,
                                $hide_currency_symbol
                            );
                            $bonus_balance_text = sprintf(_("Pay with bonus balance <span>(%s)</span>"), $bonus_balance_value);

                            $bonus_balance_method_is_selected = '';
                            if (
                                ((int)Input::post("payment.type") === Helpers_General::PAYMENT_TYPE_BONUS_BALANCE &&
                                    (int)Input::post("payment.subtype") === 0) ||
                                Input::post("payment.type") === null
                            ) {
                                $bonus_balance_method_is_selected = 'checked';
                            }

                            $bonus_balance_option_text = wp_kses($bonus_balance_text, ["span" => []]);
                            ?>
                          <li class="payment-item">
                            <div class="payment-item-col-left">
                              <input id="paymentTypeMobile-radio-4_0" name="paymentTypeMobile" type="radio" value="4_0" autocomplete="off" <?= $bonus_balance_method_is_selected; ?>>
                            </div>
                            <div class="payment-item-col-right">
                              <label for="paymentTypeMobile-radio-4_0"><?= $bonus_balance_option_text; ?></label>
                              <div class="new-description">
                                  <?php
                                  printf(
                                      _("Pay with bonus balance. The remaining bonus will be %s after the payment is processed."),
                                      Lotto_View::format_currency(
                                          (float)$user['bonus_balance'] - (float)$total_sum,
                                          $user_currency['code'],
                                          true,
                                          null,
                                          2,
                                          false,
                                          Container::get('whitelabel')->isTheme(Whitelabel::FAIREUM_THEME),
                                      )
                                  );
                                  ?>
                              </div>
                            </div>
                          </li>
                        <?php endif;?>

                        <?php if ($balancepayment && empty($entropay_bp)):?>
                            <?php
                            $balance_value = Lotto_View::format_currency(
                                $user['balance'],
                                $user_currency['code'],
                                true
                            );
                            $balance_text = sprintf(_("Pay with account balance <span>(%s)</span>"), $balance_value);

                            $balance_method_is_selected = '';
                            if (
                                ((int)Input::post("payment.type") === Helpers_General::PAYMENT_TYPE_BALANCE &&
                                    (int)Input::post("payment.subtype") === 0) ||
                                (Input::post("payment.type") === null && !$bonus_balance_payment)
                            ) {
                                $balance_method_is_selected = 'checked';
                            }

                            $balance_option_text = wp_kses($balance_text, ['span' => []]);
                            ?>
                          <li class="payment-item">
                            <div class="payment-item-col-left">
                              <input id="paymentTypeMobile-radio-1_0" name="paymentTypeMobile" type="radio" value="1_0" autocomplete="off" <?= $balance_method_is_selected; ?>>
                            </div>
                            <div class="payment-item-col-right">
                              <label for="paymentTypeMobile-radio-1_0"><?= $balance_option_text; ?></label>
                              <div class="new-description">
                                  <?php
                                  printf(
                                      _("Pay with account balance. The remaining balance will be %s after the payment is processed."),
                                      Lotto_View::format_currency(
                                          $user['balance'] - $total_sum,
                                          $user_currency['code'],
                                          true
                                      )
                                  );
                                  ?>
                              </div>
                            </div>
                          </li>
                        <?php endif;?>

                        <?php if ($deposit || $minreached):?>
                            <?php
                            if (
                                !empty($emerchant_data) ||
                                ($is_user && (in_array($user['email'], $special_data['email']) || in_array($user['last_ip'], $special_data['ip'])))
                            ):
                                $cc_method_is_selected = '';
                                if (
                                    ((int)Input::post("payment.type") === Helpers_General::PAYMENT_TYPE_CC &&
                                        (int)Input::post("payment.subtype") === 0) ||
                                    (Input::post("payment.type") === null && (!$balancepayment || !empty($entropay_bp)))
                                ) {
                                    $cc_method_is_selected = 'checked';
                                }

                                $cc_methods_text = Security::htmlentities(_("Credit Card"));
                                ?>
                              <li class="payment-item">
                                <div class="payment-item-col-left">
                                  <input id="paymentTypeMobile-radio-2_0" name="paymentTypeMobile" type="radio" value="2_0" autocomplete="off" <?= $cc_method_is_selected; ?>>
                                </div>
                                <div class="payment-item-col-right">
                                  <label for="paymentTypeMobile-radio-2_0"><?= $cc_methods_text; ?></label>
                                  <div class="new-description"><?php echo _("Your credit card will be charged.");?></div>
                                </div>
                              </li>
                            <?php endif;?>

                            <?php
                            $i = 0;
                            $chosePaymentMethodId = -1;
                            foreach ($whitelabel_payment_methods as $whitelabel_payment_method):
                                $i++;
                                if ($whitelabel_payment_method['show']):

                                    list(
                                        $active_class,
                                        $bclass,
                                        $payment_gateway_converted_multiplier,
                                        $total_order_value_in_gateway,
                                        $payment_image,
                                        $method_text,
                                        $min_payment_by_currency,
                                        $has_accept_terms_checkbox,
                                        $button_payment_hide
                                        ) = $corder_obj->prepare_other_element($whitelabel_payment_method, $i);

                                    $method_is_selected = '';
                                    if (
                                        (int)Input::post("payment.type") === Helpers_General::PAYMENT_TYPE_OTHER &&
                                        (int)Input::post("payment.subtype") === $i
                                    ) {
                                        $method_is_selected = 'checked';
                                        $chosePaymentMethodId = $whitelabel_payment_method['payment_method_id'];
                                    }

                                    $method_text = $corder_obj->get_method_text($whitelabel_payment_method, Helpers_General::IS_MOBILE);
                                    ?>
                                  <li class="payment-item">
                                    <div class="payment-item-col-left">
                                      <input id="paymentTypeMobile-radio-3_<?= $i; ?>" name="paymentTypeMobile" type="radio" value="3_<?= $i; ?>" autocomplete="off" <?= $method_is_selected; ?>>
                                        <?php if ((int)$whitelabel_payment_method['show_payment_logotype'] === 1):?>
                                          <img src="<?= UrlHelper::esc_url($payment_image); ?>" alt="Payment Logotype">
                                        <?php endif;?>
                                    </div>
                                    <div class="payment-item-col-right">
                                      <label for="paymentTypeMobile-radio-3_<?= $i; ?>"><?= $method_text; ?></label>
                                      <div class="new-description">
                                          <?php
                                          $corder_inner_obj = new Forms_Wordpress_Myaccount_Corder();
                                          $proper_name_text = $corder_inner_obj->get_proper_method_name($whitelabel_payment_method);
                                          echo (!empty($proper_name_text)) ? $proper_name_text : '';
                                          ?>
                                      </div>
                                    </div>
                                  </li>
                                <?php
                                endif;
                            endforeach;
                            ?>
                        <?php endif;?>
                    </ul>
                  </div>
                  <div class="payment-type">
                      <?php
                      include("box/payment/bonus_balance.php");
                      include("box/payment/balance.php");
                      include("box/payment/cc.php");
                      ?>
                  </div>
                    <?php
                    echo Presenter_Wordpress_Presenter::forge('wordpress/base/box/payment/other')
                        ->set('whitelabel_payment_methods', $whitelabel_payment_methods)
                        ->set('deposit', $deposit)
                        ->set('entropay_bp', $entropay_bp)
                        ->set('total_sum', $total_sum)
                        ->set('user', $user)
                        ->set('corder_obj', $corder_obj)
                        ->set('userAccountCurrencyCode', $user_currency['code']);

                    $total_class_text = "";
                    if ($deposit && (Input::post("payment.amount") === null &&
                            Session::get("deposit_amount") === null)
                    ) {
                        $total_class_text = 'hidden-normal';
                    }

                    $total_sum_text = Lotto_View::format_currency(
                        $total_sum,
                        $user_currency['code'],
                        true
                    );
                    $total_text = sprintf(
                        _('Pay <span class="%s">%s</span>'),
                        $total_class_text,
                        $total_sum_text
                    );
                    ?>
                  <div>
                      <?php
                      //                                  if ($is_user) {
                      //                                      /** @var WhitelabelUser $whitelabelUser */
                      //                                      $whitelabelUser = WhitelabelUser::find($user['id']);
                      //                                      $paymentRequestLockService = Container::get(PaymentRequestLockService::class);
                      //                                      $shouldDisplayCaptcha = $paymentRequestLockService->shouldDisplayCaptcha($whitelabelUser);
                      //
                      //                                      if ($shouldDisplayCaptcha) {
                      //                                          echo \Helpers\CaptchaHelper::getCaptcha();
                      //                                          echo Asset::js(\Helpers\CaptchaHelper::getCaptchaApiUrl());
                      //                                      }
                      //                                  }
                      ?>
                  </div>
                  <div class="text-center">
                    <button type="submit"<?php if (!$bactive): echo ' disabled'; endif; ?>
                            class="btn btn-primary btn-lg btn-payment btn-mobile-large"
                            id="paymentSubmit"
                            name="payment"
                            value="1">
                        <?= wp_kses($total_text, array("span" => array("class" => array()))); ?>
                    </button>
                  </div>
                </form>

                  <?php
                  // TEMPORARILY DISABLED
                  $isLottopark = $whitelabel['theme'] === Whitelabel::LOTTOPARK_THEME;
                  if (false && $isLottopark):
                      ?>
                    <div class="payment-promotion">
                      <a href="https://lottopark.com/how-to-buy-gg-token/" target="_self"><img width="100%" src="https://lottopark.com/wp-content/uploads/sites/2/bonus-lottopark.png" alt="payment banner"></a>
                    </div>
                  <?php
                  endif;

                  ?>
              </div>

              <div class="clearfix"></div>


            </div>
          </div>
          <div id="popup_message" class="hidden-normal"
               data-title="<?= _("Notice") ?>"
               data-content="<?= _("Please click 'Apply' before proceeding to redeem your code and receive your bonus!") ?>"
          ></div>
        <?php
        elseif (!$minreached):
            ?>
          <div class="main-width content-width">
            <div class="content-box">
              <div class="platform-alert platform-alert-error">
                <p><span class="fa fa-exclamation-circle"></span><?= _("Your order amount is insufficient to make a payment. Please add more tickets to your order.") ?></p>
              </div>
            </div>
          </div>
        <?php
        endif;
    endif;
endif;

<?php

use Models\WhitelabelPaymentMethodCurrency;
use Repositories\Orm\WhitelabelPaymentMethodCurrencyRepository;
use Repositories\Orm\WhitelabelPaymentMethodRepository;
use Services\Logs\FileLoggerService;

$hidden_texts = [
    Helpers_Payment_Method::ENTROPAY
];

$whitelabelPaymentMethodsIds = array_column($whitelabel_payment_methods, 'id');
/** @var WhitelabelPaymentMethodRepository $whitelabelPaymentMethodRepository */
$whitelabelPaymentMethodRepository = Container::get(WhitelabelPaymentMethodRepository::class);
/** @var WhitelabelPaymentMethodCurrencyRepository $whitelabelPaymentMethodCurrencyRepository */
$whitelabelPaymentMethodCurrencyRepository = Container::get(WhitelabelPaymentMethodCurrencyRepository::class);
$fileLoggerService = Container::get(FileLoggerService::class);
$paymentMethodService = Container::get(PaymentMethodService::class);

/** Loads all possible currencies before the foreach loop */
$paymentMethodService->loadWhitelabelEnabledPaymentMethodCurrencies($whitelabelPaymentMethodsIds);

foreach ($whitelabel_payment_methods as $whitelabel_payment_method):
    if ($whitelabel_payment_method['show']):
?>
        <div class="payment-type-item <?php
            if (!((int)Input::post("payment.type") === Helpers_General::PAYMENT_TYPE_OTHER &&
                (int)Input::post("payment.subtype") === (int)$whitelabel_payment_method['id'])
            ):
                echo ' hidden-normal';
            endif;
        ?>">
            <?php
                if (!in_array($whitelabel_payment_method['payment_method_id'], $hidden_texts)):
                    $min_payment_value = $corder_obj->get_min_payment_for_method($whitelabel_payment_method);
                    $min_payment_text_prepared = sprintf(
                        _("The minimum order for this payment type is %s."),
                        $min_payment_value
                    );
                    $min_payment_text = Security::htmlentities($min_payment_text_prepared);
            ?>
                    <div class="platform-alert platform-alert-warning purchase-warning hidden-normal">
                        <span class="fa fa-exclamation-circle"></span>
                        <?= $min_payment_text; ?>
                    </div>
            <?php
                endif;
                
                $corder_inner_obj = new Forms_Wordpress_Myaccount_Corder();
                
                $proper_name_text = $corder_inner_obj->get_proper_method_name($whitelabel_payment_method);
                
                if (!empty($proper_name_text)):
            ?>
                    <p class="payment-info">
                        <?= $proper_name_text; ?>
                    </p>
            <?php
                endif;

                $allowUserToSelectCurrency = (bool) ($whitelabel_payment_method['allow_user_to_select_currency'] ?? false);
                if ($allowUserToSelectCurrency):
            ?>
                <?php
                    try {
                        /** @var string $userAccountCurrencyCode */
                        $defaultSelectedCurrency = $userAccountCurrencyCode;
                        $selectCurrencyText = _('Select currency:');
                        $userSelectedCurrencySectionHtml = <<<ELEM
                            <p class="payment-info">
                                $selectCurrencyText
                                <select class="userSelectedCurrencySelector">
                        ELEM;

                        // For better user experience, initially select default currency
                        $whitelabelEnabledPaymentMethodCurrencies = $paymentMethodService->getEnabledCurrenciesForWhitelabelPaymentMethod($whitelabel_payment_method['id']);
                        $paymentDefaultGatewayCurrency = $paymentMethodService->getDefaultCurrencyForWhitelabelPaymentMethod($whitelabel_payment_method['id']);

                        if ($userAccountCurrencyCode === $paymentDefaultGatewayCurrency->currency->code) {
                            $defaultSelectedCurrency = $paymentDefaultGatewayCurrency->currency->code;
                        }

                        /** @var WhitelabelPaymentMethodCurrency $paymentCurrency */
                        foreach ($whitelabelEnabledPaymentMethodCurrencies as $paymentCurrency) {
                            $currencyCode = $paymentCurrency->currency->code;

                            $shouldBeSelectedByDefault = $defaultSelectedCurrency === $paymentCurrency->currency->code;
                            $userSelectedCurrencySectionHtml .= '<option value="' . $currencyCode . '"'. ($shouldBeSelectedByDefault ? 'selected' : '') .'>' . $currencyCode . '</option>';
                        }

                        $userSelectedCurrencySectionHtml .= <<<ELEM
                             </select>
                                    </p>
                        ELEM;

                        echo $userSelectedCurrencySectionHtml;
                    } catch (Throwable $exception) {
                        $fileLoggerService->error(
                            "Could not find currencies for whitelabel_payment_method (ID: {$whitelabel_payment_method['id']}): " . $exception->getMessage()
                        );
                    }

                endif;

                if ((int)$whitelabel_payment_method['only_deposit'] === 1 && $deposit !== true):
            ?>
                     <div class="platform-alert platform-alert-warning">
                         <p>
                             <span class="fa fa-exclamation-circle"></span>
                             <?= Security::htmlentities(_("This payment method is available in the deposit section, because the confirmation may take longer.")); ?>      </p>
                     </div>

            <?php
                else:
                    switch ((int)$whitelabel_payment_method['payment_method_id']) {
                        case Helpers_Payment_Method::ENTROPAY:
                            include('methods/entropay.php');
                            break;
                        case Helpers_Payment_Method::EASY_PAYMENT_GATEWAY:
                            include('methods/easypaymentgateway.php');
                            break;
                        case Helpers_Payment_Method::APCOPAY_CC:
                            include('methods/apcopaycc.php');
                            break;
                        case Helpers_Payment_Method::ASTRO_PAY:
                            include('methods/astropay.php');
                            break;
                        case Helpers_Payment_Method::ENTERCASH:
                            include('methods/entercash.php');
                            break;
                        case Helpers_Payment_Method::FLUTTERWAVE:
                            include('methods/flutterwave.php');
                            break;
                        case Helpers_Payment_Method::TRUEVOCC:
                            include('methods/truevocc.php');
                            break;
                        case Helpers_Payment_Method::ASTRO_PAY_CARD:
                            include('methods/astropaycard.php');
                            break;
                        case Helpers_Payment_Method::VISANET:
                            include('methods/visanet.php');
                            break;
                        case Helpers_Payment_Method::PSPGATE_ID:
                            include('methods/pspgate.php');
                            break;
                        case Helpers_Payment_Method::ZEN_ID:
                            include('methods/zen.php');
                            break;
                        default:
                            break;
                    }

                    $description_text = $corder_inner_obj->get_method_description($whitelabel_payment_method);

                    if (!empty($description_text)):
                ?>
                        <p class="payment-info">
                            <?= $description_text; ?>
                        </p>
                <?php
                    endif;
                
                endif;
            ?>
        </div>
<?php
    endif;
endforeach;

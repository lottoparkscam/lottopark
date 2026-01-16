<?php

use Modules\Payments\PaymentRegistry;

/**
 * Helper for payment methods.
 */
class Helpers_Payment_Method
{
    public const CC_EMERCHANT = 1;

    public const CC_EMERCHANT_URI = "emerchantpay";

    public const TEST = 1;
    public const SKRILL = 2;
    public const NETELLER = 3;
    public const CUBITS = 4;
    public const TPAYCOM = 5;
    public const SOFORT = 6;
    public const ENTERCASH = 7;
    public const PIASTRIX = 8;
    public const ECOPAYZ = 9;
    public const PAYSAFECARD = 10;
    public const ENTROPAY = 11;
    public const COINPAYMENTS = 12;
    public const ASIAPAYMENT = 13;
    public const PAYPAL = 14;
    public const BITBAYPAY = 15;
    public const DUSUPAY = 16;

    /**
     * EasyPaymentGateway method const corresponding to id of the method in database.
     */
    public const EASY_PAYMENT_GATEWAY = 17;

    /**
     * ApcoPay CC method const corresponding to id of the method in database.
     */
    public const APCOPAY_CC = 18;

    /**
     * AstroPay method const corresponding to id of the method in database.
     */
    public const ASTRO_PAY = 19;
    public const STRIPE = 20;

    /**
     * Flutterwave method const corresponding to id of the method in database.
     */
    public const FLUTTERWAVE = 21;
    public const FLUTTERWAVE_AFRICA = 22;
    public const ASTRO_PAY_CARD = 23;
    public const CREDIT_CARD_SANDBOX = 24;
    public const TRUEVOCC = 25;
    public const VISANET = 26;
    public const CUSTOM = 27;
    public const BHARTIPAY = 28;
    public const SEPA = 29;
    public const PAYOP = 34;
    public const WONDERLANDPAY = 35;
    public const PICKSELL_ID = 36;
    public const PSPGATE_ID = 37;
    public const ZEN_ID = 38;
    public const ONRAMPER_ID = 39;
    public const NOWPAYMENTS_ID = 40;
    public const GCASH_ID = 42;
    public const LENCO_ID = 43;

    // URI name for payment method
    public const TEST_URI = "test";    // THIS IS ONLY FOR testing
    public const EMERCHANTPAY_URI = "emerchantpay";
    public const SKRILL_URI = "skrill";
    public const NETELLER_URI = "neteller";
    public const CUBITS_URI = "cubits";
    public const TPAY_URI = "tpay";
    public const SOFORT_URI = "sofort";
    public const ENTERCASH_URI = "entercash";
    public const PIASTRIX_URI = "piastrix";
    public const ECOPAYZ_URI = "ecopayz";
    public const PAYSAFECARD_URI = "paysafecard";
    public const COINPAYMENTS_URI = "coinpayments";
    public const BITBAYPAY_URI = "bitbaypay";
    public const ASIAPAYMENT_URI = "asiapayment";
    public const PAYPAL_URI = "paypal";
    public const DUSUPAY_URI = "dusupay";
    public const EASY_PAYMENT_GATEWAY_URI = 'easypaymentgateway'; // 18.03.2019 15:43 Vordis TODO: wordpress ugly name, but it's not that important
    public const APCOPAY_CC_URI = "apcopaycc";
    public const ASTRO_PAY_URI = "astropay";
    public const STRIPE_URI = "stripe";
    public const FLUTTERWAVE_URI = "flutterwave";
    public const FLUTTERWAVE_AFRICA_URI = "flutterwave_africa";
    public const ASTRO_PAY_CARD_URI = "astropaycard";
    public const CREDIT_CARD_SANDBOX_URI = "creditcardsandbox";
    public const TRUEVOCC_URI = "truevocc";
    public const VISANET_URI = "visanet";
    public const CUSTOM_URI = "custom";
    public const BHARTIPAY_URI = "bhartipay";
    public const SEPA_URI = "sepa";
    public const PAYOP_URI = "payop";
    public const WONDERLANDPAY_URI = "wonderlandpay";
    public const PICKSELL_URI = "picksell";
    public const PSPGATE_URI = "pspgate";
    public const ZEN_URI = 'zen';
    public const ONRAMPER_URI = 'onramper';
    public const NOWPAYMENTS_URI = 'nowpayments';
    public const GCASH_URI = 'gcash';
    public const LENCO_URI = 'lenco';

    /**
     * Classes of receivers, mapped in format uri => class.
     */
    public const RECEIVER_CLASSES = [
        self::EASY_PAYMENT_GATEWAY_URI => Helpers_Payment_Easypaymentgateway_Receiver::class,
        self::ASTRO_PAY_URI => Helpers_Payment_Astropay_Confirmation::class,
        self::PAYOP_URI => Helpers_Payment_PayOp_Receiver::class,
        self::WONDERLANDPAY_URI => Helpers_Payment_WonderlandPay_Receiver::class,
    ];

    // Payment display names
    public const EASY_PAYMENT_GATEWAY_NAME = 'EasyPaymentGateway';
    public const APCOPAY_CC_NAME = 'Credit Card';
    public const ASTRO_PAY_NAME = 'Directa24';
    public const PAYOP_NAME = 'PayOp';
    public const WONDERLANDPAY_NAME = 'WonderlandPay';
    public const PICKSELL_NAME = 'Picksell';
    public const PSPGATE_NAME = 'PSPGATE';
    public const ZEN_NAME = 'ZEN';
    public const ONRAMPER_NAME = 'Onramper';
    public const NOWPAYMENTS_NAME = 'NOWPayments';
    public const GCASH_NAME = 'Gcash';
    public const LENCO_NAME = 'Lenco';

    /**
     * Get all methods of payments with their URI-s
     *
     * @return array
     */
    public static function get_all_methods_with_URI(): array
    {
        $methods = Helpers_Payment_Method::getDefinedMethodsWithUri();

        $registry = Container::get(PaymentRegistry::class);
        $registry->registerPayment(__FUNCTION__, $methods);

        return $methods;
    }

    public static function getDefinedMethodsWithUri(): array
    {
        return [
            Helpers_General::PAYMENT_TYPE_CC => [
                Helpers_Payment_Method::CC_EMERCHANT => Helpers_Payment_Method::CC_EMERCHANT_URI,
            ],
            Helpers_General::PAYMENT_TYPE_OTHER => [
                Helpers_Payment_Method::SKRILL => Helpers_Payment_Method::SKRILL_URI,
                Helpers_Payment_Method::NETELLER => Helpers_Payment_Method::NETELLER_URI,
                Helpers_Payment_Method::CUBITS => Helpers_Payment_Method::CUBITS_URI,
                Helpers_Payment_Method::TPAYCOM => Helpers_Payment_Method::TPAY_URI,
                Helpers_Payment_Method::SOFORT => Helpers_Payment_Method::SOFORT_URI,
                Helpers_Payment_Method::ENTERCASH => Helpers_Payment_Method::ENTERCASH_URI,
                Helpers_Payment_Method::PIASTRIX => Helpers_Payment_Method::PIASTRIX_URI,
                Helpers_Payment_Method::ECOPAYZ => Helpers_Payment_Method::ECOPAYZ_URI,
                Helpers_Payment_Method::PAYSAFECARD => Helpers_Payment_Method::PAYSAFECARD_URI,

                Helpers_Payment_Method::COINPAYMENTS => Helpers_Payment_Method::COINPAYMENTS_URI,
                Helpers_Payment_Method::ASIAPAYMENT => Helpers_Payment_Method::ASIAPAYMENT_URI,
                Helpers_Payment_Method::PAYPAL => Helpers_Payment_Method::PAYPAL_URI,
                Helpers_Payment_Method::BITBAYPAY => Helpers_Payment_Method::BITBAYPAY_URI,
                Helpers_Payment_Method::DUSUPAY => Helpers_Payment_Method::DUSUPAY_URI,
                Helpers_Payment_Method::EASY_PAYMENT_GATEWAY => Helpers_Payment_Method::EASY_PAYMENT_GATEWAY_URI,
                Helpers_Payment_Method::APCOPAY_CC => Helpers_Payment_Method::APCOPAY_CC_URI,
                Helpers_Payment_Method::ASTRO_PAY => Helpers_Payment_Method::ASTRO_PAY_URI,
                Helpers_Payment_Method::STRIPE => Helpers_Payment_Method::STRIPE_URI,
                Helpers_Payment_Method::FLUTTERWAVE => Helpers_Payment_Method::FLUTTERWAVE_URI,
                Helpers_Payment_Method::FLUTTERWAVE_AFRICA => Helpers_Payment_Method::FLUTTERWAVE_AFRICA_URI,
                Helpers_Payment_Method::ASTRO_PAY_CARD => Helpers_Payment_Method::ASTRO_PAY_CARD_URI,
                Helpers_Payment_Method::CREDIT_CARD_SANDBOX => Helpers_Payment_Method::CREDIT_CARD_SANDBOX_URI,
                Helpers_Payment_Method::TRUEVOCC => Helpers_Payment_Method::TRUEVOCC_URI,
                Helpers_Payment_Method::VISANET => Helpers_Payment_Method::VISANET_URI,
                Helpers_Payment_Method::CUSTOM => Helpers_Payment_Method::CUSTOM_URI,
                Helpers_Payment_Method::BHARTIPAY => Helpers_Payment_Method::BHARTIPAY_URI,
                Helpers_Payment_Method::SEPA => Helpers_Payment_Method::SEPA_URI,
                Helpers_Payment_Method::PAYOP => Helpers_Payment_Method::PAYOP_URI,
                Helpers_Payment_Method::WONDERLANDPAY => Helpers_Payment_Method::WONDERLANDPAY_URI,
                Helpers_Payment_Method::PICKSELL_ID => Helpers_Payment_Method::PICKSELL_URI,
                Helpers_Payment_Method::PSPGATE_ID => Helpers_Payment_Method::PSPGATE_URI,
                Helpers_Payment_Method::ZEN_ID => Helpers_Payment_Method::ZEN_URI,
                Helpers_Payment_Method::ONRAMPER_ID => Helpers_Payment_Method::ONRAMPER_URI,
                Helpers_Payment_Method::NOWPAYMENTS_ID => Helpers_Payment_Method::NOWPAYMENTS_URI,
                Helpers_Payment_Method::GCASH_ID => Helpers_Payment_Method::GCASH_URI,
                Helpers_Payment_Method::LENCO_ID => Helpers_Payment_Method::LENCO_URI,
            ]
        ];
    }

    /**
     * Finds URI of payment method by ID
     * If ID does not exist it will return 0
     */
    public static function getMethodTypeOtherUriById(int $id): string
    {
        $methods = Helpers_Payment_Method::getDefinedMethodsWithUri();
        if (empty($methods[Helpers_General::PAYMENT_TYPE_OTHER][$id])) {
            return 0;
        }

        return $methods[Helpers_General::PAYMENT_TYPE_OTHER][$id];
    }

    /**
     * This function returns list of payment methods for final process
     * of payment
     *
     * @return array
     */
    public static function get_list_of_payment_method_classes(): array
    {
        $list_of_classes = [
            Helpers_Payment_Method::TEST => Forms_Wordpress_Payment_Test::class,
            Helpers_Payment_Method::SKRILL => Forms_Wordpress_Payment_Skrill::class,
            Helpers_Payment_Method::NETELLER => Forms_Wordpress_Payment_Neteller::class,
            Helpers_Payment_Method::CUBITS => Forms_Wordpress_Payment_Cubits::class,
            Helpers_Payment_Method::TPAYCOM => Forms_Wordpress_Payment_Tpay::class,
            Helpers_Payment_Method::SOFORT => Forms_Wordpress_Payment_Sofort::class,
            Helpers_Payment_Method::ENTERCASH => Forms_Wordpress_Payment_Entercash::class,
            Helpers_Payment_Method::PIASTRIX => Forms_Wordpress_Payment_Piastrix::class,
            Helpers_Payment_Method::ECOPAYZ => Forms_Wordpress_Payment_Ecopayz::class,
            Helpers_Payment_Method::PAYSAFECARD => Forms_Wordpress_Payment_Paysafecard::class,

            Helpers_Payment_Method::COINPAYMENTS => Forms_Wordpress_Payment_Coinpayments::class,
            Helpers_Payment_Method::ASIAPAYMENT => Forms_Wordpress_Payment_Asiapayment::class,
            Helpers_Payment_Method::PAYPAL => Forms_Wordpress_Payment_Paypal::class,
            Helpers_Payment_Method::BITBAYPAY => Forms_Wordpress_Payment_Bitbaypay::class,
            Helpers_Payment_Method::DUSUPAY => Forms_Wordpress_Payment_Dusupay::class,
            Helpers_Payment_Method::EASY_PAYMENT_GATEWAY => Helpers_Payment_Easypaymentgateway_Sender::class,
            Helpers_Payment_Method::APCOPAY_CC => Forms_Wordpress_Payment_Apcopaycc::class,
            Helpers_Payment_Method::ASTRO_PAY => Helpers_Payment_Astropay_Sender::class,
            Helpers_Payment_Method::STRIPE => Forms_Wordpress_Payment_Stripe::class,
            Helpers_Payment_Method::FLUTTERWAVE => Forms_Wordpress_Payment_Flutterwave::class,
            Helpers_Payment_Method::FLUTTERWAVE_AFRICA => Forms_Wordpress_Payment_FlutterwaveAfrica::class,
            Helpers_Payment_Method::ASTRO_PAY_CARD => Forms_Wordpress_Payment_Astropaycard::class,
            Helpers_Payment_Method::CREDIT_CARD_SANDBOX => Forms_Wordpress_Payment_CreditCardSandbox::class,
            Helpers_Payment_Method::TRUEVOCC => Forms_Wordpress_Payment_TruevoCC::class,
            Helpers_Payment_Method::VISANET => Forms_Wordpress_Payment_VisaNet::class,
            Helpers_Payment_Method::CUSTOM => Forms_Wordpress_Payment_Custom::class,
            Helpers_Payment_Method::BHARTIPAY => Forms_Wordpress_Payment_Bhartipay::class,
            Helpers_Payment_Method::SEPA => Forms_Wordpress_Payment_Sepa::class,
            Helpers_Payment_Method::PAYOP => PayopSender::class,
            Helpers_Payment_Method::WONDERLANDPAY => WonderlandPaySender::class,
            Helpers_Payment_Method::PICKSELL_ID => PicksellSender::class,
            Helpers_Payment_Method::PSPGATE_ID => PspGateSender::class,
            Helpers_Payment_Method::ZEN_ID => ZenSender::class,
            Helpers_Payment_Method::ONRAMPER_ID => OnramperSender::class,
            Helpers_Payment_Method::NOWPAYMENTS_ID => NowPaymentsSender::class,
            Helpers_Payment_Method::GCASH_ID => GcashSender::class,
            Helpers_Payment_Method::LENCO_ID => LencoSender::class,
        ];

        $registry = Container::get(PaymentRegistry::class);
        $registry->registerPayment(__FUNCTION__, $list_of_classes);

        return $list_of_classes;
    }

    /**
     * All methods from that list are confirmed in standard way -> through
     * wordpress page.
     *
     * @return array
     */
    public static function get_list_of_payment_method_classes_URI_as_key(): array
    {
        $list_of_classes = [
            Helpers_Payment_Method::TEST_URI => Forms_Wordpress_Payment_Test::class,
            Helpers_Payment_Method::SKRILL_URI => Forms_Wordpress_Payment_Skrill::class,
            Helpers_Payment_Method::NETELLER_URI => Forms_Wordpress_Payment_Neteller::class,
            Helpers_Payment_Method::CUBITS_URI => Forms_Wordpress_Payment_Cubits::class,
            Helpers_Payment_Method::TPAY_URI => Forms_Wordpress_Payment_Tpay::class,
            Helpers_Payment_Method::SOFORT_URI => Forms_Wordpress_Payment_Sofort::class,
            Helpers_Payment_Method::ENTERCASH_URI => Forms_Wordpress_Payment_Entercash::class,
            Helpers_Payment_Method::PIASTRIX_URI => Forms_Wordpress_Payment_Piastrix::class,
            Helpers_Payment_Method::ECOPAYZ_URI => Forms_Wordpress_Payment_Ecopayz::class,
            Helpers_Payment_Method::PAYSAFECARD_URI => Forms_Wordpress_Payment_Paysafecard::class,

            Helpers_Payment_Method::COINPAYMENTS_URI => Forms_Wordpress_Payment_Coinpayments::class,
//            Helpers_Payment_Method::ASIAPAYMENT_URI => Forms_Wordpress_Payment_Asiapayment::class,
            Helpers_Payment_Method::PAYPAL_URI => Forms_Wordpress_Payment_Paypal::class,
            Helpers_Payment_Method::BITBAYPAY_URI => Forms_Wordpress_Payment_Bitbaypay::class,
            Helpers_Payment_Method::DUSUPAY_URI => Forms_Wordpress_Payment_Dusupay::class,
            Helpers_Payment_Method::EASY_PAYMENT_GATEWAY_URI => Helpers_Payment_Easypaymentgateway_Receiver::class,
            Helpers_Payment_Method::APCOPAY_CC_URI => Forms_Wordpress_Payment_Apcopaycc::class,
            Helpers_Payment_Method::ASTRO_PAY_URI => Helpers_Payment_Astropay_Confirmation::class,
            Helpers_Payment_Method::STRIPE_URI => Forms_Wordpress_Payment_Stripe::class,
            Helpers_Payment_Method::FLUTTERWAVE_URI => Forms_Wordpress_Payment_Flutterwave::class,
            Helpers_Payment_Method::FLUTTERWAVE_AFRICA_URI => Forms_Wordpress_Payment_FlutterwaveAfrica::class,
//            Helpers_Payment_Method::ASTRO_PAY_CARD_URI => Forms_Wordpress_Payment_Astropaycard::class,
//            Helpers_Payment_Method::CREDIT_CARD_SANDBOX_URI => Forms_Wordpress_Payment_CreditCardSandbox::class,
//            Helpers_Payment_Method::TRUEVOCC_URI => Forms_Wordpress_Payment_TruevoCC::class,
//            Helpers_Payment_Method::VISANET_URI => Forms_Wordpress_Payment_VisaNet::class,
//            Helpers_Payment_Method::CUSTOM_URI => Forms_Wordpress_Payment_Custom::class,
//            Helpers_Payment_Method::BHARTIPAY_URI => Forms_Wordpress_Payment_Bhartipay::class,
            Helpers_Payment_Method::SEPA_URI => Forms_Wordpress_Payment_Sepa::class,
            Helpers_Payment_Method::PAYOP_URI => Helpers_Payment_PayOp_Receiver::class,
            Helpers_Payment_Method::WONDERLANDPAY_URI => Helpers_Payment_WonderlandPay_Receiver::class,
            Helpers_Payment_Method::PICKSELL_URI => Helpers_Payment_Picksell_Receiver::class,
            Helpers_Payment_Method::PSPGATE_URI => Helpers_Payment_PspGate_Receiver::class,
            Helpers_Payment_Method::ZEN_URI => Helpers_Payment_Zen_Receiver::class,
            Helpers_Payment_Method::ONRAMPER_URI => Helpers_Payment_Onramper_Receiver::class,
            Helpers_Payment_Method::NOWPAYMENTS_URI => Helpers_Payment_NowPayments_Receiver::class,
            Helpers_Payment_Method::GCASH_URI => Helpers_Payment_Gcash_Receiver::class,
            Helpers_Payment_Method::LENCO_URI => Helpers_Payment_Lenco_Receiver::class,
        ];

        $registry = Container::get(PaymentRegistry::class);
        $registry->registerPayment(__FUNCTION__, $list_of_classes);

        return $list_of_classes;
    }

    /**
     * This function returns all list of classes for validate important data
     * while new payment data is defined
     *
     * @return array
     */
    public static function get_list_of_payment_method_classes_for_validation(): array
    {
        $list_of_classes = [
            Helpers_Payment_Method::TEST => Forms_Whitelabel_Payment_Test::class,
            Helpers_Payment_Method::SKRILL => Forms_Whitelabel_Payment_Skrill::class,
            Helpers_Payment_Method::NETELLER => Forms_Whitelabel_Payment_Neteller::class,
            Helpers_Payment_Method::CUBITS => Forms_Whitelabel_Payment_Cubits::class,
            Helpers_Payment_Method::TPAYCOM => Forms_Whitelabel_Payment_Tpay::class,
            Helpers_Payment_Method::SOFORT => Forms_Whitelabel_Payment_Sofort::class,
            Helpers_Payment_Method::ENTERCASH => Forms_Whitelabel_Payment_Entercash::class,
            Helpers_Payment_Method::PIASTRIX => Forms_Whitelabel_Payment_Piastrix::class,
            Helpers_Payment_Method::ECOPAYZ => Forms_Whitelabel_Payment_Ecopayz::class,
            Helpers_Payment_Method::PAYSAFECARD => Forms_Whitelabel_Payment_Paysafecard::class,
            Helpers_Payment_Method::ENTROPAY => Forms_Whitelabel_Payment_Entropay::class,
            Helpers_Payment_Method::COINPAYMENTS => Forms_Whitelabel_Payment_Coinpayments::class,
            Helpers_Payment_Method::ASIAPAYMENT => Forms_Whitelabel_Payment_Asiapayment::class,
            Helpers_Payment_Method::PAYPAL => Forms_Whitelabel_Payment_Paypal::class,
            Helpers_Payment_Method::BITBAYPAY => Forms_Whitelabel_Payment_Bitbaypay::class,
            Helpers_Payment_Method::DUSUPAY => Forms_Whitelabel_Payment_Dusupay::class,
            Helpers_Payment_Method::EASY_PAYMENT_GATEWAY => Validator_Whitelabel_Payments_Easypaymentgateway::class,
            Helpers_Payment_Method::APCOPAY_CC => Forms_Whitelabel_Payment_Apcopaycc::class,
            Helpers_Payment_Method::ASTRO_PAY => Validator_Whitelabel_Payments_Astropay::class,
            Helpers_Payment_Method::STRIPE => Forms_Whitelabel_Payment_Stripe::class,
            Helpers_Payment_Method::FLUTTERWAVE => Forms_Whitelabel_Payment_Flutterwave::class,
            Helpers_Payment_Method::FLUTTERWAVE_AFRICA => Forms_Whitelabel_Payment_FlutterwaveAfrica::class,
            Helpers_Payment_Method::ASTRO_PAY_CARD => Forms_Whitelabel_Payment_Astropaycard::class,
            Helpers_Payment_Method::CREDIT_CARD_SANDBOX => Forms_Whitelabel_Payment_CreditCardSandbox::class,
            Helpers_Payment_Method::TRUEVOCC => Forms_Whitelabel_Payment_TruevoCC::class,
            Helpers_Payment_Method::VISANET => Forms_Whitelabel_Payment_VisaNet::class,
            Helpers_Payment_Method::CUSTOM => Forms_Whitelabel_Payment_Custom::class,
            Helpers_Payment_Method::BHARTIPAY => Forms_Whitelabel_Payment_Bhartipay::class,
            Helpers_Payment_Method::SEPA => Forms_Whitelabel_Payment_Sepa::class,
            Helpers_Payment_Method::PAYOP => Forms_Whitelabel_Payment_PayOp::class,
            Helpers_Payment_Method::WONDERLANDPAY => Forms_Whitelabel_Payment_WonderlandPay::class,
            Helpers_Payment_Method::PICKSELL_ID => Validator_Whitelabel_Payments_Picksell::class,
            Helpers_Payment_Method::PSPGATE_ID => Validator_Whitelabel_Payments_PspGate::class,
            Helpers_Payment_Method::ZEN_ID => Validator_Whitelabel_Payments_Zen::class,
            Helpers_Payment_Method::ONRAMPER_ID => Validator_Whitelabel_Payments_Onramper::class,
            Helpers_Payment_Method::NOWPAYMENTS_ID => Validator_Whitelabel_Payments_NowPayments::class,
            Helpers_Payment_Method::GCASH_ID => Validator_Whitelabel_Payments_Gcash::class,
            Helpers_Payment_Method::LENCO_ID => Validator_Whitelabel_Payments_Lenco::class,
        ];

        $registry = Container::get(PaymentRegistry::class);
        $registry->registerPayment(__FUNCTION__, $list_of_classes);

        return $list_of_classes;
    }

    /**
     * This is a function for process validation
     * strictly by use of static validation() function!
     *
     * @return array
     */
    public static function get_list_of_payment_method_classes_validation_special(): array
    {
        $list_of_classes = [
            Helpers_Payment_Method::EASY_PAYMENT_GATEWAY => Validator_Whitelabel_Payments_Easypaymentgateway::class,
            Helpers_Payment_Method::ASTRO_PAY => Validator_Whitelabel_Payments_Astropay::class,
            Helpers_Payment_Method::PICKSELL_ID => Validator_Whitelabel_Payments_Picksell::class,
            Helpers_Payment_Method::PSPGATE_ID => Validator_Whitelabel_Payments_PspGate::class,
            Helpers_Payment_Method::ZEN_ID => Validator_Whitelabel_Payments_Zen::class,
            Helpers_Payment_Method::ONRAMPER_ID => Validator_Whitelabel_Payments_Onramper::class,
            Helpers_Payment_Method::NOWPAYMENTS_ID => Validator_Whitelabel_Payments_NowPayments::class,
            Helpers_Payment_Method::GCASH_ID => Validator_Whitelabel_Payments_Gcash::class,
            Helpers_Payment_Method::LENCO_ID => Validator_Whitelabel_Payments_Lenco::class,
        ];

        /** @var PaymentRegistry $registry */
        $registry = Container::get(PaymentRegistry::class);
        $registry->registerPayment(__FUNCTION__, $list_of_classes);

        return $list_of_classes;
    }

    /**
     * This function returns all classes needed to check supported currencies
     * while new payment method is defined
     *
     * @return array
     */
    public static function get_list_of_payment_method_classes_for_check_currency_support(): array
    {
        $list_of_classes = [
            Helpers_Payment_Method::TEST => Forms_Whitelabel_Payment_Test::class,
            Helpers_Payment_Method::SKRILL => Forms_Whitelabel_Payment_Skrill::class,
            Helpers_Payment_Method::NETELLER => Forms_Whitelabel_Payment_Neteller::class,
            Helpers_Payment_Method::CUBITS => Forms_Whitelabel_Payment_Cubits::class,
            Helpers_Payment_Method::TPAYCOM => Forms_Whitelabel_Payment_Tpay::class,
            Helpers_Payment_Method::SOFORT => Forms_Whitelabel_Payment_Sofort::class,
            Helpers_Payment_Method::ENTERCASH => Forms_Whitelabel_Payment_Entercash::class,
            Helpers_Payment_Method::PIASTRIX => Forms_Whitelabel_Payment_Piastrix::class,
            Helpers_Payment_Method::ECOPAYZ => Forms_Whitelabel_Payment_Ecopayz::class,
            Helpers_Payment_Method::PAYSAFECARD => Forms_Whitelabel_Payment_Paysafecard::class,
            Helpers_Payment_Method::ENTROPAY => Forms_Whitelabel_Payment_Entropay::class,
            Helpers_Payment_Method::COINPAYMENTS => Forms_Whitelabel_Payment_Coinpayments::class,
            Helpers_Payment_Method::ASIAPAYMENT => Forms_Whitelabel_Payment_Asiapayment::class,
            Helpers_Payment_Method::PAYPAL => Forms_Whitelabel_Payment_Paypal::class,
            Helpers_Payment_Method::BITBAYPAY => Forms_Whitelabel_Payment_Bitbaypay::class,
            Helpers_Payment_Method::DUSUPAY => Forms_Whitelabel_Payment_Dusupay::class,
            Helpers_Payment_Method::EASY_PAYMENT_GATEWAY => Forms_Whitelabel_Payment_Easypaymentgateway::class,
            Helpers_Payment_Method::APCOPAY_CC => Forms_Whitelabel_Payment_Apcopaycc::class,
            Helpers_Payment_Method::ASTRO_PAY => Forms_Whitelabel_Payment_Astropay::class,
            Helpers_Payment_Method::STRIPE => Forms_Whitelabel_Payment_Stripe::class,
            Helpers_Payment_Method::FLUTTERWAVE => Forms_Whitelabel_Payment_Flutterwave::class,
            Helpers_Payment_Method::FLUTTERWAVE_AFRICA => Forms_Whitelabel_Payment_FlutterwaveAfrica::class,
            Helpers_Payment_Method::ASTRO_PAY_CARD => Forms_Whitelabel_Payment_Astropaycard::class,
            Helpers_Payment_Method::CREDIT_CARD_SANDBOX => Forms_Whitelabel_Payment_CreditCardSandbox::class,
            Helpers_Payment_Method::TRUEVOCC => Forms_Whitelabel_Payment_TruevoCC::class,
            Helpers_Payment_Method::VISANET => Forms_Whitelabel_Payment_VisaNet::class,
            Helpers_Payment_Method::CUSTOM => Forms_Whitelabel_Payment_Custom::class,
            Helpers_Payment_Method::BHARTIPAY => Forms_Whitelabel_Payment_Bhartipay::class,
            Helpers_Payment_Method::SEPA => Forms_Whitelabel_Payment_Sepa::class,
            Helpers_Payment_Method::PAYOP => Forms_Whitelabel_Payment_PayOp::class,
            Helpers_Payment_Method::WONDERLANDPAY => Forms_Whitelabel_Payment_WonderlandPay::class,
            Helpers_Payment_Method::PICKSELL_ID => Forms_Whitelabel_Payment_Picksell::class,
            Helpers_Payment_Method::PSPGATE_ID => Forms_Whitelabel_Payment_PspGate::class,
            Helpers_Payment_Method::ZEN_ID => Forms_Whitelabel_Payment_Zen::class,
            Helpers_Payment_Method::ONRAMPER_ID => Forms_Whitelabel_Payment_Onramper::class,
            Helpers_Payment_Method::NOWPAYMENTS_ID => Forms_Whitelabel_Payment_NowPayments::class,
            Helpers_Payment_Method::GCASH_ID => Forms_Whitelabel_Payment_Gcash::class,
            Helpers_Payment_Method::LENCO_ID => Forms_Whitelabel_Payment_Lenco::class,
        ];

        /** @var PaymentRegistry $registry */
        $registry = Container::get(PaymentRegistry::class);
        $registry->registerPayment(__FUNCTION__, $list_of_classes);

        return $list_of_classes;
    }
}

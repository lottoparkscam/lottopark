<?php

use Helpers\Wordpress\LanguageHelper;
use Modules\Payments\Astro\Form\AstroCustomOptionsValidation;
use Modules\Payments\Astro\Form\AstroPaymentForm;
use Modules\Payments\Jeton\Form\JetonCustomOptionsValidation;
use Modules\Payments\Jeton\Form\JetonPaymentForm;
use Modules\Payments\PaymentType;
use Modules\Payments\Tamspay\Form\TamspayCustomOptionsValidation;
use Modules\Payments\Tamspay\Form\TamspayPaymentForm;
use Modules\Payments\Trustpayments\Form\TrustPaymentForm;
use Modules\Payments\Trustpayments\Form\TrustpaymentsCustomOptionsValidation;
use Services\Shared\System;

$system = Container::get(System::class);

return [
    /*
     *  Synchronizer is a task run in the background.
     *  It checks all failed transactions against provider client status service.
     */
    'synchronizer' => [
        # when false, task will not processes
        'is_enabled' => $_ENV['PAYMENTS_SYNCHRONIZER_ENABLED'] ?? true,

        # max number of attempts to check transaction status
        'max_attempts' => 3,

        # script will not process payments younger than given minutes (to give a chance for IPN)
        # it will be calculated by "now() - N minutes"
        'minimal_age_mins' => $_ENV['PAYMENTS_SYNCHRONIZER_MINIMAL_AGE_MINS'] ?? 0,

        # each attempt is saved in transaction, it is none sense to check
        # it's status every minute, so this configuration allows to delay this
        'retry_interval_mins' => 0,

        # script won't process entries older than this value in datetime
        # syntax: "7 days", "5 minutes" etc
        # it will be calculated by "now() - 5 minutes" etc
        'max_age' => $_ENV['PAYMENTS_SYNCHRONIZER_MAX_AGE'] ?? '7 days',

        # each transaction will = request, so we have to limit it
        'max_transactions_chunk' => 100,
    ],

    /*
     *  Each field below provides payment basic configuration
     *  by it's slug. It is used initially in seeder and also in
     *  PaymentConfigDecorator (when no db value found, it will be
     *  fetched from here).
     */
    'jeton' => [
        'id' => 30,
        'slug' => 'jeton',
        'name' => 'Jeton',
        'type' => PaymentType::OTHER(),
        'url' => null, # null will be casted to slug by default

        'supported_currency' => ['EUR', 'GBP', 'INR', 'JPY', 'NOK', 'PLN', 'RUB', 'USD'],

        'payment_form' => JetonPaymentForm::class,
        'currency_check_form' => JetonPaymentForm::class,

        'admin_custom_validation' => JetonCustomOptionsValidation::class,
        'admin_custom_view' => APPPATH . 'classes/modules/Payments/Jeton/resources/jeton.custom.options.view.php',

        'jeton_base_url' => !$system->is_production_env() ? 'https://sandbox-walletapi.jeton.com/' : 'https://walletapi.jeton.com/',
        'jeton_api_key' => !$system->is_production_env() ? '97b10c3680f44170a26e2f8a8ffd0187' : null,
    ],
    'tamspay' => [
        'id' => 31,
        'slug' => 'tamspay',
        'name' => 'Tamspay',
        'type' => PaymentType::OTHER(),
        'url' => null, # null will be casted to slug by default

        'supported_currency' => ['KRW'],

        'payment_form' => TamspayPaymentForm::class,
        'currency_check_form' => TamspayPaymentForm::class,

        'payment_processing_view' => APPPATH . 'classes/modules/Payments/Tamspay/resources/payment-processing.view.php',

        'admin_custom_validation' => TamspayCustomOptionsValidation::class,
        'admin_custom_view' => APPPATH . 'classes/modules/Payments/Tamspay/resources/admin.custom.options.view.php',

        'base_url' => 'https://mg.tamspay.com/',

        'tamspay_sid' => 'TAMS1004',
    ],
    'astro' => [
        'id' => 32,
        'slug' => 'astro',
        'name' => 'AstroPay One Touch',
        'type' => PaymentType::OTHER(),
        'url' => null, # null will be casted to slug by default

        # weird, not documented fields (reached after support conversations)
        'mcc' => 7995,
        'payment_method_code' => 'UI',

        'supported_currency' => ['AED', 'AUD', 'BRL', 'CLP', 'CNY', 'EUR', 'GBP', 'IDR', 'INR', 'JPY', 'MXN', 'MYR', 'NGN', 'THB', 'USD', 'VND'],

        'payment_form' => AstroPaymentForm::class,
        'currency_check_form' => AstroPaymentForm::class,

        'admin_custom_validation' => AstroCustomOptionsValidation::class,
        'admin_custom_view' => APPPATH . 'classes/modules/Payments/Astro/resources/admin.custom.options.view.php',

        'astro_base_url' => !$system->is_production_env() ? 'https://onetouch-api-sandbox.astropay.com/' : 'https://onetouch-api.astropay.com',
        'astro_api_key' => !$system->is_production_env() ? 'TXjwpxv0bGY3SyBubK0dIRCM7hujoFI4iNpbU7shdNKltSGoZaKnK1iZjmSHZdtv' : '',
        'astro_secret_key' => !$system->is_production_env() ? 'vIjsYCAENTtBHaZUkdxSUxjjOgW4XN2a' : '',
        'astro_default_country' => 'UY', # this value is mandatory for valid request, if we are unable to determine country, use this default
    ],
    'trustpayments' => [
        'id' => 33,
        'slug' => 'trustpayments',
        'name' => 'Trustpayments',
        'type' => PaymentType::OTHER(),

        'supported_currency' => [
            'GBP', 'KRW', 'CLP', 'PLN', 'AUD',
            'EUR', 'KWD', 'CNY', 'PYG', 'CAD',
            'USD', 'KYD', 'COP', 'QAR', 'CHF',
            'JPY', 'KZT', 'CRC', 'RON', 'DKK',
            'CAD', 'LAK', 'CYP', 'RSD', 'EUR',
            'AED', 'LBP', 'CZK', 'RUB', 'GBP',
            'AFN', 'LKR', 'DKK', 'SAR', 'HKD',
            'ALL', 'LRD', 'DOP', 'SCR', 'JPY',
            'AMD', 'LTL', 'DZD', 'SEK', 'NOK',
            'ANG', 'LVL', 'EGP', 'SGD', 'NZD',
            'AOA', 'MAD', 'FJD', 'SIT', 'PLN',
            'ARS', 'MDL', 'GEL', 'THB', 'SEK',
            'AUD', 'MKD', 'GIP', 'TJS', 'USD',
            'AZN', 'MNT', 'GTQ', 'TMT', 'ZAR',
            'BAM', 'MOP', 'HKD', 'TND',
            'BBD', 'MUR', 'HNL', 'TRL',
            'BDT', 'MXN', 'HRK', 'TRY',
            'BGN', 'MYR', 'HUF', 'TTD',
            'BHD', 'MZN', 'IDR', 'TWD',
            'BMD', 'NGN', 'ILS', 'TZS',
            'BND', 'NOK', 'INR', 'UAH',
            'BOB', 'NZD', 'IQD', 'UYU',
            'BRL', 'OMR', 'ISK', 'UZS',
            'BSD', 'PAB', 'JMD', 'VND',
            'BWP', 'PEN', 'JOD', 'XCD',
            'BYR', 'PGK', 'KES', 'XOF',
            'BZD', 'PHP', 'KGS', 'YER',
            'CHF', 'PKR', 'KHR', 'ZAR',
        ],

        'supported_locales' => [
            'cy_GB', 'da_DK', 'de_DE' , 'en_US', 'en_GB',
            'es_ES', 'fr_FR', 'it_IT' , 'nl_NL', 'no_NO', 'sv_SE'
        ],

        'default_locale' => LanguageHelper::DEFAULT_LANGUAGE_CODE,

        'payment_form' => TrustPaymentForm::class,
        'currency_check_form' => TrustPaymentForm::class,

        'payment_processing_view' => APPPATH . 'classes/modules/Payments/Trustpayments/resources/payment-processing.view.php',

        'admin_custom_validation' => TrustpaymentsCustomOptionsValidation::class,
        'admin_custom_view' => APPPATH . 'classes/modules/Payments/Trustpayments/resources/admin.custom.options.view.php',

        'base_url' => 'https://payments.securetrading.net/', // @see https://docs.trustpayments.com/document/knowledge-base/supported-domains/

        'trustpayments_sitereference' => 'test_whitelto86346',
    ],
];

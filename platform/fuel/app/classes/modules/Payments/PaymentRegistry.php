<?php

namespace Modules\Payments;

use Webmozart\Assert\Assert;
use Wrappers\Decorators\ConfigContract;

use const ARRAY_FILTER_USE_KEY;

/**
 * Class PaymentRegistry
 * Helper for registering new payments in legacy code (to avoid multiple places jumping, adding etc).
 */
final class PaymentRegistry
{
    public const IGNORED_KEYS = ['synchronizer'];

    private ConfigContract $config;

    public function __construct(ConfigContract $config)
    {
        $this->config = $config;
    }

    /**
     * Refers to:
     * @get_list_of_payment_method_classes
     * @get_list_of_payment_method_classes_for_check_currency_support
     * @get_list_of_payment_method_classes_validation_special
     * @get_list_of_payment_method_classes_for_validation
     * @get_all_methods_with_URI
     *
     * @param string $method
     * @param array $list
     */
    public function registerPayment(string $method, array &$list): void
    {
        $payments = $this->getPaymentConfigs();

        foreach ($payments as $slug => $config) {
            $this->verifyConfig($config);
            $paymentId = $config['id'];

            switch ($method) {
                case 'get_list_of_payment_method_classes':
                    $form = $config['payment_form'];
                    $list[$paymentId] = $form;
                    break;

                case 'get_list_of_payment_method_classes_for_check_currency_support':
                    if (empty($config['currency_check_form'])) {
                        continue 2;
                    }
                    $form = $config['currency_check_form'];
                    $list[$paymentId] = $form;
                    break;

                case 'get_list_of_payment_method_classes_validation_special':
                case 'get_list_of_payment_method_classes_for_validation':
                    $form = $config['admin_custom_validation'];
                    $list[$paymentId] = $form;
                    break;

                case 'get_all_methods_with_URI':
                    $url = $config['url'] ?? strtolower($slug);
                    $type = (string)$config['type'];
                    $list[$type][$paymentId] = $url;
                    break;

                case 'get_list_of_payment_method_classes_URI_as_key':
                    $list[$slug] = $config['payment_form'];
                    break;
            }
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function registerViews(): void
    {
        $payments = $this->getPaymentConfigs();

        foreach ($payments as $config) {
            if (empty($config['admin_custom_view'])) {
                continue;
            }
            include($config['admin_custom_view']);
        }
    }

    /**
     * In config we have "synchronizer" key which is not the payment,
     * so we have to filter it out.
     *
     * @return array
     */
    private function getPaymentConfigs(): array
    {
        return array_filter(
            $this->config->get('payments'),
            fn (string $key) => !in_array($key, self::IGNORED_KEYS),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @return string[]
     */
    public function getPaymentSlugs(): array
    {
        return array_keys($this->getPaymentConfigs());
    }

    private function verifyConfig(array $config): void
    {
        Assert::keyExists($config, 'id');
        Assert::keyExists($config, 'payment_form');
        Assert::keyExists($config, 'admin_custom_validation');
    }
}

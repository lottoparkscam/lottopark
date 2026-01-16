<?php

namespace Modules\Payments;

interface PaymentFacadeContract
{
    /**
     * In this step we should attempt to call provider payment gateway.
     * After successfully initialization we should return transaction
     * url, redirect in app payment request to received url and after
     * in another endpoint - handlePaymentStatus.
     *
     * @param string $transactionPrefixedToken - in our system it will be token
     * @param float $amount - amount to pay in external system
     * @param string $currencyCode
     * @param mixed ...$args - rest of the arguments to be passed
     *
     * @return string
     */
    public function requestCheckoutUrl(string $transactionPrefixedToken, int $whitelabelId, float $amount, string $currencyCode, ...$args): string;

    /**
     * Place for the logic responsible for handling post checkout action logic.
     * For example if our provider supports instant payment transaction checkout,
     * then we can verify it and switch transaction status in our system to paid.
     *
     * As args it might receives event data triggered from wordpress @ action_payment_success.
     *
     * VERY IMPORTANT! Transaction ID must be with all prefixes etc.
     * In our DB we have raw int values, but we sent it with "LPR" at the beginning for example..
     *
     * At this point, we also do not expect too throw any exception (these should be logged by logger).
     * We need simple answer that transaction has been finally successful.
     *
     * @param string $transactionPrefixedToken
     * @param mixed ...$args
     *
     * @return PaymentStatus
     */
    public function getPaymentStatus(string $transactionPrefixedToken, int $whitelabelId, ...$args): PaymentStatus;

    /**
     * Should return fields, that can be modified in empire panel.
     * Keys should target /config/payments concrete, editable keys.
     * Modified data will be stored in DB > whitelabel_payment_method > data & data_json.
     *
     * @return array
     */
    public function getCustomizableOptions(): array;

    /**
     * Shortcut for fetching /config/payments config (stored as array).
     * This method returns base, constant payment data.
     *
     * @param array $fields - if empty, should returns all
     * @return array
     */
    public function getConfig(array $fields = []): array;

    /**
     * This class merges getConfig base data into specific editable WL payment CustomOptionsAwareContract(data_json).
     */
    public function getWhitelabelPaymentConfig(int $paymentMethodId, int $whitelabelId): array;

    /**
     * @param string $transactionPrefixedToken
     * @param array<string, mixed> $details
     */
    public function confirmPayment(string $transactionPrefixedToken, int $whitelabelId, array $details = []): void;

    public function failPayment(string $transactionPrefixedToken, int $whitelabelId): void;
}

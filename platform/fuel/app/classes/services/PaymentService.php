<?php

namespace Services;

use Exception;
use Helper_Route;
use Helpers\UrlHelper;
use Services\Shared\System;

/**
 * Do not consume exceptions from this class. Payments have auto handler and will terminate proces
 */
class PaymentService
{
    private System $system;
    private string $paymentMethodUri;
    private int $whitelabelPaymentMethodId;

    public function __construct(System $system)
    {
        $this->system = $system;
    }

    /**
     * Some functions require more information about the payment method
     * When executing given method, it will throw exception that some details are missing
     * Use this method to set them
     */
    public function configure(string $paymentMethodUri = '', int $whitelabelPaymentMethodId = 0)
    {
        $this->paymentMethodUri = $paymentMethodUri;
        $this->whitelabelPaymentMethodId = $whitelabelPaymentMethodId;
    }

    /**
     * Use only this function to generate confirmation URL for receiving IPN
     * !!! we cannot have casino subdomain here as IPN will not reach us
     * Handles language, so if user buys from https://lottopark.loc/pl/ it will still return https://lottopark.loc
     * Example: https://lottopark.loc
     * Casino example: payment from https://casino.lottopark.loc must return https://lottopark.loc
     * @return string url without trailing slash
     */
    public function getPaymentConfirmationBaseUrl(): string
    {
        return rtrim($this->system->baseFullUrl(), "/");
    }

    /**
     * @throws Exception when service not configured with $paymentMethodUri and $whitelabelPaymentMethodId
     * Do not catch exception, it needs to terminate payment - confirmation url without uri and id will break IPN
     */
    public function getPaymentConfirmationFullUrl(): string
    {
        $this->checkNeededParametersNotEmpty();

        $paymentUrlPart = $this->paymentMethodUri . '/' . $this->whitelabelPaymentMethodId . '/';
        return $this->getPaymentConfirmationBaseUrl() . Helper_Route::ORDER_CONFIRM . $paymentUrlPart;
    }

    /**
     * @throws Exception when essential payment parameters are empty.
     */
    private function checkNeededParametersNotEmpty(): void
    {
        if (empty($this->paymentMethodUri) || empty($this->whitelabelPaymentMethodId)) {
            throw new Exception('Payment method URI and whitelabel payment method ID are needed');
        }
    }

    /**
     * Example:
     * - https://lottopark.loc/order/result/pspgate/430/
     * - https://casino.lottopark.loc/order/result/pspgate/430/
     * Can also pass transaction token:
     * - https://casino.lottopark.loc/order/result/pspgate/430/?token=LPD207791039
     * Result url does not have language, as Platform.php will decide it when handling user redirect from gateway
     * @throws Exception when service not configured with $paymentMethodUri and $whitelabelPaymentMethodId
     * @throws Exception when prefixed transaction token is in incorrect format
     */
    public function getPaymentResultFullUrl(string $prefixedTransactionToken = ''): string
    {
        $this->checkNeededParametersNotEmpty();

        $paymentUrlPart = $this->paymentMethodUri . '/' . $this->whitelabelPaymentMethodId . '/';
        if (!empty($prefixedTransactionToken)) {
            if (preg_match('/^[a-zA-Z]{3}\d+$/', $prefixedTransactionToken)) {
                $paymentUrlPart .= '?token=' . $prefixedTransactionToken;
            } else {
                throw new Exception('Transaction token is in incorrect format. Probably not prefixed.');
            }
        }
        return UrlHelper::getHomeUrlWithoutLanguage() . Helper_Route::ORDER_RESULT . $paymentUrlPart;
    }

    /**
     * Use to convert amount to whole cents (i.e. 10.00 => 1000)
     * For example: rounding is required for 4.89 * 100, which returned 488.999999 instead of 489.
     */
    public static function convertAmountToCents(float $amount): int
    {
        return round($amount * 100);
    }
}

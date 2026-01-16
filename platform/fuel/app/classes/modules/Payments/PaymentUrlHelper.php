<?php

namespace Modules\Payments;

use Helper_Route;
use Helpers\UrlHelper;
use Models\WhitelabelTransaction;
use Models\WhitelabelPaymentMethod;
use Repositories\Orm\TransactionRepository;
use Repositories\Orm\WhitelabelPaymentMethodRepository;
use Services\Shared\System;

class PaymentUrlHelper
{
    private System $system;
    private WhitelabelPaymentMethodRepository $repo;
    private TransactionRepository $transactionRepository;
    private PaymentFacadeLocator $locator;

    public function __construct(
        System $system,
        WhitelabelPaymentMethodRepository $repo,
        TransactionRepository $transactionRepository,
        PaymentFacadeLocator $locator
    ) {
        $this->system = $system;
        $this->repo = $repo;
        $this->transactionRepository = $transactionRepository;
        $this->locator = $locator;
    }

    public function getConfirmationUrl(WhitelabelTransaction $transaction): string
    {
        $whitelabelPaymentMethodId = $transaction->whitelabel_payment_method_id;
        $transactionPrefixedToken = $transaction->prefixed_token;
        $out = $transaction->transaction_out_id;

        /** @var WhitelabelPaymentMethod $method */
        $method = $this->repo->getById($whitelabelPaymentMethodId, ['payment_method']);
        $paymentMethodSlug = $this->getPaymentMethodSlug($method->payment_method_id);

        # should be something like: lottopark.loc/order/confirm/slug/id/?token=ABC123&out=some_unreadable_hash
        $url = vsprintf('%s%s/%s/%d/?token=%s&out=%s', [
            $this->system->baseFullUrl(),
            Helper_Route::ORDER_CONFIRM,
            $paymentMethodSlug,
            $whitelabelPaymentMethodId,
            $transactionPrefixedToken,
            $out
        ]);

        return $this->cleanedFromDoubleSlashed($url);
    }

    public function getConfirmationUrlById(int $transactionId): string
    {
        $transaction = $this->transactionRepository->getById($transactionId, ['whitelabel_payment_method']);
        return $this->getConfirmationUrl($transaction);
    }

    public function getSuccessUrl(bool $isCasino = false): string
    {
        $url = vsprintf('%s%s', [
            $this->system->baseFullUrl(),
            Helper_Route::ORDER_SUCCESS,
        ]);

        $url = UrlHelper::changeAbsoluteUrlToCasinoUrl($url, $isCasino);

        return $this->cleanedFromDoubleSlashed($url);
    }

    public function getResultUrl(WhitelabelTransaction $transaction): string
    {
        $whitelabelPaymentMethodId = $transaction->whitelabel_payment_method_id;
        /** @var WhitelabelPaymentMethod $method */
        $method = $this->repo->getById($whitelabelPaymentMethodId, ['payment_method']);
        $paymentMethodSlug = $this->getPaymentMethodSlug($method->payment_method_id);

        $url = vsprintf('%s%s/%s/%d/?token=%s', [
            $this->system->baseFullUrl(),
            Helper_Route::ORDER_RESULT,
            $paymentMethodSlug,
            $whitelabelPaymentMethodId,
            $transaction->prefixed_token
        ]);

        $url = UrlHelper::changeAbsoluteUrlToCasinoUrl($url);

        return $this->cleanedFromDoubleSlashed($url);
    }

    public function getFailureUrl(bool $isCasino = false): string
    {
        $url = vsprintf('%s%s', [
            $this->system->baseFullUrl(),
            Helper_Route::ORDER_FAILURE,
        ]);

        $url = UrlHelper::changeAbsoluteUrlToCasinoUrl($url, $isCasino);

        return $this->cleanedFromDoubleSlashed($url);
    }

    private function cleanedFromDoubleSlashed(string $url): string
    {
        $url = str_replace('//', '/', $url);
        return str_replace(':/', '://', $url);
    }

    private function getPaymentMethodSlug(int $paymentMethodId): string
    {
        $config = $this->locator->getById($paymentMethodId)->getConfig();
        return $config['slug'];
    }
}

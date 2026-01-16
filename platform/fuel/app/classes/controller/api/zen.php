<?php

use Fuel\Core\Input;
use Fuel\Core\Response;
use GGLib\Zen\Entity\CheckoutItem;
use GGLib\Zen\Entity\CreateCheckoutRequest;
use GGLib\Zen\PsrClientGateway;
use GuzzleHttp\Exception\RequestException;
use Repositories\WhitelabelPaymentMethodRepository;
use Repositories\WhitelabelRepository;
use Repositories\WhitelabelTransactionRepository;
use Fuel\Core\Controller;

class Controller_Api_Zen extends Controller
{
    // whitelabel url that will serve as a zen payment intermediary
    private const WHITELABEL_URL = 'https://lottopark.com';

    private function sendResponse(array $data, int $httpCode): Response
    {
        $headers = ['Content-type' => 'application/json'];
        return new Response(json_encode($data), $httpCode, $headers);
    }

    private function getTransactionTokenWithoutPrefix(string $transactionToken): int|string
    {
        $prefix = substr($transactionToken, 0, 3);
        if (ctype_alpha($prefix)) {
            return substr($transactionToken, 3);
        }

        return $transactionToken;
    }

    private function getConfirmationFullUrl(int $whitelabelPaymentMethodId): string
    {
        $methodUri = Helpers_Payment_Method::getMethodTypeOtherUriById(Helpers_Payment_Method::ZEN_ID);

        return self::WHITELABEL_URL . "/order/confirm/{$methodUri}/{$whitelabelPaymentMethodId}/";
    }

    public function post_generate_redirect_url(): Response
    {
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabelTransactionRepository = Container::get(WhitelabelTransactionRepository::class);
        $whitelabelPaymentMethodRepository = Container::get(WhitelabelPaymentMethodRepository::class);

        $whitelabelId = (int) Input::json('whitelabelId');
        $whitelabel = $whitelabelRepository->findOneById($whitelabelId);
        $isCasino = !empty(Input::json('isCasino')) && Input::json('isCasino') === true;

        if (!$whitelabel) {
            return $this->sendResponse(['logMessage' => 'Whitelabel not found'], 404);
        }

        $transactionToken = Input::json('transactionToken');
        $encryptedTransactionToken = Input::json('encryptedTransactionToken');
        $currencyCode = Input::json('currencyCode');

        $customerFirstName = Input::json('customerFirstName');
        $customerLastName = Input::json('customerLastName');
        $customerEmail = Input::json('customerEmail');

        $isEmptyInputData = empty($transactionToken) || empty($currencyCode) || empty($customerFirstName) || empty($encryptedTransactionToken) || empty($customerLastName) || empty($customerEmail);
        if ($isEmptyInputData) {
            return $this->sendResponse(['logMessage' => 'Invalid input data'], 400);
        }

        $transactionTokenWithoutPrefix = $this->getTransactionTokenWithoutPrefix($transactionToken);
        $transaction = $whitelabelTransactionRepository->getTransactionByTokenAndWhitelabelId(
            $transactionTokenWithoutPrefix,
            $whitelabel->id,
        );

        $paymentData = $whitelabelPaymentMethodRepository->getSettingsByMethodIdAndWhitelabelId(
            $transaction['whitelabel_payment_method_id'],
            $whitelabel->id,
        );

        $paymentDescription = sprintf(_("Transaction %s"), $encryptedTransactionToken);

        $item = new CheckoutItem(
            $paymentDescription,
            $transaction['amount_payment'],
            1,
            $transaction['amount_payment'],
        );

        $terminalUuid = $isCasino ? $paymentData['casino_terminal_uuid'] : $paymentData['terminal_uuid'];
        $paywallSecret = $isCasino ? $paymentData['casino_paywall_secret'] : $paymentData['paywall_secret'];

        $createCheckoutRequest = new CreateCheckoutRequest(
            $terminalUuid,
            $transaction['amount_payment'],
            $currencyCode,
            $encryptedTransactionToken,
            $customerFirstName,
            $customerLastName,
            $customerEmail,
            [
                $item,
            ]
        );

        $whitelabelPaymentMethodId = $transaction['whitelabel_payment_method_id'];
        $customIpnUrl = $isCasino ?
            $this->getConfirmationFullUrl($whitelabelPaymentMethodId) . '?' . Helpers_Payment_Zen_Receiver::IS_CASINO_FIELD . '=true' :
            $this->getConfirmationFullUrl($whitelabelPaymentMethodId);
        $createCheckoutRequest->setCustomIpnUrl($customIpnUrl);
        $createCheckoutRequest->setUrlSuccess(self::WHITELABEL_URL . '/order/success?transactionToken=' . $encryptedTransactionToken);
        $createCheckoutRequest->setUrlFailure(self::WHITELABEL_URL . '/order/failure?transactionToken=' . $encryptedTransactionToken);

        try {
            /** @var PsrClientGateway $gateway */
            $gateway = Container::make(PsrClientGateway::class, ['testMode' => $paymentData['is_test']]);
            $createCheckoutResponse = $gateway->createCheckout($paywallSecret, $createCheckoutRequest);
        } catch (RequestException $exception) {
            return $this->sendResponse(['logMessage' => $createCheckoutRequest->toArraySigned()], 400);
        } catch (Throwable $throwable) {
            return $this->sendResponse(['logMessage' => $throwable->getMessage()], 400);
        }

        return $this->sendResponse([
            'logMessage' => $createCheckoutRequest->toArraySigned(),
            'url' => $createCheckoutResponse->getRedirectUrl()
        ], 200);
    }
}

<?php

use Abstracts\Controllers\Internal\AbstractPublicController;
use Fuel\Core\Response;
use Helpers\Wordpress\LanguageHelper;
use Helpers\CurrencyHelper;
use Validators\CurrencyConverterValidator;

class Controller_Api_Internal_Currency extends AbstractPublicController
{
    /**
     * @OA\Patch(
     *     path="/currency/converter",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 required={"amount", "currency", "convertToCurrency"},
     *                 @OA\Property(
     *                     property="amount",
     *                     type="float",
     *                     description="Amount to convert from"
     *                 )
     *                 @OA\Property(
     *                     property="currency",
     *                     type="string",
     *                     description="Three letters ISO currency code. Currency to convert from.",
     *                     example="EUR"
     *                 ),
     *                 @OA\Property(
     *                     property="convertToCurrency",
     *                     type="string",
     *                     description="Three letters ISO currency code. Converts amount into this currency.",
     *                     example="USD"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="",
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="amount",
     *                          type="float",
     *                          example="10"
     *                      ),
     *                      @OA\Property(
     *                          property="currency",
     *                          type="string",
     *                          example="EUR"
     *                      )
     *                  )
     *              )
     *          )
     * )
     */
    public function before()
    {
        parent::before();
    }

    public function get_index(): Response
    {
        $currentCurrencyCode = Helpers_Currency::getUserCurrencyTable()['code'];
        $currentCurrencySign = Lotto_View::format_currency_code($currentCurrencyCode);
        $currentWhitelabelLanguage = LanguageHelper::getCurrentWhitelabelLanguage();
        $jsCurrencyFormat = $currentWhitelabelLanguage['js_currency_format'];

        return $this->returnResponse([
            'currentCurrencyCode' => $currentCurrencyCode,
            'currentCurrencySign' => $currentCurrencySign,
            'jsCurrencyFormat' => $jsCurrencyFormat,
            'zeroInCurrentCurrency' => Lotto_View::format_currency(0, $currentCurrencyCode, true),
        ]);
    }

    public function get_converter(): Response
    {
        /** @var CurrencyConverterValidator $currencyConverterValidator */
        $currencyConverterValidator = Container::get(CurrencyConverterValidator::class);
        $isInputNotValid = $currencyConverterValidator->isNotValid();

        if ($isInputNotValid) {
            return $this->returnResponse(
                [
                    'amount' => 0,
                    'currency' => null
                ]
            );
        }

        [$amount, $currency, $convertToCurrency] = $currencyConverterValidator->getProperties(
            ['amount', 'currency', 'convertToCurrency']
        );

        $currencyTab = Helpers_Currency::get_mtab_currency(
            false,
            $currency,
        );

        $convertToCurrencyId = CurrencyHelper::getCurrencyByCode($convertToCurrency)->id;
        $convertToCurrencyTab = Helpers_Currency::get_mtab_currency(
            true,
            $currency,
            $convertToCurrencyId,
        );

        $convertedAmount = Helpers_Currency::get_single_converted_from_currency($currencyTab, $amount, $convertToCurrencyTab);

        $response = [
            'amount' => $convertedAmount,
            'currency' => $convertToCurrency
        ];

        return $this->returnResponse($response);
    }
}

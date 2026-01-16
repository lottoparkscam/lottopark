<?php

declare(strict_types=1);

use Models\Currency;
use Models\Whitelabel;
use Models\WhitelabelPaymentMethod;
use Models\WhitelabelPaymentMethodCurrency;
use Repositories\Orm\WhitelabelPaymentMethodCurrencyRepository;
use Repositories\Orm\WhitelabelPaymentMethodRepository;
use Fuel\Core\Cookie;
use Fuel\Core\Validation;
use Services\Logs\FileLoggerService;
use Helpers\CurrencyHelper;
use Exceptions\PaymentMethod\WhitelabelPaymentMethodCurrencyNotSupportedException;

class PaymentMethodService
{
    private const COOKIE_NAME_PREFIX = 'payment-';

    private int $paymentMethodId;
    private ?Validation $validation = null;
    private ?Whitelabel $whitelabel = null;
    private ?WhitelabelPaymentMethod $whitelabelPaymentMethod = null;

    private ?Currency $currency = null;

    /**
     * @var WhitelabelPaymentMethodCurrency[]|null
     */
    private ?array $whitelabelEnabledPaymentMethodCurrencies = [];

    private WhitelabelPaymentMethodRepository $whitelabelPaymentMethodRepository;
    private WhitelabelPaymentMethodCurrencyRepository $whitelabelPaymentMethodCurrencyRepository;

    private FileLoggerService $fileLoggerService;

    public function __construct(
        WhitelabelPaymentMethodRepository $whitelabelPaymentMethodRepository,
        WhitelabelPaymentMethodCurrencyRepository $whitelabelPaymentMethodCurrencyRepository,
        FileLoggerService $fileLoggerService
    ) {
        $this->whitelabelPaymentMethodRepository = $whitelabelPaymentMethodRepository;
        $this->whitelabelPaymentMethodCurrencyRepository = $whitelabelPaymentMethodCurrencyRepository;
        $this->fileLoggerService = $fileLoggerService;
    }

    public function setWhitelabel(Whitelabel $whitelabel): void
    {
        $this->whitelabel = $whitelabel;
    }

    public function setWhitelabelPaymentMethod(int $whitelabelPaymentMethodId): void
    {
        $this->whitelabelPaymentMethod = $this->whitelabelPaymentMethodRepository->findOneById(
            $whitelabelPaymentMethodId
        );
    }

    /**
     * @throws Exception
     */
    public function setCurrencyByCode(string $currencyCode): void
    {
        try {
            $this->currency = CurrencyHelper::getCurrencyByCode($currencyCode);
        } catch (Exception) {
            throw new WhitelabelPaymentMethodCurrencyNotSupportedException($currencyCode);
        }
    }

    public function getCurrencyId(): int
    {
        return $this->currency->id;
    }

    public function getCurrencyCode(): string
    {
        return $this->currency->code;
    }

    /**
     * @throws Exception
     */
    public function isUserAllowedToSelectPaymentCurrency(): bool
    {
        if ($this->whitelabelPaymentMethod === null) {
            throw new Exception('WhitelabelPaymentMethod is not set.');
        }

        return $this->whitelabelPaymentMethod->allowUserToSelectCurrency;
    }

    /**
     * @throws Exception
     */
    public function isCurrencySupportedForWhitelabelPaymentMethod(): bool
    {
        if ($this->whitelabelPaymentMethod === null) {
            throw new Exception('WhitelabelPaymentMethod is not set.');
        }

        if ($this->currency === null) {
            throw new Exception('Currency is not set.');
        }

        return $this->whitelabelPaymentMethodCurrencyRepository->isCurrencySupportedForWhitelabelPaymentMethod(
            $this->whitelabelPaymentMethod->id,
            $this->currency->id
        );
    }

    public function loadWhitelabelEnabledPaymentMethodCurrencies(array $whitelabelPaymentMethodIds = []): void
    {
        $this->whitelabelEnabledPaymentMethodCurrencies = $this->whitelabelPaymentMethodCurrencyRepository
            ->getAllEnabledCurrencies($whitelabelPaymentMethodIds);
    }

    public function getEnabledCurrenciesForWhitelabelPaymentMethod(int $whitelabelPaymentMethodId): array
    {
        $currencies = [];

        foreach ($this->whitelabelEnabledPaymentMethodCurrencies as $paymentMethodCurrency) {
            $shouldAddCurrency = $paymentMethodCurrency->whitelabel_payment_method_id === $whitelabelPaymentMethodId;
            if ($shouldAddCurrency) {
                $currencies[] = $paymentMethodCurrency;
            }
        }

        return $currencies;
    }

    /**
     * @throws Exception
     */
    public function getDefaultCurrencyForWhitelabelPaymentMethod(int $whitelabelPaymentMethodId): ?WhitelabelPaymentMethodCurrency
    {
        foreach ($this->whitelabelEnabledPaymentMethodCurrencies as $paymentMethodCurrency) {
            $isDefaultCurrency = $paymentMethodCurrency->whitelabel_payment_method_id === $whitelabelPaymentMethodId
                && $paymentMethodCurrency->is_default;
            if ($isDefaultCurrency) {
                return $paymentMethodCurrency;
            }
        }

        return null;
    }

    public function configureUserFormValidation(int $paymentMethodId, Validation $validation): void
    {
        $this->paymentMethodId = $paymentMethodId;
        $this->validation = $validation;
    }

    public function isUserFormValidationConfigured(): bool
    {
        return $this->validation !== null;
    }

    public function saveFormUserDetailsToCookie(): void
    {
        try {
            $cookieName = self::COOKIE_NAME_PREFIX . $this->paymentMethodId;
            $cookieWhitelabelDomain = !empty($this->whitelabel->domain) ? '.' . $this->whitelabel->domain : null;

            Cookie::set(
                $cookieName,
                json_encode($this->validation->validated(), JSON_THROW_ON_ERROR),
                Helpers_Time::YEAR_IN_SECONDS,
                '/',
                $cookieWhitelabelDomain
            );

        } catch (Throwable $throwable) {
            $this->fileLoggerService->error(
                "Cannot save payment user cookie. Error message:" . $throwable->getMessage()
            );
        }
    }

    public function getFormUserDetailsFromCookie(int $paymentMethodId, string $inputName = null): array
    {
        try {
            $cookieName = self::COOKIE_NAME_PREFIX . $paymentMethodId;
            $userFormDetails = Cookie::get($cookieName);

            if (!empty($userFormDetails)) {
                $jsonDecode = json_decode(stripslashes($userFormDetails), true);

                return $jsonDecode[$inputName] ?? $jsonDecode;
            }

        } catch (Throwable $throwable) {
            $this->fileLoggerService = Container::get(FileLoggerService::class);
            $this->fileLoggerService->error(
                "Cannot read payment user cookie. Error message:" . $throwable->getMessage()
            );
        }

        return [];
    }
}

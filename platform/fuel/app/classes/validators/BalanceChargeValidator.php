<?php

namespace Validators;

use Exception;
use Models\Whitelabel;
use Repositories\Orm\CurrencyRepository;
use Repositories\Orm\WhitelabelUserRepository;
use Validators\Rules\Amount;
use Validators\Rules\CurrencyCode;
use Validators\Rules\Email;
use Validators\Rules\Login;

class BalanceChargeValidator extends Validator
{
    protected static string $method = Validator::PATCH;
    private WhitelabelUserRepository $whitelabelUserRepository;
    private CurrencyRepository $currencyRepository;

    public function __construct(WhitelabelUserRepository $whitelabelUserRepository, CurrencyRepository $currencyRepository)
    {
        parent::__construct();
        $this->whitelabelUserRepository = $whitelabelUserRepository;
        $this->currencyRepository = $currencyRepository;
    }

    protected function buildValidation(...$args): void
    {
        /** @var Whitelabel $whitelabel */
        [$whitelabel] = $args;

        $isBalanceChangeGlobalLimitEnabledInApi = $whitelabel->isBalanceChangeGlobalLimitEnabledInApi;
        $isUserIdentifyByLogin = $whitelabel->useLoginsForUsers;

        if ($isUserIdentifyByLogin) {
            $this->addFieldRule(Login::build('user_login'));
        } else {
            $this->addFieldRule(Email::build('user_email'));
        }

        $balanceAmountChange = Amount::build();
        $this->addFieldRule($balanceAmountChange);

        if ($isBalanceChangeGlobalLimitEnabledInApi) {
            $userBalanceChangeLimit = $whitelabel->userBalanceChangeLimit;
            $maxBalanceAmountPerWhitelabel = $userBalanceChangeLimit < 0 ? 0 : $userBalanceChangeLimit;

            $balanceAmountChange
                ->addRule('numeric_between', 0, $maxBalanceAmountPerWhitelabel)
                ->setErrorMessage('numeric_between', 'Wrong balance amount. Limit has been reached.');
        } else {
            $balanceAmountChange
                ->addRule('numeric_min', 0)
                ->setErrorMessage('numeric_min', 'Wrong balance amount');
        }

        /** @var CurrencyCode $currencyCodeRule */
        $currencyCodeRule = CurrencyCode::build();
        $currencyCodeRule->configure($this->currencyRepository);
        $this->addFieldRule($currencyCodeRule);
    }

    /**
     * @param mixed ...$args
     * @return bool
     * @throws Exception
     */
    protected function extraChecks(...$args): bool
    {
        [$whitelabel] = $args;

        [$login, $email] = $this->getProperties(['user_login', 'user_email']);

        $whitelabelUser = $this->whitelabelUserRepository->findSpecificUser($login, $email, $whitelabel);
        if (empty($whitelabelUser)) {
            $this->setErrors(['errors' => 'User does not exist']);
            return false;
        }

        return true;
    }
}
<?php

namespace Validators;

use Exception;
use Repositories\Orm\CurrencyRepository;
use Repositories\Orm\WhitelabelUserRepository;
use Validators\Rules\Amount;
use Validators\Rules\CurrencyCode;
use Validators\Rules\Email;
use Validators\Rules\Login;

class BalanceDebitValidator extends Validator
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
        [$identifyByLogin] = $args;

        if ($identifyByLogin) {
            $this->addFieldRule(Login::build('user_login'));
        } else {
            $this->addFieldRule(Email::build('user_email'));
        }

        $balanceAmountChange = Amount::build();
        /** @var CurrencyCode $currencyCodeRule */
        $currencyCodeRule = CurrencyCode::build();
        $currencyCodeRule->configure($this->currencyRepository);
        $this->addFieldRules([
            $balanceAmountChange,
            $currencyCodeRule
        ]);

        $balanceAmountChange
            ->addRule('numeric_min', 0)
            ->setErrorMessage('numeric_min', 'Wrong balance amount');
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
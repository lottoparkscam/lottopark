<?php

namespace Tests\Fixtures;

use Fuel\Core\Str;
use Lotto_Security;
use Models\Whitelabel;
use Models\WhitelabelAff;
use Models\WhitelabelUser;
use Models\WhitelabelUser as User;
use Models\WhitelabelUserAff;

final class WhitelabelUserFixture extends AbstractFixture
{
    public const CURRENCY = 'currency';
    public const WHITELABEL = 'whitelabel';
    public const WITHOUT_WHITELABEL = 'without_whitelabel';
    public const BALANCE_10000 = 'balance_1000';
    public const BONUS_BALANCE_10000 = 'bonus_balance_1000';
    public const BALANCE_0 = 'balance_0';
    public const USD = 'usd';
    public const EUR = 'eur';

    public function getDefaults(): array
    {
        return [
            'activation_hash' => null,
            'activation_valid' => null,
            'address_1' => $this->faker->address(),
            'address_2' => $this->faker->streetAddress(),
            'balance' => $this->faker->numberBetween(0, 100000),
            'bonus_balance' => $this->faker->numberBetween(0, 1000),
            'birthdate' => null,
            'browser_type' => null,
            'city' => $this->faker->city(),
            'country' => $this->faker->countryCode(),
            'date_register' => $this->faker->date(),
            'email' => $this->faker->email(),
            'first_deposit' => null,
            'first_deposit_amount_manager' => null,
            'first_purchase' => null,
            'gender' => $this->faker->randomElement([0, 1, 2]),
            'is_active' => $this->faker->boolean(80),
            'hash' => $this->faker->uuid(),
            'is_confirmed' => $this->faker->boolean(70),
            'is_deleted' => $isDeleted = $this->faker->boolean(20),
            'date_delete' => $isDeleted ? $this->faker->date() : null,
            'language_id' => 1, // todo
            'last_active' => $this->faker->date(),
            'last_country' => null,
            'last_deposit_amount_manager' => null,
            'last_deposit_date' => null,
            'last_ip' => $this->faker->ipv4(),
            'last_purchase_amount_manager' => null,
            'last_purchase_date' => null,
            'last_update' => $this->faker->date(),
            'login' => $this->faker->name(),
            'login_hash' => Str::random('alnum', 64),
            'login_by_hash_last' => null,
            'lines_sold_quantity' => 0,
            'lost_hash' => null,
            'national_id' => $this->faker->numerify('ABC###'),
            'lost_last' => null,
            'name' => $this->faker->name(),
            'net_winnings_manager' => null,
            'phone' => $this->faker->phoneNumber(),
            'phone_country' => $this->faker->countryCode(),
            'pnl_manager' => null,
            'refer_bonus_used' => $this->faker->boolean(20),
            'register_country' => $this->faker->countryCode(),
            'register_ip' => $this->faker->ipv4(),
            'resend_hash' => null,
            'sale_status' => 0,
            'salt' => $this->faker->uuid(),
            'second_deposit' => null,
            'second_purchase' => null,
            'sent_welcome_mail' => $this->faker->boolean(),
            'state' => $this->faker->state(),
            'surname' => $this->faker->firstNameMale(),
            'system_type' => null,
            'tickets_sold_quantity' => 0,
            'timezone' => $this->faker->timezone(),
            'token' => $this->faker->numberBetween(10000, 9999999),
            'total_deposit_manager' => null,
            'total_net_income_manager' => null,
            'total_purchases_manager' => null,
            'total_withdrawal_manager' => null,
            'zip' => $this->faker->numberBetween(1000, 999999),
        ];
    }

    public static function getClass(): string
    {
        return User::class;
    }

    public function getStates(): array
    {
        return [
            self::CURRENCY => $this->reference('currency', CurrencyFixture::class),
            self::WHITELABEL => $this->reference('whitelabel', WhitelabelFixture::class),
            // Just marker to avoid circular dependency
            self::WITHOUT_WHITELABEL => function (User $user, array $attributes = []) {
            },
            self::BASIC => $this->basic(),
            self::BALANCE_10000 => fn (User $user, array $attributes = []) => $user->balance = 10000,
            self::BONUS_BALANCE_10000 => fn (User $user, array $attributes = []) => $user->bonus_balance = 10000,
            self::BALANCE_0 => fn (User $user, array $attributes = []) => $user->bonus_balance = $user->balance = 0,
            self::USD => $this->usd(),
            self::EUR => $this->eur(),
        ];
    }

    private function basic(): callable
    {
        return function (User $user, array $attributes = []) {
            if (empty($user->currency)) {
                $user->currency = $this->fixture(self::CURRENCY)
                    ->makeOne(['code' => $this->faker->randomElement(['USD', 'EUR', 'PLN'])]);
            }

            if (empty($user->whitelabel)) {
                if (!$this->inUse(self::WITHOUT_WHITELABEL)) {
                    $user->whitelabel = $this->fixture(self::WHITELABEL)->with('basic')();
                }
            }
        };
    }

    /** @param array $modifiedData eg. ['email' => 'test', 'password' => 'xx'] field password is required */
    public function addModifiedUser(array $modifiedData = []): WhitelabelUser
    {
        $password = $modifiedData['password'];
        // generate hash
        $hash = $salt = '';
        extract($this->generateHashAndSalt($password));

        $userParams = [
            'hash' => $hash,
            'salt' => $salt,
            'currency_id' => 2, //eur
        ];

        $modifiedData = array_merge($modifiedData, $userParams);

        return $this->with('basic')->createOne($modifiedData);
    }

    public function generateHashAndSalt(string $password): array
    {
        $salt = Lotto_Security::generate_salt();
        $hash = Lotto_Security::generate_hash(
            $password,
            $salt
        );

        return [
            'hash' => $hash,
            'salt' => $salt
        ];
    }

    public function addUser(
        string $login,
        string $password,
        int $balance = 10000,
        int $bonusBalance = 10000,
        ?Whitelabel $whitelabel = null
    ) {
        //Generate password hash and salt
        $hash = $salt = '';
        extract($this->generateHashAndSalt($password));

        $loginField = !is_null($whitelabel) && $whitelabel->useLoginsForUsers ? 'login' : 'email';

        $userParams = [
            $loginField => $login,
            'balance' => $balance,
            'bonus_balance' => $bonusBalance,
            'whitelabel_id' => is_null($whitelabel) ? 1 : $whitelabel->id,
            'hash' => $hash,
            'salt' => $salt,
            'is_active' => 1,
            'is_deleted' => 0,
            'currency_id' => 2 //EUR
        ];

        $shouldAddLoginField = !is_null($whitelabel) && $whitelabel->useLoginsForUsers;
        if ($shouldAddLoginField) {
            $userParams['login'] = $login;
        }

        //Create new Fixture User
        $this->user = $this->with('basic')->createOne($userParams);
    }

    public function addRandomUser(int $balance = 10000, int $bonusBalance = 10000): void
    {
        //Create new Fixture User
        $this->user = $this->with(
            'basic',
            // 'currency.eur',
        )->createOne([
            'balance' => $balance,
            'bonus_balance' => $bonusBalance,
            'whitelabel_id' => 1,
            'is_active' => 1,
            'currency_id' => 2 //EUR
        ]);
    }

    private function usd(): callable
    {
        return function (User $user, array $attributes = []) {
            $user->currency = $this->fixture(self::CURRENCY)->with('usd')();
        };
    }

    private function eur(): callable
    {
        return function (User $user, array $attributes = []) {
            $user->currency = $this->fixture(self::CURRENCY)->with('eur')();
        };
    }

    public function withWhitelabel(Whitelabel $whitelabel): self
    {
        $this->with(function (WhitelabelUser $whitelabelUser, array $attributes = []) use ($whitelabel) {
            $whitelabelUser->whitelabel = $whitelabel;
        });
        return $this;
    }

    public function withUserAff(WhitelabelUserAff $whitelabelUserAff): self
    {
        $this->with(function (WhitelabelUser $whitelabelUser, array $attributes = []) use ($whitelabelUserAff) {
            $whitelabelUser->whitelabel_user_aff = $whitelabelUserAff;
        });
        return $this;
    }

    public function withWhitelabelAff(WhitelabelAff $whitelabelAff): self
    {
        $this->with(function (WhitelabelUser $whitelabelUser, array $attributes = []) use ($whitelabelAff) {
            $whitelabelUser->whitelabel_user_aff->whitelabel_aff = $whitelabelAff;
        });
        return $this;
    }
}

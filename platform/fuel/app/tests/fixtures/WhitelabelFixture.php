<?php

namespace Tests\Fixtures;

use Models\Whitelabel;
use OverflowException;
use Exception;

final class WhitelabelFixture extends AbstractFixture
{
    public const CURRENCY = 'currency';
    private int $prefixPoolLimit = 10;
    private array $prefixes = [];

    /** @inheritdoc */
    public function getDefaults(): array
    {
        return [
            'name' => $this->faker->name(),
            'domain' => $this->faker->domainName(),
            'type' => 1,
            'language_id' => 1, // todo
            'user_activation_type' => 0,
            'user_registration_through_ref_only' => 0,
            'aff_activation_type' => 1,
            'aff_auto_accept' => 1,
            'aff_payout_type' => 1,
            'aff_lead_auto_accept' => 1,
            'aff_ref_lifetime' => 0,
            'aff_hide_ticket_and_payment_cost' => false,
            'aff_hide_amount' => false,
            'aff_hide_income' => false,
            'aff_enable_sign_ups' => false,
            'max_payout' => $this->faker->numberBetween(1000, 5000),
            'welcome_popup_timeout' => $this->faker->numberBetween(10, 30),
            'username' => $this->faker->randomElement(['lottopark', 'whitelotto', $this->faker->word()]),
            'hash' => $this->faker->uuid(),
            'salt' => $this->faker->uuid(),
            'email' => $this->faker->email(),
            'support_email' => $this->faker->email(),
            'payment_email' => $this->faker->email(),
            'realname' => $this->faker->firstName(),
            'company_details' => $this->faker->address(),
            'timezone' => $this->faker->timezone(),
            'prefix' => $this->randomPrefix(),
            'def_commission_value' => null,
            'def_commission_value_manager' => null,
            'def_commission_value_2' => null,
            'def_commission_value_2_manager' => null,
            'def_ftp_commission_value' => null,
            'def_ftp_commission_value_manager' => null,
            'def_ftp_commission_value_2' => null,
            'def_ftp_commission_value_2_manager' => null,
            'analytics' => null,
            'analytics_casino' => null,
            'ceg_seal_id' => null,
            'default_site_currency' => null,
            'fb_pixel' => null,
            'fb_pixel_match' => 0,
            'margin' => $this->faker->numberBetween(5, 30),
            'prepaid' => $prepaid = $this->faker->numberBetween(0, 400),
            'prepaid_alert_limit' => $prepaid / $this->faker->numberBetween(1, 5),
            'manager_site_currency_id' => 2,
            'max_order_count' => $this->faker->numberBetween(1, 5),
            'user_balance_change_limit' => 0,
            'theme' => $this->faker->randomElement(['lottopark', 'whitelotto']),
            'us_state_active' => $this->faker->boolean(),
            'enabled_us_states' => null,
            'show_ok_in_welcome_popup' => $this->faker->boolean(),
            'show_categories' => $this->faker->boolean(),
            'last_login',
            'last_active',
            'is_report' => $this->faker->boolean(),
            'user_can_change_group' => $this->faker->boolean(),
            'can_user_select_group_while_register' => $this->faker->boolean(),
            'can_user_register_via_site' => $this->faker->boolean(),
            'can_user_login_via_site' => $this->faker->boolean(),
            'display_deposit_button' => $this->faker->boolean(),
            'max_daily_balance_change_per_user' => 0.0,
            'is_reducing_balance_increases_limits' => $this->faker->boolean(),
            'is_balance_change_global_limit_enabled_in_api' => $this->faker->boolean(80),
            'use_logins_for_users' => $this->faker->boolean(),
            'register_name_surname' => $this->faker->numberBetween(0, 2),
            'register_phone' => $this->faker->numberBetween(0, 2),
            'assert_unique_emails_for_users' => $this->faker->boolean(80),
            'aff_auto_create_on_register' => $this->faker->boolean(20),
        ];
    }

    /**
     * @throws Exception When the fixture creation exceeds the number of the prefixes pool created.
     * By default, the limit is set to 10.
     * Please increase the limit programmatically when it is exceeded, e.g.:
     * $this->container->get(WhitelabelFixture::class)->setPrefixPoolLimit(20)
     */
    public function randomPrefix(): string
    {
        if (empty($this->prefixes)) {
            $limit = $this->prefixPoolLimit;

            for ($i = 0; $i < $limit; $i++) {
                $prefix = mb_strtoupper($this->faker->randomLetter() . $this->faker->randomLetter());

                $isGeneratedPrefixSameAsLottoPark = $prefix === Whitelabel::LOTTOPARK_PREFIX;
                $isPrefixAlreadyAdded = in_array($prefix, $this->prefixes);

                if ($isPrefixAlreadyAdded || $isGeneratedPrefixSameAsLottoPark) {
                    $limit++;
                    continue;
                }

                $this->prefixes[] = $prefix;
            }

            $this->prefixes = array_unique($this->prefixes);
        }

        if (empty($this->prefixes)) {
            return Whitelabel::LOTTOPARK_PREFIX;
        }

        try {
            return $this->faker->unique()->randomElement($this->prefixes);
        } catch (OverflowException) {
            throw new Exception(sprintf(
                'The unique prefix pool limit \'%s\' has been exceeded (%s). Please increase the limit.',
                $this->prefixPoolLimit,
                implode(',', $this->prefixes)
            ));
        }
    }

    public function setPrefixPoolLimit(int $limit): void
    {
        $this->prefixPoolLimit = $limit;
    }

    public static function getClass(): string
    {
        return Whitelabel::class;
    }

    /** @inheritdoc */
    public function getStates(): array
    {
        return [
            self::CURRENCY => $this->reference('currency', CurrencyFixture::class),
            self::BASIC => $this->basic(),
        ];
    }

    private function basic(): callable
    {
        return function (Whitelabel $whitelabel, array $attributes = []): void {
            $whitelabel->currency = $this->fixture(self::CURRENCY)->makeOne();
            // todo: make wl savable by adding relations, mandatory fields etc
            // @see https://trello.com/c/jg6eCOe2
        };
    }
}

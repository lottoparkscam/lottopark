<?php

namespace Tests\Fixtures;

use Models\WhitelabelAff;
use Tests\Fixtures\AbstractFixture;

class WhitelabelAffFixture extends AbstractFixture
{
    public function getDefaults(): array
    {
        return [
            'is_active' => true,
            'is_confirmed' => $this->faker->boolean(),
            'is_accepted' => $this->faker->boolean(),
            'login' => $this->faker->userName(),
            'email' => $this->faker->email(),
            'token' => $this->faker->bothify('#?#???#?##'),
            'sub_affiliate_token' => $this->faker->bothify('#?#???#?##'),
            'hash' => $this->faker->uuid(),
            'salt' => $this->faker->uuid(),
            'password_reset_hash' => $this->faker->uuid(),
            'company' => $this->faker->company(),
            'name' => $this->faker->userName(),
            'surname' => $this->faker->userName(),
            'address1' => $this->faker->address(),
            'address2' => $this->faker->secondaryAddress(),
            'city' => $this->faker->city(),
            'country' => $this->faker->country(),
            'state' => $this->faker->state(),
            'zip' => $this->faker->postcode(),
            'phone_country' => $this->faker->countryCode(),
            'phone' => $this->faker->phoneNumber(),
            'birthdate' => $this->faker->date(),
            'timezone' => $this->faker->timezone(),
            'withdrawal_data' => $this->faker->lexify('???????'),
            'analytics' => $this->faker->bothify('#?#???#?###?#???#?#'),
            'fb_pixel' => $this->faker->bothify('#?#???#?###?#???#?#'),
            'fb_pixel_match' => $this->faker->boolean(70),
            'date_created' => $this->faker->dateTime(),
            'last_ip' => $this->faker->ipv4(),
            'last_country' => $this->faker->country(),
            'last_active' => $this->faker->dateTime(),
            'is_deleted' => false,
            'date_delete' => null,
            'aff_lead_lifetime' => $this->faker->boolean(),
            'is_show_name' => $this->faker->boolean(),
            'hide_lead_id' => $this->faker->boolean(),
            'hide_transaction_id' => $this->faker->boolean(),
            'activation_hash' => $this->faker->uuid(),
            'activation_valid' => $this->faker->dateTime(),
            'resend_hash' => $this->faker->uuid(),
            'resend_last' => $this->faker->dateTime(),
        ];
    }

    public static function getClass(): string
    {
        return WhitelabelAff::class;
    }

    public function getStates(): array
    {
        return [
            self::BASIC => function (WhitelabelAff $model, array $attributes = []) {
            },
        ];
    }
}

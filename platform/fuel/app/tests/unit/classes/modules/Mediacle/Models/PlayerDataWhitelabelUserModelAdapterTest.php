<?php

namespace Unit\Modules\Mediacle\Models;

use Models\Whitelabel;
use Models\WhitelabelUser;
use Models\WhitelabelAff;
use Models\WhitelabelCampaign;
use Models\WhitelabelPlugin;
use Models\WhitelabelPromoCode;
use Models\WhitelabelUserAff;
use Models\WhitelabelUserPromoCode;
use Modules\Mediacle\MediaclePlugin;
use Modules\Mediacle\Models\PlayerDataWhitelabelUserModelAdapter;
use Test_Unit;

class PlayerDataWhitelabelUserModelAdapterTest extends Test_Unit
{
    /** @test */
    public function getFirstName(): void
    {
        // Given
        $expected = 'name';
        $user = new WhitelabelUser(['name' => $expected]);

        // When
        $adapter = new PlayerDataWhitelabelUserModelAdapter($user);
        $actual = $adapter->getFirstName();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getPromoCode__not_exists__returns_null(): void
    {
        // Given
        $expected = null;
        $user = new WhitelabelUser();

        // When
        $adapter = new PlayerDataWhitelabelUserModelAdapter($user);
        $actual = $adapter->getPromoCode();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getPromoCode__exists__returns_promo_token(): void
    {
        // Given
        $expected = 'token123';
        $wlPromoCode = new WhitelabelPromoCode();
        $wlUserPromoCode = new WhitelabelUserPromoCode();
        $wlUserPromoCode->whitelabel_promo_code = $wlPromoCode;
        $wlUserPromoCode->whitelabel_promo_code->whitelabel_campaign = new WhitelabelCampaign(['token' => $expected, 'type' => 2]);
        $user = new WhitelabelUser();
        $user->whitelabel_user_promo_code = $wlUserPromoCode;

        // When
        $adapter = new PlayerDataWhitelabelUserModelAdapter($user);
        $actual = $adapter->getPromoCode();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getPromoCode__exists_no_register__returns_null(): void
    {
        // Given
        $expected = null;
        $wlPromoCode = new WhitelabelPromoCode();
        $wlUserPromoCode = new WhitelabelUserPromoCode();
        $wlUserPromoCode->whitelabel_promo_code = $wlPromoCode;
        $wlUserPromoCode->whitelabel_promo_code->whitelabel_campaign = new WhitelabelCampaign(['token' => $expected]);
        $user = new WhitelabelUser();
        $user->whitelabel_user_promo_code = $wlUserPromoCode;

        // When
        $adapter = new PlayerDataWhitelabelUserModelAdapter($user);
        $actual = $adapter->getPromoCode();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getFirstName__no_name_provided__returns_null(): void
    {
        // Given
        $expected = null;
        $user = new WhitelabelUser();

        // When
        $adapter = new PlayerDataWhitelabelUserModelAdapter($user);
        $actual = $adapter->getFirstName();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getLastName(): void
    {
        // Given
        $expected = 'name';
        $user = new WhitelabelUser(['surname' => $expected]);

        // When
        $adapter = new PlayerDataWhitelabelUserModelAdapter($user);
        $actual = $adapter->getLastName();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getLastName__no_name_provided__returns_null(): void
    {
        // Given
        $expected = null;
        $user = new WhitelabelUser();

        // When
        $adapter = new PlayerDataWhitelabelUserModelAdapter($user);
        $actual = $adapter->getLastName();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getEmail(): void
    {
        // Given
        $expected = 'email';
        $user = new WhitelabelUser(['email' => $expected]);

        // When
        $adapter = new PlayerDataWhitelabelUserModelAdapter($user);
        $actual = $adapter->getEmail();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getTrackingIdentityKey__whitelabel_plugins_exists__returns_first_user_wl_matching_id(): void
    {
        // Given
        $expected = 'key';

        $wlId = 1;

        $user = new WhitelabelUser();
        $user->whitelabel_id = $wlId;

        $mediaclePlugin1 = new WhitelabelPlugin();
        $mediaclePlugin1->whitelabel_id = 123;
        $mediaclePlugin1->plugin = MediaclePlugin::NAME;
        $mediaclePlugin1->options = ['key' => 'fake'];

        $mediaclePlugin2 = new WhitelabelPlugin();
        $mediaclePlugin2->whitelabel_id = $wlId;
        $mediaclePlugin2->plugin = MediaclePlugin::NAME;
        $mediaclePlugin2->options = ['key' => $expected];

        $plugins = [$mediaclePlugin1, $mediaclePlugin2];

        $wl = new Whitelabel();
        $wl->id = $wlId;
        $wl->whitelabel_plugins = $plugins;
        $user->whitelabel = $wl;
        $user->whitelabel_id = $wlId;

        // When
        $adapter = new PlayerDataWhitelabelUserModelAdapter($user);
        $actual = $adapter->getTrackingIdentityKey();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getTrackingId__user_exists__returns_aff_token(): void
    {
        // Given
        $expected = 'email';
        $user = new WhitelabelUser(['referrer_id' => $expected]);
        $user->whitelabel_user_aff = new WhitelabelUserAff([]);
        $user->whitelabel_user_aff->whitelabel_aff = new WhitelabelAff(['token' => $expected]);

        // When
        $adapter = new PlayerDataWhitelabelUserModelAdapter($user);
        $actual = $adapter->getTrackingId();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getTrackingId__user_not_exists__returns_null(): void
    {
        // Given
        $expected = null;
        $user = new WhitelabelUser();

        // When
        $adapter = new PlayerDataWhitelabelUserModelAdapter($user);
        $actual = $adapter->getTrackingId();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getBtag__user_exists__returns_aff_token(): void
    {
        // Given
        $expected = 'btag123asdqwe';
        $user = new WhitelabelUser([]);
        $user->whitelabel_user_aff = new WhitelabelUserAff(['btag' => $expected]);

        // When
        $adapter = new PlayerDataWhitelabelUserModelAdapter($user);
        $actual = $adapter->getBtag();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getBtag__user_not_exists__returns_null(): void
    {
        // Given
        $expected = null;
        $user = new WhitelabelUser();

        // When
        $adapter = new PlayerDataWhitelabelUserModelAdapter($user);
        $actual = $adapter->getBtag();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getPhone__user_not_exists__returns_null(): void
    {
        // Given
        $expected = null;
        $user = new WhitelabelUser();

        // When
        $adapter = new PlayerDataWhitelabelUserModelAdapter($user);
        $actual = $adapter->getPhoneNumber();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getPhone(): void
    {
        // Given
        $expected = '+49123123123';
        $user = new WhitelabelUser(['phone' => $expected]);

        // When
        $adapter = new PlayerDataWhitelabelUserModelAdapter($user);
        $actual = $adapter->getPhoneNumber();

        // Then
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     * @dataProvider companyDataProvider
     */
    public function getCompany($expectedCompany): void
    {
        $userModel = new WhitelabelUser(['company' => $expectedCompany]);

        $adapter = new PlayerDataWhitelabelUserModelAdapter($userModel);
        $actualCompany = $adapter->getCompany();

        $this->assertSame($expectedCompany, $actualCompany);
    }

    public function companyDataProvider(): array
    {
        return array(
            array('My Company'),
            array(''), // No company provided, returns empty
            array(null) // Existing users before introduction of this field
        );
    }
}

<?php

namespace Feature\Helpers;

use Container;
use Helpers\CurrencyHelper;
use Models\WhitelabelUser;
use Test_Feature;
use Tests\Fixtures\WhitelabelUserFixture;

final class CurrencyHelperTest extends Test_Feature
{
    public WhitelabelUser $user;
    public WhitelabelUserFixture $whitelabelUserFixture;
    public function setUp(): void
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $this->whitelabelUserFixture = Container::get(WhitelabelUserFixture::class);
        $this->whitelabelUserFixture->addRandomUser(0, 0);
        /** @var WhitelabelUser $user */
        $user = $this->whitelabelUserFixture->user;
        $this->user = WhitelabelUser::find($user->id);

        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->removeUserFromSession();
        if (!empty($this->user)) {
            $this->user->delete();
        }
    }

    /** @test */
    public function getCurrentCurrency_defaultForSystem(): void
    {
        $expected = 'EUR';
        $actual = CurrencyHelper::getCurrentCurrency()->code;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getCurrentCurrency_defaultForUser(): void
    {
        $this->setUserAsCurrentInSession($this->user);
        $this->user->currency_id = 15;
        $this->user->save();

        $expected = 'BAM';
        $actual = CurrencyHelper::getCurrentCurrency()->code;
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function getCurrencyByCode_ExistingCurrency(): void
    {
        $actual = CurrencyHelper::getCurrencyByCode('USD')->code;

        $expected = 'USD';
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function getCurrencyByCode_NotExistingCurrency(): void
    {
        $this->expectExceptionMessage('Currency "XXX" does not exist.');

        $actual = CurrencyHelper::getCurrencyByCode('XXX');

        $this->assertNull($actual);
    }
}

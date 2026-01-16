<?php

namespace Feature\Classes\Lotto;

use Carbon\Carbon;
use Lotto_Security;
use Repositories\IpLoginTryRepository;
use Test_Feature;

class SecurityTest extends Test_Feature
{
    private IpLoginTryRepository $ipLoginTryRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->ipLoginTryRepository = $this->container->get(IpLoginTryRepository::class);
    }

    /**
     * @dataProvider \Tests\DataProviders\IpDataProvider::ipAddressCases
     * @test
     */
    public function checkIP_FirstLogIn_ShouldInsertLogWithOneCount(string $ip): void
    {
        $_SERVER['HTTP_CF_CONNECTING_IP'] = $ip;

        $isChecked = Lotto_Security::check_IP();
        $result = $this->ipLoginTryRepository->findByIp($ip);

        $this->assertTrue($isChecked);
        $this->assertNotEmpty($result);

        $this->assertSame(1, $result->loginTryCount);
    }

    /**
     * Sixth LogIn is max attempts
     *
     * @dataProvider \Tests\DataProviders\IpDataProvider::ipAddressCases
     * @test
     */
    public function checkIP_SeventhLogIn_ShouldReturnFalseAndNotIncreaseIpLoginTryCount(string $ip): void
    {
        $_SERVER['HTTP_CF_CONNECTING_IP'] = $ip;

        Lotto_Security::check_IP(); // first login
        Lotto_Security::check_IP(); // second login
        Lotto_Security::check_IP(); // third login
        Lotto_Security::check_IP(); // fourth login
        Lotto_Security::check_IP(); // fifth login
        $isSixthChecked = Lotto_Security::check_IP();
        $isSeventhChecked = Lotto_Security::check_IP();

        $result = $this->ipLoginTryRepository->findByIp($ip);

        $this->assertFalse($isSixthChecked);
        $this->assertFalse($isSeventhChecked);
        $this->assertSame(6, $result->loginTryCount);
    }

    /**
     * @dataProvider \Tests\DataProviders\IpDataProvider::ipAddressCases
     * @test
     */
    public function checkIP_SixthLogIn30MinutesAfterLastOne_ShouldResetIpLoginTryCount(string $ip): void
    {
        $_SERVER['HTTP_CF_CONNECTING_IP'] = $ip;

        Lotto_Security::check_IP(); // first login
        Lotto_Security::check_IP(); // second login
        Lotto_Security::check_IP(); // third login
        Lotto_Security::check_IP(); // fourth login
        Lotto_Security::check_IP(); // fifth login
        Lotto_Security::check_IP(); // sixth login

        $ipLoginTry = $this->ipLoginTryRepository->findByIp($ip);
        $ipLoginTry->lastLoginTryAt = Carbon::parse($ipLoginTry->lastLoginTryAt)
            ->subMinutes(30);
        $ipLoginTry->save();

        $isSeventhChecked = Lotto_Security::check_IP();
        $ipLoginTry = $this->ipLoginTryRepository->findByIp($ip);

        $this->assertTrue($isSeventhChecked);
        $this->assertSame(1, $ipLoginTry->loginTryCount);
    }

    /**
     * @dataProvider \Tests\DataProviders\IpDataProvider::ipAddressCases
     * @test
     */
    public function checkIP_SixthLogIn10MinutesAfterLastOne_ShouldReturnFalse(string $ip): void
    {
        $_SERVER['HTTP_CF_CONNECTING_IP'] = $ip;

        Lotto_Security::check_IP(); // first login
        Lotto_Security::check_IP(); // second login
        Lotto_Security::check_IP(); // third login
        Lotto_Security::check_IP(); // fourth login
        Lotto_Security::check_IP(); // fifth login
        Lotto_Security::check_IP(); // sixth login

        $ipLoginTry = $this->ipLoginTryRepository->findByIp($ip);
        $ipLoginTry->lastLoginTryAt = Carbon::parse($ipLoginTry->lastLoginTryAt)
            ->subMinutes(10);
        $ipLoginTry->save();

        $isSeventhChecked = Lotto_Security::check_IP();
        $ipLoginTry = $this->ipLoginTryRepository->findByIp($ip);

        $this->assertFalse($isSeventhChecked);
        $this->assertSame(6, $ipLoginTry->loginTryCount);
    }

    /** @test */
    public function resetIp_IpNotExists_ShouldReturnFalse(): void
    {
        $_SERVER['HTTP_CF_CONNECTING_IP'] = '127.0.0.99';

        $isResetIp = Lotto_Security::reset_IP();

        $this->assertFalse($isResetIp);
    }

    /** @test */
    public function resetIp_IpExists_ShouldResetIpLoginTryCount(): void
    {
        $_SERVER['HTTP_CF_CONNECTING_IP'] = $ip = '127.0.0.99';

        $this->ipLoginTryRepository->insert([
            'ip' => $ip,
            'last_login_try_at' => Carbon::now(),
            'login_try_count' => 4
        ]);

        $isResetIp = Lotto_Security::reset_IP();

        $ipLoginTry = $this->ipLoginTryRepository->findByIp($ip);

        $this->assertTrue($isResetIp);
        $this->assertSame(0, $ipLoginTry->loginTryCount);
    }
}

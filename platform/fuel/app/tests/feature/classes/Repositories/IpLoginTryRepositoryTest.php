<?php

namespace Tests\Feature\Classes\Repositories;

use Models\IpLoginTry;
use Repositories\IpLoginTryRepository;
use Test_Feature;

class IpLoginTryRepositoryTest extends Test_Feature
{
    private const FAKE_DATE = '2022-01-01 12:00:00';

    private IpLoginTryRepository $ipLoginTryRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->ipLoginTryRepository = $this->container->get(IpLoginTryRepository::class);
    }

    private function createIpLoginTry(string $ip): IpLoginTry
    {
        $ipLoginTry = new IpLoginTry();
        $ipLoginTry->ip = $ip;
        $ipLoginTry->lastLoginTryAt = self::FAKE_DATE;
        $ipLoginTry->loginTryCount = 0;
        $ipLoginTry->save();

        return $ipLoginTry;
    }

    /**
     * @dataProvider \Tests\DataProviders\IpDataProvider::ipAddressCases
     * @test
     */
    public function findByIp_VerifyCorrectCases(string $ip): void
    {
        $ipLoginTry = $this->createIpLoginTry($ip);

        $result = $this->ipLoginTryRepository->findByIp($ip);

        $this->assertEquals($ipLoginTry, $result);
    }

    /** @test */
    public function findByIp_NotExistsIp_ShouldReturnNull(): void
    {
        $result = $this->ipLoginTryRepository->findByIp('192.192.192.192');

        $this->assertNull($result);
    }

    /** @test */
    public function updateById_ExistsId_ShouldReturnUpdatedObject(): void
    {
        $ipLoginTry = $this->createIpLoginTry('192.192.192.192');
        $result = $this->ipLoginTryRepository->updateById(
            $ipLoginTry->id,
            1,
            self::FAKE_DATE
        );

        $this->assertSame(1, $result->loginTryCount);
        $this->assertEquals(self::FAKE_DATE, $result->lastLoginTryAt);
    }

    /** @test */
    public function updateById_NotExistsId_ShouldReturnNull(): void
    {
        $result = $this->ipLoginTryRepository->updateById(9999);

        $this->assertNull($result);
    }

    /** @test */
    public function insert_CorrectCredentials_ShouldInsert(): void
    {
        $credentials = [
            'ip' => '192.168.1.1',
            'last_login_try_at' => self::FAKE_DATE,
            'login_try_count' => 0
        ];

        $ipLoginTry = $this->ipLoginTryRepository->insert($credentials);

        $this->assertSame('192.168.1.1', $ipLoginTry->ip);
        $this->assertSame(0, $ipLoginTry->loginTryCount);
        $this->assertEquals(self::FAKE_DATE, $ipLoginTry->lastLoginTryAt);
    }
}

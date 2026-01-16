<?php

namespace Tests\Unit\Classes\Services\SocialMediaConnect;

use Container;
use Core\App;
use Exceptions\SocialMedia\IncorrectAdapterException;
use Exceptions\SocialMedia\WhitelabelDoesNotUseSocialConnectException;
use Models\SocialType;
use Models\WhitelabelSocialApi;
use PHPUnit\Framework\MockObject\MockObject;
use Repositories\WhitelabelSocialApiRepository;
use Services\SocialMediaConnect\AdapterFactory;
use Test_Unit;

class AdapterFactoryTest extends Test_Unit
{
    private AdapterFactory $adapterFactoryUnderTest;
    private WhitelabelSocialApiRepository|MockObject $whitelabelSocialApiRepositoryMock;
    private WhitelabelSocialApi $whitelabelSocialApiStub;
    private string $testDomain;

    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabelSocialApiRepositoryMock = $this->createMock(WhitelabelSocialApiRepository::class);
        $this->appMock = $this->createMock(App::class);
        $this->whitelabelSocialApiStub = new WhitelabelSocialApi();
        $this->adapterFactoryUnderTest = new AdapterFactory(
            $this->whitelabelSocialApiRepositoryMock,
        );
        $this->testDomain = Container::get('domain');
    }

    /** @test */
    public function createAdapter_throwIncorrectSocialAdapterException_socialParameterNotExists(): void
    {
        $this->expectException(IncorrectAdapterException::class);

        $this->adapterFactoryUnderTest->createAdapter('asd');
    }

    /** @test */
    public function getFacebookConfig(): void
    {
        $this->whitelabelSocialApiStub->appId = '123as123aa123';
        $this->whitelabelSocialApiStub->secret = 'asdqwe123asd131ad';
        $this->whitelabelSocialApiStub->isEnabled = true;
        $expected = [
            'callback' => 'https://' . $this->testDomain . '/auth/signup/last-steps/?socialName=' . SocialType::FACEBOOK_TYPE,
            'keys' => ['id' => '123as123aa123', 'secret' => 'asdqwe123asd131ad'],
            ];

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with(SocialType::FACEBOOK_TYPE)
            ->willReturn($this->whitelabelSocialApiStub);

        $result = $this->adapterFactoryUnderTest->getFacebookConfigPerWhitelabel();

        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function getFacebookConfig_throwWhitelabelDoesNotUseSocialConnectException_whitelabelSocialConnectIsNotEnabled(): void
    {
        $this->expectException(WhitelabelDoesNotUseSocialConnectException::class);

        $this->whitelabelSocialApiStub->appId = '123as123aa123';
        $this->whitelabelSocialApiStub->secret = 'asdqwe123asd131ad';
        $this->whitelabelSocialApiStub->isEnabled = false;

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with(SocialType::FACEBOOK_TYPE)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->adapterFactoryUnderTest->getFacebookConfigPerWhitelabel();
    }

    /** @test */
    public function getFacebookConfig_throwWhitelabelDoesNotUseSocialConnectException_whenWhitelabelUseLoginsForUsers(): void
    {
        $this->expectException(WhitelabelDoesNotUseSocialConnectException::class);

        $whitelabel = Container::get('whitelabel');
        $this->whitelabelSocialApiStub->appId = '123as123aa123';
        $this->whitelabelSocialApiStub->secret = 'asdqwe123asd131ad';
        $this->whitelabelSocialApiStub->isEnabled = true;
        $whitelabel->useLoginsForUsers = true;

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with(SocialType::FACEBOOK_TYPE)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->adapterFactoryUnderTest->getFacebookConfigPerWhitelabel();
    }

    /** @test */
    public function getFacebookConfig_WhitelabelDoseNotUseSocialConnect_configNotExists(): void
    {
        $this->expectException(WhitelabelDoesNotUseSocialConnectException::class);

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with(SocialType::FACEBOOK_TYPE)
            ->willReturn(null);

        $this->adapterFactoryUnderTest->getFacebookConfigPerWhitelabel();
    }
}

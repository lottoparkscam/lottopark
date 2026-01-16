<?php

namespace Tests\Unit\Classes\Services\SocialMediaConnect;

use Exceptions\SocialMedia\WhitelabelDoesNotUseSocialConnectException;
use PHPUnit\Framework\MockObject\MockObject;
use Services\SocialMediaConnect\AdapterFactory;
use Services\SocialMediaConnect\PresenterService;
use Test_Unit;

class PresenterServiceTest extends Test_Unit
{
    private AdapterFactory|MockObject $adapterFactoryMock;
    private PresenterService $presenterServiceUnderTest;

    public function setUp(): void
    {
        parent::setUp();
        $this->adapterFactoryMock = $this->createMock(AdapterFactory::class);
        $this->presenterServiceUnderTest = new PresenterService(
            $this->adapterFactoryMock,
        );
    }

    /** @test */
    public function generateSocialConnectSection_socialIsNotEnabled(): void
    {
        $_SERVER['REQUEST_URI'] = 'last-steps';

        $this->adapterFactoryMock->expects($this->once())
            ->method('getFacebookConfigPerWhitelabel')
            ->willThrowException(new WhitelabelDoesNotUseSocialConnectException());

        $results = $this->presenterServiceUnderTest->generateSocialButtonsView();
        $this->assertFalse(str_contains($results, 'facebook'));
    }

    /** @test */
    public function generateSocialConnectSection_withEnableAndDisabledSocials(): void
    {
        $_SERVER['REQUEST_URI'] = 'last-steps';

        $this->adapterFactoryMock->expects($this->once())
            ->method('getFacebookConfigPerWhitelabel')
            ->willThrowException(new WhitelabelDoesNotUseSocialConnectException());

        $this->adapterFactoryMock->expects($this->once())
            ->method('getGoogleConfigPerWhitelabel')
            ->willReturn(['id' => 1, 'secret' => 22]);

        $results = $this->presenterServiceUnderTest->generateSocialButtonsView();
        $this->assertFalse(str_contains($results, 'facebook'));
        $this->assertTrue(str_contains($results, 'google'));
    }


    /** @test */
    public function generateSocialConnectSection_withTwoSocials(): void
    {
        $_SERVER['REQUEST_URI'] = 'last-steps';

        $this->adapterFactoryMock->expects($this->once())
            ->method('getFacebookConfigPerWhitelabel')
            ->willReturn(['id' => 2, 'secret' => 22]);

        $this->adapterFactoryMock->expects($this->once())
            ->method('getGoogleConfigPerWhitelabel')
            ->willReturn(['id' => 1, 'secret' => 22]);

        $results = $this->presenterServiceUnderTest->generateSocialButtonsView();
        $this->assertTrue(str_contains($results, 'facebook'));
        $this->assertTrue(str_contains($results, 'google'));
    }

    /** @test */
    public function generateSocialConnectSection(): void
    {
        $_SERVER['REQUEST_URI'] = 'login';
        $_SERVER['HTTP_HOST'] = 'lottopark.loc';

        $this->adapterFactoryMock->expects($this->once())
            ->method('getFacebookConfigPerWhitelabel')
            ->willReturn(['id' => 1, 'secret' => 22]);

        $results = $this->presenterServiceUnderTest->generateSocialButtonsView();
        $this->assertTrue($this->isHtml($results));
    }

    private function isHtml(string $string): bool
    {
        return preg_match('/<[^<]+>/', $string) != 0;
    }
}

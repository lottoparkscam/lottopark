<?php

namespace Tests\Unit\Classes\Services\SocialMediaConnect;

use Container;
use Exception;
use Exceptions\SocialMedia\IncorrectTypeException;
use Helpers\SocialMediaConnect\LastStepsHelper;
use Hybridauth\Exception\InvalidAuthorizationStateException;
use Models\SocialType;
use Test_Unit;

class LastStepsHelperTest extends Test_Unit
{
    /** @test */
    public function getSocialType_throwException_emptySocialName(): void
    {
        $this->setInput('get', [LastStepsHelper::SOCIAL_NAME_PARAMETER => '']);
        $this->expectException(IncorrectTypeException::class);
        LastStepsHelper::getSocialType();
    }

    /** @test */
    public function getSocialType_throwException_incorrectSocialName(): void
    {
        $this->setInput('get', [LastStepsHelper::SOCIAL_NAME_PARAMETER => 'asdqwe']);
        $this->expectException(IncorrectTypeException::class);
        LastStepsHelper::getSocialType();
    }

    /** @test */
    public function getSocialType_throwException_correctSocialName(): void
    {
        $this->setInput('get', [LastStepsHelper::SOCIAL_NAME_PARAMETER => SocialType::FACEBOOK_TYPE]);
        $result = LastStepsHelper::getSocialType();
        $this->assertSame(SocialType::FACEBOOK_TYPE, $result);
    }

    /** @test */
    public function isLastStepsPage_isNotLastSteps(): void
    {
        $_SERVER['REQUEST_URI'] = 'lasd';
        $this->assertFalse(LastStepsHelper::isLastStepsPage());
    }

    /** @test */
    public function isLastStepsPage(): void
    {
        $_SERVER['REQUEST_URI'] = 'auth/signup/last-steps';
        $this->assertTrue(LastStepsHelper::isLastStepsPage());
    }

    /** @test */
    public function generateLastStepsUrlForSocial(): void
    {
        $result = LastStepsHelper::generateLastStepsUrlPerSocial(SocialType::FACEBOOK_TYPE);

        $testDomain = Container::get('domain');
        $this->assertEquals('https://' . $testDomain . '/auth/signup/last-steps/?socialName=' . SocialType::FACEBOOK_TYPE, $result);
    }

    /**
     * @test
     * @dataProvider oauthErrorProvider
     */
    public function isSocialAccessTokenExpired(Exception $errorMessage, bool $expected): void
    {
        $result = LastStepsHelper::isSocialAccessTokenExpired($errorMessage);

        $this->assertEquals($expected, $result);
    }

    public function oauthErrorProvider(): array
    {
        return [
            [new Exception('ala alala'), false],
            [new Exception('Error validating access token: The user has not authorized application'), true],
            [new InvalidAuthorizationStateException(), true]
        ];
    }
}

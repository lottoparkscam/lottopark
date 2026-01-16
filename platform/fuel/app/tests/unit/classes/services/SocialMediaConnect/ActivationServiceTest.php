<?php

namespace Tests\Unit\Classes\Services\SocialMediaConnect;

use Container;
use Exceptions\SocialMedia\ConfirmLoginException;
use Exceptions\SocialMedia\InvalidActivationHash;
use Exceptions\SocialMedia\InvalidUserTokenException;
use Exceptions\SocialMedia\UserConfirmSocialLoginBeforeException;
use Exceptions\SocialMedia\WhitelabelUserSocialConnectionNotExists;
use Fuel\Core\Session;
use Helpers\FlashMessageHelper;
use Helpers\SocialMediaConnect\LastStepsHelper;
use Models\SocialType;
use Models\Whitelabel;
use Models\WhitelabelSocialApi;
use Models\WhitelabelUser;
use Models\WhitelabelUserSocial;
use PHPUnit\Framework\MockObject\MockObject;
use Repositories\Orm\WhitelabelUserRepository;
use Repositories\WhitelabelSocialApiRepository;
use Repositories\WhitelabelUserSocialRepository;
use Services\Auth\AbstractAuthService;
use Services\Auth\WordpressLoginService;
use Services\RedirectService;
use Services\SocialMediaConnect\ActivationService;
use Services\SocialMediaConnect\MessageHelper;
use Test_Unit;

class ActivationServiceTest extends Test_Unit
{
    private ActivationService $activationServiceUnderTest;
    private WhitelabelUserSocialRepository|MockObject $whitelabelUserSocialRepositoryMock;
    private WhitelabelUserSocial $whitelabelUserSocialStub;
    private WhitelabelUser $whitelabelUserStub;
    private WhitelabelUserRepository|MockObject $whitelabelUserRepositoryMock;
    private RedirectService|MockObject $redirectServiceMock;
    private WhitelabelSocialApiRepository|MockObject $whitelabelSocialApiRepositoryMock;
    private WhitelabelSocialApi $whitelabelSocialApiStub;
    private WordpressLoginService $wordpressLoginService;
    public function setUp(): void
    {
        parent::setUp();
        $this->redirectServiceMock = $this->createMock(RedirectService::class);
        $this->activationServiceMock = $this->createMock(ActivationService::class);
        $this->whitelabelUserSocialRepositoryMock = $this->getMockBuilder(WhitelabelUserSocialRepository::class)
            ->addMethods(['findOneById'])
            ->onlyMethods([
                'updateHash',
                'findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId',
                'confirmSocialLogin',
                'removeUnusedHashAndHashSentDate'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->whitelabelUserRepositoryMock = $this->getMockBuilder(WhitelabelUserRepository::class)
            ->onlyMethods(['findByTokenAndWhitelabelId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->activationServiceMock = $this->createMock(ActivationService::class);
        $this->whitelabelSocialApiRepositoryMock = $this->createMock(WhitelabelSocialApiRepository::class);
        $this->wordpressLoginService = $this->createMock(WordpressLoginService::class);
        $this->whitelabelSocialApiStub = new WhitelabelSocialApi();
        $this->whitelabelUserSocialStub = new WhitelabelUserSocial();
        $this->whitelabelUserStub = new WhitelabelUser();
        $this->activationServiceUnderTest = new ActivationService(
            $this->whitelabelUserSocialRepositoryMock,
            $this->whitelabelUserRepositoryMock,
            $this->redirectServiceMock,
            $this->whitelabelSocialApiRepositoryMock,
            $this->wordpressLoginService,
        );

        $this->whitelabelSocialApiStub->id = 9999;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Session::delete('is_user');
    }

    /** @test */
    public function isNotSocialActivation(): void
    {
        $result = $this->activationServiceUnderTest->isSocialActivation();
        $this->assertFalse($result);
    }

    /** @test */
    public function isSocialActivation(): void
    {
        $this->setInput('get', [LastStepsHelper::SOCIAL_NAME_PARAMETER => SocialType::FACEBOOK_TYPE]);
        $result = $this->activationServiceUnderTest->isSocialActivation();

        $this->assertTrue($result);
    }

    /** @test */
    public function setNewActivationHashPerSocialUser_HashIsInactive(): void
    {
        $this->whitelabelUserSocialStub->id = 999999;
        $this->whitelabelUserStub->salt = '2c03ccac7b0353eed87d516f5960bfe1acf50ce39ebbb5e83cf9eeb61fe55bab81291bc9f14fdd3a319ac91df09282766c3323869f8b715e09bdc916f35286f4';
        $this->whitelabelUserSocialStub->lastHashSentAt = '2022-12-02 10:30:43';

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findOneById')
            ->with($this->whitelabelUserSocialStub->id)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('updateHash');

        $this->activationServiceUnderTest->setNewActivationHashPerSocialUser($this->whitelabelUserSocialStub->id, $this->whitelabelUserStub->salt);
    }

    /** @test */
    public function setNewActivationHashPerSocialUser_WhitelabelUserSocialNotExists(): void
    {
        $this->whitelabelUserSocialStub->id = 1;
        $this->whitelabelUserStub->salt = '2c03ccac7b0353eed87d516f5960bfe1acf50ce39ebbb5e83cf9eeb61fe55bab81291bc9f14fdd3a319ac91df09282766c3323869f8b715e09bdc916f35286f4';

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findOneById')
            ->with($this->whitelabelUserSocialStub->id)
            ->willReturn(null);

        $this->whitelabelUserSocialRepositoryMock->expects($this->never())
            ->method('updateHash');

        $this->activationServiceUnderTest->setNewActivationHashPerSocialUser($this->whitelabelUserSocialStub->id, $this->whitelabelUserStub->salt);
    }

    /** @test */
    public function setNewActivationHashPerSocialUser_HashSendDateNotExists(): void
    {
        $this->whitelabelUserSocialStub->id = 999999;
        $this->whitelabelUserStub->salt = '2c03ccac7b0353eed87d516f5960bfe1acf50ce39ebbb5e83cf9eeb61fe55bab81291bc9f14fdd3a319ac91df09282766c3323869f8b715e09bdc916f35286f4';
        $this->whitelabelUserSocialStub->lastHashSentAt = null;

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findOneById')
            ->with($this->whitelabelUserSocialStub->id)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->never())
            ->method('updateHash');

        $this->activationServiceUnderTest->setNewActivationHashPerSocialUser($this->whitelabelUserSocialStub->id, $this->whitelabelUserStub->salt);
    }

    /** @test */
    public function getUserTokenFromRequestUri(): void
    {
        $_SERVER['REQUEST_URI'] = 'activation/305944933/69a239f6ba5c31f29b389fe6630ddfa93bbda6dcbcf27a1e53e4067b1cdf6b7d?socialName=' . SocialType::FACEBOOK_TYPE;

        $result = $this->activationServiceUnderTest->getUserToken();
        $this->assertEquals('305944933', $result);
    }

    /** @test */
    public function getUserActivationHashFromRequestUri(): void
    {
        $_SERVER['REQUEST_URI'] = 'activation/305944933/69a239f6ba5c31f29b389fe6630ddfa93bbda6dcbcf27a1e53e4067b1cdf6b7d?socialName=' . SocialType::FACEBOOK_TYPE;

        $result = $this->activationServiceUnderTest->getUserActivationHash();
        $this->assertEquals('69a239f6ba5c31f29b389fe6630ddfa93bbda6dcbcf27a1e53e4067b1cdf6b7d', $result);
    }

    /** @test */
    public function activateSocialLogin_throwConfirmSocialLoginException(): void
    {
        $this->expectException(ConfirmLoginException::class);

        $userId = 9123;
        $socialUserActivationHash = '123asdq3aqd1223asd1223qa9fdua7y629ht9oas9fr8y362495yrh9qhfr92y345w9f9235yt9';
        $userToken = '305944933';
        $this->whitelabelUserStub->id = $userId;
        $this->whitelabelUserSocialStub->whitelabelUserId = $userId;
        $this->whitelabelUserSocialStub->isConfirmed = false;
        $this->whitelabelUserSocialStub->activationHash = $socialUserActivationHash;
        $socialType = SocialType::FACEBOOK_TYPE;
        $_SERVER['REQUEST_URI'] = 'activation/' . $userToken . '/' . $socialUserActivationHash . '?socialName=' . SocialType::FACEBOOK_TYPE;

        $this->whitelabelUserRepositoryMock->expects($this->once())
            ->method('findByTokenAndWhitelabelId')
            ->with($userToken)
            ->willReturn($this->whitelabelUserStub);

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId')
            ->with($userId, $this->whitelabelSocialApiStub->id)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->activationServiceUnderTest->activateSocialLogin($socialType);
    }

    /** @test */
    public function activateSocialLogin_throwInvalidUserTokenException(): void
    {
        $this->expectException(InvalidUserTokenException::class);

        $userToken = 'asdqwe';
        $_SERVER['REQUEST_URI'] = 'activation/' . $userToken . '/123asdq3aqd1223asd1223qa9fdua7y629ht9oas9fr8y362495yrh9qhfr92y345w9f9235yt9?socialName=' . SocialType::FACEBOOK_TYPE;
        $_GET['socialName'] = SocialType::FACEBOOK_TYPE;

        $this->whitelabelUserRepositoryMock->expects($this->once())
            ->method('findByTokenAndWhitelabelId')
            ->with($userToken)
            ->willReturn(null);

        $this->activationServiceUnderTest->activateSocialLogin(SocialType::FACEBOOK_TYPE);
    }

    /** @test */
    public function activateSocialLogin_userSocialNotExists_throwWhitelabelUserSocialConnectionNotExistsException(): void
    {
        $this->expectException(WhitelabelUserSocialConnectionNotExists::class);

        $userId = 9123;
        $socialUserActivationHash = '123asdq3aqd1223asd1223qa9fdua7y629ht9oas9fr8y362495yrh9qhfr92y345w9f9235yt9';
        $userToken = '305944933';
        $this->whitelabelUserStub->id = $userId;
        $this->whitelabelUserSocialStub->whitelabelUserId = $userId;
        $this->whitelabelUserSocialStub->isConfirmed = true;
        $this->whitelabelUserSocialStub->activationHash = $socialUserActivationHash;
        $socialType = SocialType::FACEBOOK_TYPE;
        $_SERVER['REQUEST_URI'] = 'activation/' . $userToken . '/' . $socialUserActivationHash . '?socialName=' . $socialType;
        $_GET['socialName'] = $socialType;

        $this->whitelabelUserRepositoryMock->expects($this->once())
            ->method('findByTokenAndWhitelabelId')
            ->with($userToken)
            ->willReturn($this->whitelabelUserStub);

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId')
            ->with($userId, $this->whitelabelSocialApiStub->id)
            ->willReturn(null);

        $this->activationServiceUnderTest->activateSocialLogin(SocialType::FACEBOOK_TYPE);
    }

    /** @test */
    public function startSocialAccountActivation_WhitelabelSocialUserNotExists_showErrorMessage(): void
    {
        $socialType = SocialType::FACEBOOK_TYPE;
        $this->setInput('GET', [LastStepsHelper::SOCIAL_NAME_PARAMETER => $socialType]);
        $userId = 9123;
        $socialUserActivationHash = '123asdq3aqd1223asd1223qa9fdua7y629ht9oas9fr8y362495yrh9qhfr92y345w9f9235yt9';
        $userToken = '305944933';
        $this->whitelabelUserStub->id = $userId;
        $_SERVER['REQUEST_URI'] = 'activation/' . $userToken . '/' . $socialUserActivationHash . '?socialName=' . $socialType;

        $this->whitelabelUserRepositoryMock->expects($this->once())
            ->method('findByTokenAndWhitelabelId')
            ->with($userToken)
            ->willReturn($this->whitelabelUserStub);

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId')
            ->with($userId, $this->whitelabelSocialApiStub->id)
            ->willReturn(null);

        $this->activationServiceUnderTest->startSocialAccountActivation();
        $this->assertEquals(FlashMessageHelper::getLast(), MessageHelper::getTranslatedActivationIncorrectLink());
    }

    /** @test */
    public function activateSocialLogin_userConfirmSocialLoginBefore_throwUserConfirmSocialLoginBeforeException(): void
    {
        $this->expectException(UserConfirmSocialLoginBeforeException::class);

        $userId = 9123;
        $socialUserActivationHash = '123asdq3aqd1223asd1223qa9fdua7y629ht9oas9fr8y362495yrh9qhfr92y345w9f9235yt9';
        $userToken = '305944933';
        $this->whitelabelUserStub->id = $userId;
        $this->whitelabelUserSocialStub->whitelabelUserId = $userId;
        $this->whitelabelUserSocialStub->isConfirmed = true;
        $this->whitelabelUserSocialStub->activationHash = $socialUserActivationHash;
        $socialType = SocialType::FACEBOOK_TYPE;
        $_SERVER['REQUEST_URI'] = 'activation/' . $userToken . '/' . $socialUserActivationHash . '?socialName=' . $socialType;
        $_GET['socialName'] = $socialType;

        $this->whitelabelUserRepositoryMock->expects($this->once())
            ->method('findByTokenAndWhitelabelId')
            ->with($userToken)
            ->willReturn($this->whitelabelUserStub);

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId')
            ->with($userId, $this->whitelabelSocialApiStub->id)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->activationServiceUnderTest->activateSocialLogin(SocialType::FACEBOOK_TYPE);
    }

    /** @test */
    public function startSocialAccountActivation_userConfirmSocialLoginBefore_showErrorMessageForLoggedInUser(): void
    {
        Session::set('is_user', true);
        $socialType = SocialType::FACEBOOK_TYPE;
        $this->setInput('GET', [LastStepsHelper::SOCIAL_NAME_PARAMETER => $socialType]);
        $userId = 9123;
        $socialUserActivationHash = '123asdq3aqd1223asd1223qa9fdua7y629ht9oas9fr8y362495yrh9qhfr92y345w9f9235yt9';
        $userToken = '305944933';
        $this->whitelabelUserStub->id = $userId;
        $this->whitelabelUserSocialStub->whitelabelUserId = $userId;
        $this->whitelabelUserSocialStub->isConfirmed = true;
        $this->whitelabelUserSocialStub->activationHash = $socialUserActivationHash;
        $_SERVER['REQUEST_URI'] = 'activation/' . $userToken . '/' . $socialUserActivationHash . '?socialName=' . $socialType;

        $this->whitelabelUserRepositoryMock->expects($this->once())
            ->method('findByTokenAndWhitelabelId')
            ->with($userToken)
            ->willReturn($this->whitelabelUserStub);

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId')
            ->with($userId, $this->whitelabelSocialApiStub->id)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->activationServiceUnderTest->startSocialAccountActivation();
        $this->assertEquals(MessageHelper::getTranslatedAccountHasBeenActivatedBeforeLoggedInUser(), FlashMessageHelper::getLast());
    }

    /** @test */
    public function activateSocialLogin_userConfirmSocialLoginBefore_throwUserConfirmSocialLoginBeforeException_LoggedUser(): void
    {
        $this->expectException(UserConfirmSocialLoginBeforeException::class);

        $userId = 9123;
        $socialUserActivationHash = '123asdq3aqd1223asd1223qa9fdua7y629ht9oas9fr8y362495yrh9qhfr92y345w9f9235yt9';
        $userToken = '305944933';
        $this->whitelabelUserStub->id = $userId;
        $this->whitelabelUserSocialStub->whitelabelUserId = $userId;
        $this->whitelabelUserSocialStub->isConfirmed = true;
        $this->whitelabelUserSocialStub->activationHash = $socialUserActivationHash;
        $socialType = SocialType::FACEBOOK_TYPE;
        $_SERVER['REQUEST_URI'] = 'activation/' . $userToken . '/' . $socialUserActivationHash . '?socialName=' . $socialType;
        $_GET['socialName'] = $socialType;

        $this->whitelabelUserRepositoryMock->expects($this->once())
            ->method('findByTokenAndWhitelabelId')
            ->with($userToken)
            ->willReturn($this->whitelabelUserStub);

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId')
            ->with($userId, $this->whitelabelSocialApiStub->id)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->activationServiceUnderTest->activateSocialLogin(SocialType::FACEBOOK_TYPE);
    }

    /** @test */
    public function startSocialAccountActivation_userConfirmSocialLoginBefore_showErrorMessageForLoggedOutUser(): void
    {
        $socialType = SocialType::FACEBOOK_TYPE;
        $this->setInput('GET', [LastStepsHelper::SOCIAL_NAME_PARAMETER => $socialType]);
        $userId = 9123;
        $socialUserActivationHash = '123asdq3aqd1223asd1223qa9fdua7y629ht9oas9fr8y362495yrh9qhfr92y345w9f9235yt9';
        $userToken = '305944933';
        $this->whitelabelUserStub->id = $userId;
        $this->whitelabelUserSocialStub->whitelabelUserId = $userId;
        $this->whitelabelUserSocialStub->isConfirmed = true;
        $this->whitelabelUserSocialStub->activationHash = $socialUserActivationHash;
        $_SERVER['REQUEST_URI'] = 'activation/' . $userToken . '/' . $socialUserActivationHash . '?socialName=' . $socialType;

        $this->whitelabelUserRepositoryMock->expects($this->once())
            ->method('findByTokenAndWhitelabelId')
            ->with($userToken)
            ->willReturn($this->whitelabelUserStub);

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId')
            ->with($userId, $this->whitelabelSocialApiStub->id)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->activationServiceUnderTest->startSocialAccountActivation();
        $this->assertEquals(MessageHelper::getTranslatedAccountHasBeenActivatedBefore(), FlashMessageHelper::getLast());
    }

    /** @test */
    public function activateSocialLogin_userConfirmSocialLoginBefore_throwConfirmSocialLoginException(): void
    {
        $this->expectException(InvalidActivationHash::class);

        $userId = 9123;
        $socialUserActivationHash = '123asdq3aqd1223asd1223qa9fdua7y629ht9oas9fr8y362495yrh9qhfr92y345w9f9235yt9';
        $userToken = '305944933';
        $this->whitelabelUserStub->id = $userId;
        $this->whitelabelUserSocialStub->whitelabelUserId = $userId;
        $this->whitelabelUserSocialStub->isConfirmed = false;
        $this->whitelabelUserSocialStub->activationHash = 'asqw';
        $socialType = SocialType::FACEBOOK_TYPE;
        $_SERVER['REQUEST_URI'] = 'activation/' . $userToken . '/' . $socialUserActivationHash . '?socialName=' . $socialType;
        $_GET['socialName'] = $socialType;

        $this->whitelabelUserRepositoryMock->expects($this->once())
            ->method('findByTokenAndWhitelabelId')
            ->with($userToken)
            ->willReturn($this->whitelabelUserStub);

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId')
            ->with($userId, $this->whitelabelSocialApiStub->id)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->activationServiceUnderTest->activateSocialLogin(SocialType::FACEBOOK_TYPE);
    }

    /** @test */
    public function startSocialLoginActivation_confirmSocialLogin(): void
    {
        $userId = 9123;
        $socialUserActivationHash = '123asdq3aqd1223asd1223qa9fdua7y629ht9oas9fr8y362495yrh9qhfr92y345w9f9235yt9';
        $userToken = '305944933';
        $this->whitelabelUserStub->isActive = true;
        $this->whitelabelUserStub->id = $userId;
        $this->whitelabelUserStub->token = $userToken;
        $this->whitelabelUserStub->hash = 'asdqweqdadqwdqdas';
        $this->whitelabelUserStub->email = 'a@test.pl';
        $this->whitelabelUserSocialStub->whitelabelUserId = $userId;
        $this->whitelabelUserSocialStub->isConfirmed = false;
        $this->whitelabelUserSocialStub->activationHash = $socialUserActivationHash;
        $_SERVER['REQUEST_URI'] = 'activation/' . $userToken . '/' . $socialUserActivationHash . '?socialName=' . SocialType::FACEBOOK_TYPE;
        $this->setInput('get', [LastStepsHelper::SOCIAL_NAME_PARAMETER => SocialType::FACEBOOK_TYPE]);

        $this->whitelabelUserRepositoryMock->expects($this->any())
            ->method('findByTokenAndWhitelabelId')
            ->with($userToken)
            ->willReturn($this->whitelabelUserStub);

        $this->whitelabelSocialApiRepositoryMock->expects($this->exactly(2))
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with(SocialType::FACEBOOK_TYPE)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId')
            ->with($userId, $this->whitelabelSocialApiStub->id)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('confirmSocialLogin')
            ->with($userId, $this->whitelabelSocialApiStub->id);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('removeUnusedHashAndHashSentDate')
            ->with($userId, $this->whitelabelSocialApiStub->id);

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToHomePage');

        $this->activationServiceUnderTest->startSocialAccountActivation();

        $this->assertSame(FlashMessageHelper::getLast(), MessageHelper::getTranslatedSucceedConfirmedMail());
        $this->assertTrue(Session::get('is_user'));
    }

    /** @test */
    public function startSocialLoginActivation_confirmSocialLogin_AccountNotActive(): void
    {
        $userId = 9123;
        $socialUserActivationHash = '123asdq3aqd1223asd1223qa9fdua7y629ht9oas9fr8y362495yrh9qhfr92y345w9f9235yt9';
        $userToken = '305944933';
        $this->whitelabelUserStub->id = $userId;
        $this->whitelabelUserStub->token = $userToken;
        $this->whitelabelUserStub->hash = 'asdqweqdadqwdqdas';
        $this->whitelabelUserStub->email = 'a@test.pl';
        $this->whitelabelUserSocialStub->whitelabelUserId = $userId;
        $this->whitelabelUserSocialStub->isConfirmed = false;
        $this->whitelabelUserSocialStub->activationHash = $socialUserActivationHash;
        $_SERVER['REQUEST_URI'] = 'activation/' . $userToken . '/' . $socialUserActivationHash . '?socialName=' . SocialType::FACEBOOK_TYPE;
        $this->setInput('get', [LastStepsHelper::SOCIAL_NAME_PARAMETER => SocialType::FACEBOOK_TYPE]);

        $this->whitelabelUserRepositoryMock->expects($this->any())
            ->method('findByTokenAndWhitelabelId')
            ->with($userToken)
            ->willReturn($this->whitelabelUserStub);

        $this->whitelabelSocialApiRepositoryMock->expects($this->exactly(2))
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with(SocialType::FACEBOOK_TYPE)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId')
            ->with($userId, $this->whitelabelSocialApiStub->id)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('confirmSocialLogin')
            ->with($userId, $this->whitelabelSocialApiStub->id);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('removeUnusedHashAndHashSentDate')
            ->with($userId, $this->whitelabelSocialApiStub->id);

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToSignUpPage');

        $this->activationServiceUnderTest->startSocialAccountActivation();

        $this->assertSame(FlashMessageHelper::getLast(), AbstractAuthService::MESSAGES['notActiveAccount']);
        $this->assertEmpty(Session::get('is_user'));
    }

    /** @test */
    public function startSocialLoginActivation_confirmSocialLogin_ActivationRequired(): void
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $whitelabel->userActivationType = \Helpers_General::ACTIVATION_TYPE_REQUIRED;
        $userId = 9123;
        $socialUserActivationHash = '123asdq3aqd1223asd1223qa9fdua7y629ht9oas9fr8y362495yrh9qhfr92y345w9f9235yt9';
        $userToken = '305944933';
        $this->whitelabelUserStub->id = $userId;
        $this->whitelabelUserStub->token = $userToken;
        $this->whitelabelUserStub->hash = 'asdqweqdadqwdqdas';
        $this->whitelabelUserStub->email = 'a@test.pl';
        $this->whitelabelUserSocialStub->whitelabelUserId = $userId;
        $this->whitelabelUserSocialStub->isConfirmed = false;
        $this->whitelabelUserSocialStub->activationHash = $socialUserActivationHash;
        $_SERVER['REQUEST_URI'] = 'activation/' . $userToken . '/' . $socialUserActivationHash . '?socialName=' . SocialType::FACEBOOK_TYPE;
        $this->setInput('get', [LastStepsHelper::SOCIAL_NAME_PARAMETER => SocialType::FACEBOOK_TYPE]);
        $fakeResendLink = 'test.pl';

        $this->whitelabelUserRepositoryMock->expects($this->any())
            ->method('findByTokenAndWhitelabelId')
            ->with($userToken)
            ->willReturn($this->whitelabelUserStub);

        $this->whitelabelSocialApiRepositoryMock->expects($this->exactly(2))
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with(SocialType::FACEBOOK_TYPE)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId')
            ->with($userId, $this->whitelabelSocialApiStub->id)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('confirmSocialLogin')
            ->with($userId, $this->whitelabelSocialApiStub->id);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('removeUnusedHashAndHashSentDate')
            ->with($userId, $this->whitelabelSocialApiStub->id);

        $this->wordpressLoginService->expects($this->once())
            ->method('getResendLink')
            ->willReturn($fakeResendLink);

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToLoginPage');

        $this->activationServiceUnderTest->startSocialAccountActivation();

        $this->assertEquals(sprintf(_(AbstractAuthService::MESSAGES['activationLink']), $fakeResendLink), FlashMessageHelper::getLast());
        $this->assertEmpty(Session::get('is_user'));
        $this->assertNotEmpty(FlashMessageHelper::getLast());
    }

    /** @test */
    public function startSocialLoginActivation_incorrectHash(): void
    {
        $userId = 9123;
        $socialUserActivationHash = '123asdq3aqd1223asd1223qa9fdua7y629ht9oas9fr8y362495yrh9qhfr92y345w9f9235yt9';
        $userToken = '305944933';
        $this->whitelabelUserStub->id = $userId;
        $this->whitelabelUserSocialStub->whitelabelUserId = $userId;
        $this->whitelabelUserSocialStub->isConfirmed = false;
        $this->whitelabelUserSocialStub->activationHash = 'asdqwe';
        $_SERVER['REQUEST_URI'] = 'activation/' . $userToken . '/' . $socialUserActivationHash . '?socialName=' . SocialType::FACEBOOK_TYPE;
        $this->setInput('get', [LastStepsHelper::SOCIAL_NAME_PARAMETER => SocialType::FACEBOOK_TYPE]);

        $this->whitelabelUserRepositoryMock->expects($this->any())
            ->method('findByTokenAndWhitelabelId')
            ->with($userToken)
            ->willReturn($this->whitelabelUserStub);

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with(SocialType::FACEBOOK_TYPE)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId')
            ->with($userId, $this->whitelabelSocialApiStub->id)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->never())
            ->method('confirmSocialLogin');

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToHomePage');

        $this->activationServiceUnderTest->startSocialAccountActivation();

        $this->assertSame(FlashMessageHelper::getLast(), MessageHelper::getTranslatedActivationIncorrectLink());
        $this->assertEmpty(Session::get('is_user'));
    }

    /** @test */
    public function startSocialLoginActivation_incorrectToken(): void
    {
        $socialUserActivationHash = '123asdq3aqd1223asd1223qa9fdua7y629ht9oas9fr8y362495yrh9qhfr92y345w9f9235yt9';
        $userToken = '305944933';
        $socialType = SocialType::FACEBOOK_TYPE;
        $_SERVER['REQUEST_URI'] = 'activation/' . $userToken . '/' . $socialUserActivationHash . '?socialName=' . SocialType::FACEBOOK_TYPE;
        $_GET['socialName'] = $socialType;

        $this->whitelabelUserRepositoryMock->expects($this->any())
            ->method('findByTokenAndWhitelabelId')
            ->with($userToken)
            ->willReturn(null);

        $this->whitelabelUserSocialRepositoryMock->expects($this->never())
            ->method('findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId');

        $this->whitelabelUserSocialRepositoryMock->expects($this->never())
            ->method('confirmSocialLogin');

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToHomePage');

        $this->activationServiceUnderTest->startSocialAccountActivation();

        $this->assertSame(FlashMessageHelper::getLast(), MessageHelper::getTranslatedActivationIncorrectLink());
        $this->assertEmpty(Session::get('is_user'));
    }
}

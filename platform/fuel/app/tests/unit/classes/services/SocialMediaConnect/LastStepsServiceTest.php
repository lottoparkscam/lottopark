<?php

namespace Tests\Unit\Classes\Services\SocialMediaConnect;

use Carbon\Carbon;
use Container;
use Exception;
use Exceptions\SocialMedia\FillRegisterFormException;
use Exceptions\SocialMedia\UserProfileWithEmptyEmailException;
use Exceptions\SocialMedia\UserIsCorrectlyConnectedException;
use Exceptions\SocialMedia\UserIsNotConnectedOrIsDeletedException;
use Exceptions\SocialMedia\SocialUserEmailEqualsEmailWhichHaveSocialConnectionException;
use Fuel\Core\Session;
use Helpers\FlashMessageHelper;
use Helpers\SocialMediaConnect\ConnectHelper;
use Helpers\SocialMediaConnect\ProfileHelper;
use Helpers_General;
use Helpers_Time;
use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception\AuthorizationDeniedException;
use Hybridauth\Exception\HttpClientFailureException;
use Hybridauth\Exception\NotImplementedException;
use Hybridauth\User\Profile;
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
use Services\CartService;
use Services\Logs\FileLoggerService;
use Services\RedirectService;
use Services\SocialMediaConnect\ActivationService;
use Services\SocialMediaConnect\ConfirmMailerService;
use Services\SocialMediaConnect\ConnectService;
use Services\SocialMediaConnect\FormService;
use Services\SocialMediaConnect\LastStepsService;
use Services\SocialMediaConnect\MessageHelper;
use Services\SocialMediaConnect\SessionService;
use Test_Unit;

class LastStepsServiceTest extends Test_Unit
{
    private LastStepsService $lastStepsServiceUnderTest;
    private ConnectService|MockObject $connectServiceMock;
    private FileLoggerService|MockObject $fileLoggerServiceMock;
    private Profile $profileStub;
    private OAuth2|MockObject $oauth2Mock;
    private WhitelabelUserRepository|MockObject $whitelabelUserRepositoryMock;
    private WhitelabelUserSocialRepository|MockObject $whitelabelUserSocialRepositoryMock;
    private WhitelabelUserSocial $whitelabelUserSocialStub;
    private WhitelabelUser $whitelabelUserStub;
    private RedirectService $redirectServiceMock;
    private ConfirmMailerService|MockObject $confirmedMailerServiceMock;
    private ActivationService|MockObject $activationHashGeneratorServiceMock;
    private Carbon $carbon;
    private WhitelabelSocialApiRepository|MockObject $whitelabelSocialApiRepositoryMock;
    private WhitelabelSocialApi $whitelabelSocialApiStub;
    private WordpressLoginService|MockObject $wordpressLoginServiceMock;
    private WhitelabelSocialApi|MockObject $whitelabelSocialApiModelMock;
    private SessionService $sessionService;
    private FormService $formServiceMock;
    public CartService $cartServiceMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->carbon = $this->createMock(Carbon::class);
        $this->whitelabelUserRepositoryMock = $this->getMockBuilder(WhitelabelUserRepository::class)
            ->addMethods(['findOneById'])
            ->onlyMethods(['findUserByEmailAndWhitelabelId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectServiceMock = $this->createMock(ConnectService::class);
        $this->oauth2Mock = $this->createMock(OAuth2::class);
        $this->fileLoggerServiceMock = $this->createMock(FileLoggerService::class);
        $this->whitelabelUserSocialRepositoryMock = $this->createMock(WhitelabelUserSocialRepository::class);
        $this->redirectServiceMock = $this->createMock(RedirectService::class);
        $this->confirmedMailerServiceMock = $this->createMock(ConfirmMailerService::class);
        $this->activationHashGeneratorServiceMock = $this->createMock(ActivationService::class);
        $this->whitelabelSocialApiRepositoryMock = $this->createMock(WhitelabelSocialApiRepository::class);
        $this->wordpressLoginServiceMock = $this->createMock(WordpressLoginService::class);
        $this->formServiceMock = $this->createMock(FormService::class);
        $this->whitelabelSocialApiModelMock = $this->getMockBuilder(WhitelabelSocialApi::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionService = $this->createMock(SessionService::class);
        $this->whitelabelSocialApiStub = new WhitelabelSocialApi();
        $this->profileStub = new Profile();
        $this->whitelabelUserSocialStub = new WhitelabelUserSocial();
        $this->whitelabelUserStub = new WhitelabelUser();

        Session::delete("order");
        $this->cartServiceMock = $this->createMock(CartService::class);
        $this->cartServiceMock->method('getCart')->willReturn([]);

        $this->lastStepsServiceUnderTest = new LastStepsService(
            $this->connectServiceMock,
            $this->fileLoggerServiceMock,
            $this->oauth2Mock,
            $this->whitelabelUserRepositoryMock,
            $this->whitelabelUserSocialRepositoryMock,
            $this->redirectServiceMock,
            $this->confirmedMailerServiceMock,
            $this->activationHashGeneratorServiceMock,
            $this->carbon,
            $this->whitelabelSocialApiRepositoryMock,
            $this->wordpressLoginServiceMock,
            $this->sessionService,
            $this->formServiceMock,
            $this->cartServiceMock
        );
        $this->whitelabelSocialApiStub->id = 1;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Session::set('is_user');
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowUserIsCorrectlyConnectedException_withoutEmail_loginUser(): void
    {
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $this->whitelabelUserSocialStub->isConfirmed = true;
        $this->whitelabelUserStub->email = 'test@test.pl';
        $this->whitelabelUserStub->isActive = true;
        $userId = 99999;
        $this->whitelabelUserStub->id = $userId;
        $this->whitelabelUserSocialStub->whitelabelUserId = $userId;
        $this->whitelabelUserStub->hash = 'tsadasdqw3412312aqsd1234';
        $this->whitelabelUserStub->token = 'ASD3ADS';
        $this->whitelabelUserSocialStub->socialUserId = $this->profileStub->identifier;
        $this->whitelabelUserSocialStub->whitelabelUser = $this->whitelabelUserStub;
        $this->profileStub->email = null;
        $socialType = get_class($this->oauth2Mock);

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new UserIsCorrectlyConnectedException());

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByUserSocialIdAndWhitelabelSocialAppId')
            ->with($this->profileStub->identifier, $this->whitelabelSocialApiStub->id)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToHomePage');

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();

        $this->assertEquals(Session::get('user.email'), $this->whitelabelUserStub->email);
        $this->assertEquals(Session::get('user.hash'), $this->whitelabelUserStub->hash);
        $this->assertEquals(Session::get('user.id'), $this->whitelabelUserStub->id);
        $this->assertEquals(Session::get('user.token'), $this->whitelabelUserStub->token);
        $this->assertTrue(Session::get('is_user'));
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowUserIsCorrectlyConnectedException_showPopUpMessage(): void
    {
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $this->whitelabelUserSocialStub->isConfirmed = false;
        $this->whitelabelUserStub->email = 'test@test.pl';
        $userId = 99999;
        $this->whitelabelUserStub->id = $userId;
        $this->whitelabelUserSocialStub->whitelabelUserId = $userId;
        $this->whitelabelUserStub->hash = 'tsadasdqw3412312aqsd1234';
        $this->whitelabelUserStub->token = 'ASD3ADS';
        $this->whitelabelUserSocialStub->socialUserId = $this->profileStub->identifier;
        $this->whitelabelUserSocialStub->whitelabelUser = $this->whitelabelUserStub;
        $socialType = get_class($this->oauth2Mock);

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new UserIsCorrectlyConnectedException());

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByUserSocialIdAndWhitelabelSocialAppId')
            ->with($this->profileStub->identifier, $this->whitelabelSocialApiStub->id)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToSignUpPage');

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();

        $this->assertEquals(FlashMessageHelper::getLast(), MessageHelper::getTranslatedActivationConfirmEmailBeforeDayPassed());
        $this->assertEmpty(Session::get('is_user'));
    }


    /** @test */
    public function startSocialAccountIntegration_ConnectThrowUserIsCorrectlyConnectedException_SocialConnectionIsNotConfirmed_sendConfirmEmail(): void
    {
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $this->whitelabelUserSocialStub->isConfirmed = false;
        $this->whitelabelUserStub->email = 'test@test.pl';
        $this->whitelabelUserStub->hash = 'tsadasdqw3412312aqsd1234';
        $this->whitelabelUserStub->salt = 'asd123asd';
        $this->whitelabelUserStub->token = 'ASD3ADS';
        $lastHashSentAt = '2022-12-14 13:29:20';
        $hash = 'asdqwesdas';
        $this->whitelabelUserSocialStub->id = 999;
        $userId = $this->whitelabelUserStub->id;
        $this->whitelabelUserStub->id = $userId;
        $this->whitelabelUserSocialStub->whitelabelUserId = $userId;
        $this->whitelabelUserSocialStub->activationHash = $hash;
        $this->whitelabelUserSocialStub->lastHashSentAt = $lastHashSentAt;
        $this->whitelabelUserSocialStub->socialUserId = $this->profileStub->identifier;
        $this->whitelabelUserSocialStub->whitelabelUser = $this->whitelabelUserStub;
        $socialType = get_class($this->oauth2Mock);

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new UserIsCorrectlyConnectedException());

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByUserSocialIdAndWhitelabelSocialAppId')
            ->with($this->profileStub->identifier, $this->whitelabelSocialApiStub->id)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->activationHashGeneratorServiceMock->expects($this->once())
            ->method('generateActivationHash')
            ->with($this->whitelabelUserStub->salt)
            ->willReturn($hash);

        $this->activationHashGeneratorServiceMock->expects($this->once())
            ->method('setNewActivationHashPerSocialUser')
            ->with($this->whitelabelUserSocialStub->id, $hash);

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToSignUpPage');

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();

        $this->assertNotEmpty(FlashMessageHelper::getLast(), MessageHelper::getTranslatedActivationConfirmEmail());
        $this->assertEmpty(Session::get('is_user'));
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowUserIsNotConnectedException(): void
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $this->whitelabelUserSocialStub->isConfirmed = false;
        $this->whitelabelUserStub->email = 'test@test.pl';
        $this->whitelabelUserStub->id = 99999;
        $this->whitelabelUserSocialStub->id = 999999;
        $this->whitelabelUserStub->salt = '2c03ccac7b0353eed87d516f5960bfe1acf50ce39ebbb5e83cf9eeb61fe55bab81291bc9f14fdd3a319ac91df09282766c3323869f8b715e09bdc916f35286f4';
        $this->whitelabelUserStub->token = 'ASD123SAD';
        $activationHash = 'sadqweasdmqiwvbe81238126e9ayfd9a8E619824890QDA6E1';
        $this->whitelabelUserSocialStub->activationHash = $activationHash;
        $lastHashSentAt = '2022-12-14 13:29:20';
        $this->whitelabelUserSocialStub->lastHashSentAt = $lastHashSentAt;
        $this->whitelabelUserSocialStub->socialUserId = $this->profileStub->identifier;
        $socialType = get_class($this->oauth2Mock);
        $credentials = [
            'whitelabelUserId' => $this->whitelabelUserStub->id,
            'whitelabelSocialApiId' => $this->whitelabelSocialApiStub->id,
            'socialUserId' => $this->profileStub->identifier,
            'isConfirmed' => false,
            'activationHash' => $activationHash,
            'lastHashSentAt' => $lastHashSentAt,
        ];

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new UserIsNotConnectedOrIsDeletedException());

        $this->activationHashGeneratorServiceMock->expects($this->once())
            ->method('generateActivationHash')
            ->with($this->whitelabelUserStub->salt)
            ->willReturn($activationHash);

        $this->carbon->expects($this->once())
            ->method('format')
            ->with('Y-m-d H:i:s')
            ->willReturn($lastHashSentAt);

        $this->whitelabelUserRepositoryMock->expects($this->once())
            ->method('findUserByEmailAndWhitelabelId')
            ->with($this->profileStub->email, $whitelabel->id)
            ->willReturn($this->whitelabelUserStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('insert')
            ->with($credentials)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->fileLoggerServiceMock->expects($this->never())
            ->method('error');

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowUserIsNotConnectedException_getUserProfileThrowNotImplementedException(): void
    {
        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willThrowException(new NotImplementedException());

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->connectServiceMock->expects($this->never())
            ->method('connect');

        $this->whitelabelUserSocialRepositoryMock->expects($this->never())
            ->method('insert');

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToSignUpPage');

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowUserIsNotConnectedException_getUserProfileThrowHttpClientFailureException(): void
    {
        $this->oauth2Mock->expects($this->once())
            ->method('authenticate')
            ->willThrowException(new HttpClientFailureException());

        $this->fileLoggerServiceMock->expects($this->once())
            ->method('warning');

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToSignUpPage');

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();

        $this->assertEquals(FlashMessageHelper::getLast(), MessageHelper::getTranslatedAuthenticationError());
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowUserIsNotConnectedException_getUserProfileThrowException_redirectToSignUpPage(): void
    {
        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willThrowException(new Exception());

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->connectServiceMock->expects($this->never())
            ->method('connect');

        $this->whitelabelUserSocialRepositoryMock->expects($this->never())
            ->method('insert');

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToSignUpPage');

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowUserIsNotConnectedException_getUserProfileThrowException_accessTokenExpired(): void
    {
        $socialType = strtolower(get_class($this->oauth2Mock));
        $_SESSION['HYBRIDAUTH::STORAGE'] = [$socialType . '.access_token' => 'asdqwedasdqdas'];
        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willThrowException(new Exception('Error validating access token: The user has not authorized application'));

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->oauth2Mock->expects($this->any())
            ->method('disconnect');

        $this->connectServiceMock->expects($this->never())
            ->method('connect');

        $this->whitelabelUserSocialRepositoryMock->expects($this->never())
            ->method('insert');

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToSignUpPage');

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowUserIsNotConnectedException_InsertReturnNull_addError(): void
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $this->whitelabelUserSocialStub->isConfirmed = false;
        $this->whitelabelUserStub->email = 'test@test.pl';
        $this->whitelabelUserStub->id = 99999;
        $this->whitelabelUserSocialStub->id = 999999;
        $this->whitelabelUserStub->salt = '2c03ccac7b0353eed87d516f5960bfe1acf50ce39ebbb5e83cf9eeb61fe55bab81291bc9f14fdd3a319ac91df09282766c3323869f8b715e09bdc916f35286f4';
        $this->whitelabelUserStub->token = 'ASD123SAD';
        $activationHash = 'sadqweasdmqiwvbe81238126e9ayfd9a8E619824890QDA6E1';
        $this->whitelabelUserSocialStub->activationHash = $activationHash;
        $lastHashSentAt = '2022-12-14 13:29:20';
        $this->whitelabelUserSocialStub->lastHashSentAt = $lastHashSentAt;
        $this->whitelabelUserSocialStub->socialUserId = $this->profileStub->identifier;
        $socialType = get_class($this->oauth2Mock);

        $credentials = [
            'whitelabelUserId' => $this->whitelabelUserStub->id,
            'whitelabelSocialApiId' => $this->whitelabelSocialApiStub->id,
            'socialUserId' => $this->profileStub->identifier,
            'isConfirmed' => false,
            'activationHash' => $activationHash,
            'lastHashSentAt' => $lastHashSentAt,
        ];

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new UserIsNotConnectedOrIsDeletedException());

        $this->activationHashGeneratorServiceMock->expects($this->once())
            ->method('generateActivationHash')
            ->with($this->whitelabelUserStub->salt)
            ->willReturn($activationHash);

        $this->carbon->expects($this->once())
            ->method('format')
            ->with('Y-m-d H:i:s')
            ->willReturn($lastHashSentAt);

        $this->whitelabelUserRepositoryMock->expects($this->once())
            ->method('findUserByEmailAndWhitelabelId')
            ->with($this->profileStub->email, $whitelabel->id)
            ->willReturn($this->whitelabelUserStub);

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('insert')
            ->with($credentials)
            ->willReturn(null);

        $this->confirmedMailerServiceMock->expects($this->never())
            ->method('sendConfirmationEmail');

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToSignUpPage');

        $this->fileLoggerServiceMock->expects($this->once())
            ->method('error');

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowUserIsNotConnectedException_sendEmail(): void
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $this->whitelabelUserSocialStub->isConfirmed = false;
        $this->whitelabelUserStub->email = 'test@test.pl';
        $this->whitelabelUserStub->id = 99999;
        $this->whitelabelUserSocialStub->id = 999999;
        $this->whitelabelUserStub->salt = '2c03ccac7b0353eed87d516f5960bfe1acf50ce39ebbb5e83cf9eeb61fe55bab81291bc9f14fdd3a319ac91df09282766c3323869f8b715e09bdc916f35286f4';
        $this->whitelabelUserStub->token = 'ASD123SAD';
        $activationHash = 'sadqweasdmqiwvbe81238126e9ayfd9a8E619824890QDA6E1';
        $this->whitelabelUserSocialStub->activationHash = $activationHash;
        $lastHashSentAt = '2022-12-14 13:29:20';
        $this->whitelabelUserSocialStub->lastHashSentAt = $lastHashSentAt;
        $this->whitelabelUserSocialStub->socialUserId = $this->profileStub->identifier;
        $socialType = get_class($this->oauth2Mock);

        $credentials = [
            'whitelabelUserId' => $this->whitelabelUserStub->id,
            'whitelabelSocialApiId' => $this->whitelabelSocialApiStub->id,
            'socialUserId' => $this->profileStub->identifier,
            'isConfirmed' => false,
            'activationHash' => $activationHash,
            'lastHashSentAt' => $lastHashSentAt,
        ];

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new UserIsNotConnectedOrIsDeletedException());

        $this->activationHashGeneratorServiceMock->expects($this->once())
            ->method('generateActivationHash')
            ->with($this->whitelabelUserStub->salt)
            ->willReturn($activationHash);

        $this->carbon->expects($this->once())
            ->method('format')
            ->with('Y-m-d H:i:s')
            ->willReturn($lastHashSentAt);

        $this->whitelabelUserRepositoryMock->expects($this->once())
            ->method('findUserByEmailAndWhitelabelId')
            ->with($this->profileStub->email, $whitelabel->id)
            ->willReturn($this->whitelabelUserStub);

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('insert')
            ->with($credentials)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->confirmedMailerServiceMock->expects($this->once())
            ->method('sendConfirmationEmail');

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowUserIsNotConnectedException_ShowErrorPopup(): void
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $this->whitelabelUserSocialStub->isConfirmed = false;
        $this->whitelabelUserStub->email = 'test@test.pl';
        $this->whitelabelUserStub->id = 99999;
        $this->whitelabelUserSocialStub->id = 999999;
        $this->whitelabelUserStub->salt = '2c03ccac7b0353eed87d516f5960bfe1acf50ce39ebbb5e83cf9eeb61fe55bab81291bc9f14fdd3a319ac91df09282766c3323869f8b715e09bdc916f35286f4';
        $this->whitelabelUserStub->token = 'ASD123SAD';
        $activationHash = 'sadqweasdmqiwvbe81238126e9ayfd9a8E619824890QDA6E1';
        $this->whitelabelUserSocialStub->activationHash = $activationHash;
        $lastHashSentAt = '2022-12-14 13:29:20';
        $this->whitelabelUserSocialStub->lastHashSentAt = $lastHashSentAt;
        $this->whitelabelUserSocialStub->socialUserId = $this->profileStub->identifier;
        $socialType = get_class($this->oauth2Mock);

        $credentials = [
            'whitelabelUserId' => $this->whitelabelUserStub->id,
            'whitelabelSocialApiId' => $this->whitelabelSocialApiStub->id,
            'socialUserId' => $this->profileStub->identifier,
            'isConfirmed' => false,
            'activationHash' => $activationHash,
            'lastHashSentAt' => $lastHashSentAt,
        ];

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new UserIsNotConnectedOrIsDeletedException());

        $this->activationHashGeneratorServiceMock->expects($this->once())
            ->method('generateActivationHash')
            ->with($this->whitelabelUserStub->salt)
            ->willReturn($activationHash);

        $this->carbon->expects($this->once())
            ->method('format')
            ->with('Y-m-d H:i:s')
            ->willReturn($lastHashSentAt);

        $this->whitelabelUserRepositoryMock->expects($this->once())
            ->method('findUserByEmailAndWhitelabelId')
            ->with($this->profileStub->email, $whitelabel->id)
            ->willReturn($this->whitelabelUserStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('insert')
            ->with($credentials)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->confirmedMailerServiceMock->expects($this->once())
            ->method('sendConfirmationEmail');

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToLoginPage');

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();

        $this->assertSame(FlashMessageHelper::getLast(), MessageHelper::getTranslatedActivationConfirmEmail());
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowFillRegisterFormException_fillRegisterForm(): void
    {
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $socialType = get_class($this->oauth2Mock);
        $socialTypeModel = $this->whitelabelSocialApiStub->socialType = new SocialType();
        $socialTypeModel->type = $socialType;

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new FillRegisterFormException());

        $this->formServiceMock->expects($this->once())
            ->method('setRegisterFormValuesAndDeleteRegisterValuesFromSessionAfterUse');

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowFillRegisterFormException_fillRegisterForm_setUserProfileToSession(): void
    {
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $socialType = get_class($this->oauth2Mock);
        $socialTypeModel = $this->whitelabelSocialApiStub->socialType = new SocialType();
        $socialTypeModel->type = $socialType;

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new FillRegisterFormException());

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();

        $this->assertSame($this->profileStub, ProfileHelper::getSocialProfileFromSession());
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowUserIsCorrectlyConnectedException_userIsNotActive(): void
    {
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $this->whitelabelUserSocialStub->isConfirmed = true;
        $this->whitelabelUserStub->isActive = false;
        $this->whitelabelUserStub->email = 'test@test.pl';
        $userId = 99999;
        $this->whitelabelUserStub->id = $userId;
        $this->whitelabelUserSocialStub->whitelabelUserId = $userId;
        $this->whitelabelUserStub->hash = 'tsadasdqw3412312aqsd1234';
        $this->whitelabelUserStub->token = 'ASD3ADS';
        $this->whitelabelUserSocialStub->socialUserId = $this->profileStub->identifier;
        $this->whitelabelUserSocialStub->whitelabelUser = $this->whitelabelUserStub;
        $socialType = get_class($this->oauth2Mock);

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new UserIsCorrectlyConnectedException());

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByUserSocialIdAndWhitelabelSocialAppId')
            ->with($this->profileStub->identifier, $this->whitelabelSocialApiStub->id)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToSignUpPage');

        $this->confirmedMailerServiceMock->expects($this->never())
            ->method('sendConfirmationEmail');

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();

        $this->assertNotEmpty(FlashMessageHelper::getLast(), AbstractAuthService::MESSAGES['activationLink']);
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowUserIsCorrectlyConnectedException(): void
    {
        $socialType = get_class($this->oauth2Mock);
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $this->whitelabelUserSocialStub->socialUserId = $this->profileStub->identifier;
        $this->whitelabelUserStub->id = 1;
        $this->whitelabelUserStub->token = 'asdasd';
        $this->whitelabelUserStub->hash = 'asdasd';
        $this->whitelabelUserStub->email = $this->profileStub->email;
        $this->whitelabelUserSocialStub->whitelabelUser = $this->whitelabelUserStub;
        $this->whitelabelUserSocialStub->whitelabelUserId = $this->whitelabelUserStub->id;
        $this->whitelabelUserSocialStub->isConfirmed = true;
        $this->whitelabelUserStub->isActive = true;

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new UserIsCorrectlyConnectedException());

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByUserSocialIdAndWhitelabelSocialAppId')
            ->with($this->profileStub->identifier, $this->whitelabelSocialApiStub->id)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToHomePage');

        $this->confirmedMailerServiceMock->expects($this->never())
            ->method('sendConfirmationEmail');

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();

        $this->assertEquals(Session::get('user.email'), $this->whitelabelUserStub->email);
        $this->assertEquals(Session::get('user.hash'), $this->whitelabelUserStub->hash);
        $this->assertEquals(Session::get('user.id'), $this->whitelabelUserStub->id);
        $this->assertEquals(Session::get('user.token'), $this->whitelabelUserStub->token);
        $this->assertTrue(Session::get('is_user'));
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowFillRegisterFormException_socialUserIdIsSet(): void
    {
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $socialType = get_class($this->oauth2Mock);
        $socialTypeModel = $this->whitelabelSocialApiStub->socialType = new SocialType();
        $socialTypeModel->type = $socialType;

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new FillRegisterFormException());

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();

        $this->assertTrue(Session::get('socialUserId') === $this->profileStub->identifier);
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowSocialUserProfileWithEmptyEmailException_socialUserIdIsSet(): void
    {
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $socialType = get_class($this->oauth2Mock);
        $socialTypeModel = $this->whitelabelSocialApiStub->socialType = new SocialType();
        $socialTypeModel->type = $socialType;

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new UserProfileWithEmptyEmailException());

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();

        $this->assertTrue(Session::get('socialUserId') === $this->profileStub->identifier);
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowFillRegisterFormException_setSocialNameToSession(): void
    {
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $socialType = get_class($this->oauth2Mock);
        $socialTypeModel = $this->whitelabelSocialApiStub->socialType = new SocialType();
        $socialTypeModel->type = $socialType;

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new FillRegisterFormException());

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();
        $this->assertTrue(is_string(Session::get('socialType')));
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowSocialUserProfileWithEmptyEmailException_setSocialNameToSession(): void
    {
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $socialType = get_class($this->oauth2Mock);
        $socialTypeModel = $this->whitelabelSocialApiStub->socialType = new SocialType();
        $socialTypeModel->type = $socialType;

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new UserProfileWithEmptyEmailException());

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();

        $this->assertTrue(is_string(Session::get('socialType')));
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowFillRegisterFormException_MarkRegisterAsSocialConnect(): void
    {
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $socialType = get_class($this->oauth2Mock);
        $socialTypeModel = $this->whitelabelSocialApiStub->socialType = new SocialType();
        $socialTypeModel->type = $socialType;

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new FillRegisterFormException());

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();

        $this->assertTrue(Session::get(ConnectHelper::SOCIAL_CONNECT_KEY));
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowSocialUserProfileWithEmptyEmailException_MarkRegisterAsSocialConnect(): void
    {
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $socialType = get_class($this->oauth2Mock);
        $socialTypeModel = $this->whitelabelSocialApiStub->socialType = new SocialType();
        $socialTypeModel->type = $socialType;

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new UserProfileWithEmptyEmailException());

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();

        $this->assertTrue(Session::get(ConnectHelper::SOCIAL_CONNECT_KEY));
    }

    /** @test  */
    public function startSocialAccountIntegration_ConnectThrowSocialUserProfileWithEmptyEmailException_fillRegisterForm(): void
    {
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $socialType = get_class($this->oauth2Mock);
        $socialTypeModel = $this->whitelabelSocialApiStub->socialType = new SocialType();
        $socialTypeModel->type = $socialType;

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new UserProfileWithEmptyEmailException());

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowUserIsCorrectlyConnectedException_SocialUserIsConfirmed_loginUser(): void
    {
        $socialType = get_class($this->oauth2Mock);
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $this->whitelabelUserSocialStub->isConfirmed = true;
        $this->whitelabelUserStub->email = 'test@test.pl';
        $this->whitelabelUserStub->id = 99999;
        $this->whitelabelUserStub->hash = 'tsadasdqw3412312aqsd1234';
        $this->whitelabelUserStub->token = 'ASD3ADS';
        $this->whitelabelUserSocialStub->whitelabelUser = $this->whitelabelUserStub;
        $this->whitelabelUserSocialStub->socialUserId = $this->profileStub->identifier;
        $this->whitelabelUserStub->isActive = true;

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new UserIsCorrectlyConnectedException());

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByUserSocialIdAndWhitelabelSocialAppId')
            ->with($this->profileStub->identifier, $this->whitelabelSocialApiStub->id)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToHomePage');

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();

        $this->assertEquals(Session::get('user.email'), $this->whitelabelUserStub->email);
        $this->assertEquals(Session::get('user.hash'), $this->whitelabelUserStub->hash);
        $this->assertEquals(Session::get('user.id'), $this->whitelabelUserStub->id);
        $this->assertEquals(Session::get('user.token'), $this->whitelabelUserStub->token);
        $this->assertTrue(Session::get('is_user'));
    }


    /** @test */
    public function startSocialAccountIntegration_ThrowSocialUserIsCorrectlyConnectedException_SocialIdIsIncorrect(): void
    {
        $socialType = get_class($this->oauth2Mock);
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $this->whitelabelUserSocialStub->isConfirmed = true;
        $this->whitelabelUserStub->email = 'test@test.pl';
        $this->whitelabelUserStub->id = 99999;
        $this->whitelabelUserStub->hash = 'tsadasdqw3412312aqsd1234';
        $this->whitelabelUserStub->token = 'ASD3ADS';
        $this->whitelabelUserSocialStub->socialUserId = 'asdqweasdq';
        $this->whitelabelUserSocialStub->whitelabelUser = $this->whitelabelUserStub;

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new UserIsCorrectlyConnectedException());

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByUserSocialIdAndWhitelabelSocialAppId')
            ->with($this->profileStub->identifier, $this->whitelabelSocialApiStub->id)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();

        $this->assertNull(Session::get('is_user'));
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowUserIsCorrectlyConnectedException_showConfirmEmailMessage(): void
    {
        $socialType = get_class($this->oauth2Mock);
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $this->whitelabelUserSocialStub->isConfirmed = false;
        $this->whitelabelUserStub->email = 'test@test.pl';
        $this->whitelabelUserStub->id = 99999;
        $this->whitelabelUserSocialStub->id = 999999;
        $this->whitelabelUserStub->salt = '2c03ccac7b0353eed87d516f5960bfe1acf50ce39ebbb5e83cf9eeb61fe55bab81291bc9f14fdd3a319ac91df09282766c3323869f8b715e09bdc916f35286f4';
        $this->whitelabelUserSocialStub->socialUserId = $this->profileStub->identifier;
        $this->whitelabelUserStub->token = 'ASD123SAD';
        $hash = 'sadqweasdmqiwvbe81238126e9ayfd9a8E619824890QDA6E1';
        $this->whitelabelUserSocialStub->activationHash = $hash;
        $this->whitelabelUserSocialStub->lastHashSentAt = '1990-12-14 13:29:20';
        $this->whitelabelUserSocialStub->whitelabelUser = $this->whitelabelUserStub;

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new UserIsCorrectlyConnectedException());

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByUserSocialIdAndWhitelabelSocialAppId')
            ->willReturn($this->whitelabelUserSocialStub);

        $this->activationHashGeneratorServiceMock->expects($this->once())
            ->method('generateActivationHash')
            ->with($this->whitelabelUserStub->salt)
            ->willReturn($hash);

        $this->activationHashGeneratorServiceMock->expects($this->once())
            ->method('setNewActivationHashPerSocialUser')
            ->with($this->whitelabelUserSocialStub->id, $hash);

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToSignUpPage');

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();

        $this->assertSame(FlashMessageHelper::getLast(), MessageHelper::getTranslatedActivationConfirmEmail());
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowUserIsCorrectlyConnectedException_showConfirmEmailMessageAfterDay(): void
    {
        $socialType = get_class($this->oauth2Mock);
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $this->whitelabelUserSocialStub->isConfirmed = false;
        $this->whitelabelUserStub->email = 'test@test.pl';
        $this->whitelabelUserSocialStub->id = 999999;
        $this->whitelabelUserStub->salt = '2c03ccac7b0353eed87d516f5960bfe1acf50ce39ebbb5e83cf9eeb61fe55bab81291bc9f14fdd3a319ac91df09282766c3323869f8b715e09bdc916f35286f4';
        $this->whitelabelUserSocialStub->socialUserId = $this->profileStub->identifier;
        $this->whitelabelUserStub->token = 'ASD123SAD';
        $this->whitelabelUserSocialStub->activationHash = 'sadqweasdmqiwvbe81238126e9ayfd9a8E619824890QDA6E1';
        $this->whitelabelUserSocialStub->lastHashSentAt = Carbon::now()->format(Helpers_Time::ACTIVATION_HASH_SEND_DATE_CARBON_FORMAT);
        $this->whitelabelUserSocialStub->whitelabelUser = $this->whitelabelUserStub;

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new UserIsCorrectlyConnectedException());

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByUserSocialIdAndWhitelabelSocialAppId')
            ->with($this->profileStub->identifier, $this->whitelabelSocialApiStub->id)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToSignUpPage');

        $this->confirmedMailerServiceMock->expects($this->never())
            ->method('sendConfirmationEmail');

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();

        $this->assertEquals(FlashMessageHelper::getLast(), MessageHelper::getTranslatedActivationConfirmEmailBeforeDayPassed());
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowUserIsCorrectlyConnectedException_sendSocialConfirmedEmail(): void
    {
        $socialType = get_class($this->oauth2Mock);
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $this->whitelabelUserSocialStub->isConfirmed = false;
        $this->whitelabelUserStub->email = 'test@test.pl';
        $this->whitelabelUserStub->id = 99999;
        $this->whitelabelUserSocialStub->id = 999999;
        $this->whitelabelUserStub->salt = '2c03ccac7b0353eed87d516f5960bfe1acf50ce39ebbb5e83cf9eeb61fe55bab81291bc9f14fdd3a319ac91df09282766c3323869f8b715e09bdc916f35286f4';
        $this->whitelabelUserStub->token = 'ASD123SAD';
        $hash = 'sadqweasdmqiwvbe81238126e9ayfd9a8E619824890QDA6E1';
        $this->whitelabelUserSocialStub->activationHash = $hash;
        $this->whitelabelUserSocialStub->socialUserId = $this->profileStub->identifier;
        $this->whitelabelUserSocialStub->whitelabelUser = $this->whitelabelUserStub;
        $this->whitelabelUserSocialStub->lastHashSentAt = '2022-04-13';

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new UserIsCorrectlyConnectedException());

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByUserSocialIdAndWhitelabelSocialAppId')
            ->with($this->profileStub->identifier, $this->whitelabelSocialApiStub->id)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->activationHashGeneratorServiceMock->expects($this->once())
            ->method('generateActivationHash')
            ->with($this->whitelabelUserStub->salt)
            ->willReturn($hash);

        $this->activationHashGeneratorServiceMock->expects($this->once())
            ->method('setNewActivationHashPerSocialUser')
            ->with($this->whitelabelUserSocialStub->id, $hash);

        $this->confirmedMailerServiceMock->expects($this->once())
            ->method('sendConfirmationEmail');

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();
    }

    /** @test */
    public function authenticationError(): void
    {
        $this->oauth2Mock->expects($this->once())
            ->method('authenticate')
            ->willThrowException(new AuthorizationDeniedException());

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToSignUpPage');

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();

        $this->assertEquals(FlashMessageHelper::getLast(), MessageHelper::getTranslatedAuthenticationError());
    }

    /** @test */
    public function startSocialAccountIntegration_ThrowSocialUserIsCorrectlyConnectedException_ActivationRequired_doNotLoginUser(): void
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $whitelabel->userActivationType = Helpers_General::ACTIVATION_TYPE_REQUIRED;
        $socialType = get_class($this->oauth2Mock);
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $this->whitelabelUserSocialStub->isConfirmed = true;
        $this->whitelabelUserStub->is_confirmed = false;
        $this->whitelabelUserStub->is_active = false;
        $this->whitelabelUserStub->email = 'test@test.pl';
        $this->whitelabelUserStub->id = 99999;
        $this->whitelabelUserStub->hash = 'tsadasdqw3412312aqsd1234';
        $this->whitelabelUserStub->token = 'ASD3ADS';
        $this->whitelabelUserSocialStub->socialUserId = 'asdqweasdq';
        $this->whitelabelUserStub->whitelabel_id = 99999;
        $this->whitelabelUserSocialStub->whitelabelUser = $this->whitelabelUserStub;

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new UserIsCorrectlyConnectedException());

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByUserSocialIdAndWhitelabelSocialAppId')
            ->with($this->profileStub->identifier, $this->whitelabelSocialApiStub->id)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToLoginPage');

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();

        $this->assertNull(Session::get('is_user'));
        $this->assertNotNull(FlashMessageHelper::getLast());
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowUserIsCorrectlyConnectedException_activationRequired_showPopupWithResendLink(): void
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $whitelabel->userActivationType = Helpers_General::ACTIVATION_TYPE_REQUIRED;
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $this->whitelabelUserSocialStub->isConfirmed = true;
        $this->whitelabelUserStub->email = 'test@test.pl';
        $userId = 99999;
        $this->whitelabelUserStub->id = $userId;
        $this->whitelabelUserStub->isActive = false;
        $this->whitelabelUserStub->isConfirmed = false;
        $this->whitelabelUserSocialStub->whitelabelUserId = $userId;
        $this->whitelabelUserStub->hash = 'tsadasdqw3412312aqsd1234';
        $this->whitelabelUserStub->token = 'ASD3ADS';
        $this->whitelabelUserSocialStub->socialUserId = $this->profileStub->identifier;
        $this->whitelabelUserSocialStub->whitelabelUser = $this->whitelabelUserStub;
        $socialType = get_class($this->oauth2Mock);
        $fakeResendLink = 'whitelabel.com';

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new UserIsCorrectlyConnectedException());

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByUserSocialIdAndWhitelabelSocialAppId')
            ->with($this->profileStub->identifier, $this->whitelabelSocialApiStub->id)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->wordpressLoginServiceMock->expects($this->once())
            ->method('getResendLink')
            ->with($this->whitelabelUserStub->id)
            ->willReturn($fakeResendLink);

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToLoginPage');

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();

        $this->assertEquals(sprintf(_(AbstractAuthService::MESSAGES['activationLink']), $fakeResendLink), FlashMessageHelper::getLast());
        $this->assertEmpty(Session::get('is_user'));
        $this->assertNotEmpty(FlashMessageHelper::getLast());
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowSocialUserEmailEqualsEmailWhichHaveSocialConnectionException_EmailInUserProfileMusBeEmpty(): void
    {
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $socialType = get_class($this->oauth2Mock);
        $socialTypeModel = $this->whitelabelSocialApiStub->socialType = new SocialType();
        $socialTypeModel->type = $socialType;

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new SocialUserEmailEqualsEmailWhichHaveSocialConnectionException());

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();
        $this->assertEmpty($this->profileStub->email);
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowSocialUserEmailEqualsEmailWhichHaveSocialConnectionException_MarkRegisterAsSocialConnect(): void
    {
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $socialType = get_class($this->oauth2Mock);
        $socialTypeModel = $this->whitelabelSocialApiStub->socialType = new SocialType();
        $socialTypeModel->type = $socialType;

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new SocialUserEmailEqualsEmailWhichHaveSocialConnectionException());

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();

        $this->assertTrue(Session::get(ConnectHelper::SOCIAL_CONNECT_KEY));
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowSocialUserEmailEqualsEmailWhichHaveSocialConnectionException_setSocialNameToSession(): void
    {
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $socialType = get_class($this->oauth2Mock);
        $socialTypeModel = $this->whitelabelSocialApiStub->socialType = new SocialType();
        $socialTypeModel->type = $socialType;

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new SocialUserEmailEqualsEmailWhichHaveSocialConnectionException());

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();
        $this->assertTrue(is_string(Session::get('socialType')));
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowSocialUserEmailEqualsEmailWhichHaveSocialConnectionException_socialUserIdIsSet(): void
    {
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $socialType = get_class($this->oauth2Mock);
        $socialTypeModel = $this->whitelabelSocialApiStub->socialType = new SocialType();
        $socialTypeModel->type = $socialType;

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new SocialUserEmailEqualsEmailWhichHaveSocialConnectionException());

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();

        $this->assertTrue(Session::get('socialUserId') === $this->profileStub->identifier);
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowSocialUserEmailEqualsEmailWhichHaveSocialConnectionException_fillRegisterForm(): void
    {
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $socialType = get_class($this->oauth2Mock);
        $socialTypeModel = $this->whitelabelSocialApiStub->socialType = new SocialType();
        $socialTypeModel->type = $socialType;

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new SocialUserEmailEqualsEmailWhichHaveSocialConnectionException());

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();
    }

    /** @test */
    public function startSocialAccountIntegration_ConnectThrowSocialUserEmailEqualsEmailWhichHaveSocialConnectionException_setUserProfileToSession(): void
    {
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $socialType = get_class($this->oauth2Mock);
        $socialTypeModel = $this->whitelabelSocialApiStub->socialType = new SocialType();
        $socialTypeModel->type = $socialType;

        $this->oauth2Mock->expects($this->once())
            ->method('authenticate');

        $this->oauth2Mock->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->profileStub);

        $this->oauth2Mock->expects($this->once())
            ->method('disconnect');

        $this->whitelabelSocialApiRepositoryMock->expects($this->once())
            ->method('findWhitelabelSocialSettingsBySocialType')
            ->with($socialType)
            ->willReturn($this->whitelabelSocialApiStub);

        $this->connectServiceMock->expects($this->once())
            ->method('connect')
            ->with($this->profileStub, $this->whitelabelSocialApiStub->id)
            ->willThrowException(new SocialUserEmailEqualsEmailWhichHaveSocialConnectionException());

        $this->lastStepsServiceUnderTest->startSocialMediaAccountIntegration();

        $this->assertSame($this->profileStub, ProfileHelper::getSocialProfileFromSession());
    }
}

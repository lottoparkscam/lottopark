<?php

namespace Tests\Unit\Classes\Services\SocialMediaConnect;

use Container;
use Exceptions\SocialMedia\FillRegisterFormException;
use Exceptions\SocialMedia\UserProfileWithEmptyEmailException;
use Exceptions\SocialMedia\UserIsCorrectlyConnectedException;
use Exceptions\SocialMedia\UserIsNotConnectedOrIsDeletedException;
use Exceptions\SocialMedia\SocialUserEmailEqualsEmailWhichHaveSocialConnectionException;
use Fuel\Core\Session;
use Helpers\SocialMediaConnect\ConnectHelper;
use Hybridauth\User\Profile;
use Models\SocialType;
use Models\Whitelabel;
use Models\WhitelabelUser;
use Models\WhitelabelUserSocial;
use PHPUnit\Framework\MockObject\MockObject;
use Repositories\Orm\WhitelabelUserRepository;
use Repositories\WhitelabelUserSocialRepository;
use Services\Logs\FileLoggerService;
use Services\SocialMediaConnect\ConnectService;
use Test_Unit;

class ConnectServiceTest extends Test_Unit
{
    private WhitelabelUser $whitelabelUserStub;
    private ConnectService $socialMediaConnectServiceUnderTest;
    private Profile $profileStub;
    private WhitelabelUserRepository|MockObject $whitelabelUserRepositoryMock;
    private WhitelabelUserSocialRepository|MockObject $whitelabelUserSocialRepositoryMock;
    private WhitelabelUserSocial $whitelabelUserSocialStub;
    private FileLoggerService|MockObject $fileLoggerServiceMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabelUserStub = new WhitelabelUser();
        $this->fileLoggerServiceMock = $this->createMock(FileLoggerService::class);
        $this->whitelabelUserRepositoryMock = $this->getMockBuilder(WhitelabelUserRepository::class)
            ->onlyMethods(['findByTokenAndWhitelabelId', 'findUserByEmailAndWhitelabelId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->whitelabelUserSocialRepositoryMock = $this->createMock(WhitelabelUserSocialRepository::class);

        $this->profileStub = new Profile();
        $this->whitelabelUserSocialStub = new WhitelabelUserSocial();
        $this->socialMediaConnectServiceUnderTest = new ConnectService(
            $this->whitelabelUserRepositoryMock,
            $this->whitelabelUserSocialRepositoryMock,
            $this->fileLoggerServiceMock,
        );

        $this->whitelabelSocialApiId = 1;
    }

    /** @test */
    public function connect_withoutEmail_throwUserProfileWithIncorrectIdException(): void
    {
        $this->expectException(SocialUserEmailEqualsEmailWhichHaveSocialConnectionException::class);
        $this->whitelabelUserStub->id = 1123123;
        $this->whitelabelUserSocialStub->socialUserId = 'asdadasad';
        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);

        $this->whitelabelUserRepositoryMock->expects($this->any())
            ->method('findUserByEmailAndWhitelabelId')
            ->willReturn($this->whitelabelUserStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId')
            ->with($this->whitelabelUserStub->id, $this->whitelabelSocialApiId)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->socialMediaConnectServiceUnderTest->connect($this->profileStub, $this->whitelabelSocialApiId);
    }

    /** @test */
    public function connect_throwUserIsCorrectlyConnectedException(): void
    {
        $this->expectException(UserIsCorrectlyConnectedException::class);

        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $this->whitelabelUserSocialStub->whitelabelUser = $this->whitelabelUserStub;

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('findEnabledByUserSocialIdAndWhitelabelSocialAppId')
            ->with($this->profileStub->identifier, $this->whitelabelSocialApiId)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->socialMediaConnectServiceUnderTest->connect($this->profileStub, $this->whitelabelSocialApiId);
    }

    /** @test */
    public function connect_throwFillRegisterForm_userIsDeleted(): void
    {
        $this->expectException(FillRegisterFormException::class);

        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $this->whitelabelUserSocialStub->whitelabelUser = $this->whitelabelUserStub;
        $this->whitelabelUserStub->isDeleted = true;

        $this->whitelabelUserSocialRepositoryMock->expects($this->any())
            ->method('findEnabledByUserSocialIdAndWhitelabelSocialAppId')
            ->with($this->profileStub->identifier, 1)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->socialMediaConnectServiceUnderTest->connect($this->profileStub, 1);
    }

    /** @test */
    public function connect_withoutEmail_throwUserIsCorrectlyConnectedException_userProfileWithoutEmail(): void
    {
        $this->expectException(UserIsCorrectlyConnectedException::class);

        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $this->whitelabelUserSocialStub->whitelabelUser = $this->whitelabelUserStub;
        $this->whitelabelUserStub->isDeleted = false;
        $this->profileStub->email = null;

        $this->whitelabelUserSocialRepositoryMock->expects($this->any(2))
            ->method('findEnabledByUserSocialIdAndWhitelabelSocialAppId')
            ->with($this->profileStub->identifier, $this->whitelabelSocialApiId)
            ->willReturn($this->whitelabelUserSocialStub);

        $this->socialMediaConnectServiceUnderTest->connect($this->profileStub, $this->whitelabelSocialApiId);
    }

    /** @test */
    public function connect_withoutEmail_throwUserProfileWithEmptyEmailException(): void
    {
        $this->expectException(UserProfileWithEmptyEmailException::class);

        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $this->profileStub->email = null;

        $this->socialMediaConnectServiceUnderTest->connect($this->profileStub, $this->whitelabelSocialApiId);
    }

    /** @test */
    public function connect_fillRegisterForm_throwFillRegisterForm(): void
    {
        $this->expectException(FillRegisterFormException::class);

        $this->whitelabelUserRepositoryMock->expects($this->any())
            ->method('findUserByEmailAndWhitelabelId')
            ->willReturn(null);

        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);

        $this->socialMediaConnectServiceUnderTest->connect($this->profileStub, $this->whitelabelSocialApiId);
    }

    /** @test */
    public function connect_fillRegisterForm_userNotExists(): void
    {
        $this->expectException(FillRegisterFormException::class);

        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);

        $this->whitelabelUserRepositoryMock->expects($this->any())
            ->method('findUserByEmailAndWhitelabelId')
            ->with($this->profileStub->email)
            ->willReturn(null);

        $this->socialMediaConnectServiceUnderTest->connect($this->profileStub, $this->whitelabelSocialApiId);
    }

    /** @test */
    public function connect_throwFillRegisterFormException_userIsDeleted(): void
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $this->expectException(FillRegisterFormException::class);

        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $this->whitelabelUserStub->id = 1;
        $this->whitelabelUserStub->email = $this->profileStub->email;
        $this->whitelabelUserStub->isActive = true;
        $this->whitelabelUserStub->isDeleted = true;

        $this->whitelabelUserRepositoryMock->expects($this->any())
            ->method('findUserByEmailAndWhitelabelId')
            ->with($this->profileStub->email, $whitelabel->id)
            ->willReturn($this->whitelabelUserStub);

        $this->socialMediaConnectServiceUnderTest->connect($this->profileStub, $this->whitelabelSocialApiId);
    }

    /** @test */
    public function connect_fillRegisterForm_throwFillRegisterForm_WhenUserNotExistsInWhitelabelUser(): void
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $this->expectException(FillRegisterFormException::class);

        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);

        $this->whitelabelUserRepositoryMock->expects($this->any())
            ->method('findUserByEmailAndWhitelabelId')
            ->with($this->profileStub->email, $whitelabel->id)
            ->willReturn(null);

        $this->socialMediaConnectServiceUnderTest->connect($this->profileStub, $this->whitelabelSocialApiId);
    }

    /** @test */
    public function connect_whitelabelUserExistsAndNotConnected_throwUserIsNotConnected(): void
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $this->expectException(UserIsNotConnectedOrIsDeletedException::class);

        SocialMediaConnectTestHelper::setSocialUserProfileTestValue($this->profileStub);
        $user = $this->get_user();
        $user->is_active = true;

        $this->whitelabelUserRepositoryMock->expects($this->any())
            ->method('findUserByEmailAndWhitelabelId')
            ->with($this->profileStub->email, $whitelabel->id)
            ->willReturn($user);

        $this->whitelabelUserSocialRepositoryMock->expects($this->any())
            ->method('findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId')
            ->with($user->id, 1);

        $this->socialMediaConnectServiceUnderTest->connect($this->profileStub, $this->whitelabelSocialApiId);
    }

    /** @test */
    public function createSocialConnectionPerUser_socialUserIdNotExists(): void
    {
        $userToken = 'asdqw2asdqw23';
        $whitelabelId = 1;
        $socialType = SocialType::FACEBOOK_TYPE;
        Session::set('whitelabelSocialApiId', $socialType);
        Session::set(ConnectHelper::SOCIAL_CONNECT_KEY, true);

        $this->whitelabelUserRepositoryMock->expects($this->once())
            ->method('findByTokenAndWhitelabelId')
            ->with($userToken, $whitelabelId)
            ->willReturn($this->whitelabelUserStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->never())
            ->method('insert');

        $this->socialMediaConnectServiceUnderTest->createSocialConnection($userToken, $whitelabelId);
    }

    /** @test */
    public function createSocialConnectionPerUser_socialNameNotExists(): void
    {
        $userToken = 'asdqw2asdqw23';
        $whitelabelId = 1;
        Session::set(ConnectHelper::SOCIAL_CONNECT_KEY, true);

        $this->whitelabelUserRepositoryMock->expects($this->once())
            ->method('findByTokenAndWhitelabelId')
            ->with($userToken, $whitelabelId)
            ->willReturn($this->whitelabelUserStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->never())
            ->method('insert');

        $this->socialMediaConnectServiceUnderTest->createSocialConnection($userToken, $whitelabelId);
    }

    /** @test */
    public function createSocialConnectionPerUser(): void
    {
        $userToken = 'asdqw2asdqw23';
        $whitelabelId = 1;
        $whitelabelSocialAppId = 1;
        $socialUserId = '123asdqweda231123';
        Session::set('whitelabelSocialApiId', $whitelabelSocialAppId);
        Session::set(ConnectHelper::SOCIAL_CONNECT_KEY, true);
        Session::set('socialUserId', $socialUserId);
        $credentials = [
            'whitelabelUserId' => $this->whitelabelUserStub->id,
            'whitelabelSocialApiId' => $whitelabelSocialAppId,
            'socialUserId' => $socialUserId,
            'isConfirmed' => true,
        ];

        $this->whitelabelUserRepositoryMock->expects($this->once())
            ->method('findByTokenAndWhitelabelId')
            ->with($userToken, $whitelabelId)
            ->willReturn($this->whitelabelUserStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('insert')
            ->with($credentials);

        $this->socialMediaConnectServiceUnderTest->createSocialConnection($userToken, $whitelabelId);
    }

    /** @test */
    public function createSocialConnectionPerUser_userIsNotAdded(): void
    {
        $userToken = 'asdqw2asdqw23';
        $whitelabelId = 1;
        $socialUserId = '123asdqweda231123';
        Session::set('whitelabelSocialApiId', 1);
        Session::set(ConnectHelper::SOCIAL_CONNECT_KEY, true);
        Session::set('socialUserId', $socialUserId);
        $credentials = [
            'whitelabelUserId' => $this->whitelabelUserStub->id,
            'whitelabelSocialApiId' => 1,
            'socialUserId' => $socialUserId,
            'isConfirmed' => true,
        ];

        $this->whitelabelUserRepositoryMock->expects($this->once())
            ->method('findByTokenAndWhitelabelId')
            ->with($userToken, $whitelabelId)
            ->willReturn($this->whitelabelUserStub);

        $this->whitelabelUserSocialRepositoryMock->expects($this->once())
            ->method('insert')
            ->with($credentials)
            ->willReturn(null);

        $this->fileLoggerServiceMock->expects($this->once())
            ->method('error');

        $this->socialMediaConnectServiceUnderTest->createSocialConnection($userToken, $whitelabelId);
    }

    /** @test */
    public function createSocialConnectionPerUser_findOneByIsEmpty(): void
    {
        $userToken = 'asdqw2asdqw23';
        $whitelabelId = 1;
        $socialUserId = '123asdqweda231123';
        Session::set('whitelabelSocialApiId', 1);
        Session::set(ConnectHelper::SOCIAL_CONNECT_KEY, true);
        Session::set('socialUserId', $socialUserId);
        $this->whitelabelUserStub->whitelabel = Container::get('whitelabel');

        $this->whitelabelUserRepositoryMock->expects($this->once())
            ->method('findByTokenAndWhitelabelId')
            ->with($userToken, $whitelabelId)
            ->willReturn(null);

        $this->whitelabelUserSocialRepositoryMock->expects($this->never())
            ->method('insert');

        $this->socialMediaConnectServiceUnderTest->createSocialConnection($userToken, $whitelabelId);
    }
}

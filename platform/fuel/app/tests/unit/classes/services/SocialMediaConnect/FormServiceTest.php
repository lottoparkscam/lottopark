<?php

namespace Tests\Unit\Classes\Services\SocialMediaConnect;

use Fuel\Core\Session;
use Helpers\FlashMessageHelper;
use Helpers\SocialMediaConnect\ConnectHelper;
use Models\SocialType;
use PHPUnit\Framework\MockObject\MockObject;
use Services\RedirectService;
use Services\SocialMediaConnect\FormService;
use Test_Unit;

class FormServiceTest extends Test_Unit
{
    private FormService $formServiceUnderTest;
    private RedirectService|MockObject $redirectServiceMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->redirectServiceMock = $this->createMock(RedirectService::class);
        $this->formServiceUnderTest = new FormService(
            $this->redirectServiceMock,
        );
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Session::set('message', []);
        Session::set_flash('message', []);
        Session::delete(FormService::SESSION_REGISTRATION_VALUES_KEY);
    }

    /** @test */
    public function setRegisterFormValuesAndDeleteRegisterValuesFromSessionAfterUse(): void
    {
        $registerPostValues = [
            'email' => 'test@wp.pl',
            'company' => 'ASD',
            'phone' => 123513212,
            'prefix' => '+48',
            'group' => 'ASD',
            'name' => 'olek',
            'surname' => 'kelo',
        ];
        Session::set(FormService::SESSION_REGISTRATION_VALUES_KEY, $registerPostValues);

        $this->formServiceUnderTest->setRegisterFormValuesAndDeleteRegisterValuesFromSessionAfterUse();

        $this->assertEquals($registerPostValues['email'], $_POST['register.email']);
        $this->assertEquals($registerPostValues['company'], $_POST['register.company']);
        $this->assertEquals($registerPostValues['phone'], $_POST['register.phone']);
        $this->assertEquals($registerPostValues['prefix'], $_POST['register.prefix']);
        $this->assertEquals($registerPostValues['group'], $_POST['register.group']);
        $this->assertEquals($registerPostValues['group'], $_POST['register.group']);
        $this->assertEquals($registerPostValues['name'], $_POST['register.name']);
        $this->assertEquals($registerPostValues['surname'], $_POST['register.surname']);
    }

    /** @test */
    public function setRegisterFormValuesAndDeleteRegisterValuesFromSessionAfterUse_fewValuesNotExists(): void
    {
        $registerPostValues = [
            'company' => 'ASD',
            'group' => 'ASD',
            'surname' => 'kelo',
        ];
        Session::set(FormService::SESSION_REGISTRATION_VALUES_KEY, $registerPostValues);

        $this->formServiceUnderTest->setRegisterFormValuesAndDeleteRegisterValuesFromSessionAfterUse();

        $this->assertEquals($registerPostValues['company'], $_POST['register.company']);
        $this->assertEquals($registerPostValues['group'], $_POST['register.group']);
        $this->assertEquals($registerPostValues['surname'], $_POST['register.surname']);
        $this->assertEmpty($_POST['register.email']);
        $this->assertEmpty($_POST['register.phone']);
        $this->assertEmpty($_POST['register.prefix']);
        $this->assertEmpty($_POST['register.name']);
    }

    /** @test */
    public function setRegisterFormValuesAndDeleteRegisterValuesFromSessionAfterUse_sessionIsDeleted(): void
    {
        $registerPostValues = [
            'company' => 'ASD',
        ];
        Session::set(FormService::SESSION_REGISTRATION_VALUES_KEY, $registerPostValues);

        $this->formServiceUnderTest->setRegisterFormValuesAndDeleteRegisterValuesFromSessionAfterUse();

        $this->assertEquals($registerPostValues['company'], $_POST['register.company']);
        $this->assertEmpty(Session::get(FormService::SESSION_REGISTRATION_VALUES_KEY));
    }

    /** @test */
    public function loadFormErrorOnLastSteps(): void
    {
        Session::set('socialType', SocialType::FACEBOOK_TYPE);
        Session::set(ConnectHelper::SOCIAL_CONNECT_KEY, true);

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToLastSteps')
            ->with(SocialType::FACEBOOK_TYPE);

        $this->formServiceUnderTest->loadFormErrorOnLastSteps(['error', 'error']);

        $this->assertNotEmpty(FlashMessageHelper::getLast());
    }

    /** @test */
    public function loadFormErrorOnLastSteps_postRegisterExists(): void
    {
        Session::set('socialType', SocialType::FACEBOOK_TYPE);
        Session::set(ConnectHelper::SOCIAL_CONNECT_KEY, true);
        $registerValues = ['email' => 'email@email.emailowo'];
        $_POST['register'] = $registerValues;

        $this->redirectServiceMock->expects($this->once())
            ->method('redirectToLastSteps')
            ->with(SocialType::FACEBOOK_TYPE);

        $this->formServiceUnderTest->loadFormErrorOnLastSteps(['error', 'error']);

        $this->assertNotEmpty(FlashMessageHelper::getLast());
        $this->assertEquals($registerValues, Session::get(FormService::SESSION_REGISTRATION_VALUES_KEY));
    }

    /** @test */
    public function loadFormErrorOnLastSteps_isNotSocialConnect(): void
    {
        Session::set(ConnectHelper::SOCIAL_CONNECT_KEY, false);

        $this->redirectServiceMock->expects($this->never())
            ->method('redirectToLastSteps');

        $this->formServiceUnderTest->loadFormErrorOnLastSteps(['error', 'error']);

        $this->assertEmpty(FlashMessageHelper::getLast());
        $this->assertEmpty(Session::get(FormService::SESSION_REGISTRATION_VALUES_KEY));
    }
}

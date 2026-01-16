<?php

namespace Tests\Browser\Auth;

use Container;
use Fuel\Core\Cache;
use Models\Whitelabel;
use Tests\Fixtures\WhitelabelUserFixture;
use Models\WhitelabelUser;
use Fuel\Core\DBUtil;
use Services\Auth\UserActivationService;
use Test\Selenium;
use Test\Selenium\Interfaces\UserInterface;
use Test\Selenium\Traits\SeleniumUserAssertions;

final class UserActivationTest extends Selenium implements UserInterface
{
    use SeleniumUserAssertions;

    private Whitelabel $whitelabel;
    protected WhitelabelUser $whitelabelUser;
    private WhitelabelUserFixture $whitelabelUserFixture;
    private UserActivationService $userActivationService;
    private int $whitelabelUserActivationType;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::delete_all();
        $this->whitelabel = Container::get('whitelabel');
        $this->whitelabelUserActivationType = $this->whitelabel->userActivationType;
        $this->whitelabelUserFixture = $this->container->get(WhitelabelUserFixture::class);
        $this->userActivationService = $this->container->get(UserActivationService::class);
        $this->driver->get(self::LOGIN_URL);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->whitelabel->userActivationType = $this->whitelabelUserActivationType;
        $this->whitelabel->save();
        $this->whitelabelUser->delete();
        DBUtil::truncate_table('ip_login_try'); // dont throw tests if performing fail assertions
    }

    private function setWhitelabelUser(
        int $whitelabelUserActivationType,
        array $customFields = [],
        bool $isDeleted = false,
        bool $isActive = true,
        bool $isConfirmed = false
    ): void {
        $this->whitelabel->userActivationType = $whitelabelUserActivationType;
        $this->whitelabel->save();
        Cache::delete_all();

        $modifiedData = [
            'password' => self::TEST_USER_PASSWORD,
            'is_deleted' => $isDeleted,
            'is_active' => $isActive,
            'is_confirmed' => $isConfirmed,
            'whitelabel_id' => $this->whitelabel->id,
        ];
        $modifiedData = array_merge($modifiedData, $customFields);
        $this->whitelabelUser = $this->whitelabelUserFixture->addModifiedUser($modifiedData);
        $this->whitelabelUser->clear_cache();
    }

    /** @test */
    public function whitelabelWithRequiredActivationShouldDisplayActivationError(): void
    {
        $this->setWhitelabelUser(Whitelabel::ACTIVATION_TYPE_REQUIRED);

        $loginInput = $this->findById('inputLogin');
        $this->setInputValue($loginInput, $this->whitelabelUser->email);
        $loginInput = $this->findById('inputLoginPassword');
        $this->setInputValue($loginInput, self::TEST_USER_PASSWORD);

        $this->findByType('button', 'submit')->click();
        $this->assertRedirect(self::LOGIN_URL);

        $this->assertFlashMessage(
            'Your account is not active. Please follow the activation link provided in the e-mail, try to resend or contact us for manual activation.'
        );

        $this->assertUserIsNotLoggedIn();

        $parent = $this->findByClasses('div', 'platform-alert.platform-alert-error');
        $aTag = $this->findChildByHtmlTag($parent, 'a');
        $link = $this->getHref($aTag);
        $this->assertValidResendLink($link);
    }

    /** @test */
    public function resendSuccess(): void
    {
        $this->whitelabelWithRequiredActivationShouldDisplayActivationError();
        $this->assertResendSuccess();
    }

    /** @test */
    public function resendFailUserCanResendOncePer24Hours(): void
    {
        $this->resendSuccess();
        $this->assertResendFailOncePer24Hours();
    }

    /** @test */
    public function resendFailWrongLink(): void
    {
        $this->setWhitelabelUser(Whitelabel::ACTIVATION_TYPE_OPTIONAL);
        $this->resendLink = self::HOMEPAGE . 'resend/12312312/3123123';
        $this->assertResendFailWrongLink();
    }

    /** @test */
    public function whitelabelWithOptionalActivationShouldRedirectToHomepageAndLogin(): void
    {
        $this->setWhitelabelUser(Whitelabel::ACTIVATION_TYPE_OPTIONAL);

        $loginInput = $this->findById('inputLogin');
        $this->setInputValue($loginInput, $this->whitelabelUser->email);
        $loginInput = $this->findById('inputLoginPassword');
        $this->setInputValue($loginInput, self::TEST_USER_PASSWORD);

        $this->findByType('button', 'submit')->click();
        $this->assertRedirect(self::HOMEPAGE);

        $expected = [
            [
                'message' => 'You have been successfully logged in!',
                'type' => 'platform-alert-success'
            ],
            [
                'message' => 'We have sent you an e-mail with the activation link. Please activate your e-mail for better website experience. You can resend the activation e-mail here.',
                'type' => 'platform-alert-info'
            ]
        ];

        $this->assertFlashMessages($expected);

        $this->assertUserIsLoggedIn();

        $parent = $this->findByClasses('div', 'platform-alert.platform-alert-info');
        $aTag = $this->findChildByHtmlTag($parent, 'a');
        $link = $this->getHref($aTag);
        $this->assertValidResendLink($link);
    }

    /** @test */
    public function whitelabelWithNoneActivationShouldRedirectToHomepageAndLogin(): void
    {
        $this->setWhitelabelUser(Whitelabel::ACTIVATION_TYPE_NONE);

        $loginInput = $this->findById('inputLogin');
        $this->setInputValue($loginInput, $this->whitelabelUser->email);
        $loginInput = $this->findById('inputLoginPassword');
        $this->setInputValue($loginInput, self::TEST_USER_PASSWORD);

        $this->findByType('button', 'submit')->click();
        $this->assertRedirect(self::HOMEPAGE);

        $this->assertFlashMessages(
            [
                [
                    'message' => 'You have been successfully logged in!',
                    'type' => 'platform-alert-success'
                ]
            ]
        );

        $this->assertUserIsLoggedIn();

        // shouldn't display activation flash
        $this->noExistsInBody('div platform-alert.platform-alert-info');
    }
}

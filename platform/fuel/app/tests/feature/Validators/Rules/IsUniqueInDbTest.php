<?php

namespace Tests\Feature\Validators\Rules;

use Container;
use Repositories\Orm\WhitelabelUserRepository;
use Tests\Fixtures\WhitelabelUserFixture;
use Fuel\Core\Validation;
use Models\Whitelabel;
use Models\WhitelabelUser;
use Test_Feature;
use Validators\Rules\Login;
use Validators\Rules\LoginUnique;

final class IsUniqueInDbTest extends Test_Feature
{
    protected $in_transaction = false;

    private const LOGIN = 'tester';
    private const PASSWORD = 'admin1234';

    private WhitelabelUserFixture $whitelabelUserFixture;
    private WhitelabelUser $whitelabelUser;
    private Whitelabel $whitelabel;

    public function setUp(): void
    {
        parent::setUp();

        $this->whitelabel = Container::get('whitelabel');
        $this->whitelabel->useLoginsForUsers = true;

        $this->whitelabelUserRepository = $this->container->get(WhitelabelUserRepository::class);
        $this->whitelabelUserFixture = $this->container->get(WhitelabelUserFixture::class);
        $this->whitelabelUserFixture->addUser(self::LOGIN, self::PASSWORD, 10, 10, $this->whitelabel);
        $this->whitelabelUser = $this->whitelabelUserFixture->user;

        WhitelabelUser::flush_cache();
    }

    public function tearDown(): void
    {
        if (!empty($this->whitelabelUser)) {
            $this->whitelabelUser->delete();
        }
    }

    /** @test */
    public function isUniqueInDbIsNotUniqueShouldReturnFalse(): void
    {
        $inputToValidate = [
            'user.login' => self::LOGIN
        ];
        $this->setInput('POST', $inputToValidate);

        $validation = Validation::forge('login' . uniqid());

        $rule = new LoginUnique('user.login', 'User Login');
        $rule->setValidation($validation);
        $rule->applyRules();

        $validation->run($inputToValidate);

        $this->assertEquals(false, $validation->run($inputToValidate));
    }

    /** @test */
    public function isUniqueInDbShouldReturnTrue(): void
    {
        $this->whitelabelUser->delete();

        $inputToValidate = [
            'user.login' => self::LOGIN
        ];
        $this->setInput('POST', $inputToValidate);

        $validation = Validation::forge('login' . uniqid());

        $rule = new Login('user.login', 'User Login');
        $rule->setValidation($validation);
        $rule->applyRules();
        $validation->run($inputToValidate);

        $this->assertEquals(true, $validation->run($inputToValidate));
    }
}

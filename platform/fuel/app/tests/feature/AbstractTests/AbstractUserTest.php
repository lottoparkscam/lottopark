<?php

namespace Tests\Feature\AbstractTests;

use Container;
use Models\Whitelabel;
use Repositories\Orm\WhitelabelUserRepository;
use Test_Feature;
use Tests\Fixtures\WhitelabelUserFixture;
use Fuel\Core\Cache;
use Fuel\Core\Session;
use Helpers\UserHelper;
use Models\IpLoginTry;
use Models\WhitelabelUser;

/**
 * Abstract class for mocking users' methods
 */
abstract class AbstractUserTest extends Test_Feature
{
    protected $in_transaction = false;

    protected const EMAIL = 'testingLogin@gg.int';
    protected const LOGIN = 'testingLogin';
    protected const PASSWORD = 'admin1234';

    protected WhitelabelUserRepository $whitelabelUserRepository;
    protected WhitelabelUser $whitelabelUser;
    protected WhitelabelUserFixture $whitelabelUserFixture;
    protected Whitelabel $whitelabel;

    public function setUp(): void
    {
        parent::setUp();
        self::truncate(IpLoginTry::class);

        $this->whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $this->whitelabelUserFixture =  Container::get(WhitelabelUserFixture::class);

        $this->resetInput();
        $this->whitelabel = $this->container->get('whitelabel');

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->resetInput();
        Session::delete('user');

        if (isset($this->whitelabelUser)) {
            $this->whitelabelUser->delete();
        }
    }

    protected function setAuthInput(string $loginMethod, string $customPassword = ''): void
    {
        $this->setInput('POST', [
            'login' => [
                $loginMethod => $loginMethod === 'email' ? self::EMAIL : self::LOGIN,
                'password' => empty($customPassword) ? self::PASSWORD : $customPassword
            ]
        ], true);
    }

    /**
     * @param string $loginMethod Available: email/login
     * @param array $credentials optional eg. ['password' => 'asd', 'email' => '...', 'login'=>'asd']
     */
    protected function prepareUserVariables(string $loginMethod = 'email', bool $rememberUser = false, bool $setUserSession = true, array $credentials = []): void
    {
        $this->resetInput();

        // 1 - login, 0 - email
        $useLoginsForUsers = $loginMethod === 'login';
        $this->whitelabel->useLoginsForUsers = $useLoginsForUsers;
        $this->whitelabel->userActivationType = Whitelabel::ACTIVATION_TYPE_OPTIONAL;
        $this->whitelabel->save();
        $this->whitelabel->flush_cache();

        Cache::delete_all();
        $this->whitelabel = $this->container->get('whitelabel');
        $this->setAuthInput($loginMethod);

        $data = [
            'whitelabel_id' => $this->whitelabel->id,
            'password' => $credentials['password'] ?? self::PASSWORD,
            'email' => $credentials['email'] ?? self::EMAIL,
            'login' => $credentials['login'] ?? self::LOGIN,
            'is_deleted' => false,
            'is_active' => true,
        ];

        $this->whitelabelUser = $this->whitelabelUserFixture->addModifiedUser($data);

        if ($setUserSession) {
            UserHelper::setUserSession(
                $this->whitelabelUser->id,
                $this->whitelabelUser->token,
                $this->whitelabelUser->hash,
                self::EMAIL,
                $rememberUser
            );
        }
    }
}

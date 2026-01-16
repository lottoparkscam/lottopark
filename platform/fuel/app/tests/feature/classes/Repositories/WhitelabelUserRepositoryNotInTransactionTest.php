<?php

namespace Tests\Feature\Classes\Repositories;

use Models\WhitelabelUser;
use Tests\Feature\AbstractTests\AbstractUserTest;

final class WhitelabelUserRepositoryNotInTransactionTest extends AbstractUserTest
{
    /** @test */
    public function checkCredentialsWithWrongCredentialsShouldReturnNull(): void
    {
        $this->prepareUserVariables();
        $actual = $this->whitelabelUserRepository->getUser(1, 'iubsicusabuycasbyubfuasixni@xx.cgg', 'xisabuidfasbuxisabidbuiasdba');
        $this->assertNull($actual);
    }

    /** @test */
    public function loginByEmailWithValidCredentialsShouldReturnUserModel(): void
    {
        $this->prepareUserVariables();
        $expectedWhitelabelUserId = $this->whitelabelUser->id;
        $this->prepareUserVariables('email', false, true, ['email' => 'asd@asd.loc', 'login' => 'asd']);
        $actual = $this->whitelabelUserRepository->getUser($this->whitelabel->id, self::EMAIL, self::PASSWORD);
        $this->assertInstanceOf(WhitelabelUser::class, $actual);
        $this->assertSame($expectedWhitelabelUserId, $actual->id);
        $actual->delete();
    }

    /** @test */
    public function checkCredentialsByLoginWithValidCredentialsShouldReturnUserModel(): void
    {
        $this->prepareUserVariables('login');
        $expectedWhitelabelUserId = $this->whitelabelUser->id;
        $this->prepareUserVariables('login', false, true, ['email' => 'asd@asd.loc', 'login' => 'asd']);

        $actual = $this->whitelabelUserRepository->getUser($this->whitelabel->id, self::LOGIN, self::PASSWORD);
        $this->assertInstanceOf(WhitelabelUser::class, $actual);
        $this->assertSame($expectedWhitelabelUserId, $actual->id);
        $actual->delete();
    }
}

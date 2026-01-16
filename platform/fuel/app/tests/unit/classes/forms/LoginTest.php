<?php

namespace Tests\Unit\Classes\Forms;

use Repositories\Orm\WhitelabelUserRepository;
use Test_Unit;

class LoginTest extends Test_Unit
{
    /**
     * @test
     * @dataProvider loginBy_dataProvider
     */
    public function findEnabledUser_byLoginOrEmail_methodExists(
        bool $isLoginByUserLoginAllowed,
        string $expectedMethodName
    ): void {
        $methodName = 'findEnabledUserBy' . ($isLoginByUserLoginAllowed ? 'Login' : 'Email');
        $actual = method_exists(WhitelabelUserRepository::class, $methodName);

        $this->assertEquals($expectedMethodName, $methodName);
        $this->assertSame(true, $actual);
    }

    public function loginBy_dataProvider(): array
    {
        return [
            // [isLoginByUserLoginAllowed, expectedMethodName]
            [true, 'findEnabledUserByLogin'],
            [false, 'findEnabledUserByEmail'],
        ];
    }
}

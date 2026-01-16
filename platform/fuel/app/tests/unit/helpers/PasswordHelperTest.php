<?php

namespace Tests\Unit\Helpers;

use Helpers\PasswordHelper;
use Test_Unit;

final class PasswordHelperTest extends Test_Unit
{
    /**
     * @test
     * @dataProvider regexProvider
     */
    public function succeedGeneratedPassword(string $regex, bool $expectedResults): void
    {
        $password = PasswordHelper::generateRandomPassword();
        $this->assertEquals(preg_match($regex, $password), $expectedResults);
    }

    public function regexProvider(): array
    {
        return [
            ['/[0-9]/', true],
            ['/[A-Z]/', true],
            ['/[a-z]/', true],
            ['/[!#$%&()*+,-.:;<=>?@^_`{|}~\[\]]/', true],
        ];
    }

    /** @test */
    public function succeedGeneratedPassword_withAllRequireChar(): void
    {
        $password = PasswordHelper::generateRandomPassword();
        $this->assertTrue((bool)preg_match('/(?=.*[A-Z])(?=.*[0-9])(?=.*[a-z])(?=.*[!#$%&()*+,-.:;<=>?@^_`{|}~\[\]])/', $password));
    }

    /** @test */
    public function succeedGeneratedPassword_correctLength(): void
    {
        $password = PasswordHelper::generateRandomPassword();
        $this->assertTrue(strlen($password) > 14);
    }

    /** @test */
    public function succeedGeneratedPassword_shouldBeOtherThenLastPassword(): void
    {
        $password1 = PasswordHelper::generateRandomPassword();
        $password2 = PasswordHelper::generateRandomPassword();
        $this->assertNotEquals($password1, $password2);
    }
}

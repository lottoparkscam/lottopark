<?php

namespace Helpers;

final class PasswordHelper
{
    private const UPPERCASE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const LOWERCASE = 'abcdefghijklmnopqrstuvwxyz';
    private const SPECIAL = '!#$%&()*+,-.:;<=>?@[]^_`{|}~';
    private const NUMBERS = '0123456789';

    public static function generateRandomPassword(): string
    {
        $characters = self::UPPERCASE . self::LOWERCASE . self::SPECIAL . self::NUMBERS;
        $password = '';
        $characterListLength = mb_strlen($characters, '8bit') - 1;
        $shouldGeneratedPasswordAgain = true;
        while($shouldGeneratedPasswordAgain) {
            for($i = 0; $i <= 24; $i++){
                $password .= $characters[random_int(0, $characterListLength)];
                $generatedPasswordShouldContainAllTypesChars = preg_match('/(?=.*[A-Z])(?=.*[0-9])(?=.*[a-z])(?=.*[!#$%&()*+,-.:;<=>?@^_`{|}~\[\]])/', $password);
                if ($generatedPasswordShouldContainAllTypesChars) {
                    $shouldGeneratedPasswordAgain = false;
                }
            }
        }
        return $password;
    }
}

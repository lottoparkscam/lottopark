<?php

namespace Helpers;

final class TransactionTokenEncryptorHelper
{
    /**
     * It is very important not to edit this character map, as it is a critical
     * area and should not be changed
     */
    private const ENCRYPTION_MAP = [
        'A' => 'N',
        'B' => 'O',
        'C' => 'P',
        'D' => 'Q',
        'E' => 'R',
        'F' => 'S',
        'G' => 'T',
        'H' => 'U',
        'I' => 'V',
        'J' => 'W',
        'K' => 'X',
        'L' => 'Y',
        'M' => 'Z',
        'N' => '0',
        'O' => '1',
        'P' => '2',
        'Q' => '3',
        'R' => '4',
        'S' => '5',
        'T' => '6',
        'U' => '7',
        'V' => '8',
        'W' => '9',
        'X' => 'A',
        'Y' => 'B',
        'Z' => 'C',
        '0' => 'D',
        '1' => 'E',
        '2' => 'F',
        '3' => 'G',
        '4' => 'H',
        '5' => 'I',
        '6' => 'J',
        '7' => 'K',
        '8' => 'L',
        '9' => 'M',
    ];

    public static function encrypt(string $token): string
    {
        $encryptedToken = '';

        for ($i = 0; $i < strlen($token); $i++) {
            $char = $token[$i];

            $encryptedToken .= self::ENCRYPTION_MAP[$char] ?? $char;
        }

        return $encryptedToken;
    }

    public static function decrypt(string $encryptedToken): string
    {
        $decryptedToken = '';

        foreach (str_split($encryptedToken) as $char) {
            $originalChar = array_search($char, self::ENCRYPTION_MAP);
            $decryptedToken .= ($originalChar !== false) ? $originalChar : $char;
        }

        return $decryptedToken;
    }
}

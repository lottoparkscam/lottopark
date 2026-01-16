<?php

namespace unit\classes\helpers;

use Helpers\TransactionTokenEncryptorHelper;
use Test_Unit;

class TransactionTokenEncryptorHelperTest extends Test_Unit
{
    /**
     * @test
     * @dataProvider tokenProvider
     */
    public function shouldEncryptToken(string $original, string $expected)
    {
        $encryptedToken = TransactionTokenEncryptorHelper::encrypt($original);
        $this->assertEquals($expected, $encryptedToken);
    }

    /**
     * @test
     * @dataProvider tokenProvider
     */
    public function shouldDecryptToken(string $original, string $expected)
    {
        $decryptedToken = TransactionTokenEncryptorHelper::decrypt($expected);
        $this->assertEquals($original, $decryptedToken);
    }

    public function tokenProvider(): array
    {
        return [
            // original, encrypted
            ['LPD266088786', 'Y2QFJJDLLKLJ'],
            ['RFD198893075', '4SQEMLLMGDKI'],
            ['MMD810301590', 'ZZQLEDGDEIMD'],
            ['DJD661673664', 'QWQJJEJKGJJH'],
            ['FRP900152414', 'S42MDDEIFHEH'],
        ];
    }
}

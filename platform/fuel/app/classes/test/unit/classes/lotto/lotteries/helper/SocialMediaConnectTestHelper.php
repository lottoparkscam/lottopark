<?php

namespace Tests\Unit\Classes\Services\SocialMediaConnect;

use Hybridauth\User\Profile;
use PHPUnit\Framework\MockObject\MockObject;

class SocialMediaConnectTestHelper
{
    public static function setSocialUserProfileTestValue(Profile|MockObject $profile): void
    {
        $profile->email = 'tester@test.pl';
        $profile->firstName = 'Olek';
        $profile->lastName = 'kelo';
        $profile->phone = '234521334';
        $profile->identifier = '123asdqweda231123';
    }
}

<?php

namespace Test\Selenium\Interfaces;

interface UserInterface
{
    public const HOMEPAGE = 'https://lottopark.loc/';
    public const LOGIN_URL = self::HOMEPAGE . 'auth/login/';
    public const REGISTER_URL = self::HOMEPAGE . 'auth/signup/';
    public const DEPOSIT_URL = self::HOMEPAGE . 'deposit/';
    public const USER_TRANSACTIONS_URL = self::HOMEPAGE . '/account/transactions/';

    public const TEST_USER_EMAIL = 'selenium@gmail.com';
    public const TEST_USER_LOGIN = 'selenium';
    public const TEST_USER_PASSWORD = 'admin1234';
}

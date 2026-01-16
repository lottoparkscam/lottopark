<?php

namespace Test\Selenium\Interfaces;

interface CrmInterface
{
    public const CRM_URL = 'https://admin.whitelotto.loc/';
    public const CRM_LOGIN_URL = self::CRM_URL . 'login';
    public const CRM_USERS_PATH = self::CRM_URL . 'whitelabel/users';
    public const CRM_SUPERADMIN_LOGIN = 'superadmin';
    public const CRM_SUPERADMIN_PASSWORD = 'superadminpassword';
}
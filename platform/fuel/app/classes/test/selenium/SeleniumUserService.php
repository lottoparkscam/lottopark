<?php

namespace Test\Selenium;

use DB;
use Container;
use Models\WhitelabelUser;
use Test\Selenium\Abstracts\AbstractSelenium;
use Test\Selenium\Login\SeleniumLoginService;
use Repositories\Orm\WhitelabelUserRepository;

class SeleniumUserService extends AbstractSelenium
{
    public function deleteUser(string $email = SeleniumLoginService::TEST_USER_EMAIL): void
    {
        $repository = Container::get(WhitelabelUserRepository::class);
        $repository->findOneBy('email', $email)->delete();
    }

    public function updateUserTimezone(string $email, string $timezone): void
    {
        $updateQuery = DB::query(
            "UPDATE whitelabel_user 
            SET timezone = :timezone
            WHERE whitelabel_user.email = :email"
        );
        $updateQuery->param("timezone", $timezone);
        $updateQuery->param("email", $email);
        $updateQuery->execute();
    }

    public function getLastUser(): WhitelabelUser
    {
        return WhitelabelUser::find('last');
    }
}
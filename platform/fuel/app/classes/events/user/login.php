<?php

/**
 * This event should be the same as when logging in with socials.
 * Social login is in LastStepsService on line 162 and 200
 */
class Events_User_Login extends Events_Event
{
    public static function handle(array $data): void
    {
        parent::handle($data);
        self::run($data);
    }

    protected static function run(array $data): void
    {
        EventLoginHelper::addScripts($data['login_data']);
        parent::run($data);
    }
}

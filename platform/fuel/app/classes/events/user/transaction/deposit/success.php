<?php

class Events_User_Transaction_Deposit_Success extends Events_Event
{
    public static function handle(array $data): void
    {
        self::run($data);
    }

    protected static function run(array $data): void
    {
        Forms_Wordpress_Pixels_Gtag::trigger_event("deposit", $data["plugin_data"]);
        Forms_Wordpress_Pixels_Facebook::trigger_event("Deposit", $data["plugin_data"], false, true);
    }
}

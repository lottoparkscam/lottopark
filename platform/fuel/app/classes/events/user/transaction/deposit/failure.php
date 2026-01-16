<?php

class Events_User_Transaction_Deposit_Failure extends Events_Event
{
    public static function handle(array $data): void
    {
        $data['onCasinoEvent'] = true;
        self::run($data);
    }

    protected static function run(array $data): void
    {
        Forms_Wordpress_Pixels_Gtag::trigger_event("deposit_failure", $data["plugin_data"]);
        Forms_Wordpress_Pixels_Facebook::trigger_event("DepositFailure", $data["plugin_data"], false, true);

        parent::run($data);
    }
}

<?php

class Events_User_Account_Confirm extends Events_Event
{
    public static function handle(array $data): void
    {
        parent::handle($data);
        self::run($data);
    }
    protected static function run(array $data): void
    {
        Forms_Wordpress_Pixels_Gtag::trigger_event("activated", ["method" => "E-mail"], true);

        $fb_data = [
            "content_name" => $data["plugin_data"]["token"],
            "status" => true
        ];
        Forms_Wordpress_Pixels_Facebook::trigger_event("CompleteRegistration", $fb_data, true);

        parent::run($data);
    }
}

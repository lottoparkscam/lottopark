<?php

class Events_User_Item_View extends Events_Event
{
    public static function handle(array $data): void
    {
        self::run($data);
    }

    protected static function run(array $data): void
    {
        // Active implementation: @see resources/lotto-platform/js/modules/Lotto.js:3986
        //Forms_Wordpress_Pixels_Gtag::trigger_event("view_item", $data["plugin_data"]);
        Forms_Wordpress_Pixels_Facebook::trigger_event("ViewContent", $data["plugin_data"]);
    }
}

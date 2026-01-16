<?php

class Events_User_Cart_Add extends Events_Event
{
    public static function handle(array $data): void
    {
        self::run($data);
    }

    protected static function run(array $data): void
    {
        // Active implementation: @see wordpress/wp-content/themes/base/page-order.php:368
        //Forms_Wordpress_Pixels_Gtag::trigger_event("add_to_cart", $data["plugin_data"], true);
        Forms_Wordpress_Pixels_Facebook::trigger_event("AddToCart", $data["plugin_data"], true);
    }
}

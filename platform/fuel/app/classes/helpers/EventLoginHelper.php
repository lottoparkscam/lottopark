<?php

class EventLoginHelper
{
    public static function addScripts(array $data): void
    {
        Forms_Wordpress_Pixels_Gtag::trigger_event("login", $data, true);
        /** Removed direct Facebook Pixel call; the event is now sent via GTM */
        //Forms_Wordpress_Pixels_Facebook::trigger_event("Login", $pixel_data, true, true);
    }
}
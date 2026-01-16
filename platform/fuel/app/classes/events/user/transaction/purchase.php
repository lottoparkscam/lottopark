<?php

class Events_User_Transaction_Purchase extends Events_Event
{
    protected static function add_custom_data_to_plugins(array $data): array
    {
        $data['plugin_data']['last_purchase_date'] = time();

        return $data;
    }
}

<?php

class Events_Whitelabel_Withdrawal_Approve extends Events_Event
{
    protected static function add_custom_data_to_plugins(array $data): array
    {
        $data['plugin_data']['last_balance_update'] = time();

        return $data;
    }
}

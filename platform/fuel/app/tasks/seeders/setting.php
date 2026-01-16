<?php

namespace Fuel\Tasks\Seeders;

/**
* Setting seeder.
*/
final class Setting extends Seeder
{

    protected function columnsStaging(): array
    {
        return [
            'setting' => ['id', 'name', 'value']
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'setting' => [
                [1, 'admin_login', 'blacklotto'],
                [2, 'admin_salt', '8d8f99c397be9970507c25d9c1754345aa5c8b5afd7321faec2f4931bdef6add355b0fed9860bfe84cf8af5907b70d2a6cd819a5f7fe59bc987838ec3d4356d4'],
                [3, 'admin_hash', 'a14bda6c6527bf2200b139110febe54a9db8e71d6e3559e5f619fdcde0a72f7b186345934e4a0c11141a40438af6c2711a2ce5d71f9dd28f18cbae1cb58a7bb9'],
                [4, 'admin_language', '1'],
                [5, 'admin_timezone', 'UTC'],
                [6, 'admin_firsterror', ''],
                [7, 'task_lockpurchase', '0'],
                [8, 'task_currentcurrency', '3'],
            ]
        ];
    }

}
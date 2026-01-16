<?php

namespace Fuel\Tasks\Seeders;

final class Admin_user_roles extends Seeder
{
    protected function rowsDevelopment(): array
    {
        return [
            'admin_user_role' => [
                [1, 'Super-administrator'],
                [2, 'Administrator'],
                [3, 'White-label super-administrator'],
                [4, 'White-label administrator']
            ]
        ];
    }

    protected function columnsStaging(): array
    {
        return [
            'admin_user_role' => ['id', 'role']
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'admin_user_role' => [
                [1, 'Super-administrator'],
                [2, 'Administrator'],
                [3, 'White-label super-administrator'],
                [4, 'White-label administrator']
            ]
        ];
    }
}

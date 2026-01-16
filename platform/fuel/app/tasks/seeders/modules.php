<?php

namespace Fuel\Tasks\Seeders;

use Helpers\CrmModuleHelper;

final class Modules extends Seeder
{
    public const MODULE_USERS_EDIT = 'users-edit';

    protected function rowsDevelopment(): array
    {
        return [
            'module' => [
                [1, 'admins-view'],
                [2, 'admins-edit'],
                [3, 'admins-delete'],
                [4, 'users-view'],
                [5, self::MODULE_USERS_EDIT],
                [6, 'users-delete'],
                [7, 'users-balance-edit'],
                [8, 'users-manual-deposit-add'],
                [9, 'transactions-view'],
                [10, 'transactions-edit'],
                [11, 'user-groups-view'],
                [12, 'user-groups-edit'],
                [13, 'user-groups-delete'],
                [14, 'tickets-view'],
                [15, 'tickets-edit'],
                [16, 'withdrawals-view'],
                [17, 'withdrawals-edit'],
                [18, 'raffle-tickets-view'],
                [19, CrmModuleHelper::MODULE_SEO_WIDGETS_GENERATOR],
            ]
        ];
    }

    protected function columnsStaging(): array
    {
        return [
            'module' => ['id', 'name']
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'module' => [
                [1, 'admins-view'],
                [2, 'admins-edit'],
                [3, 'admins-delete'],
                [4, 'users-view'],
                [5, 'users-edit'],
                [6, 'users-delete'],
                [7, 'users-balance-edit'],
                [8, 'users-manual-deposit-add'],
                [9, 'transactions-view'],
                [10, 'transactions-edit'],
                [11, 'user-groups-view'],
                [12, 'user-groups-edit'],
                [13, 'user-groups-delete'],
                [14, 'tickets-view'],
                [15, 'tickets-edit'],
                [16, 'withdrawals-view'],
                [17, 'withdrawals-edit'],
                [18, 'raffle-tickets-view'],
                [19, CrmModuleHelper::MODULE_SEO_WIDGETS_GENERATOR],
            ]
        ];
    }
}

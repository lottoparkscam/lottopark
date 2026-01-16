<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Contact_Emails_To_Whitelabel extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel',
            [
                'support_email' => [
                    'type' => 'varchar',
                    'constraint' => 80,
                    'null' => true,
                    'after' => 'display_deposit_button'
                ],
                'payment_email' => [
                    'type' => 'varchar',
                    'constraint' => 80,
                    'null' => true,
                    'after' => 'support_email'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel',
            [
                'support_email',
                'payment_email',
            ]
        );
    }
}

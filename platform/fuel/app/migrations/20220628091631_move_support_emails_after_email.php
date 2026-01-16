<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

/**
 * We have to provide here 'type' => 'varchar', 'constraint' => 80 otherwise fuel will throw exception
 * Type/Constraint has not been changed
 * @see platform/fuel/app/migrations/20220512082302_add_contact_emails_to_whitelabel.php
 */
final class Move_Support_Emails_After_Email extends Database_Migration_Graceful
{
    private const TABLE = 'whitelabel';

    protected function up_gracefully(): void
    {
        DBUtil::modify_fields(self::TABLE, [
            'support_email' => ['type' => 'varchar', 'constraint' => 80, 'after' => 'email'],
            'payment_email' => ['type' => 'varchar', 'constraint' => 80, 'after' => 'support_email'],
        ]);
    }

    protected function down_gracefully(): void
    {
        DBUtil::modify_fields(self::TABLE, [
            'support_email' => ['type' => 'varchar', 'constraint' => 80, 'after' => 'display_deposit_button'],
            'payment_email' => ['type' => 'varchar', 'constraint' => 80, 'after' => 'support_email'],
        ]);
    }
}

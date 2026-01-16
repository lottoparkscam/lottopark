<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Models\WhitelabelUser;

final class add_pending_email_to_whitelabel_user_table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            WhitelabelUser::get_table_name(),
            [
                'pending_email' => ['type' => 'varchar', 'constraint' => 254, 'null' => true, 'after' => 'email'],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            WhitelabelUser::get_table_name(),
            [
                'pending_email',
            ]
        );
    }
}
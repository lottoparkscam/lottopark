<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Models\WhitelabelOAuthClient;

final class add_casino_url_to_whitelabel_oauth_client_table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            WhitelabelOAuthClient::get_table_name(),
            [
                'casino_url' => ['type' => 'varchar', 'constraint' => 2000, 'null' => true, 'after' => 'redirect_uri'],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            WhitelabelOAuthClient::get_table_name(),
            [
                'casino_url',
            ]
        );
    }
}
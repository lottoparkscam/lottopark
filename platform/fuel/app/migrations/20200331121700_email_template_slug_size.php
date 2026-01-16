<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

/**
 * Description of Add_Is_Report_To_Whitelabel
 */
final class Email_Template_Slug_Size extends \Database_Migration_Graceful
{
    /**
     *
     * @return void
     */
    public function up_gracefully(): void
    {
        \DBUtil::modify_fields('mail_templates', [
            'slug' => [ 'constraint' => 45, 'type' => 'varchar'],
        ]);

    }

    /**
     *
     * @return void
     */
    public function down_gracefully(): void
    {
        \DBUtil::modify_fields('mail_templates', [
            'slug' => ['constraint' => 20, 'type' => 'varchar'],
        ]);
    }
}

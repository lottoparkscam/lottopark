<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Change_Plural_Table_Names extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::rename_table('admin_whitelabels', 'admin_whitelabel');
        DBUtil::rename_table('admin_user_table_columns', 'admin_user_table_column');
        DBUtil::rename_table('admin_whitelabel_modules', 'admin_whitelabel_module');
        DBUtil::rename_table('admin_user_roles', 'admin_user_role');
        DBUtil::rename_table('mail_templates', 'mail_template');
        DBUtil::rename_table('whitelabel_mail_templates', 'whitelabel_mail_template');
        DBUtil::rename_table('raffle_rule_tier_in_kind_prizes', 'raffle_rule_tier_in_kind_prize');
        DBUtil::rename_table('wordpress_tags', 'wordpress_tag');
    }

    protected function down_gracefully(): void
    {
        DBUtil::rename_table('admin_whitelabel', 'admin_whitelabels');
        DBUtil::rename_table('admin_user_table_column', 'admin_user_table_columns');
        DBUtil::rename_table('admin_whitelabel_module', 'admin_whitelabel_modules');
        DBUtil::rename_table('admin_user_role', 'admin_user_roles');
        DBUtil::rename_table('mail_template', 'mail_templates');
        DBUtil::rename_table('whitelabel_mail_template', 'whitelabel_mail_templates');
        DBUtil::rename_table('raffle_rule_tier_in_kind_prize', 'raffle_rule_tier_in_kind_prizes');
        DBUtil::rename_table('wordpress_tag', 'wordpress_tags');
    }
}
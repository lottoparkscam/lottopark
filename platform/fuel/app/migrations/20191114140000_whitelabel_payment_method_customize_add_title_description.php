<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

/**
 * Description of Whitelabel_Payment_Method_Customize_Add_Title_Description
 */
final class Whitelabel_Payment_Method_Customize_Add_Title_Description extends \Database_Migration_Graceful
{
    /**
     *
     * @return void
     */
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel_payment_method_customize', [
            'title_in_description' => [
                'type' => 'varchar',
                'constraint' => 255,
                'default' => '',
                'after' => 'title_for_mobile'
            ],
        ]);
    }
    
    /**
     *
     * @return void
     */
    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('whitelabel_payment_method_customize', 'title_in_description');
    }
}

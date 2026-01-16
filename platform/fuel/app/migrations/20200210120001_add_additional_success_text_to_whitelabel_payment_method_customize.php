<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

final class Add_Additional_Success_Text_To_Whitelabel_Payment_Method_Customize extends \Database_Migration_Graceful
{
    /**
     *
     * @return void
     */
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel_payment_method_customize', [
            'additional_success_text' => [
                'type' => 'text',
                'null' => true,
                'default' => null,
                'after' => 'additional_failure_text'
            ],
        ]);
    }

    /**
     *
     * @return void
     */
    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('whitelabel_payment_method_customize', [
            'additional_success_text'
        ]);
    }
}

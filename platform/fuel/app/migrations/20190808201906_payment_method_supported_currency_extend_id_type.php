<?php

namespace Fuel\Migrations;

/**
 * Description of Payment_Method_Supported_Currency_Extend_Id_Type
 *
 */
class Payment_Method_Supported_Currency_Extend_Id_Type
{
    public function up()
    {
        \DBUtil::modify_fields('payment_method_supported_currency', [
            'id' => ['type' => 'MEDIUMINT', 'unsigned' => true, 'auto_increment' => true],
        ]);
    }
    
    public function down()
    {
        // TODO: {Vordis 2019-10-25 09:45:16} we cannot revert this without potentially losing data, so we will just skip it. better approach would be to catch and ignore Fuel\Core\Database_Exception: 22003 - SQLSTATE[22003]
        // \DBUtil::modify_fields('payment_method_supported_currency', array(
        //     'id' => array('type' => 'TINYINT', 'unsigned' => true, 'auto_increment' => false),
        // ));
    }
}

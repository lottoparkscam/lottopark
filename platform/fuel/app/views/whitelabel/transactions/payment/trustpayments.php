<?php

use Fuel\Core\Security;

$names = [
    'transactionstartedtimestamp' => 'Transaction Started Timestamp',
    'sitereference' => 'Site Reference',
    'interface' => 'Interface',
    'livestatus' => 'Live Status',
    'issuer' => 'Issuer',
    'settleduedate' => 'Settle Due Date',
    'errorcode' => 'Error Code',
    'baseamount' => 'Base Amount',
    'tid' => 'TID',
    'securityresponsepostcode' => 'Security Response Post Code',
    'transactionreference' => 'Transaction Reference',
    'merchantname' => 'Merchant Name',
    'paymenttypedescription' => 'Payment Type Description',
    'accounttypedescription' => 'Account Type Description',
    'fraudrating' => 'Fraud Rating',
    'splitfinalnumber' => 'Split Final Number',
    'acquirerresponsecode' => 'Acquirer Response Code',
    'requesttypedescription' => 'Request Type Description',
    'expirydate' => 'Expiry Date',
    'securityresponsesecuritycode' => 'Security Response Security Code',
    'currencyiso3a' => 'Currency ISO 3A',
    'authcode' => 'Auth Code',
    'settlebaseamount' => 'Settle Base Amount',
    'errormessage' => 'Error Message',
    'merchantcountryiso2a' => 'Merchant Country ISO 2A',
    'maskedpan' => 'Masked Pan',
    'securityresponseaddress' => 'Security Response Address',
    'operatorname' => 'Operator Name',
    'settlestatus' => 'Settle Status',
    'orderreference' => 'Order Reference',
    'requestreference' => 'Request Reference',
    'notificationreference' => 'Notification Reference',
];

if (isset($additionalDataJson) && is_array($additionalDataJson)) {
    echo '<h3>Trustpayments Additional Data</h3>';
    /** data from POST/GET are in 'all' index so it's duplication on front */
    unset($additionalDataJson['post']);
    unset($additionalDataJson['get']);
    foreach ($additionalDataJson as $key => $value) {
        if (empty($value)) {
            continue;
        }
        $label = !empty($names[$key]) ? $names[$key] : $key;
        $label = ucfirst(str_replace('_', ' ', $label));
        $key = Security::htmlentities($key);

        if (is_array($value)) {
            foreach ($value as $property => $propertyValue) {
                $property = !empty($names[$property]) ? $names[$property] : Security::htmlentities($property);
                $propertyValue = Security::htmlentities($propertyValue);
                echo <<<ARRAY
                    <span class="details-label" title="$property">$property</span>
                    <span class="details-value">$propertyValue</span>
                    <br>
                ARRAY;
            }
        } else {
            $value = Security::htmlentities($value);
            $label = Security::htmlentities($label);
            echo <<<NO_ARRAY
                <span class="details-label" title="$key">$label</span>
                <span class="details-value">$value</span>
                <br>
            NO_ARRAY;
        }
    }
}

<?php

/**
 * Description of Forms_Wordpress_Payment_TruevoCCcodes
 */
class Forms_Wordpress_Payment_TruevoCCcodes
{
    /**
     *
     * @return array
     */
    public static function get_successfully_processed_transactions_codes(): array
    {
        $codes = [
            '000.000.000' => "Transaction succeeded",
            '000.000.100' => "successful request",
            '000.100.110' => "Request successfully processed in 'Merchant in Integrator Test Mode'",
            '000.100.111' => "Request successfully processed in 'Merchant in Validator Test Mode'",
            '000.100.112' => "Request successfully processed in 'Merchant in Connector Test Mode'",
            '000.300.000' => "Two-step transaction succeeded",
            '000.300.100' => "Risk check successful",
            '000.300.101' => "Risk bank account check successful",
            '000.300.102' => "Risk report successful",
            '000.310.100' => "Account updated",
            '000.310.101' => "Account updated (Credit card expired)",
            '000.310.110' => "No updates found, but account is valid",
            '000.600.000' => "transaction succeeded due to external update",
        ];
        
        return $codes;
    }
    
    /**
     *
     * @return array
     */
    public static function get_successfully_processed_transactions_codes_keys(): array
    {
        $codes_full = self::get_successfully_processed_transactions_codes();
        $codes = array_keys($codes_full);
        
        return $codes;
    }
    
    /**
     *
     * @return array
     */
    public static function get_pending_transaction_codes(): array
    {
        $codes = [
            '000.200.000' => 'transaction pending',
            '000.200.001' => 'Transaction pending for acquirer, the consumer is not present',
            '000.200.100' => 'successfully created checkout',
            '000.200.101' => 'successfully updated checkout',
            '000.200.102' => 'successfully deleted checkout',
            '000.200.200' => 'Transaction initialized',
            '000.400.110' => 'Transaction approved without requesting the full authentication from the cardholder'
        ];
        
        return $codes;
    }
    
    /**
     *
     * @return array
     */
    public static function get_pending_transaction_codes_keys(): array
    {
        $codes_full = self::get_pending_transaction_codes();
        $codes = array_keys($codes_full);
        
        return $codes;
    }
    
    /**
     *
     * @return array
     */
    public static function get_reject_by_bank_codes(): array
    {
        $codes = [
            '800.100.100' => "transaction declined for unknown reason",
            '800.100.150' => "transaction declined (refund on gambling tx not allowed)",
            '800.100.151' => "transaction declined (invalid card)",
            '800.100.152' => "transaction declined by authorization system",
            '800.100.153' => "transaction declined (invalid CVV)",
            '800.100.154' => "transaction declined (transaction marked as invalid)",
            '800.100.155' => "transaction declined (amount exceeds credit)",
            '800.100.156' => "transaction declined (format error)",
            '800.100.157' => "transaction declined (wrong expiry date)",
            '800.100.158' => "transaction declined (suspecting manipulation)",
            '800.100.159' => "transaction declined (stolen card)",
            '800.100.160' => "transaction declined (card blocked)",
            '800.100.161' => "transaction declined (too many invalid tries)",
            '800.100.162' => "transaction declined (limit exceeded)",
            '800.100.163' => "transaction declined (maximum transaction frequency exceeded)",
            '800.100.164' => "transaction declined (merchants limit exceeded)",
            '800.100.165' => "transaction declined (card lost)",
            '800.100.166' => "transaction declined (Incorrect personal identification number)",
            '800.100.167' => "transaction declined (referencing transaction does not match)",
            '800.100.168' => "transaction declined (restricted card)",
            '800.100.169' => "transaction declined (card type is not processed by the authorization center)",
            '800.100.170' => "transaction declined (transaction not permitted)",
            '800.100.171' => "transaction declined (pick up card)",
            '800.100.172' => "transaction declined (account blocked)",
            '800.100.173' => "transaction declined (invalid currency, not processed by authorization center)",
            '800.100.174' => "transaction declined (invalid amount)",
            '800.100.175' => "transaction declined (invalid brand)",
            '800.100.176' => "transaction declined (account temporarily not available. Please try again later)",
            '800.100.177' => "transaction declined (amount field should not be empty)",
            '800.100.178' => "transaction declined (PIN entered incorrectly too often)",
            '800.100.179' => "transaction declined (exceeds withdrawal count limit)",
            '800.100.190' => "transaction declined (invalid configuration data)",
            '800.100.191' => "transaction declined (transaction in wrong state on aquirer side)",
            '800.100.192' => "transaction declined (invalid CVV, Amount has still been reserved on the customer's card and will be released in a few business days. Please ensure the CVV code is accurate before retrying the transaction)",
            '800.100.195' => "transaction declined (UserAccount Number/ID unknown)",
            '800.100.196' => "transaction declined (registration error)",
            '800.100.197' => "transaction declined (registration cancelled externally)",
            '800.100.198' => "transaction declined (invalid holder)",
            '800.100.402' => "cc/bank account holder not valid",
            '800.100.403' => "transaction declined (revocation of authorisation order)",
            '800.100.500' => "Card holder has advised his bank to stop this recurring payment",
            '800.100.501' => "Card holder has advised his bank to stop all recurring payments for this merchant",
            '800.700.100' => "transaction for the same session is currently being processed, please try again later.",
            '800.700.101' => "family name too long",
            '800.700.201' => "given name too long",
            '800.700.500' => "company name too long",
            '800.800.102' => "Invalid street",
            '800.800.202' => "Invalid zip",
            '800.800.302' => "Invalid city",
        ];
        
        return $codes;
    }
    
    /**
     *
     * @return array
     */
    public static function get_reject_by_bank_codes_keys(): array
    {
        $codes_full = self::get_reject_by_bank_codes();
        $codes = array_keys($codes_full);
        
        return $codes;
    }
    
    /**
     *
     * @return array
     */
    public static function get_reject_communication_codes(): array
    {
        $codes = [
            '000.400.030' => "Transaction partially failed (please reverse manually due to failed automatic reversal)",
            '900.100.100' => "unexpected communication error with connector/acquirer",
            '900.100.200' => "error response from connector/acquirer",
            '900.100.201' => "error on the external gateway (e.g. on the part of the bank, acquirer,...)",
            '900.100.202' => "invalid transaction flow, the requested function is not applicable for the referenced transaction.",
            '900.100.203' => "error on the internal gateway",
            '900.100.300' => "timeout, uncertain result",
            '900.100.301' => "Transaction timed out without response from connector/acquirer. It was reversed.",
            '900.100.310' => "Transaction timed out due to internal system misconfiguration. Request to acquirer has not been sent.",
            '900.100.400' => "timeout at connectors/acquirer side",
            '900.100.500' => "timeout at connectors/acquirer side (try later)",
            '900.100.600' => "connector/acquirer currently down",
            '900.200.100' => "Message Sequence Number of Connector out of sync",
            '900.300.600' => "user session timeout",
            '900.400.100' => "unexpected communication error with external risk provider",
            '300.100.100' => "SCA was not applied (please re-submit the transaction)"
        ];
        
        return $codes;
    }
    
    /**
     *
     * @return array
     */
    public static function get_reject_communication_codes_keys(): array
    {
        $codes_full = self::get_reject_communication_codes();
        $codes = array_keys($codes_full);
        
        return $codes;
    }
    
    /**
     *
     * @return array
     */
    public static function get_reject_system_codes(): array
    {
        $codes = [
            '600.100.100' => "Unexpected Integrator Error (Request could not be processed)",
            '800.500.100' => "direct debit transaction declined for unknown reason",
            '800.500.110' => "Unable to process transaction - ran out of terminalIds - please contact acquirer",
            '800.600.100' => "transaction is being already processed",
            '800.800.800' => "The payment system is currenty unavailable, please contact support in case this happens again.",
            '800.800.801' => "The payment system is currenty unter maintenance. Please apologize for the inconvenience this may cause. If you were not informed of this maintenance window in advance, contact your sales representative.",
            '999.999.888' => "UNDEFINED PLATFORM DATABASE ERROR",
            '999.999.999' => "UNDEFINED CONNECTOR/ACQUIRER ERROR",
            '000.400.109' => "Card is not enrolled for 3D Secure version 2"
        ];
        
        return $codes;
    }
    
    /**
     *
     * @return array
     */
    public static function get_reject_system_codes_keys(): array
    {
        $codes_full = self::get_reject_system_codes();
        $codes = array_keys($codes_full);
        
        return $codes;
    }
}

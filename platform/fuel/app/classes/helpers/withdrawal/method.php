<?php

/**
 * Description of Helpers_Withdrawal_Method
 */
class Helpers_Withdrawal_Method
{
    public const STATUS_WITHDRAWAL_PENDING = 0;
    public const STATUS_WITHDRAWAL_APPROVED = 1;
    public const STATUS_WITHDRAWAL_DECLINED = 2;
    public const STATUS_WITHDRAWAL_CANCELED = 3;

    public const WITHDRAWAL_BANK = 1;
    public const WITHDRAWAL_SKRILL = 2;
    public const WITHDRAWAL_NETELLER = 3;
    public const WITHDRAWAL_BTC = 4;
    public const WITHDRAWAL_DEBIT_CARD = 5;
    public const WITHDRAWAL_PAYPAL = 6;
    public const WITHDRAWAL_FAIREUM_MEMBERSHIP = 7;
    public const WITHDRAWAL_FAIREUM_USDT = 8;
    public const WITHDRAWAL_FAIREUM_CRYPTO_EXCHANGES = 9;

    public const WITHDRAWAL_BANK_URI = "bank";
    public const WITHDRAWAL_SKRILL_URI = "skrill";
    public const WITHDRAWAL_NETELLER_URI = "neteller";
    public const WITHDRAWAL_BTC_URI = "btc";
    public const WITHDRAWAL_DEBIT_CARD_URI = "debitcard";
    public const WITHDRAWAL_PAYPAL_URI = "paypal";
    public const WITHDRAWAL_FAIREUM_MEMBERSHIP_URI = "membership";
    public const WITHDRAWAL_FAIREUM_USDT_URI = "usdt";
    public const WITHDRAWAL_FAIREUM_CRYPTO_EXCHANGES_URI = 'cryptoexchanges';

    /**
     *
     * @return array
     */
    public static function get_withdrawals_statuses(): array
    {
        $withdrawalStatuses = [
            Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_PENDING => _("Pending"),
            Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_APPROVED => _("Approved"),
            Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_DECLINED => _("Declined"),
            Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_CANCELED => _("Canceled"),
        ];

        return $withdrawalStatuses;
    }

    /**
     *
     * @return array
     */
    public static function get_methods_URI(): array
    {
        $methods = [
            self::WITHDRAWAL_BANK => self::WITHDRAWAL_BANK_URI,
            self::WITHDRAWAL_SKRILL => self::WITHDRAWAL_SKRILL_URI,
            self::WITHDRAWAL_NETELLER => self::WITHDRAWAL_NETELLER_URI,
            self::WITHDRAWAL_BTC => self::WITHDRAWAL_BTC_URI,
            self::WITHDRAWAL_DEBIT_CARD => self::WITHDRAWAL_DEBIT_CARD_URI,
            self::WITHDRAWAL_PAYPAL => self::WITHDRAWAL_PAYPAL_URI,
            self::WITHDRAWAL_FAIREUM_MEMBERSHIP => self::WITHDRAWAL_FAIREUM_MEMBERSHIP_URI,
            self::WITHDRAWAL_FAIREUM_USDT => self::WITHDRAWAL_FAIREUM_USDT_URI,
            self::WITHDRAWAL_FAIREUM_CRYPTO_EXCHANGES => self::WITHDRAWAL_FAIREUM_CRYPTO_EXCHANGES_URI,
        ];

        return $methods;
    }
}

<?php

namespace Services\Api\Slots;

use Interfaces\Logs\LogsInterface;
use Services\Api\Slots\Providers\SlotegratorApiService;
use Repositories\WhitelabelSlotProviderRepository;
use Repositories\SlotOpenGameRepository;
use Services\Api\Slots\LimitService;
use Repositories\SlotGameRepository;
use Services\Logs\FileLoggerService;

final class SlotegratorInitGameService extends AbstractInitGameService
{
    private const AVAILABLE_CURRENCIES = [
        'Belatra games' => ['AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD', 'BCH', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BTC', 'BTL', 'BTN', 'BWP', 'BYN', 'BZD', 'CAD', 'CDF', 'CHF', 'CLF', 'CLP', 'CNY', 'COP', 'CRC', 'CUC', 'CUP', 'CVE', 'CZK', 'DASH', 'DJF', 'DKK', 'DOG', 'DOP', 'DTC', 'DZD', 'EGP', 'ERN', 'ETB', 'ETH', 'EUR', 'FJD', 'FKP', 'FUN', 'GAME', 'GBP', 'GEL', 'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'IQD', 'IRR', 'ISK', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LTC', 'LYD', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MRO', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDG', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SRD', 'SSP', 'STD', 'SVC', 'SYP', 'SZL', 'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'USDT', 'UYU', 'UZS', 'VEF', 'VND', 'VUV', 'WST', 'XAF', 'XCD', 'XEM', 'XMR', 'XOF', 'XPF', 'XRP', 'YER', 'ZAR', 'ZEC', 'ZMW', 'ZWL'],
        'BoomingGames'    => ['AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BTN', 'BWP', 'BZD', 'CAD', 'CDF', 'CHF', 'CLP', 'CNY', 'COP', 'CRC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK', 'DOGE', 'DOP', 'DZD', 'EGP', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'IOT', 'IQD', 'IRR', 'ISK', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KIDR', 'KMF', 'KPW', 'KRW', 'KVND', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LTC', 'LYD', 'MAD', 'MBCH', 'MBTC', 'MDL', 'METH', 'MGA', 'MKD', 'MLTC', 'MMK', 'MNT', 'MOP', 'MRO', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDG', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SRD', 'STAR', 'STD', 'SVC', 'SYP', 'SZL', 'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UBCH', 'UBTC', 'UETH', 'UGX', 'ULTC', 'USD', 'UYU', 'UZS', 'VEF', 'VND', 'VUV', 'WST', 'XAF', 'XCD', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMW'],
        'DLV'    => ['AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BTT', 'BWP', 'BYN', 'BYR', 'BZD', 'CAD', 'CDF', 'CFA', 'CHF', 'CLP', 'CNY', 'COP', 'CRC', 'CUP', 'CZK', 'DKK', 'DOGE', 'DOP', 'DZD', 'EGP', 'EOS', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GGP', 'GHS', 'GIP', 'GMD', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'IMP', 'INR', 'INR', 'IQD', 'IRR', 'IRT', 'ISK', 'JEP', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KPW', 'KPW', 'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MUR', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PHP', 'PKR', 'PLN', 'PRB', 'PYG', 'QAR', 'RMB', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDG', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SRD', 'SSP', 'SVC', 'SYP', 'THB', 'TJS', 'TMT', 'TND', 'TRC', 'TRX', 'TRY', 'TTD', 'TVD', 'TWD', 'TZS', 'UAH', 'UGX', 'URU', 'USD', 'USDT', 'UYU', 'UZS', 'VEB', 'VEF', 'VES', 'VND', 'WON', 'XAF', 'XCD', 'XEM', 'XOF', 'YER', 'ZAR', 'ZMK', 'ZMW', 'ZWD', 'ZWL', 'mBT', 'mBTC', 'mETC', 'mETH', 'mLTC', 'uBTC'],
        'Endorphina' => ['AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AWG', 'AZN', 'BAM', 'BBD', 'BCH', 'BDT', 'BGN', 'BHD', 'BIF', 'BIT', 'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BTC', 'BTN', 'BWP', 'BYN', 'BZD', 'CAD', 'CDF', 'CHF', 'CLP', 'CNY', 'COP', 'CRC', 'CUC', 'CUP', 'CVE', 'DJF', 'DKK', 'DOP', 'DZD', 'EGP', 'ERN', 'ETB', 'ETH', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'ISK', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LTC', 'LYD', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDG', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SRD', 'SVC', 'SZL', 'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TRX', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UST', 'UYU', 'UZS', 'VND', 'VUV', 'WST', 'XAF', 'XCD', 'XDG', 'XMR', 'YER', 'ZAR', 'ZMW'],
        'Evoplay' => ['AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AZN', 'BAM', 'BDT', 'BGN', 'BOB', 'BRL', 'BTN', 'BWP', 'BYN', 'CAD', 'CDF', 'CHF', 'CLP', 'CNY', 'COP', 'CRC', 'CZK', 'DKK', 'DZD', 'DOP', 'EGP', 'ETB', 'EUR', 'GBP', 'GEL', 'GHS', 'GMD', 'GNF', 'GTQ', 'HKD', 'HNL', 'HRK', 'HUF', 'IDR', 'ILS', 'INR', 'IQD', 'IRR', 'ISK', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KRW', 'KWD', 'KZT', 'LBP', 'LKR', 'MAD', 'MDL', 'MKD', 'MMK', 'MNT', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PEN', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RWF', 'RUB', 'SAR', 'SCR', 'SEK', 'SGD', 'SLL', 'SRD', 'THB', 'TJS', 'TMT', 'TND', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UYU', 'UZS', 'VES', 'VND', 'XAF', 'XCD', 'XOF', 'ZAR', 'ZMW', 'ZWL', 'CTWD', 'KIDR', 'KVND', 'KLAK', 'IRT', 'CUSD', 'DASH', 'USDT', 'MBC', 'UBTC', 'MLTC', 'METH', 'MBCH', 'DOGE'],
        'Green Jade' => ['AMD', 'ARS', 'AUD', 'AZN', 'BAM', 'BGN', 'BOB', 'BRL', 'CAD', 'CHF', 'CNY', 'CZK', 'DKK', 'DOP', 'EUR', 'GBP', 'GEL', 'GHS', 'HKD', 'HRK', 'HUF', 'INR', 'ISK', 'JPY', 'KRW', 'KZT', 'MAD', 'MUR', 'MXN', 'MYR', 'NAD', 'NOK', 'NZD', 'PEN', 'PLN', 'RON', 'RUB', 'SEK', 'SGD', 'THB', 'TRY', 'TWD', 'UAH', 'USD', 'VEF', 'VND', 'XTS', 'ZAR'],
        'MascotGaming' => ['AED', 'ALL', 'AMD', 'AOA', 'ARS', 'AUD', 'AZN', 'BAM', 'BDT', 'BGN', 'BHD', 'BLR', 'BMD', 'BND', 'BOB', 'BRL', 'BYN', 'CAD', 'CDF', 'CHF', 'CLP', 'CNY', 'COP', 'CRC', 'CZK', 'DAS', 'DKK', 'DOG', 'DOP', 'DZD', 'EGP', 'ETB', 'ETH', 'EUR', 'GBP', 'GEL', 'GHS', 'GMC', 'GMD', 'GNF', 'HKD', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'IQD', 'IRR', 'IRT', 'ISK', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KRW', 'KWD', 'KZT', 'LBP', 'LRD', 'MAD', 'MDL', 'MGA', 'MHC', 'MKD', 'MLT', 'MMK', 'MNT', 'MXM', 'MXN', 'MYR', 'MZN', 'NAD', 'NAN', 'NGN', 'NIO', 'NOK', 'NZD', 'OMR', 'PEN', 'PHP', 'PKR', 'PLN', 'PPT', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'SAR', 'SCR', 'SDG', 'SEK', 'SGD', 'SLL', 'SRD', 'SZL', 'THB', 'TJS', 'TMT', 'TND', 'TRY', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UYU', 'UZS', 'VEF', 'VES', 'VND', 'XAF', 'XAU', 'XMR', 'XOF', 'XRP', 'ZAR', 'ZEC', 'ZMW', 'ZWL'],
        'OneTouch' => ['AED', 'ALL', 'AMD', 'ARS', 'AZN', 'BAM', 'BDT', 'BGN', 'BNB', 'BND', 'BOB', 'BRL', 'BYN', 'CAD', 'CDF', 'CHF', 'CLP', 'CNY', 'COP', 'CRC', 'CUP', 'CZK', 'DKK', 'DOGE', 'EGP', 'EOS', 'EUR', 'FUN', 'GEL', 'GHS', 'GNF', 'HKD', 'HRK', 'HUF', 'IDR', 'IDRK', 'ILS', 'INR', 'IQD', 'IRR', 'IRT', 'JPY', 'KES', 'KGS', 'KRW', 'KWD', 'KZT', 'LSL', 'MAD', 'MDL', 'MKD', 'MNT', 'MXN', 'MYR', 'NGN', 'NOK', 'NZD', 'PEN', 'PHP', 'PKR', 'PLN', 'PYG', 'RUB', 'SEK', 'SGD', 'THB', 'TJS', 'TMT', 'TND', 'TRX', 'TRY', 'TWD', 'TZS', 'UAH', 'USD', 'USDT', 'UYU', 'UZS', 'VES', 'VND', 'VNDK', 'XAF', 'XOF', 'XRP', 'ZAR', 'ZMW', 'mBCH', 'mBTC', 'mETH', 'mLTC', 'uBTC', 'GBP'],
        'Playson' => ['AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BTN', 'BWP', 'BYN', 'BZD', 'CAD', 'CDF', 'CHF', 'CLP', 'CNY', 'COP', 'CRC', 'CUC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EGP', 'ERN', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GGP', 'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'IMP', 'INR', 'IQD', 'IRR', 'IRT', 'ISK', 'JEP', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LYD', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MRO', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDG', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SRD', 'STD', 'SVC', 'SYP', 'SZL', 'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TRY', 'TTD', 'TTH', 'TVD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UYU', 'UZS', 'VEF', 'VND', 'VUV', 'WST', 'XAF', 'XCD', 'XDR', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMW', 'ZWD'],
        'Push Gaming' => ['ALL', 'AMD', 'ARS', 'AUD', 'AZN', 'BAM', 'BGN', 'BOB', 'BRL', 'BYR', 'CAD', 'CHF', 'CLP', 'CNY', 'COP', 'CZK', 'DKK', 'DOP', 'EUR', 'GBP', 'GEL', 'GHS', 'HKD', 'HRK', 'HUF', 'IDR', 'ILS', 'INR', 'IQD', 'ISK', 'JPY', 'KES', 'KGS', 'KRW', 'KZT', 'MAD', 'MDL', 'MKD', 'MMK', 'MXN', 'MYR', 'NGN', 'NOK', 'NZD', 'PEN', 'PHP', 'PLN', 'PYG', 'RMB', 'RON', 'RSD', 'RUB', 'SEK', 'SGD', 'THB', 'TND', 'TRY', 'TWD', 'UAH', 'USD', 'UYU', 'UZS', 'VEF', 'VND', 'XAF', 'XOF', 'ZAR'],
        'Blueprint'    => ['AMD', 'ARS', 'AUD', 'AZN', 'BDT', 'BND', 'BRL', 'BYN', 'CAD', 'CHF', 'CLP', 'CNY', 'COP', 'CZK', 'EUR', 'GBP', 'GEL', 'GHS', 'HKD', 'HUF', 'IDR', 'INR', 'IRR', 'JPY', 'KES', 'KGS', 'KRW', 'KZT', 'LKR', 'MDL', 'MMK', 'MXN', 'MYR', 'NGN', 'NOK', 'NZD', 'PEN', 'PKR', 'PLN', 'RUB', 'SEK', 'THB', 'TMT', 'TND', 'TRY', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UZS', 'VND', 'XAF', 'ZAR', 'ZMW', 'kIDR', 'kVND'],
        'Ezugi' => ['AMD', 'ARS', 'AZN', 'BDT', 'BND', 'BRL', 'BYN', 'CAD', 'CLP', 'CNY', 'COP', 'CZK', 'EUR', 'GBP', 'GHS', 'HKD', 'IDR', 'INR', 'JPY', 'KES', 'KGS', 'KRW', 'KZT', 'LKR', 'MMK', 'MXN', 'MYR', 'NGN', 'NOK', 'NZD', 'PEN', 'PKR', 'PLN', 'RUB', 'SEK', 'THB', 'TMT', 'TND', 'TRY', 'TWD', 'TZS', 'UAH', 'USD', 'UZS', 'VND', 'XAF', 'ZAR', 'kIDR', 'kVND', 'mBTC'],
        'Quickspin' =>     ['AMD', 'ARS', 'AUD', 'AZN', 'BND', 'BRL', 'BYN', 'CAD', 'CHF', 'CLP', 'CNY', 'COP', 'CZK', 'EUR', 'GBP', 'GEL', 'GHS', 'HKD', 'HUF', 'IDR', 'INR', 'IRR', 'JPY', 'KES', 'KGS', 'KRW', 'KZT', 'LKR', 'MDL', 'MMK', 'MXN', 'MYR', 'NGN', 'NOK', 'NZD', 'PEN', 'PKR', 'PLN', 'RUB', 'SEK', 'THB', 'TND', 'TRY', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UZS', 'VND', 'ZAR', 'ZMW'],
        'Thunderkick'    => ['AMD', 'ARS', 'AUD', 'AZN', 'BGN', 'BOB', 'BRL', 'BYR', 'CAD', 'CHF', 'CLP', 'CNY', 'COP', 'CZK', 'DKK', 'EUR', 'GBP', 'GEL', 'HKD', 'HRK', 'HUF', 'IDR', 'ILS', 'INR', 'ISK', 'JPY', 'KES', 'KGS', 'KRW', 'KZT', 'LKR', 'LTL', 'LVL', 'MDL', 'MMK', 'MNT', 'MXN', 'MYR', 'NGN', 'NOK', 'NZD', 'PEN', 'PHP', 'PLN', 'PYG', 'RON', 'RSD', 'RUB', 'SEK', 'SGD', 'THB', 'TND', 'TRY', 'TWD', 'TZS', 'UAH', 'USD', 'UYU', 'UZS', 'VEF', 'VND', 'XAF', 'ZAR'],
        'Yggdrasil' =>    ['AMD', 'ARS', 'AUD', 'AZN', 'BDT', 'BND', 'BRL', 'CAD', 'CHF', 'CLP', 'CNY', 'CZK', 'EUR', 'GBP', 'GEL', 'GHS', 'HKD', 'HUF', 'IDR', 'INR', 'IRR', 'JPY', 'KGS', 'KRW', 'KZT', 'LKR', 'MDL', 'MMK', 'MXN', 'MYR', 'NGN', 'NOK', 'NZD', 'PEN', 'PKR', 'PLN', 'RUB', 'SEK', 'THB', 'TMT', 'TRY', 'TWD', 'UAH', 'USD', 'UZS', 'VND', 'XAF', 'ZAR', 'ZMW', 'kIDR', 'kVND'],
        'RevolverGaming' =>    ['AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BTC', 'BTN', 'BWP', 'BYN', 'BZD', 'CAD', 'CDF', 'CHF', 'CLP', 'CNY', 'COP', 'CRC', 'CUP', 'CVE', 'CZK', 'DIA', 'DJF', 'DKK', 'DOP', 'DZD', 'EGP', 'ERN', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'IQD', 'IRR', 'ISK', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LYD', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MRO', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDG', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SRD', 'STD', 'SVC', 'SYP', 'SZL', 'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UYU', 'UZS', 'VEF', 'VND', 'VUV', 'WST', 'XAF', 'XCD', 'XDR', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMW'],
        'Bgaming' =>    ['AMD', 'ARS', 'AUD', 'AZN', 'BGN', 'BIF', 'BRL', 'BTC', 'BYN', 'CAD', 'CDF', 'CHF', 'CLP', 'CNY', 'COP', 'CZK', 'DKK', 'ETH', 'EUR', 'FUN', 'GBP', 'GEL', 'HUF', 'IDR', 'INR', 'IQD', 'JPY', 'KES', 'KRW', 'KZT', 'LTC', 'MDL', 'MXN', 'MYR', 'NGN', 'NOK', 'NZD', 'PEN', 'PHP', 'PKR', 'PLN', 'PYG', 'RUB', 'SEK', 'THB', 'TJS', 'TMT', 'TND', 'TRY', 'TWD', 'UAH', 'UGX', 'USD', 'UZS', 'VND', 'XOF', 'ZAR', 'ZMW', 'CH', 'BTC', 'uBTC', 'DOG', 'ETH', 'LTC', 'USDT'],
        'Spadegaming' =>    ['AED', 'AMD', 'ARS', 'AUD', 'BRL', 'CAD', 'CHF', 'CLP', 'CZK', 'DKK', 'EUR', 'GBP', 'MXN', 'NOK', 'NZD', 'PEN', 'PLN', 'PYG', 'RUB', 'SEK', 'TND', 'TRY', 'UAH', 'USD', 'UYU', 'VES', 'ZAR', 'CNY', 'HKD', 'ID2', 'IDR', 'INR', 'JPY', 'KRW', 'MMK', 'MYR', 'SGD', 'THB', 'VN2', 'VND'],
        'TomHorn'    => ['AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BTN', 'BWP', 'BYR', 'BZD', 'CAD', 'CDF', 'CLP', 'CNY', 'COP', 'CRC', 'CUC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EGP', 'ERN', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'CHF', 'IDR', 'INR', 'IQD', 'IRR', 'ISK', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LYD', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MRO', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PRB', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDG', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SRD', 'STD', 'SYP', 'SZL', 'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UYU', 'UZS', 'VEF', 'VND', 'VUV', 'WST', 'XAF', 'XBT', 'XCD', 'XDA', 'XDC', 'XLC', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMW', 'ZWL'],
        'TripleCherry' =>    ['AED', 'AFN', 'ALL', 'AMD', 'ARS', 'AUD', 'AZN', 'BAM', 'BDT', 'BGN', 'BHD', 'BIF', 'BND', 'BOB', 'BRL', 'BWP', 'BYN', 'BZD', 'CAD', 'CDF', 'CHF', 'CLP', 'CNY', 'COP', 'CRC', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EGP', 'ERN', 'ETB', 'EUR', 'GBP', 'GEL', 'GHS', 'GNF', 'GTQ', 'HKD', 'HNL', 'HRK', 'HUF', 'IDR', 'ILS', 'INR', 'IQD', 'IRR', 'ISK', 'JMD', 'JOD', 'JPY', 'KES', 'KHR', 'KMF', 'KRW', 'KWD', 'KZT', 'LBP', 'LKR', 'LYD', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MOP', 'MUR', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SDG', 'SEK', 'SGD', 'SOS', 'SYP', 'THB', 'TMT', 'TND', 'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UYU', 'UZS', 'VEF', 'VND', 'XAF', 'XBT', 'XOF', 'YER', 'ZAR', 'ZMK'],
        'Vivogaming'    => ['AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD', 'BCH', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BTC', 'BTN', 'BWP', 'BYN', 'BZD', 'CAD', 'CDF', 'CHF', 'CHP', 'CLP', 'CNY', 'COP', 'CRC', 'CUC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EGP', 'ERN', 'ETB', 'ETH', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GGP', 'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'IMP', 'INR', 'IQD', 'IRR', 'ISK', 'JEP', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LTC', 'LYD', 'MAD', 'MBT', 'MCH', 'MDL', 'MEH', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MRU', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDG', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SPL', 'SRD', 'STN', 'SVC', 'SYP', 'SZL', 'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TRX', 'TRY', 'TTD', 'TVD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UYU', 'UZS', 'VEF', 'VES', 'VND', 'VUV', 'WST', 'XAF', 'XAG', 'XAU', 'XCD', 'XDR', 'XOF', 'XPD', 'XPF', 'XPT', 'YER', 'ZAR', 'ZMW', 'ZWD'],
        'Betsoft' => ['AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BTN', 'BWP', 'BYN', 'BZD', 'CAD', 'CDF', 'CHF', 'CLP', 'CNY', 'COP', 'CRC', 'CUC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EGP', 'ERN', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GGP', 'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'IMP', 'INR', 'IQD', 'IRR', 'ISK', 'JEP', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LYD', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MRU', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDG', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SPL*', 'SRD', 'STN', 'SVC', 'SYP', 'SZL', 'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TRY', 'TTD', 'TVD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UYU', 'UZS', 'VEF', 'VND', 'VUV', 'WST', 'XAF', 'XCD', 'XDR', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMW', 'ZWD', 'BTC', 'MBC', 'UBC', 'TRX', 'LTC', 'MLC', 'ULC', 'XDG', 'ETH', 'MEH', 'UEH', 'BCH', 'MCH', 'UCH', 'XRP', 'TET', "USDT"]
    ];

    private SlotegratorApiService $slotegratorApiService;
    private FileLoggerService $fileLoggerService;

    public function __construct(
        SlotegratorApiService $slotegratorApiService,
        WhitelabelSlotProviderRepository $whitelabelSlotProviderRepository,
        SlotOpenGameRepository $slotOpenGameRepository,
        LimitService $limitService,
        SlotGameRepository $slotGameRepository,
        FileLoggerService $fileLoggerService,
    )
    {
        parent::__construct(
            $whitelabelSlotProviderRepository,
            $slotOpenGameRepository,
            $limitService,
            $slotGameRepository
        );

        $this->slotegratorApiService = $slotegratorApiService;
        $this->fileLoggerService = $fileLoggerService;
        $this->fileLoggerService->setSource(LogsInterface::SOURCE_DEFAULT);
    }

    /** #### IMPORTANT: Staging support only EUR currency */
    public function init(): string
    {
        $userCurrencyCode = $this->getUserCurrency();

        $params = [
            'currency' => $userCurrencyCode,
            'game_uuid' => $this->game->uuid,
            'language' => $this->language,
            'player_id' => $this->user->token,
            'player_name' => $this->user->name ?: 'Player',
            'session_id' => $this->sessionUuid,
            'return_url' => lotto_platform_get_permalink_by_slug('/')
        ];

        $this->response = $this->slotegratorApiService->sendRequest($this->slotegratorApiService->slotProviderData->initGamePath, $params);
        $this->request = $params;

        if (isset($this->response['error'])) {
            $this->isError = true;
        }

        $this->insertInitLog();

        return $this->response['url'] ?? '';
    }

    public function initDemo(): string
    {
        $params = [
            'game_uuid' => $this->game->uuid,
            'language' => $this->language,
            'return_url' => lotto_platform_get_permalink_by_slug('/')
        ];
        $this->request = $params;

        $this->response = $this->slotegratorApiService->sendRequest($this->slotegratorApiService->slotProviderData->initDemoGamePath, $params);
                
        return $this->response['url'] ?? '';
    }

    private function isUserCurrencySupported(string $userCurrencyCode): bool
    {
        $availableCurrenciesPerProvider = array_change_key_case(self::AVAILABLE_CURRENCIES);
        $provider = strtolower($this->game->provider);

        /** if provider is not defined in array it means that all currencies are available */
        if (!isset($availableCurrenciesPerProvider[$provider])) {
            return true;
        }

        $isCurrencySupported = isset($availableCurrenciesPerProvider[$provider]) && array_search($userCurrencyCode, $availableCurrenciesPerProvider[$provider]);

        return $isCurrencySupported;
    }

    private function getUserCurrency(): string
    {
        $userCurrencyCode = $this->user->currency->code;

        if ($this->isUserCurrencySupported($userCurrencyCode)) {
            return $userCurrencyCode;
        }

        return 'USD';
    }
}

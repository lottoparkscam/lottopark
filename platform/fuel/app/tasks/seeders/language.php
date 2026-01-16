<?php

namespace Fuel\Tasks\Seeders;

/**
* Language seeder.
*/
final class Language extends Seeder
{
    protected function columnsStaging(): array
    {
        return [
            'language' => ['id', 'default_currency_id', 'code', 'js_currency_format']
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'language' => [
                [1, 2, 'en_GB', '{c}{n}.{s}'],
                [2, 2, 'pl_PL', '{n},{s} {c}'],
                [3, 2, 'de_DE', '{n},{s} {c}'],
                [4, 2, 'cs_CZ', '{n},{s} {c}'],
                [5, 2, 'pt_PT', '{n},{s} {c}'],
                [6, 2, 'et_EE', '{n},{s} {c}'],
                [7, 2, 'es_ES', '{n},{s} {c}'],
                [8, 2, 'ka_GE', '{n},{s} {c}'],
                [9, 2, 'vi_VN', '{n},{s} {c}'],
                [10, 2, 'hr_HR', '{n},{s} {c}'],
                [11, 2, 'lv_LV', '{n},{s} {c}'],
                [12, 2, 'ro_RO', '{n},{s} {c}'],
                [13, 2, 'sk_SK', '{n},{s} {c}'],
                [14, 2, 'sq_AL', '{n},{s} {c}'],
                [15, 2, 'el_GR', '{n},{s} {c}'],
                [16, 2, 'mk_MK', '{n},{s} {c}'],
                [17, 2, 'nl_NL', '{n},{s} {c}'],
                [18, 2, 'lt_LT', '{n},{s} {c}'],
                [19, 2, 'sr_RS', '{n},{s} {c}'],
                [20, 2, 'sl_SI', '{n},{s} {c}'],
                [21, 2, 'ru_RU', '{n},{s} {c}'],
                [22, 2, 'sv_SE', '{n},{s} {c}'],
                [23, 2, 'it_IT', '{n},{s} {c}'],
                [24, 2, 'da_DK', '{n},{s} {c}'],
                [25, 2, 'hu_HU', '{n},{s} {c}'],
                [26, 2, 'th_TH', '{n},{s} {c}'],
                [27, 2, 'bn_BD', '{n},{s} {c}'],
                [28, 2, 'zh_CN', '{n},{s} {c}'],
                [29, 2, 'fr_FR', '{n},{s} {c}'],
                [30, 2, 'ko_KR', '{n},{s} {c}'],
                [31, 2, 'bg_BG', '{n},{s} {c}'],
                [32, 2, 'az_AZ', '{n},{s} {c}'],
                [33, 2, 'fil_PH', '{n},{s} {c}'],
                [34, 2, 'id_ID', '{n},{s} {c}'],
                [35, 2, 'tr_TR', '{n},{s} {c}'],
                [36, 2, 'hi_IN', '{n},{s} {c}'],
                [37, 2, 'ar_SA', '{n},{s} {c}'],
                [38, 2, 'fa_IR', '{n},{s} {c}'],
                [39, 2, 'ja_JP', '{n},{s} {c}'],
                [40, 2, 'uk_UA', '{n},{s} {c}'],
                [41, 2, 'pt_BR', '{n},{s} {c}'],
                [42, 2, 'fi_FI', '{n},{s} {c}'],
            ]
        ];
    }

}
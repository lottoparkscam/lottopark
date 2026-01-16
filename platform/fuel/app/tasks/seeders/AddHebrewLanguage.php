<?php

namespace Fuel\Tasks\Seeders;

final class AddHebrewLanguage extends Seeder
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
                [43, 2, 'he_IL', '{n},{s} {c}'],
            ]
        ];
    }

}

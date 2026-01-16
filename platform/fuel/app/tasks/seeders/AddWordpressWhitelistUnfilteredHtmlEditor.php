<?php

namespace Fuel\Tasks\Seeders;

final class AddWordpressWhitelistUnfilteredHtmlEditor extends Seeder
{
    use \Without_Foreign_Key_Checks;

    /**
     * Define columns used by seeder.
     * NOTE: can be for many tables.
     *
     * @return array format 'table' => [col1...coln]
     */
    protected function columnsStaging(): array
    {
        return [
            'wordpress_whitelist_unfiltered_html_editor' => ['id', 'email']
        ];
    }

    /**
     * Define rows used by seeder.
     * NOTE: can be for many tables.
     *
     * @return array format 'table' => [row1[val1...valn]...rown[val1...valn]]
     */
    protected function rowsStaging(): array
    {
        return [
            'wordpress_whitelist_unfiltered_html_editor' => [
                [1, 'pk@lottopark.com'],
            ]
        ];
    }
}

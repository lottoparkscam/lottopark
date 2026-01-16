<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;

/**
 * @property int $id
 * @property string $email
 */
class WordpressWhitelistUnfilteredHtmlEditor extends AbstractOrmModel
{
    protected static string $_table_name = 'wordpress_whitelist_unfiltered_html_editor';

    protected static array $_properties = [
        'id',
        'email'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'email' => self::CAST_STRING
    ];

    protected array $relations = [
    ];

    protected array $timezones = [
    ];

    // It is very important! Do not remove these variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
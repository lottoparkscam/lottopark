<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;

/**
 * @property int $id
 * @property string $name
 * @property string $value
 */
class Setting extends AbstractOrmModel
{
    protected static $_table_name = 'setting';

    protected static $_properties = [
        'id',
        'name',
        'value'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
    ];

    /**
     * @param Setting[] $settings
     */
    private static function remove_sensitive_data(array $settings): array
    {
        $response = [];

        foreach ($settings as $setting) {
            // do not store password related data in variable and furthermore - cache
            if (
                strpos($setting->name, 'salt') === false &&
                strpos($setting->name, 'hash') === false &&
                substr($setting->name, -6) !== "_login"
            ) {
                $response[$setting->name] = $setting->value;
            }
        }

        return $response;
    }

    /**
     * @return Setting[]
     */
    public static function get_settings_by_prefix(string $prefix): array
    {
        /** @var Setting[] $settings */
        $settings = self::find('all', [
            'where' => [
                ['name', 'LIKE', "{$prefix}%"]
            ]
        ]);

        return self::remove_sensitive_data($settings);
    }
}

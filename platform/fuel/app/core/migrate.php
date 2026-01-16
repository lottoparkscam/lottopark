<?php


class Migrate extends \Fuel\Core\Migrate
{
    protected static function run($migrations, $name, $type, $method = 'up')
    {
        set_time_limit($_ENV['MAX_MIGRATION_TIME'] ?? 60);

        return parent::run($migrations, $name, $type, $method);
    }
}
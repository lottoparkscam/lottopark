<?php

namespace Fuel\Tasks\Factory\Utils;

use Faker\Factory;
use Faker\Generator;
use Ottaviano\Faker\Gravatar;

class Faker
{
    /** @var Generator */
    private static $instance;

    public static function forge(): Generator
    {
        if (self::$instance) {
            return self::$instance;
        }

        self::$instance = Factory::create();
        self::$instance->addProvider(new Gravatar(self::$instance));
        return self::$instance;
    }
}

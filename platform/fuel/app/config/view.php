<?php

use Fuel\Core\Fuel;

return [
    'template_directories' => [
        APPPATH . Path::unifyPath('../../../wordpress/wp-content/themes/base/'),
        APPPATH . Path::unifyPath('classes/modules/Payments/Jeton/resources/'),
        APPPATH . 'views/twig/'
    ],
    'options' => [
        'debug' => Fuel::$env !== Fuel::PRODUCTION
    ],
    /*
     *  When the same extension files will be met, then this strategy will be used for rendering.
     */
    'conflict_strategy' => 'twig'
];

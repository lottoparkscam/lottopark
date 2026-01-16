<?php
if (!defined('WPINC')) {
    die;
}
class Lotto_Widget_Raffle_Customizer
{
    public static function configure(WP_Customize_Manager $manager): void
    {
        self::create_section($manager);
        self::create_play_url_input($manager);
        self::create_type_input($manager);
        self::createCustomColorsSwitch($manager);
        self::createColorInput($manager, 'buttonTextColor', 'Choose button text color');
        self::createColorInput($manager, 'buttonTextColorOnHover', 'Choose button text color on hover');
        self::createColorInput($manager, 'buttonBackgroundColor', 'Choose button background color');
        self::createColorInput($manager, 'buttonBackgroundColorOnHover', 'Choose button background color on hover');
        self::createColorInput($manager, 'backgroundColor', 'Choose background color');
        self::create_background_input($manager);
    }

    private static function create_section(WP_Customize_Manager $manager): void
    {
        $section = new WP_Customize_Section($manager, Lotto_Widget_Raffle_Promo::ID);
        $section->title = _('Raffle Promo Widget');
        $section->description = _('Change your widget options.');
        $manager->add_section($section);
    }

    private static function create_play_url_input(WP_Customize_Manager $manager): void
    {
        $input_unique_name = Lotto_Widget_Raffle_Settings::as_db_unique_key('button_play_url');

        $manager->add_setting(
            new WP_Customize_Setting($manager, $input_unique_name)
        );

        $manager->add_control(
            new WP_Customize_Control($manager, $input_unique_name, [
                'section' => Lotto_Widget_Raffle_Promo::ID,
                'label'   => _('Raffle play url'),
            ])
        );
    }
    private static function create_type_input(WP_Customize_Manager $manager): void
    {
        $input_unique_name = Lotto_Widget_Raffle_Settings::as_db_unique_key('type');

        $manager->add_setting(
            new WP_Customize_Setting($manager, $input_unique_name)
        );

        $manager->add_control(
            new WP_Customize_Control(
                $manager,
                $input_unique_name,
                [
                    'section' => Lotto_Widget_Raffle_Promo::ID,
                    'type' => 'select',
                    'choices' => [
                        'gg-world-raffle' => 'GG World',
                        'lottery-king-raffle' => 'Lottery King',
                    ]
                ]
            )
        );
    }

    private static function create_background_input(WP_Customize_Manager $manager): void
    {
        $input_unique_name = Lotto_Widget_Raffle_Settings::as_db_unique_key('background_image');

        $manager->add_setting(
            new WP_Customize_Setting($manager, $input_unique_name)
        );

        $manager->add_control(
            new WP_Customize_Image_Control($manager, $input_unique_name, [
                'section' => Lotto_Widget_Raffle_Promo::ID,
                'label'   => _('Choose background image')
            ])
        );
    }

    private static function createColorInput(WP_Customize_Manager $manager, string $inputUniqueName, string $label): void
    {
        $manager->add_setting(
        new WP_Customize_Setting($manager, $inputUniqueName)
    );

        $manager->add_control(
            new WP_Customize_Image_Control($manager, $inputUniqueName, [
                'section' => Lotto_Widget_Raffle_Promo::ID,
                'label'   => _($label)
            ])
        );
    }

    private static function createCustomColorsSwitch(WP_Customize_Manager $manager): void
    {
        $manager->add_setting(
            new WP_Customize_Setting($manager, 'useCustomColors')
        );

        $manager->add_control(
            new WP_Customize_Control(
                $manager,
                'useCustomColors',
                array(
                    'label' => __('Use custom colors'),
                    'type' => 'checkbox',
                    'section' => Lotto_Widget_Raffle_Promo::ID
                )
            )
        );
    }
}

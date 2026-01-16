<?php

class Banners_Widgets
{
    /**
     * Allowed widgets methods
     * @var array
     */
    public static $allowed_methods = [
        '1' => [
            'options' => [
                'lotteries' => 1
            ],
            'widgets' => [
                '2' => 'Horizontal',
                '3' => 'Vertical',
                '1' => 'Vertical with colorful frame'
            ]
        ],
        '2' => [
            'options' => [
                'lotteries' => 3
            ],
            'widgets' => [
                '4' => 'Vertical',
                '8' => 'Vertical #2',
                '5' => 'Horizontal'
            ]
        ],
        '3' => [
            'options' => [
                'lotteries' => 1
            ],
            'widgets' => [
                '6' => 'Horizontal',
                '7' => 'Vertical'
            ]
        ]
    ];

    /**
     * Max lotteries to choose
     * @var array
     */
    public static $max_lotteries = 3;

    /**
     * Create widget init
     *
     * @param array $lotteries
     * @param string $lang This is not used at this moment
     * @param array $translations
     * @param int $widget_div_id ID of the widget
     * @return bool|view
     */
    public function create_widget($lotteries, $lang, $translations, $widget_div_id)
    {
        $widget = Input::get('widget');

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        // Check if widget exist
        if (File::exists(APPPATH . 'views/widgets/widget' . $widget . '.php') === false) {
            echo _("Widget doesn't exist");

            return false;
        }


        // Check if whitelabel image exist
        $image_path = '/images/logo-widget.png';

        if (File::exists(get_stylesheet_directory() . $image_path) === false) {
            $whitelabel_image_path = false;
        } else {
            $whitelabel_image_path = get_stylesheet_directory_uri() . $image_path;
        }

        // Check if custom css file exist
        $child_css_path = '/css/widgets/widget' . $widget . '.css';

        if (File::exists(get_stylesheet_directory() . $child_css_path) === true) {
            $css_path = get_stylesheet_directory_uri() . $child_css_path;
        } else {
            $css_path = get_template_directory_uri() . '/css/widgets/widget' . $widget . '.css';
        }

        $data_lotteries = [];

        foreach ($lotteries as $key => $row) {
            $price = Lotto_View::format_currency(
                $row['current_jackpot'] * 1000000,
                $row['currency'],
                0,
                $translations['lang_code']
            );
            
            $data_lotteries[] = [
                'lottery' => $row,
                'last_numbers' => explode(',', $row['last_numbers']),
                'last_bnumbers' => explode(',', $row['last_bnumbers']),
                'price' => $price,
                'lotteryName' => $row['name'],
                //'ball' => 'https://'.$whitelabel['domain'].'/wp-content/plugins/lotto-platform/public/images/lotteries/lottery_' . $row['id'] . '.png'
                'ball' => Lotto_View::get_lottery_image($row['id'])
            ];
        }

        $query_string = $this->get_query_string();

        $data = [
            'buttonTitle' => $translations['play_now'],
            'jackpotText' => $translations['nearest_jackpot'],
            'lotteries' => $data_lotteries,
            'whitelabel' => $whitelabel,
            'css_path' => $css_path,
            'whitelabel_image_path' => $whitelabel_image_path,
            'widget_div_id' => $widget_div_id,
            'query_string' => $query_string
        ];

        return View::forge('widgets/widget' . $widget, $data);
    }

    /**
     * Checks get variables for additional links
     *
     * @return string URL query to add to the link
     */
    public function get_query_string():string
    {
        $get = Input::get();

        $values_to_pass = [
            "ref",
            "medium",
            "campaign",
            "content",
        ];

        $values = [];

        foreach ($values_to_pass as $key) {
            if (!empty($get[$key])) {
                $values[$key] = $get[$key];
            }
        }

        $values_string = http_build_query($values);
        if (!empty($values_string)) {
            $values_string = '?'.$values_string;
        }
        return $values_string;
    }
}

<?php

/**
 *
 */
class Banners_Create
{
    /**
     * Allowed banner methods
     * @var array
     */
    public static $allowed_methods = [
        '1' => '120x600',
        '2' => '160x600',
        '3' => '125x125',
        '4' => '250x250',
        '5' => '300x250',
        '6' => '336x280',
        '7' => '468x60',
        '8' => '728x90'
    ];

    /**
     * Allowed colors types
     * @var array
     */
    public static $allowed_colors = [
        'white' => 'White'
    ];

    /**
     * Default color type if lottery color doesn't exist
     * @var string
     */
    private $default_color = 'white';

    /**
     * Exceptions list for using different font than main
     * @var array
     */
    private $font_exceptions = [
        'ar',
        'fa'
    ];

    /**
     * Compression quality
     * @var array
     */

    private $compression = 90;


    /**
     * Banner init
     *
     * @param array $lottery - lottery information
     * @param string $banner - banner size
     * @return image
     */
    public function create_banner($lottery, $banner, $type, $lang, $translations)
    {
        $options = [];
        // Include configurations
        include(locate_template('banners.php'));

        // Load different font for exceptions
        if (in_array($lang, $this->font_exceptions)) {
            // Replace font with exception font
            $options['main']['font'] = $options['main']['exceptionFont'];
            $options['main']['fontBold'] = $options['main']['exceptionFontBold'];
        }

        if (empty($type) &&
            isset($lottery) &&
            isset($lottery['slug']) &&
            !array_key_exists($lottery['slug'], $options)
        ) {
            $type = $this->default_color;
        }

        // Validation
        $response = $this->validate($lottery, $banner, $type, $options);
        if (!$response) {
            return false;
        }

        // return main and lottery configuration to a banner
        $configuration = [
            'main' => $options['main'],
            'lottery' => $response
        ];

        // Run banner
        $function = 'create_'.self::$allowed_methods[$banner];

        $cache = new Banners_Cache();

        // Check if cached image exists
        if (!$cache->check($lottery, $banner, $lang, $type, $translations)) {
            // Generate banner
            $image = $this->${"function"}($configuration);

            // Set compression
            $image->setImageFormat("jpeg");
            $image->setCompression(Imagick::COMPRESSION_JPEG);
            $image->setImageCompressionQuality($this->compression);

            // Cache and show image
            $this->cache_image_and_destroy($image, $cache);
        } else {
            // Show cached image
            $cache->get_banner();
        }
    }

    /**
     * Banners validation
     *
     * @param array $lottery - lottery information
     * @param string $banner - banner size
     * @param string $type - color type
     * @param array $options - array with all configurations
     * @return array|boolean
     */
    public function validate($lottery, $banner, $type, $options)
    {
        // Check if lottery exist
        if (!isset($lottery['id'])) {
            http_response_code(404);
            return false;
        }

        // Check if banner size (allowed method) exist
        if (!array_key_exists($banner, self::$allowed_methods)) {
            http_response_code(404);
            return false;
        }

        // Checks color type
        if (!empty($type)) {
            // Checks if color type exist
            return $this->validate_type($type, $options);
        } else {
            $slug = '';
            if (isset($lottery) && isset($lottery['slug'])) {
                $slug = $lottery['slug'];
            }
            // Checks if lottery have a configuration
            return $this->validate_configuration($slug, $options);
        }
    }

    /**
     * Checks if color type exist
     *
     * @param string $type - color type
     * @param array $options - array with all configurations
     * @return array|boolean
     */
    public function validate_type($type, $options)
    {
        if (!array_key_exists($type, $options['standard_colors'])) {
            http_response_code(404);
            return false;
        } else {
            // return banner configuration
            return $options['standard_colors'][$type];
        }
    }

    /**
     * Checks if configuration array exists for specific lottery
     *
     * @param string $slug - lottery slug
     * @param array $options - array with all configurations
     * @return array|boolean
     */
    public function validate_configuration($slug, $options)
    {
        if (!array_key_exists($slug, $options)) {
            http_response_code(500);
            return false;
        }

        // return banner configuration
        return $options[$slug];
    }

    /**
     * Writes image to cache, destroys object and shows the banner
     *
     * @param obj $image - image object
     * @param obj $cache - cache object
     * @return image
     */
    public function cache_image_and_destroy($image, $cache)
    {
        $cache->write_image($image);
        $image->destroy();

        $cache->get_banner();
    }

    /**
     * Creates 300x250 banner
     *
     * @return obj imagick
     */
    public function create_300x250($options)
    {
        // Set BG
        $imagick = Banners_Graphic::radial_gradient_image(300, 250, $options['lottery']['gradient1'], $options['lottery']['gradient2']);

        // Add stars to BG
        $stars = Banners_Graphic::load_image_color($options['main']['starsBG'], $options['lottery']['starsColor'], 1);
        Banners_Graphic::insert_image($imagick['image'], $stars['image'], 0, 40);

        // Insert lottery name
        $lottery_name = $options['main']['lotteryName'];
        $font_size = Banners_Graphic::get_fit_font($lottery_name, $options['main']['fontBold'], 30, 300, 12);
        $lottery_name_text = Banners_Graphic::text($imagick['image'], $lottery_name, $options['lottery']['lotteryNameColor'], $options['main']['fontBold'], $font_size);
        Banners_Graphic::insert_text($imagick['image'], $lottery_name_text['text'], $lottery_name, 0, 11, Imagick::GRAVITY_NORTH);

        // Insert ball BG
        $ballBG = Banners_Graphic::load_image_resize($options['main']['wholeBallBG'], 66, 66);
        Banners_Graphic::insert_image($imagick['image'], $ballBG['image'], Banners_Calc::align_middle($ballBG['width'], $imagick['width']), 54);

        // Insert ball
        $ball = Banners_Graphic::load_image_resize($options['main']['ball'], 57, 60);
        Banners_Graphic::insert_image($imagick['image'], $ball['image'], (Banners_Calc::align_middle($ball['width'], $imagick['width'])+0.5), 60);

        // Insert Jackpot text
        $title4 = $options['main']['jackpotText'];
        $text4 = Banners_Graphic::text($imagick['image'], $title4, $options['lottery']['jackpotColor'], $options['main']['font'], 14, 0.8);
        Banners_Graphic::insert_text($imagick['image'], $text4['text'], $title4, 0, 128, Imagick::GRAVITY_NORTH);

        // Insert Price text
        $title5 = $options['main']['priceText'];
        $font_size = Banners_Graphic::get_fit_font($title5, $options['main']['fontBold'], 34, 300, 12);
        $text5 = Banners_Graphic::text($imagick['image'], $title5, $options['lottery']['priceColor'], $options['main']['fontBold'], $font_size);
        Banners_Graphic::insert_text($imagick['image'], $text5['text'], $title5, 0, 65, Imagick::GRAVITY_SOUTH);

        // Insert Play button
        $title = $options['main']['buttonTitle'];
        $text = Banners_Graphic::text($imagick['image'], $title, $options['lottery']['buttonTitleColor'], $options['main']['font'], 15);

        $rectangle = Banners_Graphic::rectangle($options['lottery']['buttonBGColor'], $text, $title, 14, 24);

        Banners_Graphic::insert_image(
            $imagick['image'],
            $rectangle['image'],
            Banners_Calc::align_middle($rectangle['width'], $imagick['width']) + 4,
            Banners_Calc::align_bottom($rectangle['height'], $imagick['height'], 6)
        );

        // Show image
        return $imagick['image'];
    }

    /**
     * Creates 336x280 banner
     *
     * @return obj imagick
     */
    public function create_336x280($options)
    {
        // Set BG
        $imagick = Banners_Graphic::radial_gradient_image(336, 280, $options['lottery']['gradient1'], $options['lottery']['gradient2']);

        // Add stars to BG
        $stars = Banners_Graphic::load_image_color($options['main']['starsBG'], $options['lottery']['starsColor'], 1);
        Banners_Graphic::insert_image($imagick['image'], $stars['image'], 20, 44);

        // Insert lottery name
        $lottery_name = $options['main']['lotteryName'];
        $font_size = Banners_Graphic::get_fit_font($lottery_name, $options['main']['fontBold'], 36, 336, 12);
        $lottery_name_text = Banners_Graphic::text($imagick['image'], $lottery_name, $options['lottery']['lotteryNameColor'], $options['main']['fontBold'], $font_size);
        Banners_Graphic::insert_text($imagick['image'], $lottery_name_text['text'], $lottery_name, 0, 9, Imagick::GRAVITY_NORTH);

        // Insert ball BG
        $ballBG = Banners_Graphic::load_image_resize($options['main']['wholeBallBG'], 75, 75);
        Banners_Graphic::insert_image($imagick['image'], $ballBG['image'], Banners_Calc::align_middle($ballBG['width'], $imagick['width']), 65);

        // Insert ball
        $ball = Banners_Graphic::load_image_resize($options['main']['ball'], 67, 71);
        Banners_Graphic::insert_image($imagick['image'], $ball['image'], (Banners_Calc::align_middle($ball['width'], $imagick['width'])+0.5), 71);

        // Insert Jackpot text
        $title4 = $options['main']['jackpotText'];
        $text4 = Banners_Graphic::text($imagick['image'], $title4, $options['lottery']['jackpotColor'], $options['main']['font'], 14, 0.8);
        Banners_Graphic::insert_text($imagick['image'], $text4['text'], $title4, 0, 155, Imagick::GRAVITY_NORTH);

        // Insert Price text
        $title5 = $options['main']['priceText'];
        $font_size = Banners_Graphic::get_fit_font($title5, $options['main']['fontBold'], 38, 336, 12);
        $text5 = Banners_Graphic::text($imagick['image'], $title5, $options['lottery']['priceColor'], $options['main']['fontBold'], $font_size);
        Banners_Graphic::insert_text($imagick['image'], $text5['text'], $title5, 0, 66, Imagick::GRAVITY_SOUTH);

        // Insert Play button
        $title = $options['main']['buttonTitle'];
        $text = Banners_Graphic::text($imagick['image'], $title, $options['lottery']['buttonTitleColor'], $options['main']['font'], 15);

        $rectangle = Banners_Graphic::rectangle($options['lottery']['buttonBGColor'], $text, $title, 14, 24);

        Banners_Graphic::insert_image(
            $imagick['image'],
            $rectangle['image'],
            Banners_Calc::align_middle($rectangle['width'], $imagick['width']) + 4,
            Banners_Calc::align_bottom($rectangle['height'], $imagick['height'], 7)
        );

        // Show image
        return $imagick['image'];
    }

    /**
     * Creates 250x250 banner
     *
     * @return obj imagick
     */
    public function create_250x250($options)
    {
        // Set BG
        $imagick = Banners_Graphic::radial_gradient_image(250, 250, $options['lottery']['gradient1'], $options['lottery']['gradient2']);

        // Add stars to BG
        $stars = Banners_Graphic::load_image_color($options['main']['starsBG'], $options['lottery']['starsColor'], 1);
        Banners_Graphic::insert_image($imagick['image'], $stars['image'], -30, 40);

        // Insert lottery name
        $lottery_name = $options['main']['lotteryName'];
        $font_size = Banners_Graphic::get_fit_font($lottery_name, $options['main']['fontBold'], 30, 250, 12);
        $lottery_name_text = Banners_Graphic::text($imagick['image'], $lottery_name, $options['lottery']['lotteryNameColor'], $options['main']['fontBold'], $font_size);
        Banners_Graphic::insert_text($imagick['image'], $lottery_name_text['text'], $lottery_name, 0, 11, Imagick::GRAVITY_NORTH);

        // Insert ball BG
        $ballBG = Banners_Graphic::load_image_resize($options['main']['wholeBallBG'], 65, 65);
        Banners_Graphic::insert_image($imagick['image'], $ballBG['image'], Banners_Calc::align_middle($ballBG['width'], $imagick['width']), 54);

        // Insert ball
        $ball = Banners_Graphic::load_image_resize($options['main']['ball'], 58, 62);
        Banners_Graphic::insert_image($imagick['image'], $ball['image'], (Banners_Calc::align_middle($ball['width'], $imagick['width'])), 59);

        // Insert Jackpot text
        $title4 = $options['main']['jackpotText'];
        $text4 = Banners_Graphic::text($imagick['image'], $title4, $options['lottery']['jackpotColor'], $options['main']['font'], 14, 0.8);
        Banners_Graphic::insert_text($imagick['image'], $text4['text'], $title4, 0, 128, Imagick::GRAVITY_NORTH);

        // Insert Price text
        $title5 = $options['main']['priceText'];
        $font_size = Banners_Graphic::get_fit_font($title5, $options['main']['fontBold'], 34, 250, 12);
        $text5 = Banners_Graphic::text($imagick['image'], $title5, $options['lottery']['priceColor'], $options['main']['fontBold'], $font_size);
        Banners_Graphic::insert_text($imagick['image'], $text5['text'], $title5, 0, 65, Imagick::GRAVITY_SOUTH);

        // Insert Play button
        $title = $options['main']['buttonTitle'];
        $text = Banners_Graphic::text($imagick['image'], $title, $options['lottery']['buttonTitleColor'], $options['main']['font'], 15);

        $rectangle = Banners_Graphic::rectangle($options['lottery']['buttonBGColor'], $text, $title, 14, 24);

        Banners_Graphic::insert_image(
            $imagick['image'],
            $rectangle['image'],
            Banners_Calc::align_middle($rectangle['width'], $imagick['width']) + 4,
            Banners_Calc::align_bottom($rectangle['height'], $imagick['height'], 6)
        );

        // Show image
        return $imagick['image'];
    }

    /**
     * Creates 160x600 banner
     *
     * @return obj imagick
     */
    public function create_160x600($options)
    {
        // Set BG
        $imagick = Banners_Graphic::radial_gradient_image(160, 600, $options['lottery']['gradient1'], $options['lottery']['gradient2']);

        // Add stars to BG
        $stars = Banners_Graphic::load_image_color($options['main']['starsBG'], $options['lottery']['starsColor'], 1);
        Banners_Graphic::insert_image($imagick['image'], $stars['image'], -30, 40);

        // Add stars to BG
        $stars = Banners_Graphic::load_image_color($options['main']['starsBG'], $options['lottery']['starsColor'], 1);
        Banners_Graphic::insert_image($imagick['image'], $stars['image'], -30, 350);

        // Insert lottery name
        $lottery_name = $options['main']['lotteryName'];
        $font_size = Banners_Graphic::get_fit_font($lottery_name, $options['main']['fontBold'], 22, 160, 12);
        $lottery_name_text = Banners_Graphic::text($imagick['image'], $lottery_name, $options['lottery']['lotteryNameColor'], $options['main']['fontBold'], $font_size);
        Banners_Graphic::insert_text($imagick['image'], $lottery_name_text['text'], $lottery_name, 0, 170, Imagick::GRAVITY_NORTH);
        
        // Insert Ball BG
        $ballBG = Banners_Graphic::load_image_resize($options['main']['wholeBallBG'], 135, 135);
        Banners_Graphic::insert_image($imagick['image'], $ballBG['image'], Banners_Calc::align_middle($ballBG['width'], $imagick['width']), 218);
        

        // Insert Ball
        $ball = Banners_Graphic::load_image_resize($options['main']['ball'], 129, 137);
        Banners_Graphic::insert_image($imagick['image'], $ball['image'], (Banners_Calc::align_middle($ball['width'], $imagick['width'])+1), 225);

        // Insert Jackpot text
        $title4 = $options['main']['jackpotText'];
        $text4 = Banners_Graphic::text($imagick['image'], $title4, $options['lottery']['jackpotColor'], $options['main']['font'], 15, 0.8);
        Banners_Graphic::insert_text($imagick['image'], $text4['text'], $title4, 0, 385, Imagick::GRAVITY_NORTH);

        // Insert Price text
        $title5 = $options['main']['priceText'];
        $font_size = Banners_Graphic::get_fit_font($title5, $options['main']['fontBold'], 23, 160, 12);
        $text5 = Banners_Graphic::text($imagick['image'], $title5, $options['lottery']['priceColor'], $options['main']['fontBold'], $font_size);
        Banners_Graphic::insert_text($imagick['image'], $text5['text'], $title5, 0, 165, Imagick::GRAVITY_SOUTH);

        // Insert Play button
        $title = $options['main']['buttonTitle'];
        $text = Banners_Graphic::text($imagick['image'], $title, $options['lottery']['buttonTitleColor'], $options['main']['font'], 15);

        $rectangle = Banners_Graphic::rectangle($options['lottery']['buttonBGColor'], $text, $title, 14, 24);

        Banners_Graphic::insert_image(
            $imagick['image'],
            $rectangle['image'],
            Banners_Calc::align_middle($rectangle['width'], $imagick['width']) + 4,
            Banners_Calc::align_bottom($rectangle['height'], $imagick['height'], 11)
        );

        // Second Play button
        $title = $options['main']['buttonTitle'];
        $text = Banners_Graphic::text($imagick['image'], $title, $options['lottery']['buttonTitleColor'], $options['main']['font'], 15);

        $rectangle = Banners_Graphic::rectangle($options['lottery']['buttonBGColor'], $text, $title, 14, 24);

        Banners_Graphic::insert_image(
            $imagick['image'],
            $rectangle['image'],
            Banners_Calc::align_middle($rectangle['width'], $imagick['width']) + 4,
            20
        );

        // Show image
        return $imagick['image'];
    }

    /**
     * Creates 120x600 banner
     *
     * @return obj imagick
     */
    public function create_120x600($options)
    {
        // Set BG
        $imagick = Banners_Graphic::radial_gradient_image(120, 600, $options['lottery']['gradient1'], $options['lottery']['gradient2']);

        // Add stars to BG
        $stars = Banners_Graphic::load_image_color($options['main']['starsBG'], $options['lottery']['starsColor'], 1);
        Banners_Graphic::insert_image($imagick['image'], $stars['image'], -30, 40);

        // Add stars to BG
        $stars = Banners_Graphic::load_image_color($options['main']['starsBG'], $options['lottery']['starsColor'], 1);
        Banners_Graphic::insert_image($imagick['image'], $stars['image'], -30, 350);

        // Insert lottery name
        $lottery_name = $options['main']['lotteryName'];
        $font_size = Banners_Graphic::get_fit_font($lottery_name, $options['main']['fontBold'], 18, 120, 12);
        $lottery_name_text = Banners_Graphic::text($imagick['image'], $lottery_name, $options['lottery']['lotteryNameColor'], $options['main']['fontBold'], $font_size);
        Banners_Graphic::insert_text($imagick['image'], $lottery_name_text['text'], $lottery_name, 0, 170, Imagick::GRAVITY_NORTH);

        
        // Insert Ball BG
        $ballBG = Banners_Graphic::load_image_resize($options['main']['wholeBallBG'], 105, 105);
        Banners_Graphic::insert_image($imagick['image'], $ballBG['image'], Banners_Calc::align_middle($ballBG['width'], $imagick['width']), 219);
        

        // Insert Ball
        $ball = Banners_Graphic::load_image_resize($options['main']['ball'], 99, 106);
        Banners_Graphic::insert_image($imagick['image'], $ball['image'], (Banners_Calc::align_middle($ball['width'], $imagick['width'])+1), 225);

        // Insert Jackpot text
        $title4 = $options['main']['jackpotText'];
        $font_size = Banners_Graphic::get_fit_font($title4, $options['main']['font'], 13, 120, 8);
        $text4 = Banners_Graphic::text($imagick['image'], $title4, $options['lottery']['jackpotColor'], $options['main']['font'], $font_size, 0.8);
        Banners_Graphic::insert_text($imagick['image'], $text4['text'], $title4, 0, 355, Imagick::GRAVITY_NORTH);

        // Insert Price text
        $title5 = $options['main']['priceText'];
        $font_size = Banners_Graphic::get_fit_font($title5, $options['main']['fontBold'], 19, 120, 12);
        $text5 = Banners_Graphic::text($imagick['image'], $title5, $options['lottery']['priceColor'], $options['main']['fontBold'], $font_size);
        Banners_Graphic::insert_text($imagick['image'], $text5['text'], $title5, 0, 201, Imagick::GRAVITY_SOUTH);

        // Insert Play button
        $title = $options['main']['buttonTitle'];
        $text = Banners_Graphic::text($imagick['image'], $title, $options['lottery']['buttonTitleColor'], $options['main']['font'], 15);

        $rectangle = Banners_Graphic::rectangle($options['lottery']['buttonBGColor'], $text, $title, 14, 24);

        Banners_Graphic::insert_image(
            $imagick['image'],
            $rectangle['image'],
            Banners_Calc::align_middle($rectangle['width'], $imagick['width']) + 4,
            Banners_Calc::align_bottom($rectangle['height'], $imagick['height'], 11)
        );

        // Second Play button
        $title = $options['main']['buttonTitle'];
        $text = Banners_Graphic::text($imagick['image'], $title, $options['lottery']['buttonTitleColor'], $options['main']['font'], 15);

        $rectangle = Banners_Graphic::rectangle($options['lottery']['buttonBGColor'], $text, $title, 14, 24);

        Banners_Graphic::insert_image(
            $imagick['image'],
            $rectangle['image'],
            Banners_Calc::align_middle($rectangle['width'], $imagick['width']) + 4,
            20
        );

        // Show image
        return $imagick['image'];
    }

    /**
     * Creates 468x60 banner
     *
     * @return obj imagick
     */
    public function create_468x60($options)
    {
        // Set BG
        $imagick = Banners_Graphic::radial_gradient_image(468, 60, $options['lottery']['gradient1'], $options['lottery']['gradient2']);
        // Banners_Graphic::set_border($imagick['image'], $options['lottery']['borderColor'], '2', '2');

        // Add stars to BG
        $stars = Banners_Graphic::load_image_color($options['main']['starsBG'], $options['lottery']['starsColor'], 1);
        Banners_Graphic::insert_image($imagick['image'], $stars['image'], 80, -50);

        // Insert ball BG
        $ballBG = Banners_Graphic::load_image_resize($options['main']['wholeBallBG'], 44.5, 46);
        Banners_Graphic::insert_image($imagick['image'], $ballBG['image'], 7, 6);

        // Insert balls
        $ball = Banners_Graphic::load_image_resize($options['main']['ball'], 39, 43);
        Banners_Graphic::insert_image($imagick['image'], $ball['image'], 10, Banners_Calc::vertical_middle(($ball['height']-3), $imagick['height'])); // -7 cause of shadow

        // Insert lottery name
        $lottery_name = $options['main']['lotteryName'];
        $lottery_name_text = Banners_Graphic::text($imagick['image'], $lottery_name, $options['lottery']['lotteryNameColor'], $options['main']['fontBold'], 18);

        Banners_Graphic::insert_text($imagick['image'], $lottery_name_text['text'], $lottery_name, 60, 0, Imagick::GRAVITY_WEST);

        // Insert Play button
        $title = $options['main']['buttonTitle'];
        $text = Banners_Graphic::text($imagick['image'], $title, $options['lottery']['buttonTitleColor'], $options['main']['font'], 15);

        $rectangle = Banners_Graphic::rectangle($options['lottery']['buttonBGColor'], $text, $title, 14, 24);

        Banners_Graphic::insert_image(
            $imagick['image'],
            $rectangle['image'],
            Banners_Calc::align_right($rectangle['width'], $imagick['width'], 3),
            11
        );

        // Insert Jackpot text
        $title4 = $options['main']['jackpotText'];
        $text4 = Banners_Graphic::text($imagick['image'], $title4, $options['lottery']['jackpotColor'], $options['main']['font'], 14, 0.8);
        Banners_Graphic::insert_text($imagick['image'], $text4['text'], $title4, Banners_Calc::margin_with_object($rectangle['width'], 20), 9, Imagick::GRAVITY_NORTHEAST);

        // Insert Price text
        $title5 = $options['main']['priceText'];
        $text5 = Banners_Graphic::text($imagick['image'], $title5, $options['lottery']['priceColor'], $options['main']['fontBold'], 20);
        Banners_Graphic::insert_text($imagick['image'], $text5['text'], $title5, Banners_Calc::margin_with_object($rectangle['width'], 20), 8, Imagick::GRAVITY_SOUTHEAST);

        // Show image
        return $imagick['image'];
    }

    /**
     * Creates 728x90 banner
     *
     * @return obj imagick
     */
    public function create_728x90($options)
    {
        // Set BG
        $imagick = Banners_Graphic::radial_gradient_image(728, 90, $options['lottery']['gradient1'], $options['lottery']['gradient2']);

        // Insert stars bg
        $stars = Banners_Graphic::load_image_color($options['main']['starsBG'], $options['lottery']['starsColor'], 1);
        Banners_Graphic::insert_image($imagick['image'], $stars['image'], 220, -30);

        // Insert ball BG
        $ballBG = Banners_Graphic::load_image($options['main']['ballBG']);
        Banners_Graphic::insert_image($imagick['image'], $ballBG['image'], 10, 0);

        // Insert ball
        $ball = Banners_Graphic::load_image_resize($options['main']['ball'], 88, 93);
        Banners_Graphic::insert_image($imagick['image'], $ball['image'], 20, 3);

        // Insert play button
        $title = $options['main']['buttonTitle'];
        $text = Banners_Graphic::text($imagick['image'], $title, $options['lottery']['buttonTitleColor'], $options['main']['font'], 15);
        $rectangle = Banners_Graphic::rectangle($options['lottery']['buttonBGColor'], $text, $title, 14, 24);

        Banners_Graphic::insert_image(
            $imagick['image'],
            $rectangle['image'],
            Banners_Calc::align_right($rectangle['width'], $imagick['width'], 5),
            28
        );

        // Insert lottery name
        $title2 =  $options['main']['lotteryName'];
        $text2 = Banners_Graphic::text($imagick['image'], $title2, $options['lottery']['lotteryNameColor'], $options['main']['fontBold'], 28);
        Banners_Graphic::insert_text($imagick['image'], $text2['text'], $title2, 140, 54);

        //$text3 = Banners_Graphic::text($imagick['image'], $title3, $options['lottery']['lotteryNameColor'], $options['main']['font'], 28);
        //Banners_Graphic::insert_text($imagick['image'], $text3['text'], $title3, 140, 67);

        // Insert jackpot text
        $title4 = $options['main']['jackpotText'];
        $text4 = Banners_Graphic::text($imagick['image'], $title4, $options['lottery']['jackpotColor'], $options['main']['font'], 18, 0.5);


        Banners_Graphic::insert_text(
            $imagick['image'],
            $text4['text'],
            $title4,
            Banners_Calc::margin_with_object($rectangle['width'], 20),
            21,
            Imagick::GRAVITY_NORTHEAST
        );

        // Insert price text
        $title5 = $options['main']['priceText'];
        $text5 = Banners_Graphic::text($imagick['image'], $title5, $options['lottery']['priceColor'], $options['main']['fontBold'], 28);
        Banners_Graphic::insert_text(
            $imagick['image'],
            $text5['text'],
            $title5,
            Banners_Calc::margin_with_object($rectangle['width'], 20),
            39,
            Imagick::GRAVITY_NORTHEAST
        );

        // Show image
        return $imagick['image'];
    }

    /**
     * Creates 125x125 banner
     *
     * @return obj imagick
     */
    public function create_125x125($options)
    {
        // Set BG
        $imagick = Banners_Graphic::radial_gradient_image(125, 125, $options['lottery']['gradient1'], $options['lottery']['gradient2']);

        // Add stars to BG
        $stars = Banners_Graphic::load_image_color($options['main']['starsBG'], $options['lottery']['starsColor'], 1);
        Banners_Graphic::insert_image($imagick['image'], $stars['image'], -30, 20);

        // Insert lottery name
        $lottery_name = $options['main']['lotteryName'];
        $font_size = Banners_Graphic::get_fit_font($lottery_name, $options['main']['fontBold'], 15, 125, 12);
        $lottery_name_text = Banners_Graphic::text($imagick['image'], $lottery_name, $options['lottery']['lotteryNameColor'], $options['main']['fontBold'], $font_size);
        Banners_Graphic::insert_text($imagick['image'], $lottery_name_text['text'], $lottery_name, 0, 2, Imagick::GRAVITY_NORTH);

        // Insert ball BG
        $ballBG = Banners_Graphic::load_image_resize($options['main']['wholeBallBG'], 42, 42);
        Banners_Graphic::insert_image($imagick['image'], $ballBG['image'], Banners_Calc::align_middle($ballBG['width'], $imagick['width']), 25);

        // Insert ball
        $ball = Banners_Graphic::load_image_resize($options['main']['ball'], 37, 40);
        Banners_Graphic::insert_image($imagick['image'], $ball['image'], (Banners_Calc::align_middle($ball['width'], $imagick['width'])+0), 28);

        // Insert Price text
        $title5 = $options['main']['priceText'];
        $font_size = Banners_Graphic::get_fit_font($title5, $options['main']['fontBold'], 16, 125, 12);
        $text5 = Banners_Graphic::text($imagick['image'], $title5, $options['lottery']['priceColor'], $options['main']['fontBold'], $font_size);
        Banners_Graphic::insert_text($imagick['image'], $text5['text'], $title5, 0, 37, Imagick::GRAVITY_SOUTH);

        // Insert Play button
        $title = $options['main']['buttonTitle'];
        $text = Banners_Graphic::text($imagick['image'], $title, $options['lottery']['buttonTitleColor'], $options['main']['font'], 13);

        $rectangle = Banners_Graphic::rectangle_small($options['lottery']['buttonBGColor'], $text, $title, 11, 17);

        Banners_Graphic::insert_image(
            $imagick['image'],
            $rectangle['image'],
            Banners_Calc::align_middle($rectangle['width'], $imagick['width']) + 2,
            Banners_Calc::align_bottom($rectangle['height'], $imagick['height'], -2)
        );

        // Show image

        return $imagick['image'];
    }
}

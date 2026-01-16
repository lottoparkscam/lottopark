<?php

/**
 * CAUTION!
 * This class consist an error that there is no checking if instance of Imagick is
 * successfully created and in couple places is used without that checking.
 * So, in such case there will not be new image generated and will be error logged.
 */
class Banners_Graphic
{
    /**
     *
     * @param int $width
     * @param int $height
     * @param string $color1
     * @param string $color2
     * @return null|array
     */
    public static function radial_gradient_image(
        int $width,
        int $height,
        string $color1,
        string $color2
    ):? array {
        $imagick['image'] = new Imagick();
        
        $pseudo_string = 'gradient:' . $color1 . '-' . $color2 . '';
        
        $imagick['image']->newPseudoImage($width, $height, $pseudo_string);

        /*$dimensions = $imagick['image']->getImageGeometry();*/

        $imagick['width'] = $width;
        $imagick['height'] = $height;

        return $imagick;
    }

    /**
     *
     * @param string $path
     * @return null|array
     */
    public static function load_image(string $path = ""):? array
    {
        if (empty($path)) {
            return null;
        }
        
        $imagick['image'] = new Imagick();
        $imagick['image']->readImage(realpath($path));

        $dimensions = $imagick['image']->getImageGeometry();

        $imagick['width'] = $dimensions['width'];
        $imagick['height'] = $dimensions['height'];

        return $imagick;
    }

    /**
     *
     * @param string $path
     * @param int $width
     * @param int $height
     * @return array|null
     */
    public static function load_image_resize(
        string $path = "",
        int $width = 0,
        int $height = 0
    ):? array {
        if (empty($path)) {
            return null;
        }
        
        $imagick['image'] = new Imagick();
        $imagick['image']->readImage(realpath($path));
        $imagick['image']->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);

        $dimensions = $imagick['image']->getImageGeometry();

        $imagick['width'] = $dimensions['width'];
        $imagick['height'] = $dimensions['height'];

        return $imagick;
    }

    /**
     *
     * @param string $imagePath
     * @param mixed $color
     * @param string $opacity
     * @return null|array
     */
    public static function load_image_color(
        string $imagePath,
        $color,
        string $opacity
    ):? array {
        $imagick['image'] = new Imagick(realpath($imagePath));
        $opacity_color = new ImagickPixel("rgba(255, 255, 255, $opacity)");
        $imagick['image']->colorizeImage($color, $opacity_color);

        $dimensions = $imagick['image']->getImageGeometry();

        $imagick['width'] = $dimensions['width'];
        $imagick['height'] = $dimensions['height'];
        
        return $imagick;
    }

    /**
     *
     * @param type $imagick
     * @param type $color
     * @param type $width
     * @param type $height
     * @return type
     */
    public static function set_border($imagick, $color, $width, $height)
    {
        $imagick->borderImage($color, $width, $height);

        return $imagick;
    }

    /**
     *
     * @param type $imagick
     */
    public static function show_image($imagick)
    {
        header("Content-Type: image/png");
        $imagick->setimageformat("png");

        echo $imagick->getImageBlob();
    }

    /**
     *
     * @param type $imagick
     * @param string $text
     * @param type $color
     * @param string $font
     * @param float $size
     * @param float $opacity
     * @return array
     */
    public static function text(
        $imagick,
        string $text,
        $color,
        string $font,
        float $size,
        float $opacity = 1
    ): array {
        $draw = new ImagickDraw();
        $draw->setFillColor($color);
        $draw->setFontSize($size);
        $draw->setFillOpacity($opacity);
        $draw->setFont($font);

        $metrics = $imagick->queryFontMetrics($draw, $text);

        return [
            'metrics' => $metrics,
            'text' => $draw
        ];
    }

    /**
     *
     * @param type $imagick
     * @param string $text
     * @param type $color
     * @param string $font
     * @param float $size
     * @return array
     */
    public static function text_stroke(
        $imagick,
        string $text,
        $color,
        string $font,
        float $size
    ): array {
        $draw = new ImagickDraw();

        $draw->setFillColor($color);
        $draw->setFontSize($size);
        $draw->setStrokeColor('#000');
        $draw->setStrokeWidth(2);
        $draw->setStrokeAntialias(false);  //try with and without
        $draw->setStrokeOpacity(0.1);
        $draw->setTextAntialias(false);
        
        $draw->setFont("assets/fonts/SourceSansPro/SourceSansPro-Bold.ttf");

        $metrics = $imagick->queryFontMetrics($draw, $text);

        return [
            'metrics' => $metrics,
            'text' => $draw
        ];
    }

    /**
     *
     * @param type $imagick
     * @param type $draw
     * @param type $text
     * @param type $x
     * @param type $y
     * @param type $gravity
     * @param type $angle
     * @return void
     */
    public static function insert_text($imagick, $draw, $text, $x, $y, $gravity = null, $angle = 0): void
    {
        if (!empty($gravity)) {
            $draw->setGravity($gravity);
        }

        $imagick->annotateImage($draw, $x, $y, $angle, $text);
    }

    /**
     *
     * @param type $imagick
     * @param type $imagickImage
     * @param type $x
     * @param type $y
     * @return type
     */
    public static function insert_image($imagick, $imagickImage, $x, $y)
    {
        $imagick->compositeImage($imagickImage, Imagick::COMPOSITE_ATOP, $x, $y);

        return $imagick;
    }

    /**
     *
     * @param type $background
     * @param type $draw2
     * @param type $text
     * @param type $x
     * @param type $y
     * @return type
     */
    public static function rectangle($background, $draw2, $text, $x, $y)
    {
        $strokeColor = 'white';
        $fillColor = $background;
        $backgroundColor = 'transparent';
        $draw = new ImagickDraw();
        $strokeColor = new ImagickPixel($strokeColor);
        $fillColor = new ImagickPixel($fillColor);
     
        $draw->setStrokeColor($strokeColor);
        $draw->setFillColor($fillColor);
        $draw->setStrokeOpacity(0);
        $draw->setStrokeWidth(2);
     
        $draw->roundRectangle(0, 0, ($draw2['metrics']['textWidth']+26), ($draw2['metrics']['textHeight']+19), 3, 3);

        $imagick['image'] = new Imagick();
        $imagick['image']->newImage(($draw2['metrics']['textWidth'] + 30), ($draw2['metrics']['textHeight'] + 29), $backgroundColor);
        $imagick['image']->setImageFormat("png");
        $imagick['image']->drawImage($draw);
        $imagick['image']->annotateImage($draw2['text'], $x, $y, 0, $text);

        $dimensions = $imagick['image']->getImageGeometry();

        $imagick['width'] = $dimensions['width'];
        $imagick['height'] = $dimensions['height'];

        return $imagick;
    }

    /**
     *
     * @param type $background
     * @param type $draw2
     * @param string $text
     * @param float $x
     * @param float $y
     * @return type
     */
    public static function rectangle_small($background, $draw2, string $text, float $x, float $y)
    {
        $strokeColor = 'white';
        $fillColor = $background;
        $backgroundColor = 'transparent';
        $draw = new ImagickDraw();
        $strokeColor = new ImagickPixel($strokeColor);
        $fillColor = new ImagickPixel($fillColor);
     
        $draw->setStrokeColor($strokeColor);
        $draw->setFillColor($fillColor);
        $draw->setStrokeOpacity(0);
        $draw->setStrokeWidth(2);
        $draw->roundRectangle(0, 0, ($draw2['metrics']['textWidth']+19), ($draw2['metrics']['textHeight']+9), 3, 3);

        $imagick['image'] = new Imagick();
        $imagick['image']->newImage(($draw2['metrics']['textWidth'] + 24), ($draw2['metrics']['textHeight'] + 18), $backgroundColor);
        $imagick['image']->setImageFormat("png");
        $imagick['image']->drawImage($draw);
        $imagick['image']->annotateImage($draw2['text'], $x, $y, 0, $text);

        $dimensions = $imagick['image']->getImageGeometry();

        $imagick['width'] = $dimensions['width'];
        $imagick['height'] = $dimensions['height'];

        return $imagick;
    }

    /**
     *
     * @param string $text
     * @param string $font
     * @param int $start_size
     * @param int $max_width
     * @param int $paddings
     * @return int
     */
    public static function get_fit_font(
        string $text,
        string $font,
        int $start_size,
        int $max_width,
        int $paddings
    ) {
        $current_width = $max_width;
        $available_width = $max_width - $paddings;
        $font_size = $start_size;

        while ($current_width > $available_width) {
            $im = new Imagick();
            $draw = new ImagickDraw();

            $draw->setFont($font);

            $draw->setFontSize($font_size);
            $fm = $im->queryFontMetrics($draw, $text);

            $current_width = $fm['textWidth'];

            if ($fm['textWidth'] > $available_width) {
                $font_size--;
            }
        }
    
        return $font_size;
    }

    /**
     *
     * @param type $object_height
     * @param type $main_height
     * @return type
     */
    public static function calc_vertical_middle($object_height, $main_height)
    {
        $calc = ($main_height - $object_height) / 2;
        
        return $calc;
    }
}

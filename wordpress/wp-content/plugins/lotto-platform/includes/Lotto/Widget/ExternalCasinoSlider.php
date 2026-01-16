<?php

if (!defined('WPINC')) {
    die;
}

use Fuel\Core\Cache;
use Helpers\AssetHelper;
use Helpers\DeviceHelper;
use Models\Whitelabel;

class Lotto_Widget_ExternalCasinoSlider extends WP_Widget
{
    private int $whitelabelId;
    private ?Whitelabel $whitelabel;
    private const CACHE_KEY = 'externalCasinoSliderWidget_';
    private bool $isMobile;

    public function __construct()
    {
        $this->whitelabel = Container::get('whitelabel');

        $sliderId = 'lotto_platform_widget_slot_games_external_slider';

        parent::__construct(
            $sliderId,
            _('External Casino Slider'),
            [
                'description' => _('Display slider widget &bull; LIMIT: none')
            ]
        );

        if (is_active_widget(false, false, $this->id_base, true)) {
            add_action('init', [$this, 'init']);
        }

        $this->isMobile = DeviceHelper::isMobile();
    }

    public function init(): void
    {
        register_post_type('slider');
    }

    public function widget($args, $instance): void
    {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $title = apply_filters('widget_title', $title, $instance, $this->id_base);
        $width = !empty($instance['width']) ? $instance['width'] : '100';

        Lotto_Helper::widget_before(true, $args);

        list(
            $width,
            $marginLeft,
            $marginRight,
            $full
        ) = Lotto_Helper::calculate_widget_width($width, $args);

        $styles = <<<STYLES
         style="width: $width; max-width:100%; margin-left: $marginLeft; margin-right: $marginRight">
        STYLES;
        echo str_replace('>', $styles, $args['before_widget']);

        if (file_exists(get_stylesheet_directory() . '/widget/slots/slider/widget.php')) {
            include(get_stylesheet_directory() . '/widget/slots/slider/widget.php');
        } else {
            include(get_template_directory() . '/widget/slots/slider/widget.php');
        }

        $casinoSliderJs = AssetHelper::mix('js/slots/ExternalWidget.min.js', AssetHelper::TYPE_WORDPRESS, true);

        wp_enqueue_script_slick_plugin();
        wp_enqueue_script('slots-external-widget', $casinoSliderJs, ['jquery'], false, [
            'strategy' => 'defer',
            'in_footer' => true
        ]);

        echo $args['after_widget'];

        # variables used in slots/widget.js
        $gamesCount = !empty($instance['games_count']) ? $instance['games_count'] : 32;
        $apiUrl = !empty($instance['api_url']) ? $instance['api_url'] : '';
        $casinoUrl = !empty($instance['casino_url']) ? $instance['casino_url'] : '';

        $translatedPlayNow = _('Play now');
        $translatedPlay = _('Play');
        $translatedChoose = ucfirst(_('choose'));

        echo "<script>
                const CASINO_SLIDER_GAMES_COUNT = {$gamesCount};
                const CASINO_WIDGET_TITLE = '{$title}';
                const CASINO_URL = '{$casinoUrl}';
                const CASINO_API_URL = '{$apiUrl}';
                const PLAY_NOW = '{$translatedPlayNow}';
                const PLAY = '{$translatedPlay}';
                const CHOOSE = '{$translatedChoose}';
            </script>";

        if ($full) {
            echo '<div class="clearfix"></div>';
        }
    }

    public function form($instance): void
    {
        $title = isset($instance['title']) ? htmlspecialchars($instance['title']) : '';
        $width = $instance['width'] ?? '100';
        $gamesCountToDisplay = !empty($instance['games_count']) ? $instance['games_count'] : 32;
        $apiUrl = !empty($instance['api_url']) ? $instance['api_url'] : '';
        $casinoUrl = !empty($instance['casino_url']) ? $instance['casino_url'] : '';

        include(LOTTO_PLUGIN_DIR . 'views/widget/small/slider/external/settings.php');
    }

    public function update($newInstance, $oldInstance): array
    {
        $instance = $oldInstance;
        $whitelabel = Container::get('whitelabel');

        $instance['title'] = sanitize_text_field($newInstance['title']);
        $instance['api_url'] = sanitize_text_field($newInstance['api_url']);
        $instance['casino_url'] = sanitize_text_field($newInstance['casino_url']);
        $instance['width'] = intval($newInstance['width']);
        $cacheKeyToRemove = $this->isMobile ? 'mobile_' : 'desktop_';
        $oldInstance['games_count'] = !empty($oldInstance['games_count']) ? $oldInstance['games_count'] : 32;
        $this->whitelabelId = $whitelabel->id ?? 0;
        $cacheKeyToRemove .= $this->whitelabelId . self::CACHE_KEY . ICL_LANGUAGE_CODE . '_' . $oldInstance['games_count'];
        $cacheKeyToRemove = Helpers_Cache::changeNumbersInCacheKeyToLetters($cacheKeyToRemove);
        Cache::delete($cacheKeyToRemove);

        if (empty($newInstance['games_count'])) {
            $newInstance['games_count'] = 32;
        }

        $gamesCount = intval($newInstance['games_count']);
        $isGamesCountValid = $gamesCount % 8 === 0;
        if ($isGamesCountValid) {
            $instance['games_count'] = $gamesCount;
        }

        if ($instance['width'] > 100) {
            $instance['width'] = 100;
        }
        if ($instance['width'] <= 0) {
            $instance['width'] = 50;
        }

        return $instance;
    }
}

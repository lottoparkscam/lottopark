<?php

use Repositories\WhitelabelLanguageRepository;

if (!defined('WPINC')) {
    die;
}
final class Lotto_Widget_Featured extends Lotto_Widget_Abstract_Featured
{
    const TYPE_SMALL = 1;
    const TYPE_LARGE = 2;
    const TYPE_WITH_BACKGROUND = 3;
    const LOTTOPARK_GREEN = '#41934c';
    const WHITE = '#fff';
    const BG_LARGE_TEXT_ID = 'bg_large_text';
    const BG_LARGE_SUBTEXT_ID = 'bg_large_subtext';
    const CUSTOM_SLIDE_COUNT = 11;

    public function __construct()
    {
        parent::__construct(
            'lotto_platform_widget_featured', // Base ID
            _('Lotto Featured'), // Name
            [
                'description' => _('Displays featured lotteries as a slider &bull; LIMIT: none')
            ] // Args
        );
    }

    public static function get_types(): array
    {
        $types = [
            self::TYPE_SMALL,
            self::TYPE_LARGE,
            self::TYPE_WITH_BACKGROUND
        ];

        return $types;
    }

    public function prepareLotteriesData(int $whitelabel_id, int $slideCount): array
    {
        $lotteries = Model_Whitelabel::get_lotteries_by_highest_jackpot_for_whitelabel($whitelabel_id, false);
        $keno = Model_Whitelabel::get_lotteries_by_highest_jackpot_for_whitelabel($whitelabel_id, false, true);
        $raffle = Model_Raffle::getActiveRaffleForWhitelabelByHighestPrize($whitelabel_id)['__by_id'];

        $result = array_merge(...array_map(null, $lotteries, $keno, $raffle));
        $result = array_filter($result);
        $result = array_values($result);
        $result = array_slice($result, 0, $slideCount);

        return $result;
    }

    public function widget($args, $instance)
    {
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $settings = $this->getSettings($instance);
        $lotteries_nosort = Model_Lottery::get_really_all_lotteries_for_whitelabel($whitelabel);
        $type = (!empty($instance['type'])) ? $instance['type'] : self::TYPE_SMALL;
        $lotteries = null;

        $languages = $this->getLanguages();
        $languages = array_filter($languages, function ($item) {
            return str_contains($item['code'], ICL_LANGUAGE_CODE);
        });
        $language = reset($languages);
        $langCode = str_replace('_', '', $language['code']);

        $order = !empty($settings[$langCode]['order']) ? $settings[$langCode]['order'] : 0;
        $slidecount = !empty($settings[$langCode]['count']) && $settings[$langCode]['count'] > 0 ? $settings[$langCode]['count'] : $this->default_count;
        $whitelabel_id = (int) $whitelabel['id'];
        $widgetId = $this->id;

        switch ($order) {
            case 1:
            case 2:
            case 3:
                $slidecount = self::CUSTOM_SLIDE_COUNT;
                $lotteries = $this->prepareLotteriesData($whitelabel_id, self::CUSTOM_SLIDE_COUNT);
                break;
            case 4:
                $lottery_settings = $settings[$langCode]['lotteries'];

                $filteredLotteries = array_filter($lottery_settings, function($lottery) {
                    return $lottery['order'] !== 0;
                });
                uasort($filteredLotteries, function($a, $b) {
                    return $a['order'] - $b['order'];
                });
                $filteredLotteries = array_keys($filteredLotteries);

                $lotteries = Model_Whitelabel::get_lotteries_by_custom_order_for_whitelabel(
                    $whitelabel_id,
                    $filteredLotteries
                );
                break;
            case 5:
                $lotteries = Model_Whitelabel::get_lotteries_by_nearest_draw_for_whitelabel($whitelabel_id);
                break;
            default:
                $slidecount = self::CUSTOM_SLIDE_COUNT;
                $lotteries = $this->prepareLotteriesData($whitelabel_id, self::CUSTOM_SLIDE_COUNT);
                break;
        }

        $title = (!empty($instance['title'])) ? $instance['title'] : '';
        $title = apply_filters('widget_title', $title, $instance, $this->id_base);

        Lotto_Helper::widget_before(false, $args);

        echo $args['before_widget'];

        if (file_exists(get_stylesheet_directory() . '/widget/featured/widget.php')) {
            include(get_stylesheet_directory() . '/widget/featured/widget.php');
        } else {
            include(get_template_directory() . '/widget/featured/widget.php');
        }

        echo $args['after_widget'];

        Lotto_Helper::widget_after(false, $args);
        // outputs the content of the widget
    }

    public function form($instance)
    {
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $languages = $this->getLanguages();
        //$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
        $type = isset($instance['type']) ? $instance['type'] : '';
        $settings = $this->getSettings($instance);
        $lotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel($whitelabel);

        include(LOTTO_PLUGIN_DIR . 'views/widget/featured/settings.php');
    }

    public function update($new_instance, $old_instance): array
    {
        $instance = $old_instance;

        //$instance['title'] = sanitize_text_field( $new_instance['title'] );
        foreach ($new_instance['settings'] as &$lang) {
            foreach ($lang as $key => &$setting) {
                if (in_array($key, ['order'])) {
                    $setting = intval($setting);
                    if ($setting < 0 || $setting > 5) {
                        $setting = 0;
                    }
                }

                if (in_array($key, ['count'])) {
                    $setting = intval($setting);
                    if ($setting < 1) {
                        $setting = 1;
                    }
                }

                if (in_array($key, ['lotteries'])) {
                    foreach ($setting as $key2 => &$lottery) {
                        //$lottery['header'] = sanitize_text_field($lottery['header']);
                        $lottery['name'] = sanitize_text_field($lottery['name']);
                        $lottery['order'] = intval($lottery['order']);
                    }
                }

                if (in_array($key, ['title'])) {
                    $setting = sanitize_text_field($setting);
                }

                if (in_array($key, ['subtitle'])) {
                    $setting = sanitize_text_field($setting);
                }
            }
        }

        $instance['settings'] = $new_instance['settings'];

        $type = self::TYPE_SMALL;
        if (in_array($new_instance["type"], self::get_types())) {
            $type = intval($new_instance["type"]);
        }
        $instance['type'] = $type;
        foreach ($this->getDefaults() as $key => $default) {
            $instance[$key] = $new_instance[$key] ?? $default;
        }

        // clear custom order cache
        Lotto_Helper::clear_cache("model_whitelabel.lotteriesbycustomorder");

        return $instance;
    }

    private function getDefaults(): array
    {
        return [
            'type' => self::TYPE_SMALL,
            'bg_large_text' => '',
            'bg_large_subtext' => '',
            'bg_large_disable_mobile' => 0,
            'backgroundColor' => self::LOTTOPARK_GREEN,
            'buttonBackgroundColor' => self::WHITE,
            'buttonBackgroundColorOnHover' => self::LOTTOPARK_GREEN,
            'buttonTextColor' => self::LOTTOPARK_GREEN,
            'buttonTextColorOnHover' => self::WHITE,
            'useCustomColors' => false,
        ];
    }

    private function getSettings($instance): array
    {
        $settings = isset($instance['settings']) ? $instance['settings'] : [];
        foreach ($this->getDefaults() as $key => $default) {
            $settings[$key] = $instance[$key] ?? $default;
        }

        return $settings;
    }

    private function getLanguages(): array
    {
        $whitelabelLanguageRepository = Container::get(WhitelabelLanguageRepository::class);
        $languages = $whitelabelLanguageRepository->getAll();

        return $languages;
    }
}

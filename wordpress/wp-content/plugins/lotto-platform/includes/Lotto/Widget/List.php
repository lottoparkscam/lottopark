<?php

use Helpers\UrlHelper;
use Helpers\WhitelabelHelper;
use Models\Whitelabel;
use Repositories\LotteryRepository;
use Services\Logs\FileLoggerService;

if (!defined('WPINC')) {
    die;
}

final class Lotto_Widget_List extends Lotto_Widget_Abstract_Widget implements Lotto_Widget_Interface_Title
{
    const TYPE_CAROUSEL = 1;
    const TYPE_GRID = 2;

    const COUNTDOWN_ALWAYS = 1;
    const COUNTDOWN_24HOURS = 2;

    const DISPLAY_TALL = 1;
    const DISPLAY_SHORT = 2;

    private FileLoggerService $fileLoggerService;

    public function __construct()
    {
        parent::__construct(
            'lotto_platform_widget_list', // Base ID
            _('Lotto List'), // Name
            [
                'description' => _('Displays the list of lotteries &bull; LIMIT: none')
            ] // Args
        );

        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }

    public function widget($args, $instance): void
    {
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        if (!empty($instance['onlyGGWorld'])) {
            try {
                /** @var LotteryRepository $lotteryRepository */
                $lotteryRepository = Container::get(LotteryRepository::class);
                $lotteries = $lotteryRepository->getGGWorldSelectedLotteries();
            } catch (Throwable $exception) {
                $this->fileLoggerService->error(
                    'Cannot display lottery banners on thank you for purchase page ' . 'Detailed message: ' . $exception->getMessage()
                );
                return;
            }
        } else {
            $lotteries = Model_Whitelabel::get_lotteries_by_highest_jackpot_for_whitelabel($whitelabel['id'], true);
        }

        $title = (!empty($instance['title'])) ? $instance['title'] : '';
        $count = (!empty($instance['count'])) ? $instance['count'] : null;
        if ($count === null || $count > count($lotteries) || $count <= 0) {
            $count = count($lotteries);
        }

        $count_type = (!empty($instance['countdown'])) ? $instance['countdown'] : Lotto_Widget_List::COUNTDOWN_ALWAYS;
        $display = (!empty($instance['display'])) ? $instance['display'] : Lotto_Widget_List::DISPLAY_TALL;
        $no_wrap_class = $display === Lotto_Widget_List::DISPLAY_TALL ? 'no-wrap px-1' : 'no-wrap pr-1';

        // set title_container value, use div as default
        $title_container = $instance['title_container'] ?? self::TITLE_CONTAINER_H2;

        // based on title_container create opening and ending tag for title
        $title_start_tag = $title_container === self::TITLE_CONTAINER_DIV ?
            '<div class="widget-div-title ' . $no_wrap_class . '">' :
            '<h2 class="' . $no_wrap_class . '">';
        $title_end_tag = $title_container === self::TITLE_CONTAINER_DIV ? '</div>' : '</h2>';

        $title = apply_filters('widget_title', $title, $instance, $this->id_base);

        // In fact under that if there is no statement to execute
        if (!empty($title)) {
            // removed h2 from title
            //$title = $args['before_title'].$title.$args['after_title'];
        }

        Lotto_Helper::widget_before(false, $args);

        echo $args['before_widget'];

        if (empty($instance['type'])) {
            $instance['type'] = Lotto_Widget_List::TYPE_CAROUSEL;
        }

        $add = "";
        if ($instance['type'] == Lotto_Widget_List::TYPE_GRID) {
            $add = "-grid";
        }

        // those variables are used inside wordpress/wp-content/themes/base/widget/list/widget-grid.php
        $casinoUrl = UrlHelper::changeAbsoluteUrlToCasinoUrl(get_home_url(), true);
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $whitelabelHasCasinoBanner = $whitelabel->hasCasinoBanner();

        if (file_exists(get_stylesheet_directory() . '/widget/list/widget' . $add . '.php')) {
            include(get_stylesheet_directory() . '/widget/list/widget' . $add . '.php');
        } else {
            include(get_template_directory() . '/widget/list/widget' . $add . '.php');
        }

        echo $args['after_widget'];

        Lotto_Helper::widget_after(false, $args);
        // outputs the content of the widget
    }

    public function form($instance): void
    {
        $title = isset($instance['title']) ? htmlspecialchars($instance['title']) : '';
        $count = isset($instance['count']) ? htmlspecialchars($instance['count']) : '';
        $countdown = isset($instance['countdown']) ? htmlspecialchars($instance['countdown']) : '';
        $display = isset($instance['display']) ? htmlspecialchars($instance['display']) : '';
        $title_container = $this->form_option($instance, 'title_container');
        include(LOTTO_PLUGIN_DIR . 'views/widget/list/settings.php');
    }

    // Update widget settings, triggered by setting form (publish)?
    public function update($new_instance, $old_instance): array
    {
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field($new_instance['title']);

        if (isset($instance['count'])) {
            unset($instance['count']);
        }

        if (!empty($new_instance['count'])) {
            $instance['count'] = intval($new_instance['count']);
            $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
            $lotteries = Model_Lottery::get_all_lotteries_for_whitelabel($whitelabel);

            if ($instance['count'] <= 0 || $instance['count'] > count($lotteries)) {
                unset($instance['count']);
            }
        }

        $types_tab = [
            Lotto_Widget_List::TYPE_CAROUSEL,
            Lotto_Widget_List::TYPE_GRID
        ];
        $countdown_tab = [
            Lotto_Widget_List::COUNTDOWN_ALWAYS,
            Lotto_Widget_List::COUNTDOWN_24HOURS
        ];
        $display_tab = [
            Lotto_Widget_List::DISPLAY_TALL,
            Lotto_Widget_List::DISPLAY_SHORT
        ];

        $instance['type'] = Lotto_Widget_List::TYPE_CAROUSEL;
        if (in_array($new_instance['type'], $types_tab)) {
            $instance['type'] = intval($new_instance['type']);
        }

        $instance['countdown'] = Lotto_Widget_List::COUNTDOWN_ALWAYS;
        if (in_array($new_instance['countdown'], $countdown_tab)) {
            $instance['countdown'] = intval($new_instance['countdown']);
        }

        $instance['display'] = Lotto_Widget_List::DISPLAY_TALL;
        if (in_array($new_instance['display'], $display_tab)) {
            $instance['display'] = intval($new_instance['display']);
        }

        // update widget title_container setting, if option doesn't exist use div as default
        $this->update_option(
            $instance,
            $new_instance,
            [self::TITLE_CONTAINER_H2, self::TITLE_CONTAINER_DIV],
            'title_container'
        );

        return $instance;
    }
}

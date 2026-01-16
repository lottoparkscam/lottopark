<?php

if (!defined('WPINC')) {
    die;
}

class Lotto_Widget_Small_Winners extends WP_Widget
{
    const TYPE_NORMAL = 1;
    const TYPE_COMPACT = 2;

    const TARGET_INFORMATION = 1;
    const TARGET_PLAY = 2;

    public function __construct()
    {
        parent::__construct(
            'lotto_platform_widget_small_winners', // Base ID
            _('Lotto Small Winners'), // Name
            [
                'description' => _("Display small last winners widget &bull; LIMIT: none")
            ] // Args
        );

        if (is_active_widget(false, false, $this->id_base, true)) {
            add_action('init', [$this, 'init']);
            add_action("save_post", [$this, "metabox_winner_save"], 10, 3);
            add_action('admin_head-edit.php', [$this, 'change_admin_title']);
        }
    }

    public function init(): void
    {
        $args = [
            'public' => true,
            'publicly_queryable' => false,
            'rewrite' => false,
            'label' => _('Small Winners'),
            'menu_position' => 20,
            'menu_icon' => 'dashicons-admin-page',
            'supports' => ['thumbnail', 'page-attributes'],
            'register_meta_box_cb' => [$this, 'metabox_data'],
            'show_in_nav_menus' => false
        ];
        register_post_type('winners', $args);
    }

    public function change_admin_title(): void
    {
        global $post_type;
        $slug = "winners";
        if ($slug != $post_type) {
            return;
        }
        add_filter('the_title', [$this, 'filter_admin_title'], 100, 2);
    }

    public function filter_admin_title($title, $id): string
    {
        $meta = get_post_meta($id, "winners-data", false);

        $result = '';
        if (isset($meta[0]) && !empty($meta[0]['name'])) {
            $result = $meta[0]['name'];
        }

        return $result;
    }

    /** 
     * @param array $post 
     * return non strict
     */
    public function metabox_data($post)
    {
        add_meta_box(
            "lotto_platform_small_winners_meta_box",
            "Winner data",
            [
                $this,
                "metabox_winner"
            ],
            "winners",
            "side",
            "high",
            null
        );
    }

    /**
     * @param boolean $update This is unused and I don't really know what is type of that variable
     */
    public function metabox_winner_save($post_id, $post, $update)
    {
        if (
            !isset($_POST["lotto_platform_small_winners_meta_box"]) ||
            !wp_verify_nonce($_POST["lotto_platform_small_winners_meta_box"], basename(__FILE__))
        ) {
            return $post_id;
        }

        /* Get the post type object. */
        $post_type = get_post_type_object($post->post_type);

        /* Check if the current user has permission to edit the post. */
        if (!current_user_can($post_type->cap->edit_post, $post_id)) {
            return $post_id;
        }

        if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
            return $post_id;
        }

        $slug = "winners";
        if ($slug != $post->post_type) {
            return $post_id;
        }

        $countries = Lotto_Helper::get_localized_country_list(ICL_LANGUAGE_CODE);

        $lottery = '';
        if (isset($_POST['winners']['lottery'])) {
            $lottery = intval($_POST['winners']['lottery']);
        }

        $name = '';
        if (isset($_POST['winners']['name'])) {
            $name = sanitize_text_field($_POST['winners']['name']);
        }

        $country = '';
        if (
            isset($_POST['winners']['country']) &&
            in_array($_POST['winners']['country'], array_keys($countries))
        ) {
            $country = $_POST['winners']['country'];
        }

        $amount = '';
        if (
            isset($_POST['winners']['amount']) &&
            is_numeric($_POST['winners']['amount'])
        ) {
            $amount = floatval($_POST['winners']['amount']);
        }

        update_post_meta(
            $post_id,
            "winners-data",
            [
                "lottery" => $lottery,
                "name" => $name,
                "country" => $country,
                "amount" => $amount
            ]
        );
    }

    public function metabox_winner($post): void
    {
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $lotteries = Model_Lottery::get_all_lotteries_for_whitelabel($whitelabel);
        $countries = Lotto_Helper::get_localized_country_list(ICL_LANGUAGE_CODE);
        $data = get_post_meta($post->ID, "winners-data", false);

        wp_nonce_field(basename(__FILE__), "lotto_platform_small_winners_meta_box");

        include(LOTTO_PLUGIN_DIR . 'views/widget/small/winners/metabox.php');
    }

    public function widget($args, $instance): void
    {
        global $post;

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $lotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel($whitelabel);

        $title = (!empty($instance['title'])) ? $instance['title'] : '';
        $width = (!empty($instance['width'])) ? $instance['width'] : '50';
        $amount = (!empty($instance['amount'])) ? $instance['amount'] : '3';
        $type = (!empty($instance['type'])) ? $instance['type'] : self::TYPE_NORMAL;
        $title = apply_filters('widget_title', $title, $instance, $this->id_base);
        $slug = (isset($instance['target']) && ($instance['target'] == self::TARGET_PLAY)) ? 'play' : 'lotteries';

        $query = [
            'post_type' => 'winners',
            'posts_per_page' => $amount
        ];

        $settings = (!empty($instance['settings'])) ? $instance['settings'] : null;

        if (
            !empty($instance['settings']['order']) &&
            $instance['settings']['order'] == "1"
        ) {
            $query['orderby'] = 'rand';
        }

        $winners = new WP_Query($query);
        $currencies = Lotto_Settings::getInstance()->get("currencies");
        $countries = Lotto_Helper::get_localized_country_list();

        Lotto_Helper::widget_before(true, $args);

        list(
            $width,
            $margin_left,
            $margin_right,
            $full
        ) = (Lotto_Helper::calculate_widget_width($width, $args));

        $style_text = ' style="width: ' . $width .
            '; margin-left: ' . $margin_left .
            '; margin-right: ' . $margin_right . ';">';

        echo str_replace('>', $style_text, $args['before_widget']);

        if (file_exists(get_stylesheet_directory() . '/widget/small/winners/widget.php')) {
            include(get_stylesheet_directory() . '/widget/small/winners/widget.php');
        } else {
            include(get_template_directory() . '/widget/small/winners/widget.php');
        }

        echo $args['after_widget'];

        if ($full) {
            echo '<div class="clearfix"></div>';
        }

        Lotto_Helper::widget_after(true, $args);
        wp_reset_postdata();
    }

    public function form($instance)
    {
        $title = isset($instance['title']) ? htmlspecialchars($instance['title']) : '';
        $settings = isset($instance['settings']) ? $instance['settings'] : '';
        $width = isset($instance['width']) ? $instance['width'] : '';
        $amount = isset($instance['amount']) ? $instance['amount'] : '';
        $type = isset($instance['type']) ? $instance['type'] : '';
        $target = isset($instance['target']) ? $instance['target'] : '';

        include(LOTTO_PLUGIN_DIR . 'views/widget/small/winners/settings.php');
    }

    public function update($new_instance, $old_instance): array
    {
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['settings']['order'] = intval($new_instance['settings']['order']);
        $instance['width'] = intval($new_instance['width']);

        if ($instance['width'] > 100) {
            $instance['width'] = 100;
        }
        if ($instance['width'] <= 0) {
            $instance['width'] = 50;
        }

        $instance['amount'] = intval($new_instance['amount']);
        if ($instance['amount'] <= 0) {
            $instance['amount'] = 3;
        }
        if ($instance['amount'] >= 10) {
            $instance['amount'] = 10;
        }

        $types_table = [
            self::TYPE_NORMAL,
            self::TYPE_COMPACT
        ];
        $instance['type'] = self::TYPE_NORMAL;

        if (in_array($new_instance['type'], $types_table)) {
            $instance['type'] = intval($new_instance['type']);
        }

        $targets_table = [
            self::TARGET_INFORMATION,
            self::TARGET_PLAY
        ];

        $instance['target'] = self::TARGET_INFORMATION;
        if (in_array($new_instance['target'], $targets_table)) {
            $instance['target'] = intval($new_instance['target']);
        }

        return $instance;
    }
}

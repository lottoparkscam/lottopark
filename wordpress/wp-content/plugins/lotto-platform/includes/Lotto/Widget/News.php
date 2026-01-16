<?php

if (!defined('WPINC')) {
    die;
}

class Lotto_Widget_News extends WP_Widget
{
    public const TWO_COLUMNS = 1;
    public const THREE_COLUMNS = 2;
    public const CASINO_NEWS_CATEGORY_SLUG = 'casino-news';

    public function __construct()
    {
        parent::__construct(
            'lotto_platform_widget_news', // Base ID
            _('Lotto News'), // Name
            array(
                'description' => _('Displays recent posts (news) &bull; LIMIT: none')
            ) // Args
        );
    }

    /**
     *
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $title = apply_filters('widget_title', $title, $instance, $this->id_base);

        $number = !empty($instance['number']) ? absint($instance['number']) : 2;

        if (!$number) {
            $number = 2;
        }

        $translatedCategoryId = 0;
        $casinoNewsCategory = get_term_by('slug', Lotto_Widget_News::CASINO_NEWS_CATEGORY_SLUG, 'category');
        if (!empty($casinoNewsCategory->term_id)) {
            $translatedCategoryId = apply_filters('wpml_object_id', $casinoNewsCategory->term_id, 'category');
        }
        $queryArguments = [
            'posts_per_page' => $number,
            'no_found_rows'       => true,
            'post_status'         => 'publish',
            'ignore_sticky_posts' => true,
        ];

        if (IS_CASINO) {
            $queryArguments['category_name'] = Lotto_Widget_News::CASINO_NEWS_CATEGORY_SLUG;
        } else {
            $queryArguments['category__not_in'] = [$translatedCategoryId];
        }
        $news_data = new WP_Query($queryArguments);

        $columns = !empty($instance['columns']) ? $instance['columns'] : Lotto_Widget_News::TWO_COLUMNS;
        $news_title = !empty($title) ? $title : '';
        
        Lotto_Helper::widget_before(false, $args);
        echo $args['before_widget'];

        if (file_exists(get_stylesheet_directory() . '/widget/news/widget.php')) {
            include(get_stylesheet_directory() . '/widget/news/widget.php');
        } else {
            include(get_template_directory() . '/widget/news/widget.php');
        }

        echo $args['after_widget'];
        Lotto_Helper::widget_after(false, $args);

        wp_reset_postdata();
    }

    public function form($instance)
    {
        $title = isset($instance['title']) ? htmlspecialchars($instance['title']) : '';
        $number = isset($instance['number']) ? absint($instance['number']) : 2;
        $columns = isset($instance['columns']) ? $instance['columns'] : '';

        include(LOTTO_PLUGIN_DIR . 'views/widget/news/settings.php');
    }

    public function update($new_instance, $old_instance): array
    {
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['number'] = (int) $new_instance['number'];

        $columns_tab = array(Lotto_Widget_News::TWO_COLUMNS, Lotto_Widget_News::THREE_COLUMNS);
        $instance['columns'] = Lotto_Widget_News::TWO_COLUMNS;

        if (in_array($new_instance['columns'], $columns_tab)) {
            $instance['columns'] =  $new_instance['columns'];
        }

        return $instance;
    }
}

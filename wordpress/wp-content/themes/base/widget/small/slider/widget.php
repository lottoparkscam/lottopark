<?php
if (!defined('WPINC')) {
    die;
}

?>
<div class="small-widget small-widget-slider">
    <div class="small-widget-title"><?= empty($title) ? _("How to play and win?") : $title; ?></div>
    <?php
        if ($slider->have_posts()):
    ?>
            <div class="small-widget-content">
                <div class="small-widget-slider-pager">
                    <div class="small-widget-slider-pager-numbers">
                        <?php
                            for ($i = 1; $i <= $slider->post_count; $i++):
                        ?>
                                <div class="small-widget-slider-page <?php if ($i == 1): echo 'small-widget-slider-page-active'; endif; ?>"><?php echo $i; ?></div>
                                <?php
                                    if ($i != $slider->post_count):
                                ?>
                                        <div class="small-widget-slider-pager-line" 
                                             style="width: calc(<?= round(100/($slider->post_count-1), 2); ?>% - <?= round((Lotto_Settings::getInstance()->get("ballwidth")*$slider->post_count)/($slider->post_count-1), 2); ?>px);">
                                            <div class="small-widget-slider-pager-line-fill"></div>
                                        </div>
                            <?php
                                    endif;
                            endfor;
                        ?>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="small-widget-slider-content">
                    <?php
                        $i = 0;
                        while ($slider->have_posts()):
                            $i++;

                            $slider->the_post();
                    ?>
                            <div class="small-widget-slider-content-item<?php if ($i != 1): echo ' hidden-normal'; endif; ?>">
                                <div class="small-widget-slider-header"><?php the_title(); ?></div>
                                <?php the_content(); ?>
                            </div>
                    <?php
                        endwhile;
                    ?>
                </div>
                <?php
                    $cats = get_categories(array('taxonomy' => 'faq-category'));
                ?>
                <a href="<?= (!empty($cats) && count($cats) > 0) ? get_term_link($cats[0]->term_id) : lotto_platform_get_permalink_by_slug('faq'); ?>" 
                   class="btn btn-md btn-primary"><?= _("Learn more") ?></a>
            </div>
    <?php
        else:
    ?>
            <div class="small-widget-no-info"><?= _("No slides.") ?></div>
    <?php
        endif;
    ?>
</div>

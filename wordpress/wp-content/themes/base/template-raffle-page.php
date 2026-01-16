<?php /* Template Name: Raffle Page */ ?>
<?php

use Helpers\UrlHelper;

if (!defined('WPINC')) {
    die;
}

get_header();

get_template_part('content', 'login-register-box-mobile');

$page_posts = apply_filters('wpml_object_id', get_option('page_for_posts'), 'page', false);

list(
    $social_share_rows,
    $counter_socials,
    $current_url
) = Helpers_General::get_prepared_social_share_links();

$widget_main_area_classes = Lotto_Helper::get_widget_main_area_classes(
    null,
    'play-more-sidebar-id'
);

$whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

$lotteries = Model_Raffle::getActiveRaffleForWhitelabel($whitelabel['id']);
$lotteries = $lotteries['__by_slug'];
$count = count($lotteries);

?>
<div class="content-area page-custom-raffle <?= $widget_main_area_classes; ?>">

    <?php get_active_sidebar('page-raffle-sidebar-top-id');?>

    <div class="main-width">
        <div class="widget-list-content-grid widget-list-type2 widget-list-display2">
            
            <?php if (!empty($lotteries) && count($lotteries) > 0):?>
                <?php get_template_part('partials/search-lottery');?>
                <div class="widget-list-grid">
                    <ul>
                        <?php
                        $lottery_number = 0;
                        foreach ($lotteries as $lottery):
                            $lottery_number++;

                            if (isset($count) && $lottery_number > $count) {
                                break;
                            }

                            $lottery_image = Lotto_View::get_lottery_image($lottery['id'], null, 'raffle');
                                
                            $lottery_image_path = Lotto_View::get_lottery_image_path($lottery['id'], null, 'raffle');
                                
                            $image_size = null;
                            if (!empty($lottery_image_path)) {
                                $image_size_check = getimagesize($lottery_image_path);
                                if ($image_size_check !== false) {
                                    $image_size = $image_size_check;
                                }
                            }
                            
                            $ticket_last_class = '';
                            if ($lottery_number == $count) {
                                $ticket_last_class = ' widget-list-ticket-last';
                            }

                            if ($lottery['is_sell_enabled'] == 0 || $lottery['is_sell_limitation_enabled'] == 1) {
                                continue;
                            }
                            ?>

                            <li class="widget-list-ticket <?= $ticket_last_class; ?>" data-lottery-slug="<?= $lottery['slug'] ?>">
                                
                                <div class="widget-list-ticket-image">
                                    <?php if (!empty($image_size)):?>
                                        <img width="<?= $image_size[0]; ?>" 
                                            height="<?= $image_size[1]; ?>" 
                                            src="<?= UrlHelper::esc_url($lottery_image); ?>" 
                                            alt="<?= htmlspecialchars(_($lottery['name'])); ?>">
                                    <?php endif;?>
                                </div>

                                <h2 class="no-wrap pr-1">
                                    <a class="widget-list-link" href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('play-raffle/' . $lottery['slug'])); ?>"><?= Security::htmlentities(_($lottery['name'])); ?></a>
                                </h2>

                                <div class="widget-list-hamount widget-list-hamount-small jackpot-to-update-raffle-<?= $lottery['slug'] ?>">
                                    <span class="loading"></span>
                                    <!-- This part will be generated automatically by JS -->
                                </div>
                                <div class="widget-remain-tickets-raffle-<?= $lottery['slug'] ?>" >
                                    <!-- This part will be generated automatically by JS -->
                                </div>
                                <div class="widget-list-button-container pl-0">
                                    <button href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('play-raffle/' . $lottery['slug'])); ?>" class="btn btn-primary widget-list-button"><?= _("Play now") ?></button>
                                </div>

                            </li>
                        <?php endforeach;?>
                    </ul>
                    <div class="clearfix"></div>
                </div>
            <?php else:?>
                <div class="widget-list-nolottery text-center">
                    <?= _('No active lotteries.') ?>
                </div>
            <?php endif;?>
        </div>
    </div>

    <?php if (!empty(get_the_content())):?>
        <div class="main-width content-width">
		    <div class="content-box <?= Lotto_Helper::get_widget_bottom_area_classes("play-more-sidebar-id"); ?>">
                <section class="page-content">
                    <article class="page">
                        <h1><?php the_title(); ?></h1>
                        <?php
                            the_content();

                            base_theme_social_share_bottom(
                                $social_share_rows,
                                $counter_socials,
                                $current_url
                            );
                        ?>
                    </article>
                </section>
		    </div>
        </div>
    <?php endif;?>
    
    <?php get_active_sidebar('play-more-sidebar-id');?>
</div>

<?php get_footer();?>

<?php /* Template Name: Promotions */ ?>
<?php

if (!defined('WPINC')) {
    die;
}

use Helpers\UrlHelper;

$homePageUrl = UrlHelper::getHomeUrlWithoutLanguage();
$linkLeft = UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('play'));
$linkCenter = UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('signup'));
$linkRight = UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('account') . 'promote');

get_header();
?>
<div class="content-area promotions-page-content">
    <div class="main-width content-width">
        <div class="content-box">
            <section class="page-content">
                <article class="page">
                    <h1><?php the_title(); ?></h1>
                    <div class="row">
                        <div class="text-center col">
                            <div>
                                <div class="ball-with-icon">
                                    <i class="fa fa-ticket" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="h2-container">
                                <h2><?= _('Multi-Draw') ?></h2>
                            </div>
                            <p><?= _('The more draws you include in your entry, the higher your discount will be! Play 5, 10, 25 or 52 consecutive draws with your lucky numbers to save up to 25%!') ?></p>
                            <a class="btn-primary btn-lg btn" href="<?= $linkLeft ?>"><?= _('Play now') ?></a>
                        </div>
                            <div class="text-center col">
                                <div>
                                    <div class="ball-with-icon">
                                        <i class="fa fa-dollar" aria-hidden="true"></i>
                                    </div>
                                </div>
                                <div class="h2-container">
                                    <h2><?= _('Free Ticket') ?></h2>
                                </div>
                                <p><?= _('Register and get a free ticket.') ?></p>
                                <a class="btn-primary btn-lg btn" href="<?= $linkCenter ?>"><?= _('Get a Free ticket') ?></a>
                            </div>
                        <div class="text-center col">
                            <div>
                                <div class="ball-with-icon">
                                    <i class="fa fa-user-plus" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="h2-container">
                                <h2><?= _('Promote and earn') ?></h2>
                            </div>
                            <p><?= _('Promote our services and earn! Simply share your reflink on social media, blog, or any other place and receive a comission for each referred player.') ?></p>
                            <a class="btn-primary btn-lg btn" href="<?= $linkRight ?>"><?= _('Get your referral link now') ?></a>
                        </div>
                    </div>
                </article>
            </section>
        </div>
    </div>
</div>
<?php get_footer(); ?>

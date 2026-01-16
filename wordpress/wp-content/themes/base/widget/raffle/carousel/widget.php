<?php

use Fuel\Core\Security;
use Helpers\UrlHelper;

if (!defined('WPINC')) {
    die;
}

$showitems = 3;
?>
<div class="main-width">
    <div class="widget-list-content widget-list-type2 widget-list-display2 widget-raffle-carousel">
        <?php if (!empty($lotteries) && count($lotteries) > 0):?>

            <?php if (count($lotteries) > $showitems):?>
                <div class="widget-list-carousel-prev">
                    <a href="#" aria-label="<?= _('Previous') ?>">
                        <span class="fa fa-angle-left" aria-hidden="true"></span>
                    </a>
                </div>
            <?php endif;?>

            <div class="widget-list-carousel" data-showitems="<?= $showitems; ?>">
                <ul>
                    <?php
                    $lottery_number = 0;
                    foreach ($lotteries as $lottery):
                        $lottery_number++;

                        if (isset($count) && $lottery_number > $count):
                            break;
                        endif;

                        $lottery_image = Lotto_View::get_lottery_image($lottery['id'], null, 'raffle');
                        $lottery_image_path = Lotto_View::get_lottery_image_path($lottery['id'], null, 'raffle');

                        $image_size = null;
                        if (!empty($lottery_image_path)) {
                            $image_size_check = getimagesize($lottery_image_path);
                            if ($image_size_check !== false) {
                                $image_size = $image_size_check;
                            }
                        }

                        if ($lottery['is_sell_enabled'] == 0 || $lottery['is_sell_limitation_enabled'] == 1) {
                            continue;
                        }
                        ?>
                        <li class="widget-list-ticket" data-lottery-slug="<?= $lottery['slug'] ?>">

                            <div class="widget-list-ticket-image">
                                <?php if (!empty($image_size)):?>
                                    <img width="<?= $image_size[0]; ?>" 
                                            height="<?= $image_size[1]; ?>" 
                                            src="<?= UrlHelper::esc_url($lottery_image); ?>" 
                                            alt="<?= htmlspecialchars(_($lottery['name'])); ?>">
                                <?php endif;?>
                            </div>

                            <h2 class="no-wrap pr-1">
                                <a class="widget-list-link" href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('play-raffle/' . $lottery['slug'])); ?>">
                                    <?= Security::htmlentities(_($lottery['name'])); ?>
                                </a>
                            </h2>

                            <div class="widget-list-hamount jackpot-to-update-raffle-<?= $lottery['slug'] ?>">
                                <span class="loading"></span>
                                <!-- This part will be generated automatically by JS -->
                            </div>

                            <div class="widget-list-button-container pl-0">
                                <button class="btn btn-primary widget-list-button"><?= _("Play now") ?></button>
                            </div>

                        </li>
                    <?php endforeach;?>

                </ul>

                <div class="clearfix"></div>

                <div class="mobile-only">
                    <a class="btn btn-secondary" href="<?php echo UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('raffle')); ?>"><?= _("See more") ?></a>
                </div>

            </div>

            <div class="clearfix"></div>

            <?php if ($count > $showitems):?>
                <div class="widget-list-carousel-next">
                    <a href="#" aria-label="<?= _('Next') ?>">
                        <span class="fa fa-angle-right" aria-hidden="true"></span>
                    </a>
                </div>
            <?php endif;?>

        <?php else:?>
            <div class="widget-list-nolottery text-center"><?= _('No active lotteries.') ?></div>
        <?php endif;?>

    </div>
</div>

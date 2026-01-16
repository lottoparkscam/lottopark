<?php

use Helpers\MiniGamesHelper;
use Helpers\UrlHelper;
use Repositories\MiniGameRepository;

if (!defined('WPINC')) {
    die;
}

$miniGameRepository = Container::get(MiniGameRepository::class);
$miniGames = $miniGameRepository->getAllEnabledGamesBasicInfoById();

get_header();

get_template_part('content', 'login-register-box-mobile');
?>
<div class="content-area">
    <?php get_active_sidebar('play-lottery-sidebar-id');?>

    <?php if (!empty($miniGames) && count($miniGames) > 0):?>
        <div class="main-width minigames-container">
            <div class="minigames-row">
                <div class="minigames-col">
                    <?php get_template_part('partials/search-lottery');?>
                </div>
            </div>
            <div class="minigames-row">

                <?php foreach ($miniGames as $miniGame):?>
                    <div class="minigames-item">
                        <div class="minigames-card">
                            <div class="minigames-card-body">
                                <img class="minigames-img" width="90" height="95" src="<?= MiniGamesHelper::getBallImageSrc($miniGame['slug']) ?>" alt="<?= _($miniGame['name']) ?>">
                                <div class="minigames-info">
                                    <h2 class="minigames-title">
                                        <a class="widget-list-link" href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('play/' . $miniGame['slug']));?>">
                                            <div><?= _('GG World') ?></div>
                                            <div class="minigames-title-name"><?php echo str_replace('GG World ', '', $miniGame['name']);?></div>
                                        </a>
                                    </h2>
                                </div>
                            </div>
                            <div class="minigames-card-footer">
                                <a class="btn btn-primary" href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('play/' . $miniGame['slug']));?>"><?= _('Play now') ?></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach;?>

            </div>
        </div>
    <?php endif;?>

    <div class="main-width content-width">
        <div class="content-box">
            <section class="page-content">
                <article class="page">
                    <h1><?php the_title(); ?></h1>
                    <?php the_content(); ?>
                </article>
            </section>
        </div>
    </div>

</div>
<?php get_footer(); ?>

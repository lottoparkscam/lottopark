<?php

use Helpers\UrlHelper;

if (!defined('WPINC')) {
    die;
}

get_header();

get_template_part('content', 'login-register-box-mobile');
?>

<div class="content-area">
    <div class="main-width">
        <div class="content-box">
            <section class="page-content">
                <article class="page page-not-found">
                    <h1 class="text-center">
                        <?php echo Security::htmlentities(_("Not found")); ?>
                    </h1>
                    <p>
                        <?php echo sprintf(wp_kses(_('Oops, the page you\'re looking for doesn\'t exist.<br>Go back to <a href="%s">home page</a> or try your luck and <a href="%s">play the lottery</a>.'), array('a' => array('href' => array()), 'br' => array())), UrlHelper::esc_url(lotto_platform_home_url()), UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('play'))); ?>
                    </p>
                    <div class="header-notfound">
                        <img src="<?php echo UrlHelper::esc_url(get_template_directory_uri().'/images/404.png'); ?>" 
                             alt="<?php echo Security::htmlentities(_("404 - Not Found")); ?>">
                    </div>
                </article>
            </section>
        </div>
    </div>
</div>
<?php
get_footer();

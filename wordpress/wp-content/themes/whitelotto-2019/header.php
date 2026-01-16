<?php
if (!defined('WPINC')) {
    die;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>;charset=<?php bloginfo('charset'); ?>">
    <link href="<?php echo get_template_directory_uri(); ?>/css/bootstrap.min.css" type="text/css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Varela+Round&display=swap&subset=latin-ext" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@latest/dist/css/splide.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <?php wp_head(); ?>
    <?php if (\Fuel::$env == \Fuel::PRODUCTION): ?>

        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-KB1G48E0FB"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', 'G-KB1G48E0FB');
        </script>

        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-43083677-11"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }

            gtag('js', new Date());

            gtag('config', 'UA-43083677-11');
        </script>
    <?php endif; ?>
</head>
<body <?php body_class(); ?> data-page="<?php echo (!empty($args['page'])) ? $args['page'] : '';?>">

<header>
    <div class="container-fluid top-bg">
        <div class="d-none" id="sticky-header">
            <header class="container">
                <div class="row align-items-center justify-content-between header-area">
                    <div class="logo d-flex align-items-center">
                        <a href="<?=lotto_platform_home_url()?>">
                            <img src="<?php echo get_template_directory_uri(); ?>/images/logo.png"
                                 title="" class="" id="logo" alt="logo">
                        </a>
                        <div>
                            <p>Creating Lottery White Label Software since 2009</p>
                        </div>
                    </div>
                    <div class="navigation row align-items-center">
                        <nav class="navbar navbar-default navbar-dark navbar-expand-xl menu" id="nav-menu" role="navigation">
                        <?php
                        wp_nav_menu([
                            'theme_location' => 'primary',
                            'depth' => 2,
                            'container' => 'div',
                            'container_class' => 'collapse navbar-collapse justify-content-end  no-transition',
                            'container_id' => 'header-menu',
                            'menu_class' => 'nav navbar navbar-nav',
                            'fallback_cb' => 'WP_Bootstrap_Navwalker::fallback',
                            'walker' => new bs4navwalker()
                        ]);
                        ?>
                            <div class="d-none d-xl-flex justify-content-end">
                                <div class="contact row bg-transparent">
                                    <a href="https://whitelotto.com/contact/" class="contact-circle">
                                        <i class="fas fa-envelope"></i>
                                    </a>
                                    <a href="https://t.me/tnigg" target="_blank" class="contact-circle">
                                        <i class="fab fa-telegram"></i>
                                    </a>
                                    <a href="skype:live:8f92c955cbf8e9c3?call" class="contact-circle">
                                        <i class="fab fa-skype"></i>
                                    </a>
                                    <a href="https://wa.me/48570059652" target="_blank" class="contact-circle-whatsupp">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                </div>
                            </div>
                        </nav>
                    </div>
                </div>
            </header>
        </div>
        <div class="container m-xl-auto m-0">
            <div class="row justify-content-between top-menu">
                <div class="logo d-flex align-items-center">
                    <a href="<?=lotto_platform_home_url()?>">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/logo.png" alt="White Lotto">
                    </a>
                    <div>
                        <p>Creating Lottery White Label Software since 2009</p>
                    </div>
                </div>
                <div class="navigation row align-items-center">
                    <nav class="navbar navbar-default navbar-dark navbar-expand-xl menu" id="nav-menu" role="navigation">
                        <?php
                        /* <a href="https://whitelotto.com/contact" class="btn d-xl-none d-md-inline-block d-none demo-md">Request a demo</a>
                       */ ?>
                        <div class="d-xl-none justify-content-end toggle-wrapper">
                                <button class="navbar-toggler" type="button" data-toggle="collapse" id="menu-toggler"
                                        data-target="#header-menu">
                                    <span><i class="fas fa-bars"></i></span>
                                </button>
                        </div>
                        <?php
                        wp_nav_menu([
                            'theme_location' => 'primary',
                            'depth' => 2,
                            'container' => 'div',
                            'container_class' => 'collapse navbar-collapse justify-content-end  no-transition',
                            'container_id' => 'header-menu',
                            'menu_class' => 'nav navbar navbar-nav',
                            'fallback_cb' => 'WP_Bootstrap_Navwalker::fallback',
                            'walker' => new bs4navwalker()
                        ]);
                        ?>
                        <div class="d-none d-xl-flex justify-content-end">
                            <div class="contact row">
                                <a href="https://whitelotto.com/contact/" class="contact-circle">
                                    <i class="fas fa-envelope"></i>
                                </a>
                                <a href="https://t.me/tnigg" target="_blank" class="contact-circle">
                                    <i class="fab fa-telegram"></i>
                                </a>
                                <a href="skype:live:8f92c955cbf8e9c3?call" class="contact-circle">
                                    <i class="fab fa-skype"></i>
                                </a>
                                <a href="https://wa.me/48570059652" target="_blank" class="contact-circle-whatsupp">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
        <hr>

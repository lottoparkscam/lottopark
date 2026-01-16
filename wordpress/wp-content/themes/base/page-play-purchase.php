<?php
if (!defined('WPINC')) {
    die;
}

get_header();

get_template_part('content', 'login-register-box-mobile');
get_template_part('content', 'raffle-purchase');

get_footer();

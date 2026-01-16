<?php

use Models\MiniGame;

if (!defined('WPINC')) {
    die;
}

get_header();

get_template_part('content', 'login-register-box-mobile');

if ($post->post_parent !== 0) {
    // we added Mini Games as a separate system, but we want to display it with /play/ in the URL
    if (in_array($post->post_name, MiniGame::GAMES_SLUG_LIST)) {
        get_template_part('content', $post->post_name . '-play');
    } else {
        get_template_part('content', 'play-lottery');
    }
} else {
    get_template_part('content', 'play');
}

get_footer();

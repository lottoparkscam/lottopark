<?php

if (!defined('WPINC')) {
    die;
}

get_header(); 

get_template_part('content', 'login-register-box-mobile');

if ($post->post_parent !== 0) {
    get_template_part('content', 'lotteries-lottery');
} else {
    get_template_part('content', 'lotteries');
}

get_footer(); 

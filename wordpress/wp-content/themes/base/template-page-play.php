<?php
/* Template Name: Page Play (All lotteries) */

get_header();

echo lotto_platform_messages(true, true);
    
get_template_part('content', 'login-register-box-mobile');

get_template_part('content', 'play');

get_footer();

?>
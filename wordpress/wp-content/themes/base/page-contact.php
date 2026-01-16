<?php

use Presenters\Wordpress\Base\Views\ContactPresenter;

get_header();
get_template_part('content', 'login-register-box-mobile');
$presenter = Container::get(ContactPresenter::class);
echo $presenter->view();

get_footer();

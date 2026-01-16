<?php

use Presenters\Wordpress\Base\Views\PageActivatedPresenter;

if (!defined('WPINC')) {
    die;
}

get_header();

/** @var PageActivatedPresenter $presenter */
$presenter = Container::get(PageActivatedPresenter::class);
echo $presenter->view();

get_footer();

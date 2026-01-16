<?php

use Helpers\AssetHelper;
use Presenters\Wordpress\Base\Account\PromotePresenter;

$jsPromotePath = AssetHelper::mix('js/promote.min.js', AssetHelper::TYPE_WORDPRESS);
wp_enqueue_script('raffle-scripts', $jsPromotePath, [], false, true);

$presenter = Container::get(PromotePresenter::class);
echo $presenter->view();

<?php

declare(strict_types=1);

use Helpers\AssetHelper;
use Presenters\Wordpress\Base\Slots\GameListPresenter;

wp_enqueue_script_slick_plugin();

/** @var GameListPresenter $gameListPresenter */
$gameListPresenter = Container::get(GameListPresenter::class);
echo $gameListPresenter->promoSliderElement(false);

$lottoSliderJsFileName = 'PromoSlider.min.js';
$lottoSliderJs = AssetHelper::mix('js/slots/' . $lottoSliderJsFileName, AssetHelper::TYPE_WORDPRESS, true);
wp_enqueue_script('promo-slider', $lottoSliderJs, ['jquery'], false, true);

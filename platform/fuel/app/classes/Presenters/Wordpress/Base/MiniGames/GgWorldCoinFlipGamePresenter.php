<?php

namespace Presenters\Wordpress\Base\MiniGames;

use Container;
use Helpers\AssetHelper;
use Models\MiniGame;
use Models\Whitelabel;
use Presenters\Wordpress\AbstractWordpressPresenter;
use Services\MiniGame\GgWorldCoinFlipGameService;

/**
 * Presenter for:
 * - template: /wordpress/wp-content/themes/base/template-ggworldcoinflip-play.php
 * - view: /wordpress/wp-content/themes/base/MiniGames/GgWorldCoinFlipGame.twig
 */
final class GgWorldCoinFlipGamePresenter extends AbstractWordpressPresenter
{
    private Whitelabel $whitelabel;

    public function __construct()
    {
        $this->whitelabel = Container::get('whitelabel');
    }

    public function view(): string
    {
        $ggWorldCoinFlipGameSlug = MiniGame::GG_WORLD_COINFLIP_SLUG;

        $gliCheckImg = AssetHelper::mix('images/widgets/gli/gli-check.png', AssetHelper::TYPE_WORDPRESS, true);
        $gliImg = AssetHelper::mix('images/widgets/gli/gli.jpg', AssetHelper::TYPE_WORDPRESS, true);
        $gameLogo = AssetHelper::mix("images/MiniGames/$ggWorldCoinFlipGameSlug/ball.png", AssetHelper::TYPE_WORDPRESS, true);
        $coinHeadsIcon = AssetHelper::mix("images/MiniGames/$ggWorldCoinFlipGameSlug/coinHeads.png", AssetHelper::TYPE_WORDPRESS, true);
        $coinTailsIcon = AssetHelper::mix("images/MiniGames/$ggWorldCoinFlipGameSlug/coinTails.png", AssetHelper::TYPE_WORDPRESS, true);
        $miniGameSlug = $ggWorldCoinFlipGameSlug;

        return $this->forge([
            'whitelabelDomain' => $this->whitelabel->domain,
            'gliCheckImg' => $gliCheckImg,
            'gliImg' => $gliImg,
            'gameLogo' => $gameLogo,
            'coinHeadsIcon' => $coinHeadsIcon,
            'coinTailsIcon' => $coinTailsIcon,
            'miniGameSlug' => $miniGameSlug,
        ]);
    }
}

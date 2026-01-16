<?php

namespace Presenters\Wordpress\Base\MiniGames;

use Container;
use Helpers\AssetHelper;
use Models\MiniGame;
use Models\Whitelabel;
use Presenters\Wordpress\AbstractWordpressPresenter;

/**
 * Presenter for:
 * - template: /wordpress/wp-content/themes/base/template-gg-world-santa-in-da-house-play.php
 * - view: /wordpress/wp-content/themes/base/MiniGames/GgWorldSantaInDaHouseView.twig
 */
final class GgWorldSantaInDaHouseGamePresenter extends AbstractWordpressPresenter
{
    private Whitelabel $whitelabel;

    public function __construct()
    {
        $this->whitelabel = Container::get('whitelabel');
    }

    public function view(): string
    {
        $ggWorldSantaInDaHouseSlug = MiniGame::GG_WORLD_SANTA_IN_DA_HOUSE_SLUG;

        $gliCheckImg = AssetHelper::mix('images/widgets/gli/gli-check.png', AssetHelper::TYPE_WORDPRESS, true);
        $gliImg = AssetHelper::mix('images/widgets/gli/gli.jpg', AssetHelper::TYPE_WORDPRESS, true);
        $gameLogoImg = AssetHelper::mix("images/MiniGames/$ggWorldSantaInDaHouseSlug/ball.png", AssetHelper::TYPE_WORDPRESS, true);
        $revealAllIcon = AssetHelper::mix("images/MiniGames/$ggWorldSantaInDaHouseSlug/reveal-all.svg", AssetHelper::TYPE_WORDPRESS, true);
        $giftImg = AssetHelper::mix("images/MiniGames/$ggWorldSantaInDaHouseSlug/gift-icon.png", AssetHelper::TYPE_WORDPRESS, true);
        $treeImg = AssetHelper::mix("images/MiniGames/$ggWorldSantaInDaHouseSlug/tree-icon.png", AssetHelper::TYPE_WORDPRESS, true);
        $happySantaImg = AssetHelper::mix("images/MiniGames/$ggWorldSantaInDaHouseSlug/santa-happy.png", AssetHelper::TYPE_WORDPRESS, true);
        $sadSantaImg = AssetHelper::mix("images/MiniGames/$ggWorldSantaInDaHouseSlug/santa-sad.png", AssetHelper::TYPE_WORDPRESS, true);
        $backgroundMusicFilePath = AssetHelper::mix("audio/MiniGames/$ggWorldSantaInDaHouseSlug/background.mp3", AssetHelper::TYPE_WORDPRESS, true);
        $clickSoundFilePath =  AssetHelper::mix("audio/MiniGames/$ggWorldSantaInDaHouseSlug/click.mp3", AssetHelper::TYPE_WORDPRESS, true);
        $wonSoundFilePath =  AssetHelper::mix("audio/MiniGames/$ggWorldSantaInDaHouseSlug/won.mp3", AssetHelper::TYPE_WORDPRESS, true);
        $lossSoundFilePath =  AssetHelper::mix("audio/MiniGames/$ggWorldSantaInDaHouseSlug/loss.mp3", AssetHelper::TYPE_WORDPRESS, true);
        $depositUrl = lotto_platform_get_permalink_by_slug('deposit');

        return $this->forge([
            'whitelabelDomain' => $this->whitelabel->domain,
            'gliCheckImg' => $gliCheckImg,
            'gliImg' => $gliImg,
            'miniGameSlug' => $ggWorldSantaInDaHouseSlug,
            'gameLogoImg' => $gameLogoImg,
            'revealAllIcon' => $revealAllIcon,
            'giftImg' => $giftImg,
            'treeImg' => $treeImg,
            'happySantaImg' => $happySantaImg,
            'sadSantaImg' => $sadSantaImg,
            'backgroundMusicFilePath' => $backgroundMusicFilePath,
            'clickSoundFilePath' => $clickSoundFilePath,
            'wonSoundFilePath' => $wonSoundFilePath,
            'lossSoundFilePath' => $lossSoundFilePath,
            'depositUrl' => $depositUrl,
        ]);
    }
}

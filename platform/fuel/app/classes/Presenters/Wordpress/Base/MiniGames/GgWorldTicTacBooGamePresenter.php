<?php

namespace Presenters\Wordpress\Base\MiniGames;

use Container;
use Helpers\AssetHelper;
use Helpers\UrlHelper;
use Models\MiniGame;
use Models\Whitelabel;
use Presenters\Wordpress\AbstractWordpressPresenter;

/**
 * Presenter for:
 * - template: /wordpress/wp-content/themes/base/template-gg-world-tic-tac-boo-play.php
 * - view: /wordpress/wp-content/themes/base/MiniGames/GgWorldTicTacBooView.twig
 */
final class GgWorldTicTacBooGamePresenter extends AbstractWordpressPresenter
{
    private Whitelabel $whitelabel;

    public function __construct()
    {
        $this->whitelabel = Container::get('whitelabel');
    }

    public function view(): string
    {
        $ggWorldTicTacBooGameSlug = MiniGame::GG_WORLD_TIC_TAC_BOO_SLUG;

        $gliCheckImg = AssetHelper::mix('images/widgets/gli/gli-check.png', AssetHelper::TYPE_WORDPRESS, true);
        $gliImg = AssetHelper::mix('images/widgets/gli/gli.jpg', AssetHelper::TYPE_WORDPRESS, true);
        $gameLogoImg = AssetHelper::mix("images/MiniGames/$ggWorldTicTacBooGameSlug/ball.png", AssetHelper::TYPE_WORDPRESS, true);
        $revealAllIcon = AssetHelper::mix("images/MiniGames/$ggWorldTicTacBooGameSlug/reveal-all.svg", AssetHelper::TYPE_WORDPRESS, true);
        $pumpkinImg = AssetHelper::mix("images/MiniGames/$ggWorldTicTacBooGameSlug/pumpkin-icon.png", AssetHelper::TYPE_WORDPRESS, true);
        $ghostImg = AssetHelper::mix("images/MiniGames/$ggWorldTicTacBooGameSlug/ghost-icon.png", AssetHelper::TYPE_WORDPRESS, true);
        $happyGhostImg = AssetHelper::mix("images/MiniGames/$ggWorldTicTacBooGameSlug/ghost-happy.png", AssetHelper::TYPE_WORDPRESS, true);
        $sadGhostImg = AssetHelper::mix("images/MiniGames/$ggWorldTicTacBooGameSlug/ghost-sad.png", AssetHelper::TYPE_WORDPRESS, true);
        $backgroundMusicFilePath = AssetHelper::mix("audio/MiniGames/$ggWorldTicTacBooGameSlug/background.mp3", AssetHelper::TYPE_WORDPRESS, true);
        $clickSoundFilePath =  AssetHelper::mix("audio/MiniGames/$ggWorldTicTacBooGameSlug/click.mp3", AssetHelper::TYPE_WORDPRESS, true);
        $wonSoundFilePath =  AssetHelper::mix("audio/MiniGames/$ggWorldTicTacBooGameSlug/won.mp3", AssetHelper::TYPE_WORDPRESS, true);
        $lossSoundFilePath =  AssetHelper::mix("audio/MiniGames/$ggWorldTicTacBooGameSlug/loss.mp3", AssetHelper::TYPE_WORDPRESS, true);
        $depositUrl = lotto_platform_get_permalink_by_slug('deposit');

        return $this->forge([
            'whitelabelDomain' => $this->whitelabel->domain,
            'gliCheckImg' => $gliCheckImg,
            'gliImg' => $gliImg,
            'miniGameSlug' => $ggWorldTicTacBooGameSlug,
            'gameLogoImg' => $gameLogoImg,
            'revealAllIcon' => $revealAllIcon,
            'pumpkinImg' => $pumpkinImg,
            'ghostImg' => $ghostImg,
            'happyGhostImg' => $happyGhostImg,
            'sadGhostImg' => $sadGhostImg,
            'backgroundMusicFilePath' => $backgroundMusicFilePath,
            'clickSoundFilePath' => $clickSoundFilePath,
            'wonSoundFilePath' => $wonSoundFilePath,
            'lossSoundFilePath' => $lossSoundFilePath,
            'depositUrl' => $depositUrl,
        ]);
    }
}

<?php

namespace Presenters\Wordpress\Base\MiniGames;

use Container;
use Helpers\AssetHelper;
use Models\MiniGame;
use Models\Whitelabel;
use Presenters\Wordpress\AbstractWordpressPresenter;

/**
 * Presenter for:
 * - template: /wordpress/wp-content/themes/base/template-ggworldredorblue-play.php
 * - view: /wordpress/wp-content/themes/base/MiniGames/GgWorldRedOrBlueGame.twig
 */
final class GgWorldRedOrBlueGamePresenter extends AbstractWordpressPresenter
{
    private Whitelabel $whitelabel;

    public function __construct()
    {
        $this->whitelabel = Container::get('whitelabel');
    }

    public function view(): string
    {
        $ggWorldRedOrBlueGameSlug = MiniGame::GG_WORLD_RED_OR_BLUE_SLUG;

        $gliCheckImg = AssetHelper::mix('images/widgets/gli/gli-check.png', AssetHelper::TYPE_WORDPRESS, true);
        $gliImg = AssetHelper::mix('images/widgets/gli/gli.jpg', AssetHelper::TYPE_WORDPRESS, true);
        $gameLogo = AssetHelper::mix("images/MiniGames/$ggWorldRedOrBlueGameSlug/ball.png", AssetHelper::TYPE_WORDPRESS, true);
        $redBoxIcon = AssetHelper::mix("images/MiniGames/$ggWorldRedOrBlueGameSlug/redBox.png", AssetHelper::TYPE_WORDPRESS, true);
        $blueBoxIcon = AssetHelper::mix("images/MiniGames/$ggWorldRedOrBlueGameSlug/blueBox.png", AssetHelper::TYPE_WORDPRESS, true);
        $backgroundMusicFilePath = AssetHelper::mix("audio/MiniGames/$ggWorldRedOrBlueGameSlug/background.mp3", AssetHelper::TYPE_WORDPRESS, true);
        $clickSoundFilePath = AssetHelper::mix("audio/MiniGames/$ggWorldRedOrBlueGameSlug/click.mp3", AssetHelper::TYPE_WORDPRESS, true);
        $wonSoundFilePath = AssetHelper::mix("audio/MiniGames/$ggWorldRedOrBlueGameSlug/won.wav", AssetHelper::TYPE_WORDPRESS, true);
        $lossSoundFilePath = AssetHelper::mix("audio/MiniGames/$ggWorldRedOrBlueGameSlug/loss.wav", AssetHelper::TYPE_WORDPRESS, true);
        $happyRedChar = AssetHelper::mix("images/MiniGames/$ggWorldRedOrBlueGameSlug/char-red-happy.png", AssetHelper::TYPE_WORDPRESS, true);
        $happyBlueChar = AssetHelper::mix("images/MiniGames/$ggWorldRedOrBlueGameSlug/char-blue-happy.png", AssetHelper::TYPE_WORDPRESS, true);
        $sadRedChar = AssetHelper::mix("images/MiniGames/$ggWorldRedOrBlueGameSlug/char-red-sad.png", AssetHelper::TYPE_WORDPRESS, true);
        $sadBlueChar = AssetHelper::mix("images/MiniGames/$ggWorldRedOrBlueGameSlug/char-blue-sad.png", AssetHelper::TYPE_WORDPRESS, true);
        $redChar = AssetHelper::mix("images/MiniGames/$ggWorldRedOrBlueGameSlug/red-char.png", AssetHelper::TYPE_WORDPRESS, true);
        $boxImage = AssetHelper::mix("images/MiniGames/$ggWorldRedOrBlueGameSlug/box.png", AssetHelper::TYPE_WORDPRESS, true);
        $depositUrl = lotto_platform_get_permalink_by_slug('deposit');

        return $this->forge([
            'whitelabelDomain' => $this->whitelabel->domain,
            'gliCheckImg' => $gliCheckImg,
            'gliImg' => $gliImg,
            'gameLogo' => $gameLogo,
            'redBoxIcon' => $redBoxIcon,
            'blueBoxIcon' => $blueBoxIcon,
            'miniGameSlug' => $ggWorldRedOrBlueGameSlug,
            'backgroundMusicFilePath' => $backgroundMusicFilePath,
            'clickSoundFilePath' => $clickSoundFilePath,
            'wonSoundFilePath' => $wonSoundFilePath,
            'lossSoundFilePath' => $lossSoundFilePath,
            'happyRedChar' => $happyRedChar,
            'happyBlueChar' => $happyBlueChar,
            'sadRedChar' => $sadRedChar,
            'sadBlueChar' => $sadBlueChar,
            'redChar' => $redChar,
            'boxImage' => $boxImage,
            'depositUrl' => $depositUrl,
        ]);
    }
}

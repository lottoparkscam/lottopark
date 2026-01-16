<?php

use Helpers\AssetHelper;
use Models\Whitelabel;

if (!defined('WPINC')) {
    die;
}
$lotto_platform_payment_success_box = "";

if (function_exists("lotto_platform_payment_success_box")):
    $lotto_platform_payment_success_box = lotto_platform_payment_success_box();
endif;

/** @var Whitelabel */
$whitelabel = Container::get('whitelabel');

get_header();
$transaction = Lotto_Settings::getInstance()->get('transaction');

$popup_message_accept = Session::get('popup_message_accept');
Session::delete('popup_message_accept');

?>
<div class="content-area">
    <div class="main-width content-width">
        <div class="content-box">
            <section class="page-content">
                <article class="page">
                    <h1 class="text-center">
                        <?php the_title(); ?>
                    </h1>
                    <?php
                        echo '<p class="text-center">' .
                            Security::htmlentities(_("Thank you! Your payment has been successfully processed."));

                        if ((int)$transaction->payment_method_type === Helpers_General::PAYMENT_TYPE_OTHER):
                            echo '<br>'.Security::htmlentities(_("Depending on the payment method, the payment's registration may take up to 60 minutes."));
                        endif;

                        echo "</p>";

                        the_content();
                    ?>
                </article>
                <?= $lotto_platform_payment_success_box ?>
                <?php Lotto_Helper::hook("page-success"); ?>
            </section>
        </div>
    </div>
    <?php

        // Temporarily disabled at the business team's request.
        $shouldDisplayGGTKN = $whitelabel->isV1() && $whitelabel->isNotTheme(Whitelabel::REDFOXLOTTO_THEME) && $whitelabel->isNotTheme(Whitelabel::LOTOKING_THEME);
        if(false && $shouldDisplayGGTKN){
            $cardImage = AssetHelper::getBaseImage('buyGgTokenWithCard.png');
            $pancakeImage = AssetHelper::getBaseImage('buyGgTokenWithPancakeSwap.png');

            echo <<<BUY_GG_TOKEN
                <div class="content-area">
                    <div class="main-width content-width">
                        <h2 class="text-center ggTokenHeader">Buy GG token and use it as a payment method to get up to 50% discount on {$whitelabel->name}</h2>
                        <div class="buyGgTokenContainer">
                            <div>
                                <a href="https://guardarian.com/buy-ggtkn-bep20" target="_blank">
                                    <img src="$cardImage" alt="Buy GG Token with Card"/>
                                </a>
                            </div>
                            <div>
                                <a href="https://changenow.io/buy/gg-token" target="_blank">
                                    <img src="$pancakeImage" alt="Buy GG Token with @PANCAKESWAP"/>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            BUY_GG_TOKEN;
        }

        if (!IS_CASINO) {
            $shouldDisplayGGWorldGames = $whitelabel->isNotTheme(Whitelabel::LOTOKING_THEME);

            the_widget(
                'Lotto_Widget_List',
                array(
                    'count' => 3,
                    'countdown' => 2,
                    'display' => 2,
                    'type' => 2,
                    'onlyGGWorld' => $shouldDisplayGGWorldGames,
                )
            );
        }
        
        if(IS_CASINO) {
            the_widget(
                'Lotto_Widget_CasinoSlider',
                [], [
                    'id' => 'frontpage-sidebar-id',
                    'widget_id' => 'lotto_platform_widget_slot_games_slider',
                ]
            );
        }
    ?>
</div>
<?php
    if (!empty($popup_message_accept)):
?>
<div id="popup_message_accept" class="hidden-normal"
     data-title="<?= Security::htmlentities($popup_message_accept['title']); ?>"
     data-content="<?= Security::htmlentities($popup_message_accept['content']); ?>"
     ></div>
<?php
    endif;
?>

<?php get_footer(); ?>

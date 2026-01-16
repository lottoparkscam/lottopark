<?php
/* Template Name: Page How to Buy GGTKN */

if (!defined('WPINC')) {
    die;
}

use Helpers\UrlHelper;

$homePageUrl = UrlHelper::getHomeUrlWithoutLanguage();
$whitelabelName = Container::get('whitelabel')->name;

get_header();

get_template_part('content', 'login-register-box-mobile');

// key is tag name in content, value is column name in database
$tags_to_replace_on_each_page = [
    'footer' => 'footer',
    'privacy' => 'privacy_policy',
    'terms' => 'terms_and_conditions',
    'country' => 'country'
];

$content = get_the_content();
$content = apply_filters( 'the_content', $content );
$content = str_replace( ']]>', ']]&gt;', $content );
$content = apply_filters('replace_wordpress_tags', $content, $tags_to_replace_on_each_page);

?>

<div class="content-area">
    <div class="main-width content-width">
        <div class="content-box">
            <section class="page-content">
                <article id="page-howtobuyggtkn" class="page">
                    <img class="img-ggtkn-logo" src="<?php echo get_template_directory_uri() . '/images/ggtkn/ggtkn-logo.png';?>" alt="GGTKN Logo">

                    <h1><?php printf(_("What are the reasons to pay with %sGG Token%s?"), '<span class="fw-bold">', '</span>'); ?></h1>

                    <p><?php printf(_("Enjoy the benefits of GG Token at %s. Simply using GG Token as the payment method at %s grants you a %s 50%s bonus money on each deposit! %s That's not all, more will be revealed soon through our Newsletter!"), $whitelabelName, $whitelabelName, '<span class="fw-bold">', '%', '</span>'); ?></p>

                    <div class="ggtkn-section-howtobuy">
                        <div class="col col-left">
                            <h2><?= _("How to buy and use GG Token?") ?></h2>
                            <ol class="ggtkn-list">
                                <li class="ggtkn-list-item">
                                    <span><?php printf(_("Visit %shttps://wallet.ggtkn.com%s and download the application for Android or iOS."), '<a href="https://wallet.ggtkn.com">', '</a>'); ?></span>
                                </li>
                                <li class="ggtkn-list-item">
                                    <span><?= _("Use the widget on the website to buy GGTKN using a card. That's it! Take note that you also receive a certain amount of BNB for free so you don't have to worry about transaction fees.") ?></span>
                                    <div class="ggtkn-img-box">
                                        <a href="https://wallet.ggtkn.com">
                                            <img class="img-ggtkn-wallet-mobile" src="<?php echo get_template_directory_uri() . '/images/ggtkn/ggtkn-wallet-mobile.png';?>" alt="Buy GGTKN">
                                        </a>
                                    </div>
                                </li>
                                <li class="ggtkn-list-item">
                                    <span><?php printf(_("Go back to %s, select GGTKN as the payment method, and use the \"GGTKN\" promo code."), "<a href=\"{$homePageUrl}\">{$whitelabelName}</a>"); ?></span>
                                </li>
                            </ol>
                        </div>
                        <div class="col col-right">
                            <img class="img-ggtkn-icon" src="<?php echo get_template_directory_uri() . '/images/ggtkn/ggtkn-icon.png';?>" alt="GGTKN Icon">
                            <a href="https://wallet.ggtkn.com">
                                <img class="img-ggtkn-wallet-pc" src="<?php echo get_template_directory_uri() . '/images/ggtkn/ggtkn-wallet-pc.png';?>" alt="Buy GGTKN">
                            </a>
                        </div>
                    </div>

                    <h2><?= _("Supported wallets") ?></h2>
                    <p><?= _("GGTKN is Metamask and TrustWallet verified. You can also use those wallets for GGTKN transactions.") ?></p>
                    
                    <ul class="ggtkn-grid-logo">
                        <li>
                            <a href="https://metamask.io">
                                <img src="<?php echo get_template_directory_uri() . '/images/ggtkn/logo-metamask.png';?>" alt="MetaMask">
                            </a>
                        </li>
                        <li>
                            <a href="https://trustwallet.com">
                                <img src="<?php echo get_template_directory_uri() . '/images/ggtkn/logo-trustwallet.png';?>" alt="Trust Wallet">
                            </a>
                        </li>
                    </ul>
                    
                </article>
            </section>
        </div>
    </div>
</div>
<?php 
get_footer();

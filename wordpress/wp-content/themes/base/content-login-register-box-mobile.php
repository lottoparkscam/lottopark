<?php

if (!defined('WPINC')) {
    die;
}

use Fuel\Tasks\Seeders\Wordpress\FaireumDepositAndWithdrawalInstructionsPage;
use Helpers\UrlHelper;
use Models\Whitelabel;

$add_margin_bottom = '';
$add_margin_bottom_second = '';

$settings_featured = new Lotto_Widget_Featured();
$settings_table = $settings_featured->get_settings();

$whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

$current_feature_type = Lotto_Widget_Featured::TYPE_SMALL;

if (!empty($settings_table)) {
    $current_settings = current($settings_table);

    if (isset($current_settings['type']) && !empty($current_settings['type'])) {
        $current_feature_type = (int) $current_settings['type'];
    }
}

if (is_front_page()) {
    if ((int)$current_feature_type === Lotto_Widget_Featured::TYPE_WITH_BACKGROUND) {
        $add_margin_bottom = 'login-signup-buttons-front-page';
    } else {
        $add_margin_bottom = 'login-signup-buttons-front-page-small';
    }
} else {
    $add_margin_bottom_second = 'login-signup-buttons-nofront-page';
}
?>
<div class="login-signup-buttons-main-div-mobile only-not-logged-user">
    <?php if ((int)$whitelabel['can_user_login_via_site'] === 1): ?>
        <div class="login-signup-buttons-login-div <?= $add_margin_bottom; ?>">
            <div class="login-button-main-div <?= $add_margin_bottom_second; ?>">
                <a href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('login')); ?>" class="btn btn-secondary btn-login btn-login-mobile">
                    <?= _("Login"); ?>
                </a>
            </div>
        </div>
    <?php
    endif;
    if ((int)$whitelabel['can_user_register_via_site'] === 1):
        ?>
        <div class="login-signup-buttons-signup-div <?= $add_margin_bottom; ?>">
            <div class="signup-button-main-div <?= $add_margin_bottom_second; ?>">
                <a href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('signup')); ?>" class="btn btn-secondary btn-signup btn-signup-mobile">
                    <?= _("Sign Up"); ?>
                </a>
            </div>
        </div>
    <?php endif ?>
</div>
<?php
$isFaireumCasino = $whitelabel['theme'] === Whitelabel::FAIREUM_THEME && IS_CASINO;
if ($isFaireumCasino) {
    $deposit_slug = lotto_platform_get_permalink_by_slug(FaireumDepositAndWithdrawalInstructionsPage::SLUG);
    $depositButtonText = _("Deposit & Withdrawal");
} else {
    $deposit_slug = lotto_platform_get_permalink_by_slug('deposit');
    $depositButtonText = _("Deposit");
}

if (!is_front_page()) {
    $add_margin_bottom = 'deposit-button-nofront-page';
} else {
    if ((int)$current_feature_type === Lotto_Widget_Featured::TYPE_WITH_BACKGROUND) {
        $add_margin_bottom_second = 'deposit-button-front-page';
    } else {
        $add_margin_bottom_second = '';
    }
}
?>
<div class="deposit-button-main-div-mobile only-logged-user">
    <div class="deposit-button-middle-div-mobile <?= $add_margin_bottom_second; ?>">
        <div class="deposit-button-div-mobile <?= $add_margin_bottom; ?>">
            <a href="<?= $deposit_slug; ?>"
               class="btn btn-primary btn-deposit-second-mobile">
                <?= Security::htmlentities($depositButtonText); ?>
            </a>
        </div>
    </div>
</div>
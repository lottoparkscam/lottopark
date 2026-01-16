<?php

use Helpers\UrlHelper;

if (!defined('WPINC')) {
    die;
}
?>
<?php
$accountlink = lotto_platform_get_permalink_by_slug('account');
$user = lotto_platform_user();
$isPromoteAccess = isset($user['connected_aff_id']) && $user['connected_aff_id'] > 0;
?>
<div class="content-nav-wrapper mobile-only">
	<select id="myaccount-mobile-nav" class="content-nav">
		<option value="0" selected="selected">
            <?= Security::htmlentities(_("choose")); ?>
        </option>
		<option value="<?= UrlHelper::esc_url($accountlink); ?>"<?php if (empty($section) || in_array($section, array('profile'))): echo ' selected="selected"'; endif; ?>>
            <?= Security::htmlentities(_("My details")); ?>
        </option>
        <?php if (!IS_CASINO): ?>
		<option value="<?= UrlHelper::esc_url($accountlink.'tickets'); ?>"<?php if (in_array($section, array('tickets'))): echo ' selected="selected"'; endif; ?>>
            <?= Security::htmlentities(_("My tickets")); ?>
        </option>
        <?php endif; ?>
		<option value="<?= UrlHelper::esc_url($accountlink.'transactions'); ?>"<?php if (in_array($section, array('transactions'))): echo ' selected="selected"'; endif; ?>>
            <?= Security::htmlentities(_("My transactions")); ?>
        </option>
        <?php if ($isPromoteAccess): ?>
		<option value="<?= UrlHelper::esc_url($accountlink.'promote'); ?>"<?php if (in_array($section, array('promote'))): echo ' selected="selected"'; endif; ?>>
            <?= _('Promote and earn') ?>
        </option>
        <?php endif; ?>
	</select>
</div>
<nav class="content-nav myaccount-nav mobile-hide">
	<ul>
		<li<?php if (empty($section) || in_array($section, array('profile', 'email', 'password'))): echo ' class="content-nav-active"'; endif; ?>>
            <a href="<?= UrlHelper::esc_url($accountlink); ?>">
                <span class="fa fa-solid fa-circle-user" aria-hidden="true"></span> <?= Security::htmlentities(_("My details")); ?>
            </a>
        </li>
        <?php
            if (!IS_CASINO):
        ?>
            <li<?php if (in_array($section, array('tickets'))): echo ' class="content-nav-active"'; endif; ?>>
                <a href="<?= UrlHelper::esc_url($accountlink.'tickets/awaiting'); ?>">
                    <span class="fa fa-ticket" aria-hidden="true"></span> <?= Security::htmlentities(_("My tickets")); ?>
                </a>
            </li>
        <?php
            endif;
        ?>
		<li<?php if (in_array($section, array('transactions'))): echo ' class="content-nav-active"'; endif; ?>>
            <a href="<?= UrlHelper::esc_url($accountlink.'transactions'); ?>">
                <span class="fa fa-money-bill-1" aria-hidden="true"></span> <?= Security::htmlentities(_("My transactions")); ?>
            </a>
        </li>
        <?php if ($isPromoteAccess): ?>
        <li<?= $section === 'promote' ? ' class="content-nav-active"' : '' ?>>
            <a href="<?= UrlHelper::esc_url($accountlink . 'promote'); ?>">
                <span class="fa fa-share-alt" aria-hidden="true"></span> <?= _('Promote and earn') ?>
            </a>
        </li>
        <?php endif; ?>
		<div class="clearfix"></div>
	</ul>
</nav>


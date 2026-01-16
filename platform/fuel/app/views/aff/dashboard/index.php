<?php

use Helpers\UrlHelper;

$default_currency_code = Helpers_Currency::get_default_currency_code();

$general_link_value = 'https://' . $whitelabel['domain'] .
    '/?ref=' . strtoupper($user['token']);
$casinoGeneralLink = UrlHelper::changeAbsoluteUrlToCasinoUrl($general_link_value, true);

$help_text_t = _(
    "You can direct your traffic to any URL you want to use as " .
    "landing page, for example %s."
);
$help_text = sprintf($help_text_t, $link_custom);

$medium_info = _(
    "The advertising or marketing medium, for example: " .
    "cpc, banner, email newsletter."
);

$campaign_info = _("The individual campaign name, slogan, promo code, etc.");

$content_info = _(
    "Used to differentiate similar content, or links within the same ad. " .
    "For example, if you have two call-to-action links within the same email message, " .
    "you can use <em>content</em> and set different values for each so you " .
    "can tell which version is more effective."
);

// include("menu.php");
?>
<div class="row dashboard">
	<div class="col-md-6">
		<div class="panel panel-default">
            <div class="panel-heading">
                <?= _("Your data"); ?>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <strong><?= _("Ref"); ?>:</strong> <?= strtoupper($user['token']); ?><br>
                </div>
                <div class="form-group">
                    <label for="general_link">
                        <?= _("General link"); ?>:
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="general_link" 
                           value="<?= $general_link_value; ?>" 
                           readonly="readonly">
                    
                    <?php if($hasCasino): ?>
                    <br>
                    <label for="casinoGeneralLink">
                        <?= _("Casino general link"); ?>:
                    </label>
                    <input type="text"
                        class="form-control"
                        id="casinoGeneralLink"
                        value="<?= $casinoGeneralLink; ?>"
                        readonly="readonly">
                    <?php endif; ?>
                    <p class="help-block">
                        <?= $help_text; ?>
                    </p>
                </div>
            </div>
		</div>
		<div class="panel panel-default">
            <div class="panel-heading">
                Your Lottery Plan
            </div>
            <div class="panel-body">
                <?php 
                    if (!empty($group_data['commission_value_manager'])):
                ?>
                        <div>
                            <?= $group_data['commission_value_manager']; ?>
                        </div>
                <?php 
                    endif;
                    
                    if (!empty($group_data['commission_value_2_manager'])):
                ?>
                        <div>
                            <?= $group_data['commission_value_2_manager']; ?>
                        </div>
                <?php 
                    endif;
                    
                    if (!empty($group_data['ftp_commission_value_manager'])):
                ?>
                        <div>
                            <?= $group_data['ftp_commission_value_manager']; ?>
                        </div>
                <?php 
                    endif;
                    
                    if (!empty($group_data['ftp_commission_value_2_manager'])):
                ?>
                        <div>
                            <?= $group_data['ftp_commission_value_2_manager']; ?>
                        </div>
                <?php 
                    endif;
                ?>
            </div>
		</div>
        <?php if ($hasCasino): ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                Your Casino Plan
            </div>
            <div class="panel-body">
                <div>1st-tier commission value: <?= $userCasinoGroupCommissions['commission_percentage_value_for_tier_1'] ?? '-' ?>%</div>
                <div>2st-tier commission value: <?= $userCasinoGroupCommissions['commission_percentage_value_for_tier_2'] ?? '-' ?>%</div>
            </div>
        </div>
        <?php endif; ?>
	</div>
	<div class="col-md-6">
		<div class="panel panel-default">
            <div class="panel-heading">
                <?= _("Generate your affiliate link"); ?>
            </div>
            <div class="panel-body">
                <?php 
                    if (isset($link)):
                ?>
                        <div class="form-group">
                            <label for="link">
                                <?= _("Generated link"); ?>:
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="link" 
                                   value="<?= $link; ?>" 
                                   readonly="readonly">
                        </div>
                        <a href="/" class="btn btn-default">
                            <?= _("Back"); ?>
                        </a>
                <?php 
                    else:
                        if (isset($this->errors)) {
                            include(APPPATH . "views/aff/shared/errors.php");
                        }
                ?>
                        <form method="post" action="/">
                            <div class="form-group">
                                <label for="medium">
                                    <?= _("Medium"); ?>:
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="medium" 
                                       name="medium" 
                                       placeholder="<?= _("Enter medium"); ?>">
                                <p class="help-block">
                                    <?= $medium_info; ?>
                                </p>
                            </div>
                            <div class="form-group">
                                <label for="campaign">
                                    <?= _("Campaign"); ?>:
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="campaign" 
                                       name="campaign" 
                                       placeholder="<?= _("Enter campaign"); ?>">
                                <p class="help-block">
                                    <?= $campaign_info; ?>
                                </p>
                            </div>
                            <div class="form-group">
                                <label for="content"><?= _("Content"); ?>:</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="content" 
                                       name="content" 
                                       placeholder="<?= _("Enter content"); ?>">
                                <p class="help-block">
                                    <?= $content_info; ?>
                                </p>
                            </div>
                            <?php if($hasCasino): ?>
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                    <input type="checkbox" id="isCasinoCampaign" value="1" name="isCasinoCampaign">
                                        <?= _("Is casino campaign?"); ?>
                                    </label>
                                </div>
                                <p class="help-block">
                                    <?= _('Determine if campaign is for casino. It will have impact on your reports.'); ?>
                                    <br>
                                    <?= _('Notice: Received link should not be changed.') ?>
                                </p>
                            </div>
                            <?php endif; ?>
                            <button type="submit" 
                                    name="submit" 
                                    value="submit" 
                                    class="btn btn-primary">
                                <?= _("Generate link"); ?>
                            </button>
                        </form>
                <?php 
                    endif;
                ?>
            </div>

		</div>
	</div>
</div>


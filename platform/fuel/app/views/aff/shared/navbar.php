<?php
    $reports_tab = [
        "payouts",
        "reports",
        "ftps",
        "leads",
        "commissions",
        "subaffiliates"
    ];
    
    // todo: move logic into presenter.
    // check if page is on reports, and which mode
    $is_report = in_array($this->action, $reports_tab);
    $is_sub_report = $reports_type === Controller_Aff::SUBAFFILIATE_REPORTS;
    $is_banner_generator = in_array($this->action, ["banners"]);
    $is_widgets_generator = in_array($this->action, ["widgets"]);
?>
<nav class="navbar navbar-default navbar-static-top">
	<div class="container-fluid">
		<div class="navbar-header">
            <button type="button" 
                    class="navbar-toggle collapsed" 
                    data-toggle="collapse" 
                    data-target="#lotto-admin-navbar-collapse" 
                    aria-expanded="false">
                <span class="sr-only"><?= _("Toggle navigation"); ?></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <h1>
                <a class="navbar-brand" href="/">
                    <?= Security::htmlentities($whitelabel['name']); ?> &bull; <?= $user['login']; ?>
                </a>
            </h1>
	    </div>
	    <div class="collapse navbar-collapse" id="lotto-admin-navbar-collapse">
            <ul class="nav navbar-nav navbar-left">
                <li<?php if ($this->action == "index"): echo ' class="active"'; endif; ?>>
                    <a href="/"><?= _("Dashboard"); ?></a>
                </li>
                
                <li<?php if ($is_report && !$is_sub_report) : echo ' class="active"'; endif; ?>>
                    <a href="/payouts"><?= _("Reports"); ?></a>
                </li>
                <li<?php if ($is_report && $is_sub_report) : echo ' class="active"'; endif; ?>>
                    <a href="/subaffiliates"><?= _("Sub-affiliates"); ?></a>
                </li>
                <li<?php if ($is_banner_generator) : echo ' class="active"'; endif; ?>>
                    <a href="/banners"><?= _("Banners"); ?></a>
                </li>
                <li<?php if ($is_widgets_generator) : echo ' class="active"'; endif; ?>>
                      <a href="/widgets"><?= _("Widgets"); ?></a>
                </li>
                <li<?php if (in_array($this->action, ["settings", "payment", "analytics"])): echo ' class="active"'; endif; ?>>
                    <a href="/settings"><?= _("Settings"); ?></a>
                </li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <a href="/signout"><span class="glyphicon glyphicon-log-out glyphicon-icon"></span> <?= _("Sign out"); ?></a>
                </li>
            </ul>
	    </div>
    </div>
</nav>

<?php
    $proper_activation_types = [
        Helpers_General::ACTIVATION_TYPE_OPTIONAL,
        Helpers_General::ACTIVATION_TYPE_REQUIRED
    ];

    if (in_array((int)$this->whitelabel['aff_activation_type'], $proper_activation_types)):
        $full_info_text = "";
        if ((int)$user['is_confirmed'] === 0) {
            $full_info_text = _(
                "Thank you for choosing us! You have been logged in. " .
                "To fully activate your account and get access to all " .
                "functionalities, please confirm your e-mail address " .
                "by following the confirmation link we have sent " .
                "you to your e-mail."
            );
        } elseif (null !== ($message = Session::get_flash("activation_message"))) {
            $full_info_text = $message;
        }
        
        if (!empty($full_info_text)):
?>
            <div class="alert alert-info">
                <?= $full_info_text; ?>
            </div>
<?php
        endif;
    endif;
?>

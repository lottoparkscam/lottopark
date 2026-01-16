<?php
    $menu_settings = [
        ['action' => ["index"], 'url' => '/', 'text' => _("Dashboard")],
        ['action' => ["users"], 'url' => '/users', 'text' => _("Users")],
        ['action' => ["transactions", "deposits", "withdrawals"], 'url' => '/transactions', 'text' => _("Transactions")],
        ['action' => ["tickets"], 'url' => '/tickets', 'text' => _("Tickets")],
        ['action' => ["winners", "reports"], 'url' => '/winners', 'text' => _("Reports")],
        ['action' => ["affs"], 'url' => '/affs', 'text' => _("Affiliates")],
        ['action' => ["bonuses"], 'url' => '/bonuses', 'text' => _("Bonuses")],
        ['action' => ["settings", "mailsettings", "settings_currency", "account", "paymentmethods", "lotterysettings", "ccsettings", "blocked_countries", "analytics", "fbpixel"], 'url' => '/settings', 'text' => _("Settings")],
    ];
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
            <h1><a class="navbar-brand" href="/"><?= Security::htmlentities($whitelabel['name']); ?></a></h1>
	    </div>
	    <div class="collapse navbar-collapse" id="lotto-admin-navbar-collapse">
            <ul class="nav navbar-nav navbar-left">
                <?php
                    foreach ($menu_settings as $setting):
                        $active = '';
                        if (in_array($this->action, $setting['action'])) {
                            $active = ' class="active"';
                        }
                ?>
                        <li role="presentation" <?= $active; ?>>
                            <a href="<?= $setting['url']; ?>">
                                <?= $setting['text']; ?>
                            </a>
                        </li>
                <?php
                    endforeach;
                ?>
            </ul>
            <ul class="nav navbar-nav navbar-right">
            <?php
                if (!Helpers_Whitelabel::is_V1($whitelabel['type'])):
                    $prepaid_list = new Forms_Admin_Whitelabels_Prepaid_List(
                        Helpers_General::SOURCE_WHITELABEL,
                        $whitelabel
                    );
                    $sum = $prepaid_list->get_sum_of_prepaids();
                    list(
                        $sum_prepaid_class,
                        $sum_to_show
                    ) = $prepaid_list->get_sum_formatted_plus_alert_class($sum);
            ?>
                    <li>
                        <a href="/prepaid">
                            <span class="text-<?= $sum_prepaid_class; ?>">
                                <?= _("Prepaid"); ?>: <?= $sum_to_show; ?>
                            </span>
                        </a>
                    </li>
            <?php
                endif;
            ?>
                <li>
                    <a href="https://support.gg.international" target="_blank"><span class="glyphicon glyphicon-question-sign"></span> <?= _("Support"); ?></a>
                </li>
                <li>
                    <a href="/signout"><span class="glyphicon glyphicon-log-out glyphicon-icon"></span> <?= _("Sign out"); ?></a>
                </li>
            </ul>
	    </div>
    </div>
</nav>
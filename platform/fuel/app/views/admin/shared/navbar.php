<?php
    $menu_settings = [
        ['action' => ["index"], 'url' => '/', 'text' => _("Dashboard")],
        ['action' => ["users"], 'url' => '/users', 'text' => _("Users")],
        ['action' => ["transactions", "deposits", "withdrawals"], 'url' => '/transactions', 'text' => _("Transactions")],
        ['action' => ["tickets"], 'url' => '/tickets', 'text' => _("Tickets")],
        ['action' => ["lotteries", "logs", "imvalaplogs", "lottorisqlogs", "delays"], 'url' => '/lotteries', 'text' => _("Lotteries")],
        ['action' => ["whitelabels"], 'url' => '/whitelabels', 'text' => _("Whitelabels")],
        ['action' => ["paymentlogs"], 'url' => '/paymentlogs', 'text' => _("Payments")],
        ['action' => ["reports"], 'url' => '/reports', 'text' => _("Reports")],
        ['action' => ["marketing-tools"], 'url' => '/marketing-tools', 'text' => 'Marketing Tools'],
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
            <h1><a class="navbar-brand" href="/"><?= _("Lotto network"); ?></a></h1>
	    </div>
	    <div class="collapse navbar-collapse" id="lotto-admin-navbar-collapse">
            <ul class="nav navbar-nav navbar-left">
                <?php 
                    include(APPPATH . "views/admin/shared/menu_items_builder.php");
                ?>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <a href="/signout">
                        <span class="glyphicon glyphicon-log-out glyphicon-icon"></span> <?= _("Sign out"); ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
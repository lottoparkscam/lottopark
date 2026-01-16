<ul class="nav nav-pills nav-stacked">
	<li role="presentation"<?php if ($action == "transactions" && $type == "transactions"): echo ' class="active"'; endif; ?>>
        <a href="/transactions">
            <?php echo _("Purchases"); ?> <span class="badge"><?php echo $pcount; ?></span>
        </a>
    </li>
	<li role="presentation"<?php if ($action == "transactions" && $type == "deposits"): echo ' class="active"'; endif; ?>>
        <a href="/deposits">
            <?php echo _("Deposits"); ?> <span class="badge"><?php echo $dcount; ?></span>
        </a>
    </li>
	<li role="presentation"<?php if ($action == "withdrawals"): echo ' class="active"'; endif; ?>>
        <a href="/withdrawals">
            <?php echo _("Withdrawals"); ?> <span class="badge"><?php echo $wcount; ?></span>
        </a>
    </li>
</ul>
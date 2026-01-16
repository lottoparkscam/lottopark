<ul class="nav nav-pills nav-stacked">
	<li role="presentation"<?php if ($action == "transactions" && $type == "transactions"): echo ' class="active"'; endif; ?>>
        <a href="/transactions">
            <?= _("Purchases"); ?> <span class="badge"><?= $pcount; ?></span>
        </a>
    </li>
	<li role="presentation"<?php if ($action == "transactions" && $type == "deposits"): echo ' class="active"'; endif; ?>>
        <a href="/deposits">
            <?= _("Deposits"); ?> <span class="badge"><?= $dcount; ?></span>
        </a>
    </li>
	<li role="presentation"<?php if ($action == "withdrawals"): echo ' class="active"'; endif; ?>>
        <a href="/withdrawals">
            <?= _("Withdrawals"); ?> <span class="badge"><?= $wcount; ?></span>
        </a>
    </li>
</ul>
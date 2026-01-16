<?php

use Helpers\AffMenuHelper;

?>
<ul class="nav nav-pills nav-stacked">
    <li role="presentation"<?php if ($action == "affs" && in_array($rparam, [null, "list"])) :
        echo ' class="active"';
                           endif; ?>>
        <a href="/affs">
            <?php echo _("Active & Accepted"); ?> <span class="badge"><?php echo $active_cnt; ?></span>
        </a>
    </li>
    <li role="presentation"<?php if ($action == "affs" && $rparam == "notaccepted") :
        echo ' class="active"';
                           endif; ?>>
        <a href="/affs/notaccepted">
            <?php echo _("Not accepted"); ?> <span class="badge"><?php echo $notaccepted_cnt; ?></span>
        </a>
    </li>
    <li role="presentation"<?php if ($action == "affs" && $rparam == "inactive") :
        echo ' class="active"';
                           endif; ?>>
        <a href="/affs/inactive">
            <?php echo _("Inactive"); ?> <span class="badge"><?php echo $inactive_cnt; ?></span>
        </a>
    </li>
    <li role="presentation"<?php if ($action == "affs" && $rparam == "deleted") :
        echo ' class="active"';
                           endif; ?>>
        <a href="/affs/deleted">
            <?php echo _("Deleted"); ?> <span class="badge"><?php echo $deleted_cnt; ?></span>
        </a>
    </li>
</ul>
<hr>
<ul class="nav nav-pills nav-stacked">
    <li role="presentation"<?php if ($action == "affs" && $rparam == "payouts") :
        echo ' class="active"';
                           endif; ?>>
        <a href="/affs/payouts">
            <?php echo _("Payouts"); ?>
        </a>
    </li>
    <li role="presentation"<?php if ($action == "affs" && $rparam == "reports") :
        echo ' class="active"';
                           endif; ?>>
        <a href="/affs/reports"><?php echo _("Reports"); ?></a>
    </li>
    <li role="presentation"<?php if ($action == "affs" && $rparam == "leads") :
        echo ' class="active"';
                           endif; ?>>
        <a href="/affs/leads">
            <?php echo _("Leads"); ?>
        </a>
    </li>
    <li role="presentation"<?php if ($action == "affs" && $rparam == "ftps") :
        echo ' class="active"';
                           endif; ?>>
        <a href="/affs/ftps">
            <?php echo _("First-Time Purchases"); ?>
        </a>
    </li>
    <li role="presentation"<?php if ($action == "affs" && $rparam == "commissions") :
        echo ' class="active"';
                           endif; ?>>
        <a href="/affs/commissions">
            <?php echo _("Commissions"); ?>
        </a>
    </li>
    <li role="presentation"<?php if ($action == "affs" && $rparam == "casinoCommissions") :
        echo ' class="active"';
                           endif; ?>>
        <a href="/affs/casinoCommissions">
            <?php echo _("Casino Commissions"); ?>
        </a>
    </li>
</ul>
<hr>
<ul class="nav nav-pills nav-stacked">
    <li role="presentation" <?= ($action == 'affs' && $rparam == 'banners') ? 'class="active"' : '' ?>>
        <a href="/affs/banners">
            Banners
        </a>
    </li>
    <li role="presentation" <?= ($action == 'affs' && $rparam == 'widgets') ? 'class="active"' : '' ?>>
        <a href="/affs/widgets">
            Widgets
        </a>
    </li>
</ul>
<hr>
<ul class="nav nav-pills nav-stacked">
    <li role="presentation" <?= AffMenuHelper::isActiveTab($action, $rparam, 'lottery-groups') ? 'class="active"' : ''
    ?>>
        <a href="/affs/lottery-groups">
            Affiliate lottery groups
        </a>
    </li>
    <li role="presentation" <?= AffMenuHelper::isActiveTab($action, $rparam, 'casino-groups') ? 'class="active"' : '' ?>>
        <a href="/affs/casino-groups">
            Affiliate casino groups
        </a>
    </li>
    <li role="presentation" <?= ($action == 'affs' && $rparam == 'settings') ? 'class="active"' : '' ?>>
        <a href="/affs/settings">
            Settings
        </a>
    </li>
</ul>
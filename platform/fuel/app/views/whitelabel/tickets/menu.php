<ul class="nav nav-pills nav-stacked">
    <li role="presentation"<?php if ($action == "tickets"): echo ' class="active"'; endif; ?>>
        <a href="/tickets"><?php echo _("Tickets"); ?> <span class="badge"><?php echo $tcount; ?></span></a>
    </li>
    <li role="presentation"<?php if ($action == "multidraw_tickets"): echo ' class="active"'; endif; ?>>
        <a href="/multidraw_tickets"><?php echo _("Multi-draw Tickets"); ?> <span class="badge"><?php echo $mtcount; ?></span></a>
    </li>
</ul>
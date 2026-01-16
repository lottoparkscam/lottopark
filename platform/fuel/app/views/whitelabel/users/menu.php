<ul class="nav nav-pills nav-stacked">
	<li role="presentation"<?php if ($action == "users" && $rparam == "users"): echo ' class="active"'; endif; ?>>
        <a href="/users"><?php echo _("Active users"); ?> <span class="badge"><?php echo $active_cnt; ?></span></a>
    </li>
	<li role="presentation"<?php if ($action == "users" && $rparam == "inactive"): echo ' class="active"'; endif; ?>>
        <a href="/inactive"><?php echo _("Inactive users"); ?> <span class="badge"><?php echo $inactive_cnt; ?></span></a>
    </li>
	<li role="presentation"<?php if ($action == "users" && $rparam == "deleted"): echo ' class="active"'; endif; ?>>
        <a href="/deleted"><?php echo _("Deleted users"); ?> <span class="badge"><?php echo $deleted_cnt; ?></span></a>
    </li>

</ul>
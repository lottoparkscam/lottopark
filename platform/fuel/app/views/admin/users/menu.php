<ul class="nav nav-pills nav-stacked">
	<li role="presentation"<?php if ($action == "users" && $rparam == "users"): echo ' class="active"'; endif; ?>>
        <a href="/users">
            <?= _("Active users"); ?> <span class="badge"><?= $active_cnt; ?></span>
        </a>
    </li>
	<li role="presentation"<?php if ($action == "users" && $rparam == "inactive"): echo ' class="active"'; endif; ?>>
        <a href="/inactive">
            <?= _("Inactive users"); ?> <span class="badge"><?= $inactive_cnt; ?></span>
        </a>
    </li>
	<li role="presentation"<?php if ($action == "users" && $rparam == "deleted"): echo ' class="active"'; endif; ?>>
        <a href="/deleted">
            <?= _("Deleted users"); ?> <span class="badge"><?= $deleted_cnt; ?></span>
        </a>
    </li>

</ul>
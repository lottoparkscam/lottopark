<ul class="nav nav-pills nav-stacked">
	<li role="presentation"<?php if ($action == "settings"): echo ' class="active"'; endif; ?>>
        <a href="/settings">
            <?= _("Edit profile"); ?>
        </a>
    </li>
	<li role="presentation"<?php if ($action == "payment"): echo ' class="active"'; endif; ?>>
        <a href="/payment">
            <?= _("Payment settings"); ?>
        </a>
    </li>
    <?php if(!empty($whitelabel['analytics'])): ?>
	<li role="presentation"<?php if ($action == "analytics"): echo ' class="active"'; endif; ?>>
        <a href="/analytics">
            <?= _("Google Analytics"); ?>
        </a>
    </li>
    <?php endif; ?>
    <?php if(!empty($whitelabel['fb_pixel'])): ?>
	<li role="presentation"<?php if ($action == "fbpixel"): echo ' class="active"'; endif; ?>>
        <a href="/fbpixel">
            <?= _("Facebook Pixel"); ?>
        </a>
    </li>
    <?php endif; ?>
</ul>
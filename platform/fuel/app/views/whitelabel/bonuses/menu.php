<ul class="nav nav-pills nav-stacked">
    <li role="presentation"<?php if ($action == "bonuses" && ($rparam == "welcome" || empty($rparam))): echo ' class="active"'; endif; ?>>
        <a href="/bonuses/welcome"><?php echo _("Welcome bonus"); ?></a>
    </li>
    <li role="presentation"<?php if ($action == "bonuses" && $rparam == "referafriend"): echo ' class="active"'; endif; ?>>
        <a href="/bonuses/referafriend"><?php echo _("Refer a friend"); ?></a>
    </li>
    <li role="presentation"<?php if ($action == "bonuses" && $rparam == "promocodes"): echo ' class="active"'; endif; ?>>
        <a href="/bonuses/promocodes">Promo Codes</a>
    </li>
    <li role="presentation"<?php if ($action == "bonuses" && $rparam == "freespins"): echo ' class="active"'; endif; ?>>
        <a href="/bonuses/freespins">Mini Games - Promo Codes</a>
    </li>
</ul>
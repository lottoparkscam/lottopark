<?php
    $menu_settings_pre = [
        ['action' => ["settings"], 'url' => '/settings', 'text' => _("Site settings")],
        ['action' => ["mailsettings"], 'url' => '/mailsettings', 'text' => _("Mail settings")],
        ['action' => ["multidrawsettings"], 'url' => '/multidrawsettings', 'text' => _("Mutli-draw settings")],
        ['action' => ["settings_currency", "settings_country_currency"], 'url' => '/settings_currency', 'text' => _("Currency settings")],
        ['action' => ["lotterysettings"], 'url' => '/lotterysettings', 'text' => _("Lottery settings")],
    ];
    
    $menu_part_special = [
        ['action' => ["paymentmethods", "ccsettings"], 'url' => '/paymentmethods', 'text' => _("Payment methods")],
    ];
    
    $menu_part_post = [
        ['action' => ["account"], 'url' => '/account', 'text' => _("Account settings")],
        ['action' => ["blocked_countries"], 'url' => '/blocked_countries', 'text' => _("Blocked countries"), 'add_hr' => true],
        ['action' => ["analytics"], 'url' => '/analytics', 'text' => _("GTM")],
        ['action' => ["fbpixel"], 'url' => '/fbpixel', 'text' => _("Facebook Pixel")],
    ];
    
    $menu_settings = array_merge($menu_settings_pre, $menu_part_special, $menu_part_post);
?>
<ul class="nav nav-pills nav-stacked">
    <?php
        include(APPPATH . "views/whitelabel/shared/menu_items_builder.php");
    ?>
</ul>
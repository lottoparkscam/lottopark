<?php 
    $menu_settings = [
        ['action' => ["winners"], 'url' => '/winners', 'text' => _("Winners")],
        ['action' => ["reports"], 'url' => '/reports', 'text' => _("Generate report")],
    ];
?>
<ul class="nav nav-pills nav-stacked">
    <?php 
        include(APPPATH . "views/whitelabel/shared/menu_items_builder.php");
    ?>
</ul>
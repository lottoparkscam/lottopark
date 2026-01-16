<?php
    $menu_settings = [
        ['action' => ["reports"], 'url' => '/reports', 'text' => _("Generate report")],
    ];
?>
<ul class="nav nav-pills nav-stacked">
    <?php 
        include(APPPATH . "views/admin/shared/menu_items_builder.php");
    ?>
</ul>
<?php
    $menu_settings = [
        ['action' => ["whitelabels"], 'url' => '/whitelabels', 'text' => _("Whitelabels")],
    ];
?>
<ul class="nav nav-pills nav-stacked">
    <?php 
        include(APPPATH . "views/admin/shared/menu_items_builder.php");
    ?>
</ul>
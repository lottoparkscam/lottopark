<?php
    $menu_settings = [
        ['action' => ["tickets"], 'url' => '/tickets', 'text' => _("Tickets"), 'badge' => $tcount],
        ['action' => ["multidraw_tickets"], 'url' => '/multidraw_tickets', 'text' => _("Multi-draw Tickets")],
    ];
?>
<ul class="nav nav-pills nav-stacked">
    <?php
        include(APPPATH . "views/admin/shared/menu_items_builder.php");
    ?>
</ul>
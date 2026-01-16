<?php
    $menu_settings = [
        ['action' => ["paymentlogs"], 'url' => '/paymentlogs', 'text' => _("Payment Logs")],
    ];
?>
<ul class="nav nav-pills nav-stacked">
    <?php 
        include(APPPATH . "views/admin/shared/menu_items_builder.php");
    ?>
</ul>
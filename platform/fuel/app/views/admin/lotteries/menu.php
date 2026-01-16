<?php 
    $menu_settings = [
        ['action' => ["lotteries"], 'url' => '/lotteries', 'text' => _("Lottery List")],
        ['action' => ["delays"], 'url' => '/lotteries/delays', 'text' => _("Postponed Draws")],
        ['action' => ["logs"], 'url' => '/lotteries/logs', 'text' => _("Lottery Logs")],
        ['action' => ["imvalaplogs"], 'url' => '/lotteries/imvalaplogs', 'text' => _("Imvalap Logs")],
        ['action' => ["lottorisqlogs"], 'url' => '/lotteries/lottorisqlogs', 'text' => _("Lottorisq Logs")],
    ];
?>
<ul class="nav nav-pills nav-stacked">
    <?php 
        include(APPPATH . "views/admin/shared/menu_items_builder.php");
    ?>
</ul>
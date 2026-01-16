<?php
include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php
            include(APPPATH . "views/whitelabel/bonuses/menu.php");
        ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Promo Codes"); ?>
        </h2>
        
        <p class="help-block">
            <?= _("You can view the promo codes of your campaign here."); ?>
        </p>

        <a href="/bonuses/promocodes<?= Lotto_View::query_vars(); ?>" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>

        <div class="container-fluid container-admin row">
            <div class="col-md-10 user-details">
                <?php foreach ($codes as $code): ?>
                        <span>
                            <?= $code['prefix'] . $code['token']; ?>
                        </span>
                        <br>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php
if(!empty($warnings)):
    foreach($warnings AS $warning):
?>
    <div class="platform-alert platform-alert-info widget-ticket-alert">
    <p><span class="fa fa-exclamation-circle"></span>
        <?= $warning; ?>
    </p>
    </div>
<?php
    endforeach;
endif;
?>
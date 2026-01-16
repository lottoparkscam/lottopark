<?php
if (isset($adata['result'])):
?>
    <h3>
        <?= _("BitBayPay"); ?>
    </h3>
<?php
    foreach ($adata['result'] as $key => $field):
?>
        <span class="details-label">
            <?= Security::htmlentities($key); ?>:
        </span>
        <span class="details-value">
            <?= Security::htmlentities($field); ?>
        </span>
        <br>
<?php
    endforeach;
endif;

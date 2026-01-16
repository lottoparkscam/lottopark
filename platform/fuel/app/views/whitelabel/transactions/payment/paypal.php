<?php
if (isset($adata['result'])):
?>
    <h3>
        <?= _("PayPal"); ?>
    </h3>
<?php
    foreach ($adata['result'] as $key => $field):
        if (!isset($field[0])) {
            continue;
        }
        
        $key_name_t = str_replace('_', ' ', $key);
        $key_name = ucfirst($key_name_t);
?>
        <span class="details-label" title="<?= Security::htmlentities($key); ?>">
            <?= Security::htmlentities($key_name); ?>:
        </span>
        <span class="details-value">
            <?= Security::htmlentities($field); ?>
        </span>
        <br>
<?php
    endforeach;
endif;

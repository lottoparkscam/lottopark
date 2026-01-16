<?php
if (isset($adata['result']) && is_array($adata['result'])):
?>
    <h3>
        <?=Helpers_Payment_Method::ASTRO_PAY_NAME?>
    </h3>
    <?php
    // TODO: {Vordis 2019-05-29 16:57:08} shared with paypal should be exported into presenter parent (closure).
    foreach ($adata['result'] as $key => $value):
        $label = ucfirst(str_replace('_', ' ', $key));
    ?>
        <span class="details-label" title="<?=Security::htmlentities($key);?>">
            <?=Security::htmlentities($label);?>:
        </span>
        <span class="details-value">
            <?=Security::htmlentities($value);?>
        </span>
        <br>
<?php
    endforeach;
endif;

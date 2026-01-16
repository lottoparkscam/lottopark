<?php
if (isset($adata['result'])):
?>
    <h3>
        <?= _("AsiaPaymentGateway"); ?>
    </h3>
<?php
    if (isset($adata['result']['Result'])) {
        $adata['result']['Result'] = urldecode($adata['result']['Result']);
    }
    
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

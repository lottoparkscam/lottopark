<h3>
    <?= _("tpay.com"); ?>
</h3>
<?php 
    if (isset($adata['id'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("tpay.com ID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['id']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['tr_date'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Date")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['tr_date']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['tr_amount'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Amount")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['tr_amount']);?></span><br>
<?php 
    endif;
    
    if (isset($adata['tr_paid'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Amount paid")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['tr_paid']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['tr_email'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("E-mail")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['tr_email']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['tr_desc'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Description")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['tr_desc']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['tr_error'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Error")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['tr_error']); ?></span><br>
<?php 
    endif;

<h3>
    <?= _("AstroPay Card"); ?>
</h3>
<?php
    if (isset($adata['response_code'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Response Code")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['response_code']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['response_subcode'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Response Subcode")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['response_subcode']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['response_reason_code'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Response Reason Code")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['response_reason_code']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['response_reason_text'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Response Reason Text")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['response_reason_text']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['approval_code'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Approval Code")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['approval_code']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['AVS'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("AVS")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['AVS']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['TransactionID'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("TransactionID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['TransactionID']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['r_unique_id'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("r_unique_id")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['r_unique_id']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_invoice_num'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_invoice_num")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_invoice_num']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_description'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_description")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_description']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_amount'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_amount")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_amount']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_method'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_method")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_method']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_type'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_type")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_type']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_cust_id'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_cust_id")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_cust_id']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_first_name'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_first_name")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_first_name']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_last_name'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_last_name")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_last_name']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_company'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_company")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_company']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_address'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_address")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_address']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_city'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_city")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_city']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_state'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_state")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_state']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_zip'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_zip")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_zip']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_country'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_country")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_country']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_phone'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_phone")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_phone']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_fax'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_fax")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_fax']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_email'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_email")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_email']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_ship_to_first_name'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_ship_to_first_name")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_ship_to_first_name']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_ship_to_last_name'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_ship_to_last_name")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_ship_to_last_name']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_ship_to_company'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_ship_to_company")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_ship_to_company']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_ship_to_address'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_ship_to_address")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_ship_to_address']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_ship_to_city'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_ship_to_city")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_ship_to_city']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_ship_to_state'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_ship_to_state")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_ship_to_state']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_ship_to_zip'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_ship_to_zip")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_ship_to_zip']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_ship_to_country'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_ship_to_country")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_ship_to_country']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_tax'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_tax")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_tax']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_duty'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_duty")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_duty']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_freight'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_freight")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_freight']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_tax_exempt'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_tax_exempt")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_tax_exempt']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_po_num'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_po_num")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_po_num']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['x_test_request'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("x_test_request")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['x_test_request']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['md5_hash'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("md5_hash")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['md5_hash']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['cc_response'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("cc_response")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['cc_response']); ?></span><br>
<?php
    endif;

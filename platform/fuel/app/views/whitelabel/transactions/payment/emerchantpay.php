<h3>
    <?= _("eMerchantPay"); ?>
</h3>
<?php 
    if (isset($adata['order_datetime'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Order date")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['order_datetime']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['order_total'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Order total")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['order_total']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['order_currency'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Order currency")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['order_currency']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['order_status'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Order status")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['order_status']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['test_transaction'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Test transaction")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['test_transaction']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['transaction_type'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Transaction type")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['transaction_type']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['transaction_response'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Response")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['transaction_response']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['transaction_response_code'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Response code")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['transaction_response_code']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['transaction_response_text'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Response text")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['transaction_response_text']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['transaction_id'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Transaction ID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['transaction_id']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['account_id'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Account ID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['account_id']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['client_id'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Client ID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['client_id']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['domain'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Domain")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['domain']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['merchant_user_id'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Merchant user ID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['merchant_user_id']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['notification_type'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Notification type")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['notification_type']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['pass_through'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("emerchantpaypassthrough")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['pass_through']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['payment_method'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Payment method")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['payment_method']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['errors'])):
?>
        <h4>
            <?= _("Errors"); ?>
        </h4>
        <?php 
            foreach ($adata['errors'] as $key => $error):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Error code")); ?> [<?= $key + 1; ?>]:</span>
                <span class="details-value"><?= Security::htmlentities($error[0]); ?></span><br>
                <span class="details-label"><?= Security::htmlentities(_("Error text")); ?> [<?= $key + 1; ?>]:</span>
                <span class="details-value"><?= Security::htmlentities($error[1]); ?></span><br>
<?php 
            endforeach;
    endif;
    
    if (isset($adata['card_brand'])):
?>
        <h4>
            <?= _("Card details"); ?>
        </h4>
<?php 
    endif;
    
    if (isset($adata['auth_code'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Auth code")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['auth_code']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['card_brand'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Brand")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['card_brand']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['card_category'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Category")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['card_category']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['card_exp_month'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Exp. month")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['card_exp_month']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['card_exp_year'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Exp. year")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['card_exp_year']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['card_holder_name'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Holder name")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['card_holder_name']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['card_issuing_bank'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Issuing bank")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['card_issuing_bank']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['card_issuing_country'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Issuing country")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['card_issuing_country']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['card_number'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Number")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['card_number']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['card_sub_category'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Subcategory")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['card_sub_category']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer_email'])):
?>
        <h4>
            <?= _("Customer data"); ?>
        </h4>
<?php 
    endif;
    
    if (isset($adata['customer_email'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("E-mail")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer_email']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer_id'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Customer ID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer_id']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer_first_name'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("First name")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer_first_name']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer_last_name'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Last name")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer_last_name']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer_company'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Company")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer_company']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer_country'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Country")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer_country']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer_state'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("State")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer_state']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer_city'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("City")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer_city']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer_postcode'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Postcode")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer_postcode']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer_address'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Address")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer_address']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer_address2'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Address #2")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer_address2']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['cart']) && count($adata['cart']) > 0):
?>
        <h4>
            <?= _("Items"); ?>
        </h4>
<?php 
    endif;
    
    if (isset($adata['cart'])):
        foreach ($adata['cart'] as $key => $citem):
            if (isset($citem['name'])):
?>
                <span class="details-label"><?= Security::htmlentities(_("Name")); ?> [<?= $key + 1; ?>]:</span>
                <span class="details-value"><?= Security::htmlentities($citem['name']); ?></span><br>
        <?php 
            endif;
            
            if (isset($citem['description'])):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Description")); ?> [<?= $key + 1; ?>]:</span>
                <span class="details-value"><?= Security::htmlentities($citem['description']); ?></span><br>
        <?php 
            endif;
            
            if (isset($citem['id'])):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Item ID")); ?> [<?= $key + 1; ?>]:</span>
                <span class="details-value"><?= Security::htmlentities($citem['id']); ?></span><br>
        <?php 
            endif;
            
            if (isset($citem['code'])):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Code")); ?> [<?= $key + 1; ?>]:</span>
                <span class="details-value"><?= Security::htmlentities($citem['code']); ?></span><br>
        <?php 
            endif;
            
            if (isset($citem['qty'])):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Quantity")); ?> [<?= $key + 1; ?>]:</span>
                <span class="details-value"><?= Security::htmlentities($citem['qty']); ?></span><br>
        <?php 
            endif;
            
            if (isset($citem['digital'])):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Digital")); ?> [<?= $key + 1; ?>]:</span>
                <span class="details-value"><?= Security::htmlentities($citem['digital']); ?></span><br>
        <?php 
            endif;
            
            if (isset($citem['pass_through'])):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Pass-through")); ?> [<?= $key + 1; ?>]:</span>
                <span class="details-value"><?= Security::htmlentities($citem['pass_through']); ?></span><br>
        <?php 
            endif;
            
            if (isset($citem['rebill'])):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Rebill")); ?> [<?= $key + 1; ?>]:</span>
                <span class="details-value"><?= Security::htmlentities($citem['rebill']); ?></span><br>
        <?php 
            endif;
            
            if (isset($citem['discount'])):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Discount")); ?> [<?= $key + 1; ?>]:</span>
                <span class="details-value"><?= Security::htmlentities($citem['discount']); ?></span><br>
        <?php 
            endif;
            
            if (isset($citem['predefined'])):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Predefined")); ?> [<?= $key + 1; ?>]:</span>
                <span class="details-value"><?= Security::htmlentities($citem['predefined']); ?></span><br>
        <?php 
            endif;
            
            if (isset($citem['unit_prices'])):
                foreach ($citem['unit_prices'] as $unitprice):
        ?>
                    <span class="details-label"><?= Security::htmlentities(_("Unit price")); ?> <?= $unitprice[0]; ?> [<?= $key + 1; ?>]:</span>
                    <span class="details-value"><?= Security::htmlentities($unitprice[1]); ?></span><br>
<?php 
                endforeach;
            endif;
        endforeach;
    endif;
